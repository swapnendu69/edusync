<?php
include 'config.php';

// Validate student access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

// Validate assignment ID
$assignment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($assignment_id <= 0) {
    header("Location: assignments.php");
    exit();
}

// Verify assignment exists
$stmt = $pdo->prepare("SELECT id FROM assignments WHERE id = ?");
$stmt->execute([$assignment_id]);
if (!$stmt->fetch()) {
    die("Invalid assignment!");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Submit Assignment</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="menu">
        <a href="dashboard.php">Dashboard</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="container">
        <h2>Submit Assignment</h2>
        <form action="submit_assignment_process.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="assignment_id" value="<?php echo $assignment_id; ?>">
            
            <div class="form-group">
                <label>Select File (PDF/DOC/DOCX):</label>
                <input type="file" name="submission_file" required>
            </div>
            
            <button type="submit" class="upload-btn">Upload Submission</button>
        </form>
    </div>
</body>
</html>