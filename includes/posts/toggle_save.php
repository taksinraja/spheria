<?php
session_start();
require_once '../config.php';
require_once '../db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

if (!isset($_POST['post_id'])) {
    echo json_encode(['success' => false, 'message' => 'Post ID is required']);
    exit;
}

$post_id = $_POST['post_id'];
$user_id = $_SESSION['user_id'];

try {
    // Check if user has already saved this post
    $check_sql = "SELECT * FROM saved_posts WHERE user_id = ? AND post_id = ?";
    $check_stmt = $db->prepare($check_sql);
    $check_stmt->bind_param("ii", $user_id, $post_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        // User has already saved this post, so unsave it
        $delete_sql = "DELETE FROM saved_posts WHERE user_id = ? AND post_id = ?";
        $delete_stmt = $db->prepare($delete_sql);
        $delete_stmt->bind_param("ii", $user_id, $post_id);
        $delete_stmt->execute();
        
        echo json_encode(['success' => true, 'saved' => false]);
    } else {
        // User hasn't saved this post, so save it
        $insert_sql = "INSERT INTO saved_posts (user_id, post_id) VALUES (?, ?)";
        $insert_stmt = $db->prepare($insert_sql);
        $insert_stmt->bind_param("ii", $user_id, $post_id);
        $insert_stmt->execute();
        
        echo json_encode(['success' => true, 'saved' => true]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>