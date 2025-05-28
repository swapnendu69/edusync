<?php
// Database configuration
$host = getenv('sql101.infinityfree.com');
$db   = getenv('if0_39104602_edusync');
$user = getenv('if0_39104602');
$pass = getenv('ax7XxZE2BO13Y');

$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// File upload paths
define('ASSIGNMENT_UPLOAD_PATH', __DIR__ . '/../uploads/assignments/');
define('SUBMISSION_UPLOAD_PATH', __DIR__ . '/../uploads/submissions/');
define('RESOURCE_UPLOAD_PATH', __DIR__ . '/../uploads/resources/');

// Maximum file sizes (in bytes)
define('MAX_ASSIGNMENT_SIZE', 104857600); // 100MB
define('MAX_SUBMISSION_SIZE', 104857600); // 100MB
define('MAX_RESOURCE_SIZE', 104857600); // 100MB

// Allowed file types
$allowedAssignmentTypes = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'txt', 'jpg', 'jpeg', 'png'];
$allowedSubmissionTypes = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'txt', 'zip', 'rar'];
$allowedResourceTypes = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'txt'];

// Start session
session_start();

// Create database connection
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Redirect if not logged in
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}

// Redirect if not teacher
function requireTeacher() {
    requireLogin();
    if ($_SESSION['role'] !== 'teacher') {
        header("Location: dashboard.php");
        exit();
    }
}

// Redirect if not student
function requireStudent() {
    requireLogin();
    if ($_SESSION['role'] !== 'student') {
        header("Location: dashboard.php");
        exit();
    }
}
?>