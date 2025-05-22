<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/db.php';

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
    <title>Verify Your Account - Spheria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/auth.css">
    <style>
        .otp-input {
            background-color: #f8f9fa;
            width: 100%;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
            letter-spacing: 5px;
            text-align: center;
            font-weight: bold;
        }
        .otp-input:focus {
            border-color:rgb(226, 74, 201);
            outline: none;
        }
        .timer {
            font-size: 14px;
            color: #666;
            margin-top: 15px;
        }
        .icon {
            font-size: 48px;
            color: #a970ff;;
            margin-bottom: 20px;
        }
        .btn-va{
            background-color: #a970ff;
            color: #fff;
            border: none;
            padding: 15px 25px;
        }
        .btn-va:hover {
            background-color: #924fe3;
            color: #fff;
        }
        .btn-secondary {
            background-color: transparent;
            color: #a970ff;
            border: 1px solid #a970ff;
            padding: 15px 25px;
            margin-top: 10px;
        }
        .btn-secondary:hover {
            background-color: #924fe3;
            color: #fff;
            border-color: #924fe3;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-image">
            <div class="auth-quote">
                <h2>Capturing Moments,<br>Creating Memories</h2>
                <p>Join our community and share your story with the world.</p>
            </div>
        </div>
        <div class="auth-card">
            <div class="auth-header">
                <div class="icon">
                    <i class="fas fa-lock"></i>
                </div>
                <h2>Verify Your Account</h2>
                <p>We've sent a 6-digit verification code to your email address. Please enter it below to complete your registration.</p>
            </div>
            
            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
            
            <form class="auth-form" action="includes/auth/verify.php" method="POST">
                <div class="form-group">
                    <label for="otp">Enter Verification Code</label>
                    <input type="text" name="otp" id="otp" class="otp-input" maxlength="6" placeholder="000000" autocomplete="off">
                </div>
                <button type="submit" class="btn btn-va w-100">Verify Account</button>
            </form>
            
            <form action="includes/auth/resend_otp.php" method="POST">
                <button type="submit" class="btn btn-secondary w-100">Resend Code</button>
            </form>
            
            <div class="timer text-center">
                Code expires in <span id="countdown">05:00</span>
            </div>
        </div>
    </div>

    <script>
        // Simple countdown timer for OTP expiration
        function startTimer(duration, display) {
            var timer = duration, minutes, seconds;
            var interval = setInterval(function () {
                minutes = parseInt(timer / 60, 10);
                seconds = parseInt(timer % 60, 10);

                minutes = minutes < 10 ? "0" + minutes : minutes;
                seconds = seconds < 10 ? "0" + seconds : seconds;

                display.textContent = minutes + ":" + seconds;

                if (--timer < 0) {
                    clearInterval(interval);
                    display.textContent = "Expired";
                }
            }, 1000);
        }

        window.onload = function () {
            var fiveMinutes = 60 * 5,
                display = document.querySelector('#countdown');
            startTimer(fiveMinutes, display);
            
            // Auto-focus the OTP input field
            document.getElementById('otp').focus();
        };
    </script>
</body>
</html>