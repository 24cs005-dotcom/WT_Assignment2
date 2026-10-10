
<?php
require "db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Use the dashboard to delete a student.");
}

$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    http_response_code(400);
    exit("Invalid student ID.");
}

// Collect associated file paths before deleting the records.
$stmt = $conn->prepare(
    "SELECT file_path FROM student_files WHERE student_id = ?"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

$paths = [];

while ($row = $result->fetch_assoc()) {
    $paths[] = $row["file_path"];
}

try {
    $conn->begin_transaction();

    $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        throw new RuntimeException("Student not found.");
    }

    $conn->commit();

} catch (Throwable $ex) {
    $conn->rollback();
    error_log($ex->getMessage());
    http_response_code(400);
    exit("Could not delete the student.");
}

// Delete physical files only from the project's uploads folder.
$uploadRoot = realpath(__DIR__ . "/uploads");

if ($uploadRoot !== false) {
    foreach ($paths as $path) {
        $fullPath = realpath(__DIR__ . "/" . $path);

        if (
            $fullPath !== false &&
            str_starts_with(
                $fullPath,
                $uploadRoot . DIRECTORY_SEPARATOR
            ) &&
            is_file($fullPath)
        ) {
            if (!unlink($fullPath)) {
                error_log("Unable to remove uploaded file.");
            }
        }
    }
}

header(
    "Location: index.php?msg=" .
    urlencode("Student deleted successfully.")
);
exit;
?>
