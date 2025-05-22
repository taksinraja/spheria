<?php
session_start();
require_once '../config.php';
require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate passwords match
    if ($new_password !== $confirm_password) {
        $_SESSION['error'] = "New passwords do not match";
        header("Location: /spheria1/settings.php");
        exit();
    }
    
    // Validate password length
    if (strlen($new_password) < 8) {
        $_SESSION['error'] = "Password must be at least 8 characters long";
        header("Location: /spheria1/settings.php");
        exit();
    }
    
    try {
        // Get current user data
        $sql = "SELECT password FROM users WHERE user_id = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $_SESSION['error'] = "User not found";
            header("Location: /spheria1/settings.php");
            exit();
        }
        
        $user = $result->fetch_assoc();
        
        // Verify current password
        if (!password_verify($current_password, $user['password'])) {
            $_SESSION['error'] = "Current password is incorrect";
            header("Location: /spheria1/settings.php");
            exit();
        }
        
        // Hash new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update password
        $update_sql = "UPDATE users SET password = ? WHERE user_id = ?";
        $update_stmt = $db->prepare($update_sql);
        $update_stmt->bind_param("si", $hashed_password, $user_id);
        
        if ($update_stmt->execute()) {
            $_SESSION['success'] = "Password updated successfully";
        } else {
            $_SESSION['error'] = "Failed to update password";
        }
        
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
    
    header("Location: /spheria1/settings.php");
    exit();
}
?>