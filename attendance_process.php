<?php
include 'config.php';

if ($_SESSION['role'] !== 'teacher') {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'];
    $date = $_POST['date'];
    $status = $_POST['status'];
    $marked_by = $_SESSION['user_id'];

    $stmt = $pdo->prepare("INSERT INTO attendance (student_id, date, status, marked_by) VALUES (?, ?, ?, ?)");
    $stmt->execute([$student_id, $date, $status, $marked_by]);
    header("Location: dashboard.php");
}
?>