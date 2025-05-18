<?php
include 'config.php';

if ($_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teacher_id = $_SESSION['user_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $deadline = $_POST['deadline'];

    // File upload handling
    $file_path = null;
    if (!empty($_FILES['file']['name'])) {
        $target_dir = "uploads/assignments/";
        $file_name = basename($_FILES['file']['name']);
        $target_file = $target_dir . uniqid() . "_" . $file_name;
        
        if (move_uploaded_file($_FILES['file']['tmp_name'], $target_file)) {
            $file_path = $target_file;
        }
    }

    $stmt = $pdo->prepare("INSERT INTO assignments (teacher_id, title, description, deadline, file_path) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$teacher_id, $title, $description, $deadline, $file_path]);
    header("Location: assignments.php");
}
?>