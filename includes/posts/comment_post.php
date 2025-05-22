<?php
session_start();
require_once '../config.php';
require_once '../db.php';
require_once '../auth/auth_check.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $post_id = $_POST['post_id'] ?? null;
    $comment_text = $_POST['comment'] ?? '';

    try {
        if (empty($comment_text)) {
            throw new Exception("Comment cannot be empty");
        }

        $sql = "INSERT INTO comments (user_id, post_id, comment_text, created_at) VALUES (?, ?, ?, NOW())";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("iis", $user_id, $post_id, $comment_text);
        
        if ($stmt->execute()) {
            // Get updated comment count and latest comment
            $count_sql = "SELECT COUNT(*) as count FROM comments WHERE post_id = ?";
            $count_stmt = $db->prepare($count_sql);
            $count_stmt->bind_param("i", $post_id);
            $count_stmt->execute();
            $count_result = $count_stmt->get_result()->fetch_assoc();
            
            echo json_encode([
                'success' => true,
                'comments_count' => $count_result['count']
            ]);
        } else {
            throw new Exception("Failed to add comment");
        }

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>