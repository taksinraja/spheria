<?php
session_start();
require_once '../config.php';
require_once '../db.php';
require_once '../auth/auth_check.php';

// Set content type to JSON for AJAX responses
header('Content-Type: application/json');

// Handle profile image upload
if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $filename = $_FILES['profile_image']['name'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    if (in_array($ext, $allowed)) {
        $target_dir = "../../uploads/profile_images/";
        
        // Create directory if it doesn't exist
        if (!is_dir($target_dir)) {
            if (!mkdir($target_dir, 0777, true)) {
                echo json_encode(['success' => false, 'message' => 'Failed to create upload directory']);
                exit;
            }
        }
        
        // Make sure the directory is writable
        if (!is_writable($target_dir)) {
            chmod($target_dir, 0777);
        }
        
        $new_filename = uniqid() . '.' . $ext;
        $target_file = $target_dir . $new_filename;
        
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
            // Update database
            $file_path = 'uploads/profile_images/' . $new_filename;
            $stmt = $db->prepare("UPDATE users SET profile_image = ? WHERE user_id = ?");
            $stmt->bind_param("si", $file_path, $_SESSION['user_id']);
            
            if ($stmt->execute()) {
                header('Location: ../../profile.php');
                // echo json_encode(['success' => true, 'file_path' => $file_path]);
                exit;
            } else {
                echo json_encode(['success' => false, 'message' => 'Database update failed: ' . $db->error]);
                exit;
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file. Error: ' . error_get_last()['message']]);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid file type. Allowed: jpg, jpeg, png, gif']);
        exit;
    }
    
    // echo json_encode(['success' => false, 'message' => 'Failed to upload profile image']);
    // exit;
}

// Handle cover image upload
if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] == 0) {
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $filename = $_FILES['cover_image']['name'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    if (in_array($ext, $allowed)) {
        $target_dir = "../../uploads/cover_images/";
        
        // Create directory if it doesn't exist
        if (!is_dir($target_dir)) {
            if (!mkdir($target_dir, 0777, true)) {
                // echo json_encode(['success' => false, 'message' => 'Failed to create upload directory']);
                exit;
            }
        }
        
        // Make sure the directory is writable
        if (!is_writable($target_dir)) {
            chmod($target_dir, 0777);
        }
        
        $new_filename = uniqid() . '.' . $ext;
        $target_file = $target_dir . $new_filename;
        
        if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $target_file)) {
            // Update database
            $file_path = 'uploads/cover_images/' . $new_filename;
            $stmt = $db->prepare("UPDATE users SET cover_image = ? WHERE user_id = ?");
            $stmt->bind_param("si", $file_path, $_SESSION['user_id']);
            
            if ($stmt->execute()) {
                header('Location: ../../profile.php');
                exit;
            } else {
                // echo json_encode(['success' => false, 'message' => 'Database update failed: ' . $db->error]);
                exit;
            }
        } else {
            // echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file. Error: ' . error_get_last()['message']]);
            exit;
        }
    } else {
        // echo json_encode(['success' => false, 'message' => 'Invalid file type. Allowed: jpg, jpeg, png, gif']);
        exit;
    }
    
    // echo json_encode(['success' => false, 'message' => 'Failed to upload cover image']);
    // exit;
}

// Handle profile information update - Only use header redirect for regular form submissions, not AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_FILES['profile_image']) && !isset($_FILES['cover_image'])) {
    // Check if this is an AJAX request
    $is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    
    $full_name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $bio = isset($_POST['bio']) ? trim($_POST['bio']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    
    // Validate username (unique)
    if (!empty($username)) {
        $check_username = $db->prepare("SELECT user_id FROM users WHERE username = ? AND user_id != ?");
        $check_username->bind_param("si", $username, $_SESSION['user_id']);
        $check_username->execute();
        if ($check_username->get_result()->num_rows > 0) {
            if ($is_ajax) {
                echo json_encode(['success' => false, 'message' => 'Username already taken']);
                exit;
            } else {
                $_SESSION['error'] = 'Username already taken';
                header("Location: ../../profile.php?tab=edit");
                exit;
            }
        }
    }
    
    // Validate email (unique)
    if (!empty($email)) {
        $check_email = $db->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
        $check_email->bind_param("si", $email, $_SESSION['user_id']);
        $check_email->execute();
        if ($check_email->get_result()->num_rows > 0) {
            if ($is_ajax) {
                echo json_encode(['success' => false, 'message' => 'Email already in use']);
                exit;
            } else {
                $_SESSION['error'] = 'Email already in use';
                header("Location: ../../profile.php?tab=edit");
                exit;
            }
        }
    }
    
    // Update profile
    $update_sql = "UPDATE users SET ";
    $params = [];
    $types = "";
    
    if (!empty($full_name)) {
        $update_sql .= "full_name = ?, ";
        $params[] = $full_name;
        $types .= "s";
    }
    
    if (!empty($username)) {
        $update_sql .= "username = ?, ";
        $params[] = $username;
        $types .= "s";
    }
    
    if (!empty($bio)) {
        $update_sql .= "bio = ?, ";
        $params[] = $bio;
        $types .= "s";
    }
    
    if (!empty($email)) {
        $update_sql .= "email = ?, ";
        $params[] = $email;
        $types .= "s";
    }
    
    // Remove trailing comma and space
    $update_sql = rtrim($update_sql, ", ");
    
    $update_sql .= " WHERE user_id = ?";
    $params[] = $_SESSION['user_id'];
    $types .= "i";
    
    $stmt = $db->prepare($update_sql);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
        if ($stmt->execute()) {
            if ($is_ajax) {
                echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
                exit;
            } else {
                // Redirect back to profile with success message
                header("Location: ../../profile.php?success=1");
                exit;
            }
        }
    }
    
    // If we get here, something went wrong
    if ($is_ajax) {
        echo json_encode(['success' => false, 'message' => 'Failed to update profile']);
    } else {
        $_SESSION['error'] = 'Failed to update profile';
        header("Location: ../../profile.php?tab=edit");
    }
    exit;
}

// If we get here, no valid action was performed
echo json_encode(['success' => false, 'message' => 'Invalid request']);
?>