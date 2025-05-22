<?php
function get_unread_messages_count($db, $user_id) {
    $query = $db->prepare("
        SELECT COUNT(*) as unread_count 
        FROM messages m
        JOIN conversations c ON m.conversation_id = c.conversation_id
        WHERE m.is_read = 0 
        AND m.sender_id != ? 
        AND (c.user1_id = ? OR c.user2_id = ?)
    ");
    
    $query->bind_param("iii", $user_id, $user_id, $user_id);
    $query->execute();
    $result = $query->get_result();
    $row = $result->fetch_assoc();
    
    return $row['unread_count'];
}
?>