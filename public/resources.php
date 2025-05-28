<?php
require 'includes/config.php';

if ($_SESSION['role'] === 'teacher') {
    requireTeacher();
} else {
    requireStudent();
}

// Teacher: Upload resource
if ($_SESSION['role'] === 'teacher' && isset($_GET['action']) && $_GET['action'] === 'upload') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $course_no = trim($_POST['course_no']);
        $course_title = trim($_POST['course_title']);
        
        $errors = [];
        
        if (empty($course_no)) {
            $errors[] = "Course number is required.";
        }
        
        if (empty($course_title)) {
            $errors[] = "Course title is required.";
        }
        
        // Handle file upload
        $file_path = null;
        if (isset($_FILES['resource_file']) && $_FILES['resource_file']['error'] === UPLOAD_ERR_OK) {
            $file_name = $_FILES['resource_file']['name'];
            $file_tmp = $_FILES['resource_file']['tmp_name'];
            $file_size = $_FILES['resource_file']['size'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            if ($file_size > MAX_RESOURCE_SIZE) {
                $errors[] = "File size exceeds maximum allowed size (10MB).";
            }
            
            if (!in_array($file_ext, $GLOBALS['allowedResourceTypes'])) {
                $errors[] = "File type not allowed. Allowed types: " . implode(', ', $GLOBALS['allowedResourceTypes']);
            }
            
            if (empty($errors)) {
                $new_file_name = uniqid('res_', true) . '.' . $file_ext;
                $upload_path = RESOURCE_UPLOAD_PATH . $new_file_name;
                
                if (move_uploaded_file($file_tmp, $upload_path)) {
                    $file_path = $upload_path;
                } else {
                    $errors[] = "Failed to upload file.";
                }
            }
        } else {
            $errors[] = "Resource file is required.";
        }
        
        if (empty($errors)) {
            $stmt = $pdo->prepare("INSERT INTO resources (course_no, course_title, teacher_id, file_path, file_name) 
                                  VALUES (?, ?, ?, ?, ?)");
            if ($stmt->execute([$course_no, $course_title, $_SESSION['user_id'], $file_path, $file_name])) {
                $_SESSION['success_message'] = "Resource uploaded successfully!";
                header("Location: resources.php");
                exit();
            } else {
                $errors[] = "Failed to upload resource. Please try again.";
            }
        }
    }
    
    include 'includes/header.php';
    ?>
    
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2 class="h4 mb-0">Upload New Resource</h2>
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
                            <label for="resource_file" class="form-label">Resource File</label>
                            <input type="file" class="form-control" id="resource_file" name="resource_file" required>
                            <div class="form-text">Allowed file types: <?php echo implode(', ', $GLOBALS['allowedResourceTypes']); ?> (Max 10MB)</div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Upload Resource</button>
                        <a href="resources.php" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <?php
    include 'includes/footer.php';
    exit();
}

// View resources
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

$query = "SELECT * FROM resources WHERE 1=1";
$params = [];

if (!empty($search_course_no)) {
    $query .= " AND course_no LIKE ?";
    $params[] = "%$search_course_no%";
}

if (!empty($search_course_title)) {
    $query .= " AND course_title LIKE ?";
    $params[] = "%$search_course_title%";
}

$query .= " ORDER BY uploaded_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$resources = $stmt->fetchAll();

if ($_SESSION['role'] === 'teacher') {
    ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Uploaded Resources</h2>
        <a href="resources.php?action=upload" class="btn btn-primary">Upload New Resource</a>
    </div>
    <?php
} else {
    ?>
    <h2 class="mb-4">Course Resources</h2>
    <?php
}
?>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Search Resources</h5>
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
                <a href="resources.php" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<?php if ($resources): ?>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Course No</th>
                    <th>Course Title</th>
                    <th>File Name</th>
                    <th>Uploaded At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($resources as $resource): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($resource['course_no']); ?></td>
                        <td><?php echo htmlspecialchars($resource['course_title']); ?></td>
                        <td><?php echo htmlspecialchars($resource['file_name']); ?></td>
                        <td><?php echo date('F j, Y, g:i a', strtotime($resource['uploaded_at'])); ?></td>
                        <td>
                            <a href="<?php echo $resource['file_path']; ?>" class="btn btn-sm btn-primary" download>Download</a>
                            <?php if ($_SESSION['role'] === 'teacher' && $resource['teacher_id'] === $_SESSION['user_id']): ?>
                                <a href="resources.php?action=delete&id=<?php echo $resource['resource_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this resource?')">Delete</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="alert alert-info">No resources found matching your criteria.</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>