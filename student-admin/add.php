
<?php
require "db.php";

$error = "";
$name = "";
$roll = "";
$email = "";
$course = "";

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
                "INSERT INTO students
                 (name, roll_number, email, course)
                 VALUES (?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "ssss", $name, $roll, $email, $course
            );
            $stmt->execute();

            $id = $conn->insert_id;

            header(
                "Location: view.php?id=" . $id .
                "&msg=" . urlencode("Student added successfully.")
            );
            exit;

        } catch (mysqli_sql_exception $ex) {
            if ($ex->getCode() === 1062) {
                $error = "This roll number already exists.";
            } else {
                error_log($ex->getMessage());
                $error = "Unable to add student. Please try again.";
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
    <title>Add Student | Campus Admin</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="navbar">
    <h2>Campus Admin</h2>
    <a href="index.php">Back to Dashboard</a>
</header>

<main class="container">
    <section class="card">
        <h1>Add New Student</h1>
        <p class="muted">Enter the student's details below.</p>

        <?php if ($error !== ""): ?>
            <div class="error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" action="add.php">
            <label for="name">Student Name</label>
            <input id="name" name="name" maxlength="100"
                   value="<?= e($name) ?>" required>

            <label for="roll_number">Roll Number</label>
            <input id="roll_number" name="roll_number" maxlength="30"
                   value="<?= e($roll) ?>" required>

            <label for="email">Email Address</label>
            <input id="email" name="email" type="email"
                   maxlength="150" value="<?= e($email) ?>" required>

            <label for="course">Course</label>
            <select id="course" name="course" required>
                <option value="">Select course</option>
                <?php foreach ($courses as $c): ?>
                    <option value="<?= e($c) ?>"
                        <?= $course === $c ? "selected" : "" ?>>
                        <?= e($c) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <br><br>
            <button type="submit">Save Student</button>
            <a href="index.php" class="btn btn-gray">Cancel</a>
        </form>
    </section>
</main>
</body>
</html>
