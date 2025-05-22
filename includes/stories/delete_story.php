<?php
session_start();
require_once '../config.php';
require_once '../db.php';

// Check if user is logged in and story_id is provided
if (!isset($_SESSION['user_id']) || !isset($_POST['story_id'])) {
    http_response_code(400);
    exit(json_encode(['success' => false, 'message' => 'Missing required parameters']));
}

$user_id = $_SESSION['user_id'];
$story_id = $_POST['story_id'];

// Verify that the story belongs to the current user
$check_sql = "SELECT story_id FROM stories WHERE story_id = ? AND user_id = ?";
$check_stmt = $db->prepare($check_sql);
$check_stmt->bind_param("ii", $story_id, $user_id);
$check_stmt->execute();

if ($check_stmt->get_result()->num_rows === 0) {
    http_response_code(403);
    exit(json_encode(['success' => false, 'message' => 'You do not have permission to delete this story']));
}

// Get media files to delete
$media_sql = "SELECT file_path FROM story_media WHERE story_id = ?";
$media_stmt = $db->prepare($media_sql);
$media_stmt->bind_param("i", $story_id);
$media_stmt->execute();
$media_result = $media_stmt->get_result();

$media_files = [];
while ($row = $media_result->fetch_assoc()) {
    $media_files[] = $row['file_path'];
}

// Begin transaction
$db->begin_transaction();

try {
    // Delete story (will cascade to story_media due to foreign key constraint)
    $delete_sql = "DELETE FROM stories WHERE story_id = ? AND user_id = ?";
    $delete_stmt = $db->prepare($delete_sql);
    $delete_stmt->bind_param("ii", $story_id, $user_id);
    $delete_stmt->execute();
    
    if ($delete_stmt->affected_rows === 0) {
        throw new Exception("Failed to delete story");
    }
    
    // Commit transaction
    $db->commit();
    
    // Delete physical media files
    foreach ($media_files as $file_path) {
        $full_path = "../../" . $file_path;
        if (file_exists($full_path)) {
            unlink($full_path);
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Story deleted successfully']);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $db->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to delete story: ' . $e->getMessage()]);
}