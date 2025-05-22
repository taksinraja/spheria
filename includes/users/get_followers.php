<?php
session_start();
require_once '../config.php';
require_once '../db.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // First check if the followers table exists
    $check_table = $db->query("SHOW TABLES LIKE 'followers'");
    if ($check_table->num_rows == 0) {
        // Create followers table if it doesn't exist
        $db->query("CREATE TABLE IF NOT EXISTS followers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            following_id INT NOT NULL,
            follower_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_follow (following_id, follower_id)
        )");
    }

    // Get followers using the correct column names
    $sql = "SELECT u.user_id, u.username, u.full_name, u.profile_image 
            FROM users u 
            JOIN followers f ON u.user_id = f.follower_id 
            WHERE f.following_id = ? 
            ORDER BY u.username ASC";
    
    $stmt = $db->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $followers = [];
    while ($row = $result->fetch_assoc()) {
        // Make sure profile image has a default if null
        if (empty($row['profile_image'])) {
            $row['profile_image'] = 'assets/images/default-avatar.png';
        }
        $followers[] = $row;
    }
    
    // If no followers found, get some suggested users
    if (empty($followers)) {
        $sql = "SELECT user_id, username, full_name, profile_image 
                FROM users 
                WHERE user_id != ? 
                ORDER BY RAND() 
                LIMIT 10";
        
        $stmt = $db->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            if (empty($row['profile_image'])) {
                $row['profile_image'] = 'assets/images/default-avatar.png';
            }
            $followers[] = $row;
        }
    }
    
    echo json_encode(['success' => true, 'followers' => $followers]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage(), 
        'error' => $e->getTraceAsString(),
        'sql_error' => $db->error
    ]);
}
?>