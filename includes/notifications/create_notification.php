<?php
function create_notification($db, $type, $from_user_id, $to_user_id, $post_id = null, $comment_id = null) {
    $query = "INSERT INTO notifications (type, from_user_id, to_user_id, post_id, comment_id) 
              VALUES (?, ?, ?, ?, ?)";
    
    try {
        $stmt = $db->prepare($query);
        $stmt->bind_param("siiii", $type, $from_user_id, $to_user_id, $post_id, $comment_id);
        return $stmt->execute();
    } catch (Exception $e) {
        error_log("Error creating notification: " . $e->getMessage());
        return false;
    }
}