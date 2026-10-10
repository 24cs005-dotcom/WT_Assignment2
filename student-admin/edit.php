
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

$error = "";
$courses = [
    "CSE", "CSE (AI & ML)", "IT",
    "ECE", "EEE", "MECH"
];

function e($value) {
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        "UTF-8"
    );
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $roll = trim($_POST["roll_number"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $course = trim($_POST["course"] ?? "");

    $student["name"] = $name;
    $student["roll_number"] = $roll;
    $student["email"] = $email;
    $student["course"] = $course;

    if ($name === "" || $roll === "" ||
        $email === "" || $course === "") {
        $error = "All fields are required.";
    } elseif (strlen($name) > 100 ||
              strlen($roll) > 30 ||
              strlen($email) > 150) {
        $error = "One or more fields exceed the allowed length.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Enter a valid email address.";
    } elseif (!in_array($course, $courses, true)) {
        $error = "Select a valid course.";
    } else {
        try {
            $stmt = $conn->prepare(
                "UPDATE students
                 SET name = ?, roll_number = ?,
                     email = ?, course = ?
                 WHERE id = ?"
            );

            $stmt->bind_param(
                "ssssi", $name, $roll, $email, $course, $id
            );
            $stmt->execute();

            header(
                "Location: view.php?id=" . $id .
                "&msg=" . urlencode("Student updated successfully.")
            );
            exit;

        } catch (mysqli_sql_exception $ex) {
            if ($ex->getCode() === 1062) {
                $error = "That roll number is already used.";
            } else {
                error_log($ex->getMessage());
                $error = "Unable to update student.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Student | Campus Admin</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="navbar">
    <h2>Campus Admin</h2>
    <a href="index.php">Back to Dashboard</a>
</header>

<main class="container">
    <section class="card">
        <h1>Edit Student Details</h1>

        <?php if ($error !== ""): ?>
            <div class="error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <label for="name">Student Name</label>
            <input id="name" name="name" maxlength="100"
                   value="<?= e($student["name"]) ?>" required>

            <label for="roll_number">Roll Number</label>
            <input id="roll_number" name="roll_number" maxlength="30"
                   value="<?= e($student["roll_number"]) ?>" required>

            <label for="email">Email Address</label>
            <input id="email" name="email" type="email"
                   maxlength="150" value="<?= e($student["email"]) ?>" required>

            <label for="course">Course</label>
            <select id="course" name="course" required>
                <option value="">Select course</option>
                <?php foreach ($courses as $c): ?>
                    <option value="<?= e($c) ?>"
                        <?= $student["course"] === $c ? "selected" : "" ?>>
                        <?= e($c) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <br><br>
            <button type="submit">Update Student</button>
            <a class="btn btn-gray" href="view.php?id=<?= $id ?>">
                Cancel
            </a>
        </form>
    </section>
</main>
</body>
</html>
