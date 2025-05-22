<?php
session_start();
require_once '../config.php';
require_once '../db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['post_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$post_id = $_POST['post_id'];
$user_id = $_SESSION['user_id'];

try {
    // Check if user already liked the post
    $check_sql = "SELECT like_id FROM likes WHERE post_id = ? AND user_id = ?";
    $check_stmt = $db->prepare($check_sql);
    $check_stmt->bind_param("ii", $post_id, $user_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        // User already liked the post, so unlike it
        $like_id = $result->fetch_assoc()['like_id'];
        $delete_sql = "DELETE FROM likes WHERE like_id = ?";
        $delete_stmt = $db->prepare($delete_sql);
        $delete_stmt->bind_param("i", $like_id);
        $delete_stmt->execute();
        $liked = false;
    } else {
        // User hasn't liked the post, so like it
        $insert_sql = "INSERT INTO likes (post_id, user_id, created_at) VALUES (?, ?, NOW())";
        $insert_stmt = $db->prepare($insert_sql);
        $insert_stmt->bind_param("ii", $post_id, $user_id);
        $insert_stmt->execute();
        $liked = true;
    }
    
    // Get updated likes count
    $count_sql = "SELECT COUNT(*) as count FROM likes WHERE post_id = ?";
    $count_stmt = $db->prepare($count_sql);
    $count_stmt->bind_param("i", $post_id);
    $count_stmt->execute();
    $likes_count = $count_stmt->get_result()->fetch_assoc()['count'];
    
    echo json_encode([
        'success' => true,
        'liked' => $liked,
        'likes_count' => $likes_count
    ]);
    
} catch (Exception $e) {
    error_log("Error toggling like: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}