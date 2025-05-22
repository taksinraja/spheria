<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__. '/../mail.php';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp_id = $_SESSION['otp_id'];

    // 1st take the user_id from otp table
    $sql = "SELECT user_id FROM otp WHERE otp_id =?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("i", $otp_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $otp_row = $result->fetch_assoc();
    $user_id = $otp_row['user_id'];

    // Get user email and full name from users table
    $sql = "SELECT email, full_name FROM users WHERE user_id =?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_row = $result->fetch_assoc();
    $email = $user_row['email'];
    $full_name = $user_row['full_name'];

    // Delete the otp entry from otp table
    $sql = "DELETE FROM otp WHERE otp_id =?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("i", $otp_id);
    $stmt->execute();

    // Create a otp entry in otp table
    $otp = rand(100000, 999999);
    $sql = "INSERT INTO otp (user_id, otp) VALUES (?,?)";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("is", $user_id, $otp);

    // Create HTML email template
    $message = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Verify Your Spheria Account</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f9f9f9;
                margin: 0;
                padding: 0;
            }
            .email-container {
                max-width: 600px;
                margin: 0 auto;
                background-color: #121212;
                border-radius: 10px;
                overflow: hidden;
                border: 1px solid #333;
            }
            .email-header {
                background-color: #1a1a1a;
                padding: 20px;
                text-align: center;
                border-bottom: 1px solid #333;
            }
            .email-header img {
                max-width: 150px;
                height: auto;
            }
            .email-body {
                padding: 30px;
                color: #ffffff;
            }
            .email-footer {
                background-color: #1a1a1a;
                padding: 15px;
                text-align: center;
                color: #666;
                font-size: 12px;
                border-top: 1px solid #333;
            }
            h1 {
                color: #ffffff;
                margin-top: 0;
            }
            p {
                color: #cccccc;
                line-height: 1.5;
            }
            .otp-container {
                background-color: rgba(169, 112, 255, 0.1);
                border: 1px solid #a970ff;
                border-radius: 8px;
                padding: 20px;
                margin: 25px 0;
                text-align: center;
            }
            .otp-code {
                font-size: 32px;
                font-weight: bold;
                letter-spacing: 5px;
                color: #a970ff;
                margin: 10px 0;
            }
            .button {
                display: inline-block;
                background-color: #a970ff;
                color: #ffffff;
                text-decoration: none;
                padding: 12px 30px;
                border-radius: 5px;
                margin-top: 20px;
                font-weight: bold;
            }
            .expiry {
                color: #ff6b6b;
                font-size: 14px;
                margin-top: 10px;
            }
        </style>
    </head>
    <body>
        <div class="email-container">
            <div class="email-header">
                <img src="https://yourdomain.com/spheria1/assets/images/full-logo.png" alt="Spheria Logo">
            </div>
            <div class="email-body">
                <h1>Verify Your Account</h1>
                <p>Hello ' . htmlspecialchars($full_name) . ',</p>
                <p>We received a request to resend your verification code. Please use the code below to verify your account:</p>
                
                <div class="otp-container">
                    <p>Your verification code is:</p>
                    <div class="otp-code">' . $otp . '</div>
                    <p class="expiry">This code will expire in 5 minutes</p>
                </div>
                
                <p>If you did not request this code, please ignore this email.</p>
                <p>Welcome to Spheria - Your world, your shpere.</p>
            </div>
            <div class="email-footer">
                <p>&copy; 2025 Spheria. All rights reserved.</p>
                <p>This is an automated message, please do not reply.</p>
            </div>
        </div>
    </body>
    </html>
    ';

    // Set content-type header for HTML email
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: Spheria <noreply@yourdomain.com>" . "\r\n";

    // Send email with HTML template
    $subject = "Spheria Verification Code";
    $mailer->sendMail($email, $subject, $message, $headers);

    if($stmt->execute()) {
        // Set new otp_id in session
        $_SESSION['otp_id'] = $stmt->insert_id;

        // Success
        $_SESSION['success'] = "OTP resent";
        header("Location: /spheria1/verify.php");
        exit();
    } else {
        // Error
        $_SESSION['error'] = "Error resending OTP";
        header("Location: /spheria1/verify.php");
    }
}