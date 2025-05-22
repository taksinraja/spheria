<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';

function get_notifications($db, $user_id, $type = 'all') {
    $notifications = [];
    
    $query = "SELECT n.*, u.username, u.profile_image, p.content as post_content, 
              pm.file_path as post_preview, n.created_at,
              CASE 
                  WHEN n.created_at > NOW() - INTERVAL 24 HOUR THEN 'New'
                  WHEN n.created_at > NOW() - INTERVAL 7 DAY THEN 'This Week'
                  ELSE 'Earlier'
              END as time_group
              FROM notifications n
              LEFT JOIN users u ON n.from_user_id = u.user_id
              LEFT JOIN posts p ON n.post_id = p.post_id
              LEFT JOIN post_media pm ON p.post_id = pm.post_id AND pm.media_type = 'image'
              WHERE n.to_user_id = ?
              ORDER BY n.created_at DESC
              LIMIT 50";
              
    try {
        $stmt = $db->prepare($query);
        if (!$stmt) {
            throw new Exception($db->error);
        }
        
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $row['time_ago'] = time_elapsed_string($row['created_at']);
            $notifications[$row['time_group']][] = $row;
        }
        
        return $notifications;
    } catch (Exception $e) {
        error_log("Error fetching notifications: " . $e->getMessage());
        return [];
    }
}

function time_elapsed_string($datetime) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    if ($diff->d == 0) {
        if ($diff->h == 0) {
            if ($diff->i == 0) {
                return "just now";
            }
            return $diff->i . "m";
        }
        return $diff->h . "h";
    }
    if ($diff->d < 7) {
        return $diff->d . "d";
    }
    return date('M j', strtotime($datetime));
}