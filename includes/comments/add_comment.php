<?php
session_start();
require_once '../config.php';
require_once '../db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['post_id']) || !isset($_POST['comment_text'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$post_id = $_POST['post_id'];
$user_id = $_SESSION['user_id'];
$comment_text = trim($_POST['comment_text']);

if (empty($comment_text)) {
    echo json_encode(['success' => false, 'message' => 'Comment cannot be empty']);
    exit;
}

try {
    $sql = "INSERT INTO comments (post_id, user_id, comment_text, created_at) VALUES (?, ?, ?, NOW())";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("iis", $post_id, $user_id, $comment_text);
    $stmt->execute();
    
    $comment_id = $db->insert_id;
    
    // Get user info for the response
    $user_sql = "SELECT username, profile_image FROM users WHERE user_id = ?";
    $user_stmt = $db->prepare($user_sql);
    $user_stmt->bind_param("i", $user_id);
    $user_stmt->execute();
    $user = $user_stmt->get_result()->fetch_assoc();
    
    echo json_encode([
        'success' => true,
        'comment' => [
            'comment_id' => $comment_id,
            'user_id' => $user_id,
            'username' => $user['username'],
            'profile_image' => $user['profile_image'] ?: 'assets/images/default-avatar.png',
            'comment_text' => $comment_text,
            'created_at' => date('Y-m-d H:i:s')
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Error adding comment: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}
exit;