<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__. '/../mail.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    
    try {
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Check if user is verified
                if ($user['is_verified'] === 1) {
                    // User is verified, proceed with login
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['username'] = $user['username'];
                    header("Location: /spheria1/index.php");
                    exit();
                } else {
                    // User is not verified, redirect to verification page
                    // Create a new OTP for verification
                    $otp = rand(100000, 999999);
                    $sql = "INSERT INTO otp (user_id, otp) VALUES (?, ?)";
                    $stmt = $db->prepare($sql);
                    $stmt->bind_param("is", $user['user_id'], $otp);
                    
                    if ($stmt->execute()) {
                        $_SESSION['otp_id'] = $db->insert_id;

                        // Send OTP via email
                        $subject = "Spheria - Verify Your Account";
                        $body = "Your OTP for verification is: $otp";
                        $mailer->sendMail($email, $subject, $body);

                        $_SESSION['message'] = "Please verify your account to continue";
                        header("Location: /spheria1/verify.php");
                        exit();
                    }
                }
            }
        }
        
        $_SESSION['error'] = "Invalid email or password";
        header("Location: /spheria1/login.php");
        exit();
    } catch (Exception $e) {
        $_SESSION['error'] = "Login failed: " . $e->getMessage();
        header("Location: /spheria1/login.php");
        exit();
    }
}
?>