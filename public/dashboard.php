<?php
require 'includes/config.php';
requireLogin();

// Get user information
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    $_SESSION['error_message'] = "User not found.";
    header("Location: login.php");
    exit();
}
?>

<?php include 'includes/header.php'; ?>

<div class="row mb-4">
    <div class="col-md-8">
        <h2>Welcome, <?php echo htmlspecialchars($user['name']); ?></h2>
        <p class="lead">EduSync Dashboard - <?php echo ucfirst($user['role']); ?> Panel</p>
    </div>
    <div class="col-md-4 text-end">
        <div class="card bg-light">
            <div class="card-body py-2">
                <p class="mb-0"><strong>ID:</strong> <?php echo htmlspecialchars($user['user_id']); ?></p>
                <p class="mb-0"><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            </div>
        </div>
    </div>
</div>

<?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success"><?php echo $_SESSION['success_message']; ?></div>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger"><?php echo $_SESSION['error_message']; ?></div>
    <?php unset($_SESSION['error_message']); ?>
<?php endif; ?>

<?php if ($_SESSION['role'] === 'teacher'): ?>
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Create Assignment</h5>
                    <p class="card-text">Create and assign new assignments to students.</p>
                    <a href="assignments.php?action=create" class="btn btn-primary">Go to Assignments</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Upload Resources</h5>
                    <p class="card-text">Upload lecture notes and study materials for courses.</p>
                    <a href="resources.php?action=upload" class="btn btn-primary">Upload Resources</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Manage Attendance</h5>
                    <p class="card-text">Mark and manage student attendance records.</p>
                    <a href="attendance.php" class="btn btn-primary">Manage Attendance</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">Student Feedback</h5>
        </div>
        <div class="card-body">
            <p>View anonymous feedback from students.</p>
            <a href="admin_feedback.php" class="btn btn-primary">View Feedback</a>
        </div>
    </div>
<?php else: ?>
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">View Assignments</h5>
                    <p class="card-text">View and submit your assignments.</p>
                    <a href="assignments.php" class="btn btn-primary">View Assignments</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Course Resources</h5>
                    <p class="card-text">Access lecture notes and study materials.</p>
                    <a href="resources.php" class="btn btn-primary">View Resources</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Check Attendance</h5>
                    <p class="card-text">View your attendance records.</p>
                    <a href="attendance.php" class="btn btn-primary">View Attendance</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">Submit Feedback</h5>
        </div>
        <div class="card-body">
            <p>You can submit anonymous feedback about your courses.</p>
            <a href="feedback.php" class="btn btn-primary">Submit Feedback</a>
        </div>
    </div>
<?php endif; ?>

<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0">Recent Activities</h5>
    </div>
    <div class="card-body">
        <?php if ($_SESSION['role'] === 'teacher'): ?>
            <?php
            // Get recent assignments created by the teacher
            $stmt = $pdo->prepare("SELECT * FROM assignments WHERE teacher_id = ? ORDER BY created_at DESC LIMIT 5");
            $stmt->execute([$_SESSION['user_id']]);
            $assignments = $stmt->fetchAll();
            
            if ($assignments): ?>
                <h6>Your Recent Assignments</h6>
                <ul class="list-group list-group-flush mb-3">
                    <?php foreach ($assignments as $assignment): ?>
                        <li class="list-group-item">
                            <strong><?php echo htmlspecialchars($assignment['course_no']); ?> - <?php echo htmlspecialchars($assignment['course_title']); ?></strong><br>
                            Deadline: <?php echo date('F j, Y, g:i a', strtotime($assignment['deadline'])); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No recent assignments found.</p>
            <?php endif; ?>
        <?php else: ?>
            <?php
            // Get recent assignments for the student
            $stmt = $pdo->prepare("SELECT a.*, s.submitted_at 
                                  FROM assignments a
                                  JOIN submissions s ON a.assignment_id = s.assignment_id 
                                  WHERE s.student_id = ? 
                                  ORDER BY s.submitted_at DESC LIMIT 5");
            $stmt->execute([$_SESSION['user_id']]);
            $submittedAssignments = $stmt->fetchAll();
            
            if ($submittedAssignments): ?>
                <h6>Your Recently Submitted Assignments</h6>
                <ul class="list-group list-group-flush mb-3">
                    <?php foreach ($submittedAssignments as $assignment): ?>
                        <li class="list-group-item">
                            <strong><?php echo htmlspecialchars($assignment['course_no']); ?> - <?php echo htmlspecialchars($assignment['course_title']); ?></strong><br>
                            Submitted on: <?php echo isset($assignment['submitted_at']) ? date('F j, Y, g:i a', strtotime($assignment['submitted_at'])) : 'Not submitted'; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No recently submitted assignments found.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>