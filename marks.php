<?php
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Marks</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="menu">
        <a href="dashboard.php">Dashboard</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="container">
        <?php if ($role === 'teacher'): ?>
            <h2>Publish Marks</h2>
            <form action="marks_process.php" method="post">
                <select name="student_id" required>
                    <?php
                    $stmt = $pdo->query("SELECT id, name FROM users WHERE role='student'");
                    while ($student = $stmt->fetch()) {
                        echo "<option value='{$student['id']}'>{$student['name']}</option>";
                    }
                    ?>
                </select>
                <input type="text" name="test_name" placeholder="Test Name" required>
                <input type="number" name="marks" placeholder="Marks" required>
                <button type="submit">Publish</button>
            </form>
        <?php else: ?>
            <h2>Your Marks</h2>
            <?php
            $stmt = $pdo->prepare("SELECT * FROM marks WHERE student_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            while ($mark = $stmt->fetch()) {
                echo "<p>{$mark['test_name']}: {$mark['marks']}</p>";
            }
            ?>
        <?php endif; ?>
    </div>
</body>
</html>