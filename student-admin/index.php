
<?php
require "db.php";

$result = $conn->query(
    "SELECT s.*,
        (SELECT COUNT(*) FROM student_files f
         WHERE f.student_id = s.id) AS file_count
     FROM students s
     ORDER BY s.id DESC"
);

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
    <title>Campus Admin | Dashboard</title>
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
    <section class="card">
        <h1>Student Management</h1>
        <p class="muted">
            Manage student details, profile images and documents.
        </p>

        <?php if (isset($_GET["msg"])): ?>
            <div class="message"><?= e($_GET["msg"]) ?></div>
        <?php endif; ?>

        <a href="add.php" class="btn btn-green">
            + Add New Student
        </a>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Roll Number</th>
                        <th>Email</th>
                        <th>Course</th>
                        <th>Files</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($result->num_rows === 0): ?>
                    <tr>
                        <td colspan="7">
                            No student records found. Add your first student.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= (int)$row["id"] ?></td>
                            <td><?= e($row["name"]) ?></td>
                            <td><?= e($row["roll_number"]) ?></td>
                            <td><?= e($row["email"]) ?></td>
                            <td><?= e($row["course"]) ?></td>
                            <td><?= (int)$row["file_count"] ?></td>
                            <td>
                                <div class="actions">
                                    <a class="btn"
                                       href="view.php?id=<?= (int)$row["id"] ?>">
                                        View
                                    </a>

                                    <a class="btn btn-green"
                                       href="edit.php?id=<?= (int)$row["id"] ?>">
                                        Edit
                                    </a>

                                    <form method="post" action="delete.php"
                                          onsubmit="return confirm('Delete this student and all associated files?');">
                                        <input type="hidden" name="id"
                                               value="<?= (int)$row["id"] ?>">
                                        <button type="submit" class="btn-red">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
</body>
</html>
