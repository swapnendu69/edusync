<?php
require 'includes/config.php';
requireTeacher();

if (!isset($_GET['file'])) {
    header("HTTP/1.0 400 Bad Request");
    die("File parameter missing");
}

// Sanitize the filename
$file_name = basename($_GET['file']);
$file_path = realpath('uploads/submissions/' . $file_name);

// Verify the file exists and is within our uploads directory
if (!file_exists($file_path) || strpos($file_path, realpath('uploads/submissions')) !== 0) {
    header("HTTP/1.0 404 Not Found");
    die("File not found or invalid path");
}

// Verify the teacher has permission (assignment belongs to them)
$stmt = $pdo->prepare("SELECT 1 FROM assignments a 
                      JOIN submissions s ON a.assignment_id = s.assignment_id
                      WHERE a.teacher_id = ? AND s.attachment_path LIKE ?");
$stmt->execute([$_SESSION['user_id'], '%'.$file_name]);
if (!$stmt->fetch()) {
    header("HTTP/1.0 403 Forbidden");
    die("You don't have permission to download this file");
}

// Log download attempt
file_put_contents('download_log.txt', 
    date('Y-m-d H:i:s') . ' - Teacher ' . $_SESSION['user_id'] . ' downloaded ' . $file_name . PHP_EOL, 
    FILE_APPEND);

// Get the original filename from database
$stmt = $pdo->prepare("SELECT attachment_path FROM submissions WHERE attachment_path LIKE ?");
$stmt->execute(['%'.$file_name]);
$original_file = $stmt->fetchColumn();
$original_filename = basename($original_file);

// Set headers and output file
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="'.$original_filename.'"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($file_path));

// Clear output buffer
if (ob_get_level()) {
    ob_end_clean();
}

readfile($file_path);
exit;
?>