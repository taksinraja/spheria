<?php
session_start();
require_once '../auth/auth_check.php';
require_once '../config.php';
require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../create.php?error=1&message=' . urlencode('Invalid request method'));
    exit;
}

$user_id = $_SESSION['user_id'];
$content = isset($_POST['caption']) ? trim($_POST['caption']) : '';
$expires_at = date('Y-m-d H:i:s', strtotime('+24 hours'));

if (!isset($_FILES['media']) || empty($_FILES['media']['name'][0])) {
    header('Location: ../../create.php?error=1&message=' . urlencode('No media file uploaded'));
    exit;
}

// Create stories directory if it doesn't exist
$upload_dir = "../../uploads/stories/{$user_id}/";
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Insert story record
$stmt = $db->prepare("INSERT INTO stories (user_id, content, created_at, expires_at) VALUES (?, ?, NOW(), ?)");
$stmt->bind_param("iss", $user_id, $content, $expires_at);

if (!$stmt->execute()) {
    header('Location: ../../create.php?error=1&message=' . urlencode('Failed to create story'));
    exit;
}

$story_id = $db->insert_id;

// Handle media files
foreach ($_FILES['media']['tmp_name'] as $key => $tmp_name) {
    $file_name = $_FILES['media']['name'][$key];
    $file_size = $_FILES['media']['size'][$key];
    $file_type = $_FILES['media']['type'][$key];
    
    // Validate file type
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'video/mp4', 'video/quicktime'];
    if (!in_array($file_type, $allowed_types)) {
        continue;
    }
    
    // Generate unique filename
    $extension = pathinfo($file_name, PATHINFO_EXTENSION);
    $new_filename = uniqid() . '.' . $extension;
    $file_path = "uploads/stories/{$user_id}/{$new_filename}";
    $full_path = $upload_dir . $new_filename;
    
    // Move uploaded file
    if (move_uploaded_file($tmp_name, $full_path)) {
        $media_type = strpos($file_type, 'image/') === 0 ? 'image' : 'video';
        
        // Insert media record
        $stmt = $db->prepare("INSERT INTO story_media (story_id, file_path, media_type, file_size, mime_type, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("issis", $story_id, $file_path, $media_type, $file_size, $file_type);
        $stmt->execute();
    }
}

header('Location: ../../index.php?success=1&message=' . urlencode('Story created successfully'));
exit;