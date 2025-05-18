<?php
include 'config.php';

if ($_SESSION['role'] !== 'student') {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Feedback</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="menu">
        <a href="dashboard.php">Dashboard</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="container">
        <h2>Submit Feedback</h2>
        <form action="feedback_process.php" method="post">
            <select name="teacher_id" required>
                <?php
                $stmt = $pdo->query("SELECT id, name FROM users WHERE role='teacher'");
                while ($teacher = $stmt->fetch()) {
                    echo "<option value='{$teacher['id']}'>{$teacher['name']}</option>";
                }
                ?>
            </select>
            <textarea name="comment" placeholder="Your feedback..." required></textarea>
            <button type="submit">Submit</button>
        </form>
    </div>
</body>
</html>