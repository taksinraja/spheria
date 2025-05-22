<?php
session_start();
require_once 'includes/config.php';

if(isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spheria - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/auth.css">
    <link rel="shortcut icon" href="assets/images/logo2.png" type="image/x-icon">
</head>
<body>
    <div class="auth-container">
        <div class="auth-image">
        <!-- <div class="auth-brand">
            <img src="assets/images/promo-image.png" alt="Spheria">
        </div> -->
        <div class="auth-quote">
            <h2>Capturing Moments,<br>Creating Memories</h2>
            <p>Join our community and share your story with the world.</p>
        </div>
        </div>
        <div class="auth-card">
            <div class="auth-header">
                <!-- <img src="assets/images/spheria-logo.png" alt="Spheria" class="auth-logo"> -->
                <h2>Welcome back</h2>
                <p class="auth-footer">
                    Don't have an account? <a href="register.php">Sign up</a>
                </p>
            </div>
            <form action="includes/auth/login.php" method="POST" class="auth-form">
            <?php
            if(isset($_SESSION['error'])) {
                echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error']) . '</div>';
                unset($_SESSION['error']);
            }
            if(isset($_SESSION['success'])) {
                echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success']) . '</div>';
                unset($_SESSION['success']);
            }
            ?>
                <div class="form-group">
                    <!-- <label>Email</label> -->
                    <input type="email" name="email" placeholder="Email" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <!-- <label>Password</label> -->
                    <div class="password-input">
                        <input type="password" name="password" placeholder="Password" class="form-control" required>
                        <i class="fas fa-eye toggle-password"></i>
                    </div>
                </div>
                
                <div class="form-options">
                    <!-- <label class="remember-me">
                        <input type="checkbox" name="remember">
                        Remember me
                    </label> -->
                    <a href="forgot-password.php" class="forgot-link">Forgot password?</a>
                </div>
                
                <button type="submit" class="btn btn-primary w-100">Sign in</button>
            </form>
            
            <!-- <div class="auth-divider">
                <span>or continue with</span>
            </div> -->
            
            <!-- <div class="social-login">
                <a href="#" class="btn btn-outline-secondary">
                    <img src="assets/images/google.png" alt="Google">
                    Google
                </a>
                <a href="#" class="btn btn-outline-secondary">
                    <img src="assets/images/github.png" alt="GitHub">
                    GitHub
                </a>
            </div> -->
        </div>
    </div>

    <script src="assets/js/auth.js"></script>
</body>
</html>