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
    <title>Spheria - Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/auth.css">
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@700&display=swap" rel="stylesheet">
    <link rel="shortcut icon" href="assets/images/logo2.png" type="image/x-icon">
    <style>
        .username-feedback {
            font-size: 0.85rem;
            margin-top: 0.25rem;
        }
        .is-available {
            color: #198754;
        }
        .not-available {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-image">
            <!-- <div class="auth-brand">
                <img src="assets/images/spheria-logo-white.png" alt="Spheria">
            </div> -->
            <div class="auth-quote">
                <h2>Capturing Moments,<br>Creating Memories</h2>
                <p>Join our community and share your story with the world.</p>
            </div>
        </div>
        <div class="auth-card">

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

            
            <div class="auth-header">
                <!-- <img src="assets/images/spheria-logo.png" alt="Spheria" class="auth-logo"> -->
                <h2>Create an account</h2>
                <p class="auth-footer">Already have an account? <a href="login.php">Sign in</a></p>
            </div>
            
            <form action="includes/auth/register.php" method="POST" class="auth-form">
                <div class="form-group">
                    <input type="text" name="fullname" class="form-control" placeholder="Full Name" required>
                </div>

                <div class="form-group">
                    <input type="text" name="username" id="username" class="form-control" placeholder="Username" required>
                    <div id="username-feedback" class="username-feedback"></div>
                </div>
                
                <div class="form-group">
                    <input type="email" name="email" class="form-control" placeholder="Email" required>
                </div>
                
                <div class="form-group">
                    <div class="password-input">
                        <input type="password" name="password" class="form-control" placeholder="Password" required>
                        <i class="fas fa-eye toggle-password"></i>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary w-100">Create account</button>
            </form>
            
            <!-- <div class="auth-divider">
                <span>or continue with</span>
            </div>
            
            <div class="social-login">
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const usernameInput = document.getElementById('username');
            const usernameFeedback = document.getElementById('username-feedback');
            let typingTimer;
            const doneTypingInterval = 500; // 500 ms delay

            // On keyup, start the countdown
            usernameInput.addEventListener('keyup', function() {
                clearTimeout(typingTimer);
                if (usernameInput.value) {
                    typingTimer = setTimeout(checkUsernameAvailability, doneTypingInterval);
                } else {
                    usernameFeedback.textContent = '';
                    usernameFeedback.className = 'username-feedback';
                }
            });

            function checkUsernameAvailability() {
                const username = usernameInput.value.trim();
                
                if (username.length < 3) {
                    usernameFeedback.textContent = 'Username must be at least 3 characters';
                    usernameFeedback.className = 'username-feedback not-available';
                    return;
                }
                
                // Show loading message
                usernameFeedback.textContent = 'Checking availability...';
                usernameFeedback.className = 'username-feedback';
                
                fetch(`includes/auth/register.php?check_username=1&username=${encodeURIComponent(username)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.available) {
                            usernameFeedback.textContent = 'Username is available';
                            usernameFeedback.className = 'username-feedback is-available';
                        } else {
                            usernameFeedback.textContent = 'Username is already taken';
                            usernameFeedback.className = 'username-feedback not-available';
                        }
                    })
                    .catch(error => {
                        console.error('Error checking username:', error);
                        usernameFeedback.textContent = '';
                    });
            }
        });
    </script>
</body>
</html>