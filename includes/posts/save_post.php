<?php
session_start();
require_once '../config.php';
require_once '../db.php';
require_once '../auth/auth_check.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $post_id = $_POST['post_id'] ?? null;

    try {
        // Check if already saved
        $check_sql = "SELECT * FROM saved_posts WHERE user_id = ? AND post_id = ?";
        $check_stmt = $db->prepare($check_sql);
        $check_stmt->bind_param("ii", $user_id, $post_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows > 0) {
            // Unsave
            $sql = "DELETE FROM saved_posts WHERE user_id = ? AND post_id = ?";
            $action = 'unsave';
        } else {
            // Save
            $sql = "INSERT INTO saved_posts (user_id, post_id, created_at) VALUES (?, ?, NOW())";
            $action = 'save';
        }

        $stmt = $db->prepare($sql);
        $stmt->bind_param("ii", $user_id, $post_id);
        
        if ($stmt->execute()) {
            // Get updated save count
            $count_sql = "SELECT COUNT(*) as count FROM saved_posts WHERE post_id = ?";
            $count_stmt = $db->prepare($count_sql);
            $count_stmt->bind_param("i", $post_id);
            $count_stmt->execute();
            $count_result = $count_stmt->get_result()->fetch_assoc();
            
            echo json_encode([
                'success' => true,
                'action' => $action,
                'saves_count' => $count_result['count']
            ]);
        } else {
            throw new Exception("Failed to update save status");
        }

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>