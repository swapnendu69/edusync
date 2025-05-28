<?php
require 'includes/config.php';
requireLogin();

// Get user information
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    $_SESSION['error_message'] = "User not found.";
    header("Location: dashboard.php");
    exit();
}

// Update profile
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Name is required.";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    
    // Check if email is already taken by another user
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND user_id != ?");
    $stmt->execute([$email, $_SESSION['user_id']]);
    $existingUser = $stmt->fetch();
    
    if ($existingUser) {
        $errors[] = "This email is already registered by another user.";
    }
    
    // Password change
    $password_changed = false;
    if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
        if (empty($current_password)) {
            $errors[] = "Current password is required to change password.";
        } elseif (!password_verify($current_password, $user['password'])) {
            $errors[] = "Current password is incorrect.";
        }
        
        if (empty($new_password)) {
            $errors[] = "New password is required.";
        } elseif (strlen($new_password) < 8) {
            $errors[] = "New password must be at least 8 characters long.";
        }
        
        if ($new_password !== $confirm_password) {
            $errors[] = "New passwords do not match.";
        }
        
        if (empty($errors)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $password_changed = true;
        }
    }
    
    if (empty($errors)) {
        if ($password_changed) {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, password = ? WHERE user_id = ?");
            $success = $stmt->execute([$name, $email, $hashed_password, $_SESSION['user_id']]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE user_id = ?");
            $success = $stmt->execute([$name, $email, $_SESSION['user_id']]);
        }
        
        if ($success) {
            $_SESSION['name'] = $name;
            $_SESSION['email'] = $email;
            $_SESSION['success_message'] = "Profile updated successfully!";
            header("Location: profile.php");
            exit();
        } else {
            $errors[] = "Failed to update profile. Please try again.";
        }
    }
}

// Delete account
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $confirm = isset($_POST['confirm']) ? $_POST['confirm'] : '';
        
        if ($confirm === 'DELETE') {
            // Begin transaction
            $pdo->beginTransaction();
            
            try {
                // Delete user's records from all tables
                $tables = ['assignments', 'submissions', 'resources', 'attendance', 'marks'];
                foreach ($tables as $table) {
                    $column = ($_SESSION['role'] === 'teacher') ? 'teacher_id' : 'student_id';
                    $stmt = $pdo->prepare("DELETE FROM $table WHERE $column = ?");
                    $stmt->execute([$_SESSION['user_id']]);
                }
                
                // Finally, delete the user
                $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                
                $pdo->commit();
                
                // Logout and redirect to home
                session_destroy();
                $_SESSION['success_message'] = "Your account has been deleted successfully.";
                header("Location: index.php");
                exit();
            } catch (Exception $e) {
                $pdo->rollBack();
                $_SESSION['error_message'] = "Failed to delete account: " . $e->getMessage();
                header("Location: profile.php");
                exit();
            }
        } else {
            $_SESSION['error_message'] = "Please type 'DELETE' to confirm account deletion.";
            header("Location: profile.php");
            exit();
        }
    }
    
    include 'includes/header.php';
    ?>
    
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h2 class="h4 mb-0">Delete Account</h2>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <strong>Warning!</strong> This action cannot be undone. All your data will be permanently deleted.
                    </div>
                    
                    <p>To confirm, please type <strong>DELETE</strong> in the box below:</p>
                    
                    <form method="post">
                        <div class="mb-3">
                            <input type="text" class="form-control" name="confirm" required>
                        </div>
                        
                        <button type="submit" class="btn btn-danger">Permanently Delete My Account</button>
                        <a href="profile.php" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <?php
    include 'includes/footer.php';
    exit();
}

include 'includes/header.php';

if (isset($_SESSION['success_message'])) {
    echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_message'])) {
    echo '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
    unset($_SESSION['error_message']);
}
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h2 class="h4 mb-0">My Profile</h2>
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
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="user_id" class="form-label"><?php echo ucfirst($user['role']); ?> ID</label>
                        <input type="text" class="form-control" id="user_id" value="<?php echo htmlspecialchars($user['user_id']); ?>" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <input type="text" class="form-control" id="role" value="<?php echo ucfirst($user['role']); ?>" readonly>
                    </div>
                    
                    <hr>
                    
                    <h5 class="mb-3">Change Password</h5>
                    
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="current_password" name="current_password">
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" minlength="8">
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" minlength="8">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Update Profile</button>
                    <a href="profile.php?action=delete" class="btn btn-danger float-end">Delete Account</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>