<?php
session_start();
require_once '../config.php';
require_once '../db.php';
require_once '../notifications/create_notification.php';
require_once '../auth/auth_check.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_id = $_POST['post_id'] ?? null;
    $user_id = $_SESSION['user_id'];

    try {
        // Check if already liked
        $check_sql = "SELECT * FROM likes WHERE user_id = ? AND post_id = ?";
        $check_stmt = $db->prepare($check_sql);
        $check_stmt->bind_param("ii", $user_id, $post_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows > 0) {
            // Unlike
            $sql = "DELETE FROM likes WHERE user_id = ? AND post_id = ?";
            $action = 'unlike';
        } else {
            // Like
            $sql = "INSERT INTO likes (user_id, post_id, created_at) VALUES (?, ?, NOW())";
            $action = 'like';
            
            // Get post owner for notification
            $post_query = "SELECT user_id FROM posts WHERE post_id = ?";
            $post_stmt = $db->prepare($post_query);
            $post_stmt->bind_param("i", $post_id);
            $post_stmt->execute();
            $post_owner = $post_stmt->get_result()->fetch_assoc();
            
            // Create notification if liker is not the post owner
            if ($post_owner && $post_owner['user_id'] != $user_id) {
                create_notification($db, 'like', $user_id, $post_owner['user_id'], $post_id);
            }
        }

        $stmt = $db->prepare($sql);
        $stmt->bind_param("ii", $user_id, $post_id);
        
        if ($stmt->execute()) {
            // Get updated likes count
            $count_sql = "SELECT COUNT(*) as count FROM likes WHERE post_id = ?";
            $count_stmt = $db->prepare($count_sql);
            $count_stmt->bind_param("i", $post_id);
            $count_stmt->execute();
            $count_result = $count_stmt->get_result()->fetch_assoc();
            
            echo json_encode([
                'success' => true,
                'action' => $action,
                'likes_count' => $count_result['count']
            ]);
        } else {
            throw new Exception("Failed to update like status");
        }

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>