<?php
// Start session if not already started
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Include database connection
require_once 'db_connect.php';

// Check if required parameters are provided
if (!isset($_POST['user_id']) || !isset($_POST['action'])) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

$follower_id = $_SESSION['user_id'];
$following_id = $_POST['user_id'];
$action = $_POST['action'];

// Validate user_id is numeric
if (!is_numeric($following_id)) {
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit;
}

// Prevent following yourself
if ($follower_id == $following_id) {
    echo json_encode(['success' => false, 'message' => 'Cannot follow yourself']);
    exit;
}

try {
    if ($action === 'follow') {
        // Check if already following
        $check_sql = "SELECT * FROM followers WHERE follower_id = ? AND following_id = ?";
        $check_stmt = $db->prepare($check_sql);
        $check_stmt->bind_param("ii", $follower_id, $following_id);
        $check_stmt->execute();
        
        if ($check_stmt->get_result()->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Already following this user']);
            exit;
        }
        
        // Add follow relationship
        $follow_sql = "INSERT INTO followers (follower_id, following_id, created_at) VALUES (?, ?, NOW())";
        $follow_stmt = $db->prepare($follow_sql);
        $follow_stmt->bind_param("ii", $follower_id, $following_id);
        $follow_stmt->execute();
        
        echo json_encode(['success' => true, 'message' => 'Successfully followed user']);
    } 
    else if ($action === 'unfollow') {
        // Remove follow relationship
        $unfollow_sql = "DELETE FROM followers WHERE follower_id = ? AND following_id = ?";
        $unfollow_stmt = $db->prepare($unfollow_sql);
        $unfollow_stmt->bind_param("ii", $follower_id, $following_id);
        $unfollow_stmt->execute();
        
        echo json_encode(['success' => true, 'message' => 'Successfully unfollowed user']);
    } 
    else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>