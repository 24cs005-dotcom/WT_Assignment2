
<?php
require "db.php";

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    http_response_code(400);
    exit("Invalid student ID.");
}

$stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

if (!$student) {
    http_response_code(404);
    exit("Student not found.");
}

$stmt = $conn->prepare(
    "SELECT * FROM student_files
     WHERE student_id = ?
     ORDER BY id DESC"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$files = $stmt->get_result();

function e($value) {
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        "UTF-8"
    );
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Profile | Campus Admin</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="navbar">
    <h2>Campus Admin</h2>
    <nav>
        <a href="index.php">Dashboard</a>
        <a href="add.php">Add Student</a>
    </nav>
</header>

<main class="container">

    <?php if (isset($_GET["msg"])): ?>
        <div class="message"><?= e($_GET["msg"]) ?></div>
    <?php endif; ?>

    <section class="card">
        <h1>Student Profile</h1>

        <p><strong>Name:</strong> <?= e($student["name"]) ?></p>
        <p><strong>Roll Number:</strong> <?= e($student["roll_number"]) ?></p>
        <p><strong>Email:</strong> <?= e($student["email"]) ?></p>
        <p><strong>Course:</strong> <?= e($student["course"]) ?></p>
        <p><strong>Registered:</strong> <?= e($student["created_at"]) ?></p>

        <a class="btn btn-green" href="edit.php?id=<?= $id ?>">
            Edit Details
        </a>
        <a class="btn btn-gray" href="index.php">Back to Dashboard</a>
    </section>

    <section class="card">
        <h2>Upload Profile Image or Document</h2>
        <p class="muted">
            Allowed formats: JPG, JPEG, PNG and PDF. Maximum size: 5 MB.
        </p>

        <form action="upload.php" method="post"
              enctype="multipart/form-data">

            <input type="hidden" name="student_id" value="<?= $id ?>">

            <label for="document">Choose File</label>
            <input id="document" type="file" name="document"
                   accept=".jpg,.jpeg,.png,.pdf" required>

            <br><br>
            <button type="submit">Upload File</button>
        </form>
    </section>

    <section class="card">
        <h2>Uploaded Files</h2>

        <?php if ($files->num_rows === 0): ?>
            <p>No files uploaded yet.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>File Name</th>
                            <th>Type</th>
                            <th>Size</th>
                            <th>Upload Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($file = $files->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <?= e($file["original_name"]) ?>

                                <?php if (
                                    in_array(
                                        $file["file_type"],
                                        ["image/jpeg", "image/png"],
                                        true
                                    )
                                ): ?>
                                    <img
                                        class="file-preview"
                                        src="download.php?id=<?= (int)$file["id"] ?>&inline=1"
                                        alt="Student uploaded image">
                                <?php endif; ?>
                            </td>

                            <td><?= e($file["file_type"]) ?></td>

                            <td>
                                <?= number_format(
                                    $file["file_size"] / 1024, 1
                                ) ?> KB
                            </td>

                            <td><?= e($file["upload_date"]) ?></td>

                            <td>
                                <a class="btn"
                                   href="download.php?id=<?= (int)$file["id"] ?>">
                                    Open / Download
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
