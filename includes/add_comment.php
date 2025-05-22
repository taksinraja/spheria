<?php
session_start();
require_once 'config.php';
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Check if required fields are provided
if (!isset($_POST['post_id']) || !isset($_POST['content']) || empty($_POST['content'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Post ID and content are required']);
    exit;
}

$post_id = intval($_POST['post_id']);
$user_id = $_SESSION['user_id'];
$content = trim($_POST['content']);

try {
    // Insert comment - use comment_text instead of content
    $sql = "INSERT INTO comments (post_id, user_id, comment_text, created_at) VALUES (?, ?, ?, NOW())";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("iis", $post_id, $user_id, $content);
    $success = $stmt->execute();
    
    if ($success) {
        // Get the comment ID
        $comment_id = $stmt->insert_id;
        
        // Notify post owner if it's not their own post
        $check_sql = "SELECT user_id FROM posts WHERE post_id = ?";
        $check_stmt = $db->prepare($check_sql);
        $check_stmt->bind_param("i", $post_id);
        $check_stmt->execute();
        $post_owner = $check_stmt->get_result()->fetch_assoc()['user_id'];
        
        if ($post_owner != $user_id) {
            $notify_sql = "INSERT INTO notifications (user_id, from_user_id, notification_type, content_id, created_at) 
                          VALUES (?, ?, 'comment', ?, NOW())";
            $notify_stmt = $db->prepare($notify_sql);
            $notify_stmt->bind_param("iii", $post_owner, $user_id, $post_id);
            $notify_stmt->execute();
        }
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'comment_id' => $comment_id]);
    } else {
        throw new Exception("Failed to add comment");
    }
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Error adding comment: ' . $e->getMessage()]);
}
?>