<?php
session_start();
require_once '../config.php';
require_once '../db.php';
require_once '../mail.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
    if (empty($email)) {
        $_SESSION['error'] = "Please enter your email address";
        header("Location: /spheria1/forgot-password.php");
        exit();
    }
    
    // Check if email exists in database
    $sql = "SELECT user_id, username FROM users WHERE email = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        // Don't reveal if email exists or not for security
        $_SESSION['success'] = "If your email is registered, you will receive a password reset link shortly";
        header("Location: /spheria1/forgot-password.php");
        exit();
    }
    
    $user = $result->fetch_assoc();
    $user_id = $user['user_id'];
    $username = $user['username'];
    
    // Generate a unique token
    $token = bin2hex(random_bytes(32));
    $expires = date("Y-m-d H:i:s", time() + 3600); // Token expires in 1 hour
    
    // Delete any existing tokens for this user
    $delete_sql = "DELETE FROM password_reset WHERE user_id = ?";
    $delete_stmt = $db->prepare($delete_sql);
    $delete_stmt->bind_param("i", $user_id);
    $delete_stmt->execute();
    
    // Insert new token
    $insert_sql = "INSERT INTO password_reset (user_id, token, expires_at) VALUES (?, ?, ?)";
    $insert_stmt = $db->prepare($insert_sql);
    $insert_stmt->bind_param("iss", $user_id, $token, $expires);
    
    if ($insert_stmt->execute()) {
        // Send email with reset link
        $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/spheria1/reset-password.php?token=" . $token;
        
        $subject = "Spheria - Password Reset";
        $body = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background-color: #a970ff; color: white; padding: 20px; text-align: center; }
                    .content { padding: 20px; background-color: #f9f9f9; }
                    .button { display: inline-block; padding: 10px 20px; background-color: #a970ff; color: white; text-decoration: none; border-radius: 5px; }
                    .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2>Spheria Password Reset</h2>
                    </div>
                    <div class='content'>
                        <p>Hello $username,</p>
                        <p>We received a request to reset your password. Click the button below to create a new password:</p>
                        <p style='text-align: center; color: white;'><a class='button' href='$reset_link'>Reset Password</a></p>
                        <p>If you didn't request this, you can safely ignore this email.</p>
                        <p>This link will expire in 1 hour for security reasons.</p>
                        <p>If the button doesn't work, copy and paste this URL into your browser:</p>
                        <p>$reset_link</p>
                    </div>
                    <div class='footer'>
                        <p>&copy; " . date('Y') . " Spheria. All rights reserved.</p>
                    </div>
                </div>
            </body>
            </html>
        ";
        
        $mailer = new Mail();
        if ($mailer->sendMail($email, $subject, $body)) {
            $_SESSION['success'] = "Password reset link has been sent to your email";
        } else {
            $_SESSION['error'] = "Failed to send reset email. Please try again later";
        }
    } else {
        $_SESSION['error'] = "Something went wrong. Please try again later";
    }
    
    header("Location: /spheria1/forgot-password.php");
    exit();
}