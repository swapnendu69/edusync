<?php
require 'includes/config.php';
requireStudent();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_no = trim($_POST['course_no']);
    $course_title = trim($_POST['course_title']);
    $teacher_id = trim($_POST['teacher_id']);
    $feedback_text = trim($_POST['feedback_text']);
    $is_anonymous = isset($_POST['is_anonymous']) ? 1 : 0;
    
    $errors = [];
    
    if (empty($course_no)) {
        $errors[] = "Course number is required.";
    }
    
    if (empty($course_title)) {
        $errors[] = "Course title is required.";
    }
    
    if (empty($teacher_id)) {
        $errors[] = "Teacher ID is required.";
    } else {
        // Verify teacher exists
        $stmt = $pdo->prepare("SELECT 1 FROM users WHERE user_id = ? AND role = 'teacher'");
        $stmt->execute([$teacher_id]);
        if (!$stmt->fetch()) {
            $errors[] = "Invalid Teacher ID";
        }
    }
    
    if (empty($feedback_text)) {
        $errors[] = "Feedback text is required.";
    }
    
    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO feedback 
                              (student_id, teacher_id, course_no, course_title, feedback_text, is_anonymous) 
                              VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([
            $_SESSION['user_id'],
            $teacher_id,
            $course_no,
            $course_title,
            $feedback_text,
            $is_anonymous
        ])) {
            $_SESSION['success_message'] = "Feedback submitted successfully!";
            header("Location: dashboard.php");
            exit();
        } else {
            $errors[] = "Failed to submit feedback. Please try again.";
        }
    }
}

// Get list of teachers for dropdown
$teachers = $pdo->query("SELECT user_id, name FROM users WHERE role = 'teacher' ORDER BY name")->fetchAll();

include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h2 class="h4 mb-0">Submit Feedback</h2>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="post">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="course_no" class="form-label">Course Number</label>
                            <input type="text" class="form-control" id="course_no" name="course_no" required>
                        </div>
                        <div class="col-md-6">
                            <label for="course_title" class="form-label">Course Title</label>
                            <input type="text" class="form-control" id="course_title" name="course_title" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="teacher_id" class="form-label">Teacher</label>
                        <select class="form-select" id="teacher_id" name="teacher_id" required>
                            <option value="">Select Teacher</option>
                            <?php foreach ($teachers as $teacher): ?>
                                <option value="<?php echo htmlspecialchars($teacher['user_id']); ?>">
                                    <?php echo htmlspecialchars($teacher['name']); ?> (<?php echo htmlspecialchars($teacher['user_id']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="feedback_text" class="form-label">Your Feedback</label>
                        <textarea class="form-control" id="feedback_text" name="feedback_text" rows="5" required></textarea>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="is_anonymous" name="is_anonymous" checked>
                        <label class="form-check-label" for="is_anonymous">Submit anonymously</label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Submit Feedback</button>
                    <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>