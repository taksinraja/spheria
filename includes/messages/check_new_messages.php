<?php
session_start();
require_once '../config.php';
require_once '../db.php';
require_once '../auth/auth_check.php';

// Get parameters
$conversation_id = isset($_GET['conversation_id']) ? intval($_GET['conversation_id']) : 0;
$last_message_id = isset($_GET['last_message_id']) ? intval($_GET['last_message_id']) : 0;

// Validate parameters
if ($conversation_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid conversation']);
    exit;
}

// Verify that the conversation exists and user is a participant
$check_conversation = $db->prepare("
    SELECT * FROM conversations 
    WHERE conversation_id = ? 
    AND (user1_id = ? OR user2_id = ?)
");
$check_conversation->bind_param("iii", $conversation_id, $_SESSION['user_id'], $_SESSION['user_id']);
$check_conversation->execute();
$conversation_result = $check_conversation->get_result();

if ($conversation_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid conversation']);
    exit;
}

// Get new messages
$messages_query = $db->prepare("
    SELECT m.*, u.username, u.profile_image 
    FROM messages m
    JOIN users u ON m.sender_id = u.user_id
    WHERE m.conversation_id = ? 
    AND m.message_id > ? 
    AND m.sender_id != ?
    ORDER BY m.created_at ASC
");
$messages_query->bind_param("iii", $conversation_id, $last_message_id, $_SESSION['user_id']);
$messages_query->execute();
$messages_result = $messages_query->get_result();

$messages = [];
while ($message = $messages_result->fetch_assoc()) {
    // Mark message as read
    $mark_read = $db->prepare("UPDATE messages SET is_read = 1 WHERE message_id = ?");
    $mark_read->bind_param("i", $message['message_id']);
    $mark_read->execute();
    
    // Format profile image
    if (empty($message['profile_image'])) {
        $message['profile_image'] = 'assets/images/default-avatar.png';
    }
    
    $messages[] = $message;
}

echo json_encode([
    'success' => true,
    'messages' => $messages
]);
?>