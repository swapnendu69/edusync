<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduSync - Khulna University ECE</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <header class="bg-primary text-white">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center py-3">
                <div class="d-flex align-items-center">
                    <img src="assets/images/ku-logo.png" alt="Khulna University Logo" height="50" class="me-3">
                    <div>
                        <h1 class="h4 mb-0">EduSync</h1>
                        <p class="mb-0 small">Electronics and Communication Engineering Discipline, Khulna University</p>
                    </div>
                </div>
                <nav>
                    <ul class="nav">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <li class="nav-item"><a href="dashboard.php" class="nav-link text-white">Dashboard</a></li>
                            <?php if ($_SESSION['role'] === 'teacher'): ?>
                                <li class="nav-item"><a href="assignments.php?action=create" class="nav-link text-white">Create Assignment</a></li>
                                <li class="nav-item"><a href="resources.php?action=upload" class="nav-link text-white">Upload Resources</a></li>
                            <?php else: ?>
                                <li class="nav-item"><a href="assignments.php" class="nav-link text-white">Assignments</a></li>
                                <li class="nav-item"><a href="resources.php" class="nav-link text-white">Resources</a></li>
                            <?php endif; ?>
                            <li class="nav-item"><a href="attendance.php" class="nav-link text-white">Attendance</a></li>
                            <li class="nav-item"><a href="marks.php" class="nav-link text-white">Marks</a></li>
                            <li class="nav-item"><a href="profile.php" class="nav-link text-white">Profile</a></li>
                            <li class="nav-item"><a href="logout.php" class="nav-link text-white">Logout</a></li>
                        <?php else: ?>
                            <li class="nav-item"><a href="index.php" class="nav-link text-white">Home</a></li>
                            <li class="nav-item"><a href="login.php" class="nav-link text-white">Login</a></li>
                            <li class="nav-item"><a href="register.php" class="nav-link text-white">Register</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </header>
    <main class="container my-4">