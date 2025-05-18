<?php
include 'config.php';

if ($_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_SESSION['student_id'];
    $assignment_id = (int)$_POST['assignment_id'];

    $target_dir = "uploads/assignments/";
    $file_name = basename($_FILES["file"]["name"]);
    $target_file = $target_dir . uniqid() . "_" . $file_name;

    if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
        $stmt = $pdo->prepare("INSERT INTO submissions (student_id, assignment_id, file_path) 
                              VALUES (?, ?, ?)");
        $stmt->execute([$student_id, $assignment_id, $target_file]);
        header("Location: assignments.php?success=1");
    } else {
        header("Location: assignments.php?error=upload_failed");
    }
}
?>