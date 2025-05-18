<?php
include 'config.php';

if ($_SESSION['role'] !== 'teacher') {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $teacher_id = $_SESSION['user_id'];
    $target_dir = "uploads/lectures/";
    $file_name = basename($_FILES["file"]["name"]);
    $target_file = $target_dir . uniqid() . "_" . $file_name;

    if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
        $stmt = $pdo->prepare("INSERT INTO lecture_slides (teacher_id, title, file_path) VALUES (?, ?, ?)");
        $stmt->execute([$teacher_id, $title, $target_file]);
        header("Location: dashboard.php");
    } else {
        echo "Upload failed!";
    }
}
?>