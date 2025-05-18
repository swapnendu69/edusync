<?php
include 'config.php';

if ($_SESSION['role'] !== 'student') {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_SESSION['user_id'];
    $teacher_id = $_POST['teacher_id'];
    $comment = $_POST['comment'];

    $stmt = $pdo->prepare("INSERT INTO feedback (student_id, teacher_id, comment) VALUES (?, ?, ?)");
    $stmt->execute([$student_id, $teacher_id, $comment]);
    header("Location: dashboard.php");
}
?>