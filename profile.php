<?php
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Profile</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="menu">
        <a href="dashboard.php">Dashboard</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="container">
        <h2>Profile</h2>
        <?php if ($user['student_id']): ?>
            <p>Student ID: <?= $user['student_id'] ?></p>
        <?php endif; ?>
        <p>Name: <?= $user['name'] ?></p>
        <p>Email: <?= $user['email'] ?></p>
        
        <form action="delete_account_process.php" method="post">
            <button type="submit" onclick="return confirm('This will permanently delete your account!')">
                Delete Account
            </button>
        </form>
    </div>
</body>
</html>