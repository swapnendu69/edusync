<?php
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

try {
    $pdo->beginTransaction();
    
    // Delete related records
    $tables = ['marks', 'attendance', 'submissions', 'feedback'];
    foreach ($tables as $table) {
        $stmt = $pdo->prepare("DELETE FROM $table WHERE student_id = ?");
        $stmt->execute([$_SESSION['student_id']]);
    }

    // Delete user
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);

    $pdo->commit();
    
    session_destroy();
    header("Location: register.php");
    
} catch (PDOException $e) {
    $pdo->rollBack();
    die("Account deletion failed: " . $e->getMessage());
}
?>