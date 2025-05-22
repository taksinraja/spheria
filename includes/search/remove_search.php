<?php
session_start();
require_once '../config.php';
require_once '../db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Get search ID
$search_id = isset($_POST['search_id']) ? intval($_POST['search_id']) : 0;

if ($search_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid search ID']);
    exit;
}

try {
    // Delete search history (only if it belongs to the current user)
    $delete_sql = "DELETE FROM search_history 
                  WHERE search_id = ? AND user_id = ?";
    $delete_stmt = $db->prepare($delete_sql);
    $delete_stmt->bind_param("ii", $search_id, $_SESSION['user_id']);
    $delete_stmt->execute();
    
    if ($delete_stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Search removed']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Search not found or not authorized']);
    }
} catch (Exception $e) {
    error_log("Remove search error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}
?>