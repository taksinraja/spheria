<?php
session_start();
require_once '../config.php';
require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    // Check if file was uploaded
    if (!isset($_FILES['profile_image']) || $_FILES['profile_image']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['error'] = "No file uploaded or upload error";
        header("Location: /spheria1/settings.php");
        exit();
    }
    
    // Validate file type
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $file_type = $_FILES['profile_image']['type'];
    
    if (!in_array($file_type, $allowed_types)) {
        $_SESSION['error'] = "Invalid file type. Only JPG, PNG, and GIF are allowed";
        header("Location: /spheria1/settings.php");
        exit();
    }
    
    // Validate file size (max 5MB)
    $max_size = 5 * 1024 * 1024; // 5MB
    if ($_FILES['profile_image']['size'] > $max_size) {
        $_SESSION['error'] = "File is too large. Maximum size is 5MB";
        header("Location: /spheria1/settings.php");
        exit();
    }
    
    // Create uploads directory if it doesn't exist
    $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/spheria1/uploads/profile_images/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Generate unique filename
    $file_extension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
    $new_filename = 'profile_' . $user_id . '_' . time() . '.' . $file_extension;
    $upload_path = $upload_dir . $new_filename;
    
    // Move uploaded file
    if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
        // Update database with new image path
        $image_url = '/spheria1/uploads/profile_images/' . $new_filename;
        
        $sql = "UPDATE users SET profile_image = ? WHERE user_id = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("si", $image_url, $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Profile image updated successfully";
        } else {
            $_SESSION['error'] = "Failed to update profile image in database";
        }
    } else {
        $_SESSION['error'] = "Failed to upload image";
    }
    
    header("Location: /spheria1/settings.php");
    exit();
}
?>