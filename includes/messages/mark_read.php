<?php
session_start();
require_once '../config.php';
require_once '../db.php';
require_once '../auth/auth_check.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if conversation ID is provided
if (!isset($_GET['conversation_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing conversation ID']);
    exit;
}

$conversation_id = intval($_GET['conversation_id']);
$user_id = $_SESSION['user_id'];

// Verify that the user is part of this conversation
$check_participant = $db->prepare("
    SELECT * FROM conversation_participants 
    WHERE conversation_id = ? AND user_id = ?
");
$check_participant->bind_param("ii", $conversation_id, $user_id);
$check_participant->execute();
$result = $check_participant->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'You are not part of this conversation']);
    exit;
}

// Mark all messages from other users as read
$mark_read = $db->prepare("
    UPDATE messages 
    SET is_read = 1 
    WHERE conversation_id = ? AND sender_id != ? AND is_read = 0
");
$mark_read->bind_param("ii", $conversation_id, $user_id);

if ($mark_read->execute()) {
    echo json_encode([
        'success' => true, 
        'message' => 'Messages marked as read'
    ]);
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to mark messages as read: ' . $db->error
    ]);
}
?>