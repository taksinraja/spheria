<?php
session_start();
require_once '../config.php';
require_once '../db.php';
require_once '../auth/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $content = $_POST['caption'] ?? '';
    $status = $_POST['status'] ?? 'published';
    $schedule_time = null;

    try {
        // Debug information
        error_log("POST data: " . print_r($_POST, true));
        error_log("FILES data: " . print_r($_FILES, true));

        if (!isset($_FILES['media']) || empty($_FILES['media']['name'][0])) {
            throw new Exception("No media file uploaded");
        }

        // Start transaction
        $db->begin_transaction();

        // Insert post with correct fields matching the database structure
        // Make sure we're only using columns that exist in the posts table
        $sql = "INSERT INTO posts (user_id, content, visibility, created_at, updated_at) 
                VALUES (?, ?, 'public', NOW(), NOW())";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("is", $user_id, $content);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to create post: " . $stmt->error);
        }
        
        $post_id = $db->insert_id;
        error_log("Post created with ID: " . $post_id);

        // Create upload directory if it doesn't exist
        $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/spheria1/uploads/posts/' . $user_id . '/';
        if (!file_exists($upload_dir)) {
            if (!mkdir($upload_dir, 0777, true)) {
                throw new Exception("Failed to create upload directory");
            }
        }
        
        // Handle media files
        foreach ($_FILES['media']['tmp_name'] as $key => $tmp_name) {
            // Skip empty uploads
            if ($_FILES['media']['error'][$key] !== UPLOAD_ERR_OK) {
                continue;
            }
        
            $file_name = $_FILES['media']['name'][$key];
            $file_size = $_FILES['media']['size'][$key];
            $file_type = $_FILES['media']['type'][$key];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            // Determine media type
            $media_type = 'image'; // Default
            if (strpos($file_type, 'video/') === 0) {
                $media_type = 'video';
            }
        
            $new_file_name = uniqid() . '.' . $file_ext;
            $file_path = 'uploads/posts/' . $user_id . '/' . $new_file_name;
            $full_path = $upload_dir . $new_file_name;
        
            if (!move_uploaded_file($tmp_name, $full_path)) {
                throw new Exception("Failed to move uploaded file: " . $file_name);
            }
            
            error_log("File moved to: " . $full_path);
        
            // Insert media record with correct field names matching post_media table
            // Make sure we're only using columns that exist in the post_media table
            $media_sql = "INSERT INTO post_media (post_id, file_path, media_type, file_size, mime_type, created_at) 
                          VALUES (?, ?, ?, ?, ?, NOW())";
            $media_stmt = $db->prepare($media_sql);
            $media_stmt->bind_param("issis", $post_id, $file_path, $media_type, $file_size, $file_type);
            
            if (!$media_stmt->execute()) {
                throw new Exception("Failed to save media record: " . $media_stmt->error);
            }
            $media_stmt->close();
            error_log("Media record saved for file: " . $file_name);
        }

        // Handle tags
        if (!empty($_POST['tags'])) {
            $tags = array_map('trim', explode(',', $_POST['tags']));
            $tag_sql = "INSERT INTO post_tags (post_id, tag_name) VALUES (?, ?)";
            $tag_stmt = $db->prepare($tag_sql);
            
            foreach ($tags as $tag) {
                if (!empty($tag)) {
                    $tag_stmt->bind_param("is", $post_id, $tag);
                    $tag_stmt->execute();
                }
            }
        }

        $db->commit();
        error_log("Transaction committed successfully");
        header('Location: ../../profile.php?success=1');
        exit;

    } catch (Exception $e) {
        if (isset($db)) {
            $db->rollback();
        }
        error_log("Error creating post: " . $e->getMessage());
        header('Location: ../../create.php?error=1&message=' . urlencode($e->getMessage()));
        exit;
    }
}
?>