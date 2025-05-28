<?php
require 'includes/config.php';

if ($_SESSION['role'] === 'teacher') {
    requireTeacher();
} else {
    requireStudent();
}

// Teacher: Mark attendance
if ($_SESSION['role'] === 'teacher' && isset($_GET['action']) && $_GET['action'] === 'mark') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $course_no = trim($_POST['course_no']);
        $course_title = trim($_POST['course_title']);
        $date = $_POST['date'];
        $attendance_data = $_POST['attendance'];
        
        $errors = [];
        
        if (empty($course_no)) {
            $errors[] = "Course number is required.";
        }
        
        if (empty($course_title)) {
            $errors[] = "Course title is required.";
        }
        
        if (empty($date)) {
            $errors[] = "Date is required.";
        }
        
        if (empty($attendance_data)) {
            $errors[] = "No attendance data provided.";
        }
        
        if (empty($errors)) {
            // Begin transaction
            $pdo->beginTransaction();
            
            try {
                // First, delete any existing attendance for this course and date
                $stmt = $pdo->prepare("DELETE FROM attendance WHERE course_no = ? AND course_title = ? AND date = ?");
                $stmt->execute([$course_no, $course_title, $date]);
                
                // Insert new attendance records
                $stmt = $pdo->prepare("INSERT INTO attendance (course_no, course_title, student_id, status, date, marked_by) 
                                      VALUES (?, ?, ?, ?, ?, ?)");
                
                foreach ($attendance_data as $student_id => $status) {
                    if (!in_array($status, ['Present', 'Absent'])) {
                        throw new Exception("Invalid attendance status for student $student_id");
                    }
                    
                    $stmt->execute([$course_no, $course_title, $student_id, $status, $date, $_SESSION['user_id']]);
                }
                
                $pdo->commit();
                $_SESSION['success_message'] = "Attendance marked successfully!";
                header("Location: attendance.php");
                exit();
            } catch (Exception $e) {
                $pdo->rollBack();
                $errors[] = "Failed to mark attendance: " . $e->getMessage();
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
                    <h2 class="h4 mb-0">Mark Attendance</h2>
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
                            <label for="date" class="form-label">Date</label>
                            <input type="date" class="form-control" id="date" name="date" required value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        
                        <h5 class="mb-3">Student Attendance</h5>
                        
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Student ID</th>
                                        <th>Name</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($students as $student): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($student['user_id']); ?></td>
                                            <td><?php echo htmlspecialchars($student['name']); ?></td>
                                            <td>
                                                <select class="form-select" name="attendance[<?php echo $student['user_id']; ?>]">
                                                    <option value="Present">Present</option>
                                                    <option value="Absent">Absent</option>
                                                </select>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Submit Attendance</button>
                        <a href="attendance.php" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <?php
    include 'includes/footer.php';
    exit();
}

// View attendance
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
        <h2>Attendance Records</h2>
        <a href="attendance.php?action=mark" class="btn btn-primary">Mark Attendance</a>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Search Attendance</h5>
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
                    <a href="attendance.php" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>
    
    <?php
    // Get distinct courses with attendance records
    $query = "SELECT DISTINCT course_no, course_title FROM attendance WHERE 1=1";
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
        <div class="accordion" id="attendanceAccordion">
            <?php foreach ($courses as $course): ?>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading<?php echo $course['course_no']; ?>">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $course['course_no']; ?>" aria-expanded="false" aria-controls="collapse<?php echo $course['course_no']; ?>">
                            <?php echo htmlspecialchars($course['course_no']); ?> - <?php echo htmlspecialchars($course['course_title']); ?>
                        </button>
                    </h2>
                    <div id="collapse<?php echo $course['course_no']; ?>" class="accordion-collapse collapse" aria-labelledby="heading<?php echo $course['course_no']; ?>" data-bs-parent="#attendanceAccordion">
                        <div class="accordion-body">
                            <?php
                            // Get attendance dates for this course
                            $stmt = $pdo->prepare("SELECT DISTINCT date FROM attendance 
                                                  WHERE course_no = ? AND course_title = ?
                                                  ORDER BY date DESC");
                            $stmt->execute([$course['course_no'], $course['course_title']]);
                            $dates = $stmt->fetchAll();
                            
                            foreach ($dates as $date):
                                ?>
                                <h6 class="mt-3"><?php echo date('F j, Y', strtotime($date['date'])); ?></h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped">
                                        <thead>
                                            <tr>
                                                <th>Student ID</th>
                                                <th>Name</th>
                                                <th>Status</th>
                                                <th>Marked By</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $stmt = $pdo->prepare("SELECT a.*, u.name as student_name, t.name as teacher_name 
                                                                  FROM attendance a
                                                                  JOIN users u ON a.student_id = u.user_id
                                                                  JOIN users t ON a.marked_by = t.user_id
                                                                  WHERE a.course_no = ? AND a.course_title = ? AND a.date = ?
                                                                  ORDER BY u.name");
                                            $stmt->execute([$course['course_no'], $course['course_title'], $date['date']]);
                                            $records = $stmt->fetchAll();
                                            
                                            foreach ($records as $record):
                                                ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($record['student_id']); ?></td>
                                                    <td><?php echo htmlspecialchars($record['student_name']); ?></td>
                                                    <td>
                                                        <span class="badge <?php echo $record['status'] === 'Present' ? 'bg-success' : 'bg-danger'; ?>">
                                                            <?php echo $record['status']; ?>
                                                        </span>
                                                    </td>
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
        <div class="alert alert-info">No attendance records found.</div>
    <?php endif; ?>
    
    <?php
} else {
    // Student view
    ?>
    <h2 class="mb-4">My Attendance</h2>
    
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Search Attendance</h5>
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
                    <a href="attendance.php" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>
    
    <?php
    // Get attendance records for the student
    $query = "SELECT a.*, c.course_title 
              FROM attendance a
              JOIN (SELECT DISTINCT course_no, course_title FROM attendance) c ON a.course_no = c.course_no
              WHERE a.student_id = ?";
    $params = [$_SESSION['user_id']];
    
    if (!empty($search_course_no)) {
        $query .= " AND a.course_no LIKE ?";
        $params[] = "%$search_course_no%";
    }
    
    if (!empty($search_course_title)) {
        $query .= " AND c.course_title LIKE ?";
        $params[] = "%$search_course_title%";
    }
    
    $query .= " ORDER BY a.date DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $attendance = $stmt->fetchAll();
    
    if ($attendance): ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Course No</th>
                        <th>Course Title</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($attendance as $record): ?>
                        <tr>
                            <td><?php echo date('F j, Y', strtotime($record['date'])); ?></td>
                            <td><?php echo htmlspecialchars($record['course_no']); ?></td>
                            <td><?php echo htmlspecialchars($record['course_title']); ?></td>
                            <td>
                                <span class="badge <?php echo $record['status'] === 'Present' ? 'bg-success' : 'bg-danger'; ?>">
                                    <?php echo $record['status']; ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Attendance Summary</h5>
            </div>
            <div class="card-body">
                <?php
                // Get attendance summary by course
                $query = "SELECT a.course_no, c.course_title, 
                         SUM(CASE WHEN a.status = 'Present' THEN 1 ELSE 0 END) as present_count,
                         COUNT(*) as total_count
                         FROM attendance a
                         JOIN (SELECT DISTINCT course_no, course_title FROM attendance) c ON a.course_no = c.course_no
                         WHERE a.student_id = ?";
                $params = [$_SESSION['user_id']];
                
                if (!empty($search_course_no)) {
                    $query .= " AND a.course_no LIKE ?";
                    $params[] = "%$search_course_no%";
                }
                
                if (!empty($search_course_title)) {
                    $query .= " AND c.course_title LIKE ?";
                    $params[] = "%$search_course_title%";
                }
                
                $query .= " GROUP BY a.course_no, c.course_title";
                
                $stmt = $pdo->prepare($query);
                $stmt->execute($params);
                $summary = $stmt->fetchAll();
                
                foreach ($summary as $course):
                    $percentage = ($course['present_count'] / $course['total_count']) * 100;
                    ?>
                    <div class="mb-3">
                        <h6><?php echo htmlspecialchars($course['course_no']); ?> - <?php echo htmlspecialchars($course['course_title']); ?></h6>
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" style="width: <?php echo $percentage; ?>%" 
                                 aria-valuenow="<?php echo $percentage; ?>" aria-valuemin="0" aria-valuemax="100">
                                <?php echo round($percentage, 1); ?>%
                            </div>
                        </div>
                        <small class="text-muted"><?php echo $course['present_count']; ?> present out of <?php echo $course['total_count']; ?> classes</small>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-info">No attendance records found.</div>
    <?php endif; ?>
    <?php
}

include 'includes/footer.php';
?>