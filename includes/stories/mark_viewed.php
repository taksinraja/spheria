<?php
session_start();
require_once '../config.php';
require_once '../db.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['story_id'])) {
    http_response_code(400);
    exit('Missing required parameters');
}

$user_id = $_SESSION['user_id'];
$story_id = $_POST['story_id'];

// Check if story exists and is within 24 hours
$check_sql = "SELECT story_id FROM stories WHERE story_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
$check_stmt = $db->prepare($check_sql);
$check_stmt->bind_param("i", $story_id);
$check_stmt->execute();

if ($check_stmt->get_result()->num_rows === 0) {
    http_response_code(404);
    exit('Story not found or expired');
}

// Insert view record if not already viewed
$sql = "INSERT IGNORE INTO story_views (story_id, viewer_id, viewed_at) VALUES (?, ?, NOW())";
try {
    $stmt = $db->prepare($sql);
    $stmt->bind_param("ii", $story_id, $user_id);
    $stmt->execute();
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to mark story as viewed']);
}