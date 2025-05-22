<?php
session_start();
require_once '../config.php';
require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    // Get form data
    $username = $_POST['username'] ?? '';
    $fullname = $_POST['fullname'] ?? '';
    $email = $_POST['email'] ?? '';
    $bio = $_POST['bio'] ?? '';
    
    // Validate data
    if (empty($username)) {
        $_SESSION['error'] = "Username cannot be empty";
        header("Location: /spheria1/settings.php");
        exit();
    }
    
    if (empty($email)) {
        $_SESSION['error'] = "Email cannot be empty";
        header("Location: /spheria1/settings.php");
        exit();
    }
    
    // Check if username is already taken by another user
    $check_sql = "SELECT user_id FROM users WHERE username = ? AND user_id != ?";
    $check_stmt = $db->prepare($check_sql);
    $check_stmt->bind_param("si", $username, $user_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['error'] = "Username is already taken";
        header("Location: /spheria1/settings.php");
        exit();
    }
    
    // Check if email is already taken by another user
    $check_email_sql = "SELECT user_id FROM users WHERE email = ? AND user_id != ?";
    $check_email_stmt = $db->prepare($check_email_sql);
    $check_email_stmt->bind_param("si", $email, $user_id);
    $check_email_stmt->execute();
    $email_result = $check_email_stmt->get_result();
    
    if ($email_result->num_rows > 0) {
        $_SESSION['error'] = "Email is already taken";
        header("Location: /spheria1/settings.php");
        exit();
    }
    
    try {
        // Update user profile
        $update_sql = "UPDATE users SET username = ?, full_name = ?, email = ?, bio = ? WHERE user_id = ?";
        $update_stmt = $db->prepare($update_sql);
        $update_stmt->bind_param("ssssi", $username, $fullname, $email, $bio, $user_id);
        
        if ($update_stmt->execute()) {
            $_SESSION['success'] = "Profile updated successfully";
        } else {
            $_SESSION['error'] = "Failed to update profile";
        }
        
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
    
    header("Location: /spheria1/settings.php");
    exit();
}
?>