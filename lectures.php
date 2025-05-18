<?php
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Lectures</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="menu">
        <a href="dashboard.php">Dashboard</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="container">
        <h2>Available Lectures</h2>
        <?php
        // Fetch all lectures from the database
        $stmt = $pdo->query("SELECT * FROM lecture_slides");
        while ($lecture = $stmt->fetch()) {
            echo "<div class='lecture'>";
            echo "<h3>{$lecture['title']}</h3>";
            echo "<a href='{$lecture['file_path']}' download>Download</a>";
            echo "</div>";
        }
        ?>
    </div>
</body>
</html>