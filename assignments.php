<?php
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Assignments</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="menu">
        <a href="dashboard.php">Dashboard</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="container">
        <?php if ($role === 'teacher'): ?>
            <!-- TEACHER VIEW -->
            <h2>Create New Assignment</h2>
            <form action="create_assignment_process.php" method="post" enctype="multipart/form-data">
                <input type="text" name="title" placeholder="Assignment Title" required>
                <textarea name="description" placeholder="Description (optional)"></textarea>
                <input type="date" name="deadline" required>
                <input type="file" name="file" accept=".pdf,.doc,.docx">
                <button type="submit">Create Assignment</button>
            </form>

            <h3>Your Created Assignments</h3>
            <?php
            $stmt = $pdo->prepare("SELECT * FROM assignments WHERE teacher_id = ?");
            $stmt->execute([$user_id]);
            $assignments = $stmt->fetchAll();

            if (count($assignments) > 0): ?>
                <?php foreach ($assignments as $assignment): ?>
                    <div class="assignment-card">
                        <h4><?php echo htmlspecialchars($assignment['title']); ?></h4>
                        <p><?php echo nl2br(htmlspecialchars($assignment['description'])); ?></p>
                        <p>Deadline: <?php echo $assignment['deadline']; ?></p>
                        <?php if (!empty($assignment['file_path'])): ?>
                            <a href="<?php echo $assignment['file_path']; ?>" download>Download Assignment File</a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No assignments created yet.</p>
            <?php endif; ?>

        <?php else: ?>
            <!-- STUDENT VIEW -->
            <h2>Your Assignments</h2>
            <?php
            $stmt = $pdo->query("SELECT * FROM assignments");
            $assignments = $stmt->fetchAll();

            if (count($assignments) > 0): ?>
                <?php foreach ($assignments as $assignment): ?>
                    <div class="assignment-card">
                        <h4><?php echo htmlspecialchars($assignment['title']); ?></h4>
                        <p><?php echo nl2br(htmlspecialchars($assignment['description'])); ?></p>
                        <p>Deadline: <?php echo $assignment['deadline']; ?></p>
                        
                        <?php if (!empty($assignment['file_path'])): ?>
                            <a href="<?php echo $assignment['file_path']; ?>" download class="download-btn">
                                Download Assignment
                            </a>
                        <?php endif; ?>

                        <?php
                        // Check if student has submitted
                        $sub_stmt = $pdo->prepare("SELECT * FROM submissions 
                                                  WHERE student_id = ? AND assignment_id = ?");
                        $sub_stmt->execute([$user_id, $assignment['id']]);
                        $submission = $sub_stmt->fetch();
                        ?>

                        <div class="submission-status">
                            <?php if ($submission): ?>
                                <p class="success">Submitted ✔️</p>
                                <a href="<?php echo $submission['file_path']; ?>" download>
                                    Download Your Submission
                                </a>
                            <?php else: ?>
                                <a href="submit_assignment.php?id=<?php echo $assignment['id']; ?>" 
                                   class="submit-btn">
                                   Submit Assignment
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No assignments available.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>