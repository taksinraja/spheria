<?php
session_start();
require_once '../config.php';
require_once '../db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Get searched user ID
$searched_user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;

if ($searched_user_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit;
}

try {
    // Check if this search already exists
    $check_sql = "SELECT search_id FROM search_history 
                 WHERE user_id = ? AND searched_user_id = ?";
    $check_stmt = $db->prepare($check_sql);
    $check_stmt->bind_param("ii", $_SESSION['user_id'], $searched_user_id);
    $check_stmt->execute();
    $existing = $check_stmt->get_result()->fetch_assoc();
    
    if ($existing) {
        // Update the timestamp of existing search
        $update_sql = "UPDATE search_history SET search_date = NOW() 
                      WHERE search_id = ?";
        $update_stmt = $db->prepare($update_sql);
        $update_stmt->bind_param("i", $existing['search_id']);
        $update_stmt->execute();
        
        echo json_encode(['success' => true, 'message' => 'Search history updated']);
    } else {
        // Insert new search history
        $insert_sql = "INSERT INTO search_history (user_id, searched_user_id, search_date) 
                      VALUES (?, ?, NOW())";
        $insert_stmt = $db->prepare($insert_sql);
        $insert_stmt->bind_param("ii", $_SESSION['user_id'], $searched_user_id);
        $insert_stmt->execute();
        
        echo json_encode(['success' => true, 'message' => 'Search history saved']);
    }
} catch (Exception $e) {
    error_log("Save search error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}
?>