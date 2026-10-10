
<?php
require "db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Invalid request method.");
}

$studentId = filter_input(
    INPUT_POST, "student_id", FILTER_VALIDATE_INT
);

if (!$studentId || $studentId < 1 ||
    !isset($_FILES["document"])) {
    exit("Invalid student or missing file.");
}

// Check whether the student exists.
$stmt = $conn->prepare("SELECT id FROM students WHERE id = ?");
$stmt->bind_param("i", $studentId);
$stmt->execute();

if (!$stmt->get_result()->fetch_assoc()) {
    exit("Student not found.");
}

$file = $_FILES["document"];

if ($file["error"] !== UPLOAD_ERR_OK) {
    exit("Upload failed. Check the file and try again.");
}

// Maximum allowed size: 5 MB.
$maxSize = 5 * 1024 * 1024;

if ($file["size"] <= 0 || $file["size"] > $maxSize) {
    exit("File must be between 1 byte and 5 MB.");
}

$originalName = basename($file["name"]);
$extension = strtolower(
    pathinfo($originalName, PATHINFO_EXTENSION)
);

$allowedMimes = [
    "jpg"  => "image/jpeg",
    "jpeg" => "image/jpeg",
    "png"  => "image/png",
    "pdf"  => "application/pdf"
];

if (!isset($allowedMimes[$extension])) {
    exit("Only JPG, JPEG, PNG and PDF files are allowed.");
}

// Verify the actual file content.
if (!class_exists("finfo")) {
    exit("PHP fileinfo extension must be enabled.");
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($file["tmp_name"]);

if ($mime !== $allowedMimes[$extension]) {
    exit("The file content does not match its extension.");
}

// Create the uploads folder if it does not exist.
$uploadDir = __DIR__ . "/uploads/";

if (
    !is_dir($uploadDir) &&
    !mkdir($uploadDir, 0750, true) &&
    !is_dir($uploadDir)
) {
    exit("Unable to create upload directory.");
}

if (!is_writable($uploadDir)) {
    exit("Upload directory is not writable.");
}

// Generate a unique server-side filename.
$storedName = bin2hex(random_bytes(16)) . "." . $extension;
$destination = $uploadDir . $storedName;
$relativePath = "uploads/" . $storedName;

if (!move_uploaded_file($file["tmp_name"], $destination)) {
    exit("Could not save the uploaded file.");
}

try {
    $stmt = $conn->prepare(
        "INSERT INTO student_files
        (student_id, original_name, stored_name,
         file_path, file_type, file_size)
        VALUES (?, ?, ?, ?, ?, ?)"
    );

    $size = (int)$file["size"];

    $stmt->bind_param(
        "issssi",
        $studentId,
        $originalName,
        $storedName,
        $relativePath,
        $mime,
        $size
    );

    $stmt->execute();

} catch (Throwable $ex) {
    // Remove the saved file if its database record fails.
    if (is_file($destination)) {
        unlink($destination);
    }

    error_log($ex->getMessage());
    exit("Unable to save file metadata in the database.");
}

header(
    "Location: view.php?id=" . $studentId .
    "&msg=" . urlencode("File uploaded successfully.")
);
exit;
?>
