<?php
include 'config.php';

if ($_SESSION['role'] !== 'teacher') {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'];
    $test_name = $_POST['test_name'];
    $marks = $_POST['marks'];
    $published_by = $_SESSION['user_id'];

    $stmt = $pdo->prepare("INSERT INTO marks (student_id, test_name, marks, published_by) VALUES (?, ?, ?, ?)");
    $stmt->execute([$student_id, $test_name, $marks, $published_by]);
    header("Location: dashboard.php");
}
?>