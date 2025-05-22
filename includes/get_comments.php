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

// Check if post_id is provided
if (!isset($_GET['post_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Post ID is required']);
    exit;
}

$post_id = intval($_GET['post_id']);

try {
    // Get comments for the post
    $sql = "SELECT c.*, u.username, u.profile_image 
            FROM comments c 
            JOIN users u ON c.user_id = u.user_id 
            WHERE c.post_id = ? 
            ORDER BY c.created_at DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $comments = [];
    while ($row = $result->fetch_assoc()) {
        // Format date
        $row['created_at'] = date('M d, Y g:i a', strtotime($row['created_at']));
        $comments[] = $row;
    }
    
    header('Content-Type: application/json');
    echo json_encode($comments);
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Error fetching comments: ' . $e->getMessage()]);
}
?>