<?php
session_start();
require_once '../config.php';
require_once '../db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Unauthorized');
}

$user_id = $_SESSION['user_id'];

// Get stories that are less than 24 hours old
$sql = "SELECT s.story_id, s.user_id, s.content, s.created_at,
               u.username, u.profile_image,
               sm.file_path, sm.media_type, sm.mime_type,
               EXISTS(SELECT 1 FROM story_views WHERE story_id = s.story_id AND viewer_id = ?) as viewed
        FROM stories s
        JOIN users u ON s.user_id = u.user_id
        JOIN story_media sm ON s.story_id = sm.story_id
        WHERE s.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        AND (
            s.user_id IN (SELECT following_id FROM followers WHERE follower_id = ?)
            OR s.user_id = ?
            OR s.visibility = 'public'
        )
        ORDER BY s.created_at DESC";

try {
    $stmt = $db->prepare($sql);
    $stmt->bind_param("iii", $user_id, $user_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $stories = [];
    while ($row = $result->fetch_assoc()) {
        $stories[] = [
            'story_id' => $row['story_id'],
            'user_id' => $row['user_id'],
            'username' => $row['username'],
            'profile_image' => $row['profile_image'],
            'content' => $row['content'],
            'created_at' => $row['created_at'],
            'file_path' => $row['file_path'],
            'media_type' => $row['media_type'],
            'mime_type' => $row['mime_type'],
            'viewed' => (bool)$row['viewed']
        ];
    }
    
    header('Content-Type: application/json');
    echo json_encode($stories);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch stories']);
}