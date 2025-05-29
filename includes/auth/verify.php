<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the OTP from the form
    $otp = htmlspecialchars(trim($_POST['otp']));

    // Check if the OTP is valid on (OTP table) using otp_id
    $otp_id = $_SESSION['otp_id'];
    $sql = "SELECT * FROM otp WHERE otp_id = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("i", $otp_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $otp_row = $result->fetch_assoc();
    $user_id = $otp_row['user_id'];

    if($otp_row['otp'] === (int)$otp) {
        // Check if OTP has expired (5 minutes validity)
        $created_at = new DateTime($otp_row['created_at']);
        $current_time = new DateTime();
        $time_diff = $current_time->getTimestamp() - $created_at->getTimestamp();
        
        if($time_diff > 300) { // 300 seconds = 5 minutes
            $_SESSION['error'] = "OTP has expired. Please request a new one.";
            header("Location: /spheria1/verify.php");
            exit();
        }

        // Delete the OTP from the table
        $sql = "DELETE FROM otp WHERE otp_id =?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("i", $otp_id);
        $stmt->execute();

        // Set user verified to 1
        $sql = "UPDATE users SET is_verified = 1 WHERE user_id =?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        // Redirect to login page
        $_SESSION['success'] = "Account verified, Please login";
        header("Location: /spheria1/login.php");
        exit();

    } else {
        $_SESSION['error'] = "Invalid OTP";
        header("Location: /spheria1/verify.php");
        exit();
    }
}