
<?php
require "db.php";

$fileId = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$fileId || $fileId < 1) {
    http_response_code(400);
    exit("Invalid file ID.");
}

$stmt = $conn->prepare(
    "SELECT original_name, file_path, file_type, file_size
     FROM student_files WHERE id = ?"
);
$stmt->bind_param("i", $fileId);
$stmt->execute();

$file = $stmt->get_result()->fetch_assoc();

if (!$file) {
    http_response_code(404);
    exit("File not found.");
}

$uploadRoot = realpath(__DIR__ . "/uploads");
$fullPath = realpath(__DIR__ . "/" . $file["file_path"]);

if (
    $uploadRoot === false ||
    $fullPath === false ||
    !str_starts_with(
        $fullPath,
        $uploadRoot . DIRECTORY_SEPARATOR
    ) ||
    !is_file($fullPath)
) {
    http_response_code(404);
    exit("Stored file is unavailable.");
}

$allowedTypes = [
    "image/jpeg",
    "image/png",
    "application/pdf"
];

if (!in_array($file["file_type"], $allowedTypes, true)) {
    http_response_code(415);
    exit("Unsupported file type.");
}

// Images can be displayed inline when requested.
$inline = isset($_GET["inline"]) &&
          $_GET["inline"] === "1" &&
          str_starts_with($file["file_type"], "image/");

// Sanitize the original filename for the response header.
$name = str_replace(
    ["\r", "\n", '"', "\\"],
    "_",
    $file["original_name"]
);

header("Content-Type: " . $file["file_type"]);
header("X-Content-Type-Options: nosniff");

header(
    "Content-Disposition: " .
    ($inline ? "inline" : "attachment") .
    '; filename="' . $name . '"'
);

header("Content-Length: " . filesize($fullPath));

readfile($fullPath);
exit;
?>
