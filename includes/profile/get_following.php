<?php
session_start();
require_once '../config.php';
require_once '../db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Check if user_id is provided
if (!isset($_GET['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'User ID is required']);
    exit;
}

$user_id = intval($_GET['user_id']);

try {
    // Get following users
    $following_sql = "SELECT u.* FROM followers f 
                     JOIN users u ON f.following_id = u.user_id 
                     WHERE f.follower_id = ? 
                     ORDER BY f.followed_at DESC";
    $following_stmt = $db->prepare($following_sql);
    $following_stmt->bind_param("i", $user_id);
    $following_stmt->execute();
    $following_result = $following_stmt->get_result();
    
    $following = [];
    while ($follow = $following_result->fetch_assoc()) {
        // Remove sensitive information
        unset($follow['password']);
        unset($follow['email']);
        
        // Set default profile image if not available
        if (empty($follow['profile_image'])) {
            $follow['profile_image'] = 'assets/images/default-avatar.png';
        }
        
        $following[] = $follow;
    }
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'following' => $following]);
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Error fetching following users: ' . $e->getMessage()]);
}
?>