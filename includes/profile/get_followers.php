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
    // Get followers
    $followers_sql = "SELECT u.* FROM followers f 
                     JOIN users u ON f.follower_id = u.user_id 
                     WHERE f.following_id = ? 
                     ORDER BY f.followed_at DESC";
    $followers_stmt = $db->prepare($followers_sql);
    $followers_stmt->bind_param("i", $user_id);
    $followers_stmt->execute();
    $followers_result = $followers_stmt->get_result();
    
    $followers = [];
    while ($follower = $followers_result->fetch_assoc()) {
        // Remove sensitive information
        unset($follower['password']);
        unset($follower['email']);
        
        // Set default profile image if not available
        if (empty($follower['profile_image'])) {
            $follower['profile_image'] = 'assets/images/default-avatar.png';
        }
        
        $followers[] = $follower;
    }
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'followers' => $followers]);
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Error fetching followers: ' . $e->getMessage()]);
}
?>