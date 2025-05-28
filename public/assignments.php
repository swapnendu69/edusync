<?php
require 'includes/config.php';

if ($_SESSION['role'] === 'teacher') {
    requireTeacher();
} else {
    requireStudent();
}

// Ensure upload directories exist
$upload_dirs = [
    ASSIGNMENT_UPLOAD_PATH,
    SUBMISSION_UPLOAD_PATH,
    RESOURCE_UPLOAD_PATH
];

foreach ($upload_dirs as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Teacher: Create assignment
if ($_SESSION['role'] === 'teacher' && isset($_GET['action']) && $_GET['action'] === 'create') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $course_no = trim($_POST['course_no']);
        $course_title = trim($_POST['course_title']);
        $assignment_text = trim($_POST['assignment_text']);
        $deadline = $_POST['deadline'];
        
        $errors = [];
        
        if (empty($course_no)) {
            $errors[] = "Course number is required.";
        }
        
        if (empty($course_title)) {
            $errors[] = "Course title is required.";
        }
        
        if (empty($deadline)) {
            $errors[] = "Deadline is required.";
        } elseif (strtotime($deadline) < time()) {
            $errors[] = "Deadline must be in the future.";
        }
        
        // Handle file upload
        $attachment_path = null;
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $file_name = $_FILES['attachment']['name'];
            $file_tmp = $_FILES['attachment']['tmp_name'];
            $file_size = $_FILES['attachment']['size'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            if ($file_size > MAX_ASSIGNMENT_SIZE) {
                $errors[] = "File size exceeds maximum allowed size (100MB).";
            }
            
            if (!in_array($file_ext, $GLOBALS['allowedAssignmentTypes'])) {
                $errors[] = "File type not allowed. Allowed types: " . implode(', ', $GLOBALS['allowedAssignmentTypes']);
            }
            
            if (empty($errors)) {
                $new_file_name = uniqid('assign_', true) . '.' . $file_ext;
                $upload_path = ASSIGNMENT_UPLOAD_PATH . $new_file_name;
                
                if (move_uploaded_file($file_tmp, $upload_path)) {
                    $attachment_path = 'uploads/assignments/' . $new_file_name;
                } else {
                    $errors[] = "Failed to upload file. Error: " . error_get_last()['message'];
                }
            }
        }
        
        if (empty($errors)) {
            $stmt = $pdo->prepare("INSERT INTO assignments (course_no, course_title, teacher_id, assignment_text, attachment_path, deadline) 
                                  VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$course_no, $course_title, $_SESSION['user_id'], $assignment_text, $attachment_path, $deadline])) {
                $_SESSION['success_message'] = "Assignment created successfully!";
                header("Location: assignments.php");
                exit();
            } else {
                $errors[] = "Failed to create assignment. Please try again.";
            }
        }
    }
    
    include 'includes/header.php';
    ?>
    
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2 class="h4 mb-0">Create New Assignment</h2>
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
                    
                    <form method="post" enctype="multipart/form-data">
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
                            <label for="assignment_text" class="form-label">Assignment Description</label>
                            <textarea class="form-control" id="assignment_text" name="assignment_text" rows="4"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="deadline" class="form-label">Deadline</label>
                            <input type="datetime-local" class="form-control" id="deadline" name="deadline" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="attachment" class="form-label">Attachment (Optional)</label>
                            <input type="file" class="form-control" id="attachment" name="attachment">
                            <div class="form-text">Allowed file types: <?php echo implode(', ', $GLOBALS['allowedAssignmentTypes']); ?> (Max 100MB)</div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Create Assignment</button>
                        <a href="assignments.php" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <?php
    include 'includes/footer.php';
    exit();
}

// Teacher: View submissions for an assignment
if ($_SESSION['role'] === 'teacher' && isset($_GET['action']) && $_GET['action'] === 'view_submissions' && isset($_GET['id'])) {
    $assignment_id = $_GET['id'];
    
    // Verify assignment exists and belongs to this teacher
    $stmt = $pdo->prepare("SELECT * FROM assignments WHERE assignment_id = ? AND teacher_id = ?");
    $stmt->execute([$assignment_id, $_SESSION['user_id']]);
    $assignment = $stmt->fetch();
    
    if (!$assignment) {
        $_SESSION['error_message'] = "Assignment not found or you don't have permission to view it.";
        header("Location: assignments.php");
        exit();
    }
    
    // Get all submissions for this assignment
    $stmt = $pdo->prepare("SELECT s.*, u.name as student_name 
                          FROM submissions s
                          JOIN users u ON s.student_id = u.user_id
                          WHERE s.assignment_id = ?
                          ORDER BY s.submitted_at DESC");
    $stmt->execute([$assignment_id]);
    $submissions = $stmt->fetchAll();
    
    include 'includes/header.php';
    ?>
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Submissions for <?php echo htmlspecialchars($assignment['course_no']); ?> - <?php echo htmlspecialchars($assignment['course_title']); ?></h2>
        <a href="assignments.php" class="btn btn-secondary">Back to Assignments</a>
    </div>
    
    <?php if (!empty($submissions)): ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Student ID</th>
                        <th>Submitted At</th>
                        <th>Submission</th>
                        <th>Attachment</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($submissions as $submission): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($submission['student_name']); ?></td>
                            <td><?php echo htmlspecialchars($submission['student_id']); ?></td>
                            <td><?php echo date('F j, Y, g:i a', strtotime($submission['submitted_at'])); ?></td>
                            <td>
                                <?php if (!empty($submission['submission_text'])): ?>
                                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#submissionModal<?php echo $submission['submission_id']; ?>">
                                        View Text
                                    </button>
                                    
                                    <!-- Modal for viewing submission text -->
                                    <div class="modal fade" id="submissionModal<?php echo $submission['submission_id']; ?>" tabindex="-1" aria-labelledby="submissionModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="submissionModalLabel">Submission from <?php echo htmlspecialchars($submission['student_name']); ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <pre><?php echo htmlspecialchars($submission['submission_text']); ?></pre>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">No text submitted</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($submission['attachment_path'])): ?>
                                    <a href="download.php?file=<?php echo basename($submission['attachment_path']); ?>" 
                                       class="btn btn-sm btn-success" 
                                       download>
                                        Download
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">No attachment</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">No submissions found for this assignment.</div>
    <?php endif; ?>
    
    <?php
    include 'includes/footer.php';
    exit();
}

// Student: Submit assignment
if ($_SESSION['role'] === 'student' && isset($_GET['action']) && $_GET['action'] === 'submit' && isset($_GET['id'])) {
    $assignment_id = $_GET['id'];
    
    // Verify assignment exists
    $stmt = $pdo->prepare("SELECT * FROM assignments WHERE assignment_id = ?");
    $stmt->execute([$assignment_id]);
    $assignment = $stmt->fetch();
    
    if (!$assignment) {
        $_SESSION['error_message'] = "Assignment not found.";
        header("Location: assignments.php");
        exit();
    }
    
    // Check if already submitted
    $stmt = $pdo->prepare("SELECT * FROM submissions WHERE assignment_id = ? AND student_id = ?");
    $stmt->execute([$assignment_id, $_SESSION['user_id']]);
    $submission = $stmt->fetch();
    
    if ($submission) {
        $_SESSION['error_message'] = "You have already submitted this assignment.";
        header("Location: assignments.php");
        exit();
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $submission_text = trim($_POST['submission_text']);
        
        $errors = [];
        
        // Handle file upload
        $attachment_path = null;
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $file_name = $_FILES['attachment']['name'];
            $file_tmp = $_FILES['attachment']['tmp_name'];
            $file_size = $_FILES['attachment']['size'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            if ($file_size > MAX_SUBMISSION_SIZE) {
                $errors[] = "File size exceeds maximum allowed size (100MB).";
            }
            
            if (!in_array($file_ext, $GLOBALS['allowedSubmissionTypes'])) {
                $errors[] = "File type not allowed. Allowed types: " . implode(', ', $GLOBALS['allowedSubmissionTypes']);
            }
            
            if (empty($errors)) {
                $new_file_name = uniqid('sub_', true) . '.' . $file_ext;
                $upload_path = SUBMISSION_UPLOAD_PATH . $new_file_name;
                
                if (move_uploaded_file($file_tmp, $upload_path)) {
                    $attachment_path = 'uploads/submissions/' . $new_file_name;
                } else {
                    $errors[] = "Failed to upload file. Error: " . error_get_last()['message'];
                }
            }
        }
        
        if (empty($submission_text) && empty($attachment_path)) {
            $errors[] = "Either submission text or attachment is required.";
        }
        
        if (empty($errors)) {
            $stmt = $pdo->prepare("INSERT INTO submissions (assignment_id, student_id, course_no, course_title, submission_text, attachment_path) 
                                  VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$assignment_id, $_SESSION['user_id'], $assignment['course_no'], $assignment['course_title'], $submission_text, $attachment_path])) {
                $_SESSION['success_message'] = "Assignment submitted successfully!";
                header("Location: assignments.php");
                exit();
            } else {
                $errors[] = "Failed to submit assignment. Please try again.";
            }
        }
    }
    
    include 'includes/header.php';
    ?>
    
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2 class="h4 mb-0">Submit Assignment</h2>
                </div>
                <div class="card-body">
                    <h5><?php echo htmlspecialchars($assignment['course_no']); ?> - <?php echo htmlspecialchars($assignment['course_title']); ?></h5>
                    <p>Deadline: <?php echo date('F j, Y, g:i a', strtotime($assignment['deadline'])); ?></p>
                    
                    <?php if (!empty($assignment['assignment_text'])): ?>
                        <div class="mb-3 p-3 bg-light rounded">
                            <h6>Assignment Description:</h6>
                            <p><?php echo nl2br(htmlspecialchars($assignment['assignment_text'])); ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="post" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="submission_text" class="form-label">Your Submission</label>
                            <textarea class="form-control" id="submission_text" name="submission_text" rows="4"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="attachment" class="form-label">Attachment (Optional)</label>
                            <input type="file" class="form-control" id="attachment" name="attachment">
                            <div class="form-text">Allowed file types: <?php echo implode(', ', $GLOBALS['allowedSubmissionTypes']); ?> (Max 100MB)</div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Submit Assignment</button>
                        <a href="assignments.php" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <?php
    include 'includes/footer.php';
    exit();
}

// View assignments
include 'includes/header.php';

if (isset($_SESSION['success_message'])) {
    echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_message'])) {
    echo '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
    unset($_SESSION['error_message']);
}

if ($_SESSION['role'] === 'teacher') {
    // Teacher view - show all assignments they've created
    $stmt = $pdo->prepare("SELECT * FROM assignments WHERE teacher_id = ? ORDER BY deadline DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $assignments = $stmt->fetchAll();
    ?>
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Your Assignments</h2>
        <a href="assignments.php?action=create" class="btn btn-primary">Create New Assignment</a>
    </div>
    
    <?php if ($assignments): ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Course No</th>
                        <th>Course Title</th>
                        <th>Deadline</th>
                        <th>Submissions</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($assignments as $assignment): ?>
                        <?php
                        // Count submissions for this assignment
                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM submissions WHERE assignment_id = ?");
                        $stmt->execute([$assignment['assignment_id']]);
                        $submission_count = $stmt->fetchColumn();
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($assignment['course_no']); ?></td>
                            <td><?php echo htmlspecialchars($assignment['course_title']); ?></td>
                            <td><?php echo date('F j, Y, g:i a', strtotime($assignment['deadline'])); ?></td>
                            <td><?php echo $submission_count; ?> submission(s)</td>
                            <td>
                                <a href="assignments.php?action=view_submissions&id=<?php echo $assignment['assignment_id']; ?>" class="btn btn-sm btn-info">View Submissions</a>
                                <a href="assignments.php?action=edit&id=<?php echo $assignment['assignment_id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">You haven't created any assignments yet.</div>
    <?php endif; ?>
    
    <?php
} else {
    // Student view - show assignments they can submit with search functionality
    $search_course_no = isset($_GET['course_no']) ? trim($_GET['course_no']) : '';
    $search_course_title = isset($_GET['course_title']) ? trim($_GET['course_title']) : '';

    $query = "SELECT a.*, 
             (SELECT COUNT(*) FROM submissions s WHERE s.assignment_id = a.assignment_id AND s.student_id = ?) as submitted
             FROM assignments a
             WHERE 1=1";
    
    $params = [$_SESSION['user_id']];
    
    if (!empty($search_course_no)) {
        $query .= " AND a.course_no LIKE ?";
        $params[] = "%$search_course_no%";
    }
    
    if (!empty($search_course_title)) {
        $query .= " AND a.course_title LIKE ?";
        $params[] = "%$search_course_title%";
    }
    
    $query .= " ORDER BY a.deadline DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $assignments = $stmt->fetchAll();
    ?>
    
    <h2 class="mb-4">Available Assignments</h2>
    
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Search Assignments</h5>
        </div>
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-6">
                    <label for="course_no" class="form-label">Course Number</label>
                    <input type="text" class="form-control" id="course_no" name="course_no" 
                           value="<?php echo htmlspecialchars($search_course_no); ?>">
                </div>
                <div class="col-md-6">
                    <label for="course_title" class="form-label">Course Title</label>
                    <input type="text" class="form-control" id="course_title" name="course_title" 
                           value="<?php echo htmlspecialchars($search_course_title); ?>">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Search</button>
                    <a href="assignments.php" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>
    
    <?php if ($assignments): ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Course No</th>
                        <th>Course Title</th>
                        <th>Deadline</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($assignments as $assignment): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($assignment['course_no']); ?></td>
                            <td><?php echo htmlspecialchars($assignment['course_title']); ?></td>
                            <td><?php echo date('F j, Y, g:i a', strtotime($assignment['deadline'])); ?></td>
                            <td>
                                <?php if ($assignment['submitted'] > 0): ?>
                                    <span class="badge bg-success">Submitted</span>
                                <?php elseif (strtotime($assignment['deadline']) < time()): ?>
                                    <span class="badge bg-danger">Missed</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($assignment['submitted'] == 0 && strtotime($assignment['deadline']) > time()): ?>
                                    <a href="assignments.php?action=submit&id=<?php echo $assignment['assignment_id']; ?>" class="btn btn-sm btn-primary">Submit</a>
                                <?php endif; ?>
                                <a href="assignments.php?action=view&id=<?php echo $assignment['assignment_id']; ?>" class="btn btn-sm btn-info">View</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">No assignments found matching your criteria.</div>
    <?php endif; ?>
    
    <?php
}

include 'includes/footer.php';
?>