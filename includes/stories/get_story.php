<?php
session_start();
require_once '../config.php';
require_once '../db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['story_id'])) {
    http_response_code(400);
    exit('Missing required parameters');
}

$user_id = $_SESSION['user_id'];
$story_id = $_GET['story_id'];

// Get story details with user information
$sql = "SELECT s.story_id, s.user_id, s.content, s.created_at,
               u.username, u.profile_image,
               sm.file_path, sm.media_type, sm.mime_type
        FROM stories s
        JOIN users u ON s.user_id = u.user_id
        JOIN story_media sm ON s.story_id = sm.story_id
        WHERE s.story_id = ? AND s.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";

try {
    $stmt = $db->prepare($sql);
    $stmt->bind_param("i", $story_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(404);
        exit('Story not found or expired');
    }
    
    $story = $result->fetch_assoc();
    
    header('Content-Type: application/json');
    echo json_encode($story);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch story']);
}