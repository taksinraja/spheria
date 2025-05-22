<?php
session_start();
require_once '../config.php';
require_once '../db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Check if post_id and user_id are provided
if (!isset($_POST['post_id']) || !isset($_POST['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$sender_id = $_SESSION['user_id'];
$recipient_id = $_POST['user_id'];
$post_id = $_POST['post_id'];

try {
    // Create a new message with the shared post
    $message_text = "[shared_post:" . $post_id . "]";
    
    // Check if messages table has required columns
    $check_columns = $db->query("SHOW COLUMNS FROM messages LIKE 'is_shared_post'");
    
    if ($check_columns->num_rows == 0) {
        // Add the missing columns if they don't exist
        $db->query("ALTER TABLE messages ADD COLUMN is_shared_post TINYINT(1) DEFAULT 0");
        $db->query("ALTER TABLE messages ADD COLUMN shared_post_id INT DEFAULT NULL");
    }
    
    $sql = "INSERT INTO messages (sender_id, recipient_id, message_text, is_shared_post, shared_post_id, created_at) 
            VALUES (?, ?, ?, 1, ?, NOW())";
    
    $stmt = $db->prepare($sql);
    $stmt->bind_param("iisi", $sender_id, $recipient_id, $message_text, $post_id);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Post shared successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to send message']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage(), 'error' => $db->error]);
}
?>