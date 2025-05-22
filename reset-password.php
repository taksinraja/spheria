<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/db.php';

// Check if token is provided
// echo $_GET['token'];
// exit;
// Check for post request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    // Validate passwords match
    if ($password !== $confirm_password) {
        $_SESSION['error'] = "New passwords do not match";
        header("Location: reset-password.php?token=" . $_GET['token']);
        exit();
    }

    // Validate password length
    if (strlen($password) < 8) {
        $_SESSION['error'] = "Password must be at least 8 characters long";
        header("Location: reset-password.php?token=". $_GET['token']);
        exit();
    }
    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Update the password in the database
    $sql = "UPDATE users SET password = ? WHERE user_id = ?";
    $stmt = $db->prepare($sql);

    if (!$stmt) {
        $_SESSION['error'] = "Database error occurred";
        header("Location: reset-password.php?token=" . $_GET['token']);
        exit();
    }

    $stmt->bind_param("si", $hashed_password, $user_id);

    if (!$stmt->execute()) {
        $_SESSION['error'] = "Failed to update password";
        header("Location: reset-password.php?token=" . $_GET['token']);
        exit();
    }
    $stmt->close();

    // Redirect to login page
    $_SESSION['success'] = "Password updated successfully. You can now login with your new password.";
    header("Location: login.php");
    exit();
}


if (!isset($_GET['token']) || empty($_GET['token'])) {
    $_SESSION['error'] = "Invalid or missing reset token";
    header("Location: login.php");
    exit();
}

$token = $_GET['token'];

// Verify token and check if it's expired
$sql = "SELECT user_id 
        FROM password_reset 
        WHERE token = ?";

$stmt = $db->prepare($sql);
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$stmt->close();
$user_id = $result['user_id'];

if (!$user_id) {
    $_SESSION['error'] = "Invalid or expired reset link. Please request a new one.";
    header("Location: forgot-password.php");
    exit();
} else {
    $sql = "SELECT username
            FROM users
            WHERE user_id =?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $username = $result['username'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spheria - Reset Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/auth.css">
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@700&display=swap" rel="stylesheet">
    <link rel="shortcut icon" href="assets/images/logo2.png" type="image/x-icon">
</head>
<body>
    <div class="auth-container">
        <div class="auth-image">
            <div class="auth-quote">
                <h2>Create New Password</h2>
                <p>Choose a strong password to keep your account secure.</p>
            </div>
        </div>
        <div class="auth-card">
            <?php
            if(isset($_SESSION['error'])) {
                echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error']) . '</div>';
                unset($_SESSION['error']);
            }
            ?>
            
            <div class="auth-header">
                <h2>Reset Password</h2>
                <p>Hello, <?php echo htmlspecialchars($username); ?>! Create your new password below.</p>
            </div>
            
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?token=' . htmlspecialchars($token);  ?>" method="POST" class="auth-form">
                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">
                
                <div class="form-group">
                    <div class="password-input">
                        <input type="password" name="password" class="form-control" placeholder="New Password" required minlength="6">
                        <i class="fas fa-eye toggle-password"></i>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="password-input">
                        <input type="password" name="confirm_password" class="form-control" placeholder="Confirm New Password" required minlength="6">
                        <i class="fas fa-eye toggle-password"></i>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary w-100">Reset Password</button>
            </form>
            
            <div class="auth-footer mt-4">
                <p>Remember your password? <a href="login.php">Back to Login</a></p>
            </div>
        </div>
    </div>

    <script src="assets/js/auth.js"></script>
</body>
</html>