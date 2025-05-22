<?php
session_start();
require_once '../config.php';
require_once '../db.php';
require_once '../auth/auth_check.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $search = $_GET['q'] ?? '';
    $current_user = $_SESSION['user_id'];

    try {
        $sql = "SELECT user_id, username, profile_image, full_name 
                FROM users 
                WHERE user_id != ? 
                AND username LIKE ? 
                ORDER BY username 
                LIMIT 20";
        
        $search_term = "%$search%";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("is", $current_user, $search_term);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        
        echo json_encode([
            'success' => true,
            'users' => $users
        ]);

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>