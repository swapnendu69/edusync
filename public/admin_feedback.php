<?php
require 'includes/config.php';
requireTeacher();

// Get all feedback (hide student info if anonymous)
$query = "SELECT f.feedback_id, 
          CASE WHEN f.is_anonymous = 1 THEN 'Anonymous' ELSE s.name END as student_name,
          t.name as teacher_name,
          f.course_no, f.course_title, f.feedback_text, f.submitted_at
          FROM feedback f
          LEFT JOIN users s ON f.student_id = s.user_id
          LEFT JOIN users t ON f.teacher_id = t.user_id
          ORDER BY f.submitted_at DESC";
$stmt = $pdo->query($query);
$feedback = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Student Feedback</h2>
</div>

<?php if (!empty($feedback)): ?>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Teacher</th>
                    <th>Course</th>
                    <th>Feedback</th>
                    <th>Submitted At</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($feedback as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['student_name']); ?></td>
                        <td><?php echo htmlspecialchars($item['teacher_name']); ?></td>
                        <td><?php echo htmlspecialchars($item['course_no']); ?> - <?php echo htmlspecialchars($item['course_title']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($item['feedback_text'])); ?></td>
                        <td><?php echo date('F j, Y, g:i a', strtotime($item['submitted_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="alert alert-info">No feedback has been submitted yet.</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>