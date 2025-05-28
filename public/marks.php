<?php
require 'includes/config.php';

if ($_SESSION['role'] === 'teacher') {
    requireTeacher();
} else {
    requireStudent();
}

// Teacher: Publish marks
if ($_SESSION['role'] === 'teacher' && isset($_GET['action']) && $_GET['action'] === 'publish') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $course_no = trim($_POST['course_no']);
        $course_title = trim($_POST['course_title']);
        $exam_name = trim($_POST['exam_name']);
        $exam_type = $_POST['exam_type'];
        $marks_data = $_POST['marks'];
        
        $errors = [];
        
        if (empty($course_no)) {
            $errors[] = "Course number is required.";
        }
        
        if (empty($course_title)) {
            $errors[] = "Course title is required.";
        }
        
        if (empty($exam_name)) {
            $errors[] = "Exam name is required.";
        }
        
        if (empty($exam_type) || !in_array($exam_type, ['Class Test', 'Sessional', 'Assignment'])) {
            $errors[] = "Invalid exam type.";
        }
        
        if (empty($marks_data)) {
            $errors[] = "No marks data provided.";
        }
        
        if (empty($errors)) {
            // Begin transaction
            $pdo->beginTransaction();
            
            try {
                // First, delete any existing marks for this exam
                $stmt = $pdo->prepare("DELETE FROM marks WHERE course_no = ? AND course_title = ? AND exam_name = ? AND exam_type = ?");
                $stmt->execute([$course_no, $course_title, $exam_name, $exam_type]);
                
                // Insert new marks records
                $stmt = $pdo->prepare("INSERT INTO marks (course_no, course_title, student_id, exam_name, exam_type, mark, published_by) 
                                      VALUES (?, ?, ?, ?, ?, ?, ?)");
                
                foreach ($marks_data as $student_id => $mark) {
                    if (!is_numeric($mark) || $mark < 0) {
                        throw new Exception("Invalid mark for student $student_id");
                    }
                    
                    $stmt->execute([$course_no, $course_title, $student_id, $exam_name, $exam_type, $mark, $_SESSION['user_id']]);
                }
                
                $pdo->commit();
                $_SESSION['success_message'] = "Marks published successfully!";
                header("Location: marks.php");
                exit();
            } catch (Exception $e) {
                $pdo->rollBack();
                $errors[] = "Failed to publish marks: " . $e->getMessage();
            }
        }
    }
    
    // Get list of students
    $stmt = $pdo->prepare("SELECT * FROM users WHERE role = 'student' ORDER BY name");
    $stmt->execute();
    $students = $stmt->fetchAll();
    
    include 'includes/header.php';
    ?>
    
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2 class="h4 mb-0">Publish Marks</h2>
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
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="exam_name" class="form-label">Exam Name</label>
                                <input type="text" class="form-control" id="exam_name" name="exam_name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="exam_type" class="form-label">Exam Type</label>
                                <select class="form-select" id="exam_type" name="exam_type" required>
                                    <option value="">Select Type</option>
                                    <option value="Class Test">Class Test</option>
                                    <option value="Sessional">Sessional</option>
                                    <option value="Assignment">Assignment</option>
                                </select>
                            </div>
                        </div>
                        
                        <h5 class="mb-3">Student Marks</h5>
                        
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Student ID</th>
                                        <th>Name</th>
                                        <th>Marks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($students as $student): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($student['user_id']); ?></td>
                                            <td><?php echo htmlspecialchars($student['name']); ?></td>
                                            <td>
                                                <input type="number" step="0.01" min="0" class="form-control" name="marks[<?php echo $student['user_id']; ?>]">
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Publish Marks</button>
                        <a href="marks.php" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <?php
    include 'includes/footer.php';
    exit();
}

// View marks
include 'includes/header.php';

if (isset($_SESSION['success_message'])) {
    echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_message'])) {
    echo '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
    unset($_SESSION['error_message']);
}

// Search functionality
$search_course_no = isset($_GET['course_no']) ? trim($_GET['course_no']) : '';
$search_course_title = isset($_GET['course_title']) ? trim($_GET['course_title']) : '';

if ($_SESSION['role'] === 'teacher') {
    ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Published Marks</h2>
        <a href="marks.php?action=publish" class="btn btn-primary">Publish New Marks</a>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Search Marks</h5>
        </div>
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-6">
                    <label for="course_no" class="form-label">Course Number</label>
                    <input type="text" class="form-control" id="course_no" name="course_no" value="<?php echo htmlspecialchars($search_course_no); ?>">
                </div>
                <div class="col-md-6">
                    <label for="course_title" class="form-label">Course Title</label>
                    <input type="text" class="form-control" id="course_title" name="course_title" value="<?php echo htmlspecialchars($search_course_title); ?>">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Search</button>
                    <a href="marks.php" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>
    
    <?php
    // Get distinct courses with marks
    $query = "SELECT DISTINCT course_no, course_title FROM marks WHERE 1=1";
    $params = [];
    
    if (!empty($search_course_no)) {
        $query .= " AND course_no LIKE ?";
        $params[] = "%$search_course_no%";
    }
    
    if (!empty($search_course_title)) {
        $query .= " AND course_title LIKE ?";
        $params[] = "%$search_course_title%";
    }
    
    $query .= " ORDER BY course_no";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $courses = $stmt->fetchAll();
    
    if ($courses): ?>
        <div class="accordion" id="marksAccordion">
            <?php foreach ($courses as $course): ?>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading<?php echo $course['course_no']; ?>">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $course['course_no']; ?>" aria-expanded="false" aria-controls="collapse<?php echo $course['course_no']; ?>">
                            <?php echo htmlspecialchars($course['course_no']); ?> - <?php echo htmlspecialchars($course['course_title']); ?>
                        </button>
                    </h2>
                    <div id="collapse<?php echo $course['course_no']; ?>" class="accordion-collapse collapse" aria-labelledby="heading<?php echo $course['course_no']; ?>" data-bs-parent="#marksAccordion">
                        <div class="accordion-body">
                            <?php
                            // Get exam types for this course
                            $stmt = $pdo->prepare("SELECT DISTINCT exam_name, exam_type FROM marks 
                                                  WHERE course_no = ? AND course_title = ?
                                                  ORDER BY exam_type, exam_name");
                            $stmt->execute([$course['course_no'], $course['course_title']]);
                            $exams = $stmt->fetchAll();
                            
                            foreach ($exams as $exam):
                                ?>
                                <h6 class="mt-3"><?php echo htmlspecialchars($exam['exam_name']); ?> (<?php echo $exam['exam_type']; ?>)</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped">
                                        <thead>
                                            <tr>
                                                <th>Student ID</th>
                                                <th>Name</th>
                                                <th>Marks</th>
                                                <th>Published By</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $stmt = $pdo->prepare("SELECT m.*, u.name as student_name, t.name as teacher_name 
                                                                  FROM marks m
                                                                  JOIN users u ON m.student_id = u.user_id
                                                                  JOIN users t ON m.published_by = t.user_id
                                                                  WHERE m.course_no = ? AND m.course_title = ? AND m.exam_name = ? AND m.exam_type = ?
                                                                  ORDER BY u.name");
                                            $stmt->execute([$course['course_no'], $course['course_title'], $exam['exam_name'], $exam['exam_type']]);
                                            $records = $stmt->fetchAll();
                                            
                                            foreach ($records as $record):
                                                ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($record['student_id']); ?></td>
                                                    <td><?php echo htmlspecialchars($record['student_name']); ?></td>
                                                    <td><?php echo $record['mark']; ?></td>
                                                    <td><?php echo htmlspecialchars($record['teacher_name']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">No marks records found.</div>
    <?php endif; ?>
    
    <?php
} else {
    // Student view
    ?>
    <h2 class="mb-4">My Marks</h2>
    
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Search Marks</h5>
        </div>
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-6">
                    <label for="course_no" class="form-label">Course Number</label>
                    <input type="text" class="form-control" id="course_no" name="course_no" value="<?php echo htmlspecialchars($search_course_no); ?>">
                </div>
                <div class="col-md-6">
                    <label for="course_title" class="form-label">Course Title</label>
                    <input type="text" class="form-control" id="course_title" name="course_title" value="<?php echo htmlspecialchars($search_course_title); ?>">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Search</button>
                    <a href="marks.php" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>
    
    <?php
    // Get marks records for the student
    $query = "SELECT m.*, c.course_title 
              FROM marks m
              JOIN (SELECT DISTINCT course_no, course_title FROM marks) c ON m.course_no = c.course_no
              WHERE m.student_id = ?";
    $params = [$_SESSION['user_id']];
    
    if (!empty($search_course_no)) {
        $query .= " AND m.course_no LIKE ?";
        $params[] = "%$search_course_no%";
    }
    
    if (!empty($search_course_title)) {
        $query .= " AND c.course_title LIKE ?";
        $params[] = "%$search_course_title%";
    }
    
    $query .= " ORDER BY m.course_no, m.exam_type, m.exam_name";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $marks = $stmt->fetchAll();
    
    if ($marks): ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Course No</th>
                        <th>Course Title</th>
                        <th>Exam Name</th>
                        <th>Exam Type</th>
                        <th>Marks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($marks as $record): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($record['course_no']); ?></td>
                            <td><?php echo htmlspecialchars($record['course_title']); ?></td>
                            <td><?php echo htmlspecialchars($record['exam_name']); ?></td>
                            <td><?php echo $record['exam_type']; ?></td>
                            <td><?php echo $record['mark']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Marks Summary</h5>
            </div>
            <div class="card-body">
                <?php
                // Get marks summary by course
                $query = "SELECT m.course_no, c.course_title, 
                         AVG(m.mark) as average_mark,
                         MAX(m.mark) as highest_mark,
                         MIN(m.mark) as lowest_mark
                         FROM marks m
                         JOIN (SELECT DISTINCT course_no, course_title FROM marks) c ON m.course_no = c.course_no
                         WHERE m.student_id = ?";
                $params = [$_SESSION['user_id']];
                
                if (!empty($search_course_no)) {
                    $query .= " AND m.course_no LIKE ?";
                    $params[] = "%$search_course_no%";
                }
                
                if (!empty($search_course_title)) {
                    $query .= " AND c.course_title LIKE ?";
                    $params[] = "%$search_course_title%";
                }
                
                $query .= " GROUP BY m.course_no, c.course_title";
                
                $stmt = $pdo->prepare($query);
                $stmt->execute($params);
                $summary = $stmt->fetchAll();
                
                foreach ($summary as $course):
                    ?>
                    <div class="mb-3">
                        <h6><?php echo htmlspecialchars($course['course_no']); ?> - <?php echo htmlspecialchars($course['course_title']); ?></h6>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">Average</h6>
                                        <p class="card-text display-6"><?php echo round($course['average_mark'], 2); ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">Highest</h6>
                                        <p class="card-text display-6"><?php echo round($course['highest_mark'], 2); ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">Lowest</h6>
                                        <p class="card-text display-6"><?php echo round($course['lowest_mark'], 2); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-info">No marks records found.</div>
    <?php endif; ?>
    <?php
}

include 'includes/footer.php';
?>