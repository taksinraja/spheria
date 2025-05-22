<?php
session_start();
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $fullname = filter_var($_POST['fullname'], FILTER_SANITIZE_STRING);
    $username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
    // Check if username is taken by another user
    $sql = "SELECT id FROM users WHERE username = ? AND id != ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $username, $user_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $_SESSION['error'] = "Username already taken";
        header("Location: ../../profile.php");
        exit();
    }
    
    // Update user profile
    $sql = "UPDATE users SET fullname = ?, username = ?, email = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $fullname, $username, $email, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['username'] = $username;
        $_SESSION['success'] = "Profile updated successfully";
    } else {
        $_SESSION['error'] = "Failed to update profile";
    }
    
    header("Location: ../../profile.php");
    exit();
}
?>