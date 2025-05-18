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
    <title>Attendance</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="menu">
        <a href="dashboard.php">Dashboard</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="container">
        <?php if ($role === 'teacher'): ?>
            <h2>Mark Attendance</h2>
            <form action="attendance_process.php" method="post">
                <select name="student_id" required>
                    <?php
                    $stmt = $pdo->query("SELECT id, name FROM users WHERE role='student'");
                    while ($student = $stmt->fetch()) {
                        echo "<option value='{$student['id']}'>{$student['name']}</option>";
                    }
                    ?>
                </select>
                <input type="date" name="date" required>
                <select name="status" required>
                    <option value="present">Present</option>
                    <option value="absent">Absent</option>
                </select>
                <button type="submit">Submit</button>
            </form>
        <?php else: ?>
            <h2>Your Attendance</h2>
            <table>
                <tr><th>Date</th><th>Status</th></tr>
                <?php
                $stmt = $pdo->prepare("SELECT date, status FROM attendance WHERE student_id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                while ($att = $stmt->fetch()) {
                    echo "<tr><td>{$att['date']}</td><td>{$att['status']}</td></tr>";
                }
                ?>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>