<?php
session_start();
require_once '../config.php';
require_once '../db.php';
require_once '../auth/auth_check.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $post_id = $_GET['post_id'] ?? null;

    try {
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
            $row['created_at'] = date('M d, Y H:i', strtotime($row['created_at']));
            $comments[] = $row;
        }
        
        echo json_encode([
            'success' => true,
            'comments' => $comments
        ]);

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>