<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Debug information
error_log("Follow request received: " . json_encode($_POST));
error_log("Session user ID: " . ($_SESSION['user_id'] ?? 'not set'));

// Check if files exist
error_log("Config file exists: " . (file_exists(__DIR__ . '/../config.php') ? 'Yes' : 'No'));
error_log("DB file exists: " . (file_exists(__DIR__ . '/../db.php') ? 'Yes' : 'No'));

// Correct path to database files
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';

// Check database connection first
if (!isset($db) || $db->connect_error) {
    error_log("Database connection error: " . ($db->connect_error ?? 'No connection'));
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Check if followers table exists
$table_check = $db->query("SHOW TABLES LIKE 'followers'");
if ($table_check->num_rows === 0) {
    error_log("Followers table does not exist");
    echo json_encode(['success' => false, 'message' => 'Followers table does not exist']);
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    error_log("Follow error: User not logged in");
    echo json_encode(['success' => false, 'message' => 'You must be logged in to follow users']);
    exit;
}

// Get user ID to follow/unfollow
$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;

error_log("User ID to follow/unfollow: " . $user_id);

if ($user_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit;
}

if ($user_id == $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'You cannot follow yourself']);
    exit;
}

// Verify the user exists
$user_check_sql = "SELECT user_id FROM users WHERE user_id = ?";
$user_check_stmt = $db->prepare($user_check_sql);
$user_check_stmt->bind_param("i", $user_id);
$user_check_stmt->execute();
$user_result = $user_check_stmt->get_result();

if ($user_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'User does not exist']);
    exit;
}

try {
    // Begin transaction for data consistency
    $db->begin_transaction();
    
    // Check if already following
    $check_sql = "SELECT * FROM followers WHERE follower_id = ? AND following_id = ?";
    $check_stmt = $db->prepare($check_sql);
    $check_stmt->bind_param("ii", $_SESSION['user_id'], $user_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    error_log("Check query executed. Found rows: " . $result->num_rows);
    
    if ($result->num_rows > 0) {
        // Already following, so unfollow
        $delete_sql = "DELETE FROM followers WHERE follower_id = ? AND following_id = ?";
        $delete_stmt = $db->prepare($delete_sql);
        $delete_stmt->bind_param("ii", $_SESSION['user_id'], $user_id);
        $success = $delete_stmt->execute();
        
        error_log("Unfollow executed. Success: " . ($success ? 'true' : 'false'));
        
        if ($success) {
            $db->commit();
            echo json_encode(['success' => true, 'action' => 'unfollowed']);
        } else {
            $db->rollback();
            echo json_encode(['success' => false, 'message' => 'Failed to unfollow: ' . $delete_stmt->error]);
        }
    } else {
        // Not following, so follow
        $insert_sql = "INSERT INTO followers (follower_id, following_id, followed_at) VALUES (?, ?, NOW())";
        $insert_stmt = $db->prepare($insert_sql);
        $insert_stmt->bind_param("ii", $_SESSION['user_id'], $user_id);
        $success = $insert_stmt->execute();
        
        error_log("Follow executed. Success: " . ($success ? 'true' : 'false'));
        if (!$success) {
            error_log("Follow error: " . $insert_stmt->error);
        }
        
        if ($success) {
            $db->commit();
            echo json_encode(['success' => true, 'action' => 'followed']);
        } else {
            $db->rollback();
            echo json_encode(['success' => false, 'message' => 'Failed to follow: ' . $insert_stmt->error]);
        }
    }
} catch (Exception $e) {
    // Rollback transaction on error
    try {
        $db->rollback();
    } catch (Exception $e) {
        // Ignore rollback error since we're already in an error state
    }
    
    error_log("Follow error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>