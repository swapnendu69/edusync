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
    <title>EduSync - Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="menu">
        <?php if ($role === 'teacher'): ?>
            <a href="dashboard.php">Dashboard</a>
            <a href="upload_lecture.php">Upload Lecture</a>
            <a href="assignments.php">Create Assignment</a>
            <a href="marks.php">Publish Marks</a>
            <a href="attendance.php">Mark Attendance</a>
        <?php else: ?>
            <a href="dashboard.php">Dashboard</a>
            <a href="lectures.php">Lectures</a>
            <a href="assignments.php">Assignments</a>
            <a href="marks.php">Marks</a>
            <a href="attendance.php">Attendance</a>
            <a href="feedback.php">Feedback</a>
        <?php endif; ?>
        <a href="profile.php">Profile</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="container">
        <h2>Welcome, <?php echo $_SESSION['name']; ?>!</h2>
        <?php if ($role === 'teacher'): ?>
            <p>Teacher Dashboard: Upload lectures, create assignments, and manage class data.</p>
        <?php else: ?>
            <p>Student Dashboard: Access lectures, submit assignments, and view progress.</p>
        <?php endif; ?>
    </div>
</body>
</html>