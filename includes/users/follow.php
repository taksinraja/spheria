<?php
session_start();
require_once '../config.php';
require_once '../db.php';
require_once '../auth/auth_check.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $follower_id = $_SESSION['user_id'];
    $following_id = $_POST['user_id'] ?? null;

    try {
        // Check if already following
        $check_sql = "SELECT * FROM followers WHERE follower_id = ? AND following_id = ?";
        $check_stmt = $db->prepare($check_sql);
        $check_stmt->bind_param("ii", $follower_id, $following_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows > 0) {
            // Unfollow
            $sql = "DELETE FROM followers WHERE follower_id = ? AND following_id = ?";
        } else {
            // Follow
            $sql = "INSERT INTO followers (follower_id, following_id, created_at) VALUES (?, ?, NOW())";
        }

        $stmt = $db->prepare($sql);
        $stmt->bind_param("ii", $follower_id, $following_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            throw new Exception("Failed to update follow status");
        }

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>