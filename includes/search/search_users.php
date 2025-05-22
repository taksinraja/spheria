<?php
session_start();
require_once '../config.php';
require_once '../db.php';

// Get search query
$query = isset($_GET['q']) ? $_GET['q'] : '';

if (empty($query)) {
    echo json_encode([]);
    exit;
}

try {
    // Search for users by username or full name
    $search_term = "%$query%";
    $sql = "SELECT user_id, username, full_name, profile_image FROM users 
            WHERE username LIKE ? OR full_name LIKE ? 
            ORDER BY 
                CASE 
                    WHEN username = ? THEN 0
                    WHEN username LIKE ? THEN 1
                    WHEN full_name LIKE ? THEN 2
                    ELSE 3
                END
            LIMIT 10";
    
    $stmt = $db->prepare($sql);
    $stmt->bind_param("sssss", $search_term, $search_term, $query, $search_term, $search_term);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $users = [];
    while ($user = $result->fetch_assoc()) {
        // Check if the current user is following this user
        $is_following = false;
        if (isset($_SESSION['user_id'])) {
            $follow_check = $db->prepare("SELECT * FROM followers WHERE follower_id = ? AND following_id = ?");
            $follow_check->bind_param("ii", $_SESSION['user_id'], $user['user_id']);
            $follow_check->execute();
            $follow_result = $follow_check->get_result();
            $is_following = ($follow_result->num_rows > 0);
        }
        
        // Add following status to user data
        $user['is_following'] = $is_following;
        $users[] = $user;
    }
    
    echo json_encode($users);
} catch (Exception $e) {
    error_log("Search error: " . $e->getMessage());
    echo json_encode(['error' => 'An error occurred during search']);
}
?>