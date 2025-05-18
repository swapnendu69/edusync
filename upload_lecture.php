<?php
include 'config.php';

if ($_SESSION['role'] !== 'teacher') {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Upload Lecture</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="menu">
        <a href="dashboard.php">Dashboard</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="container">
        <h2>Upload Lecture Slides</h2>
        <form action="upload_lecture_process.php" method="post" enctype="multipart/form-data">
            <input type="text" name="title" placeholder="Lecture Title" required>
            <input type="file" name="file" accept=".pdf,.ppt,.pptx" required>
            <button type="submit">Upload</button>
        </form>
    </div>
</body>
</html>