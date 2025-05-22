<?php
session_start();
require_once '../config.php';
require_once '../db.php';
require_once '../auth/auth_check.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if required parameters are provided
if (!isset($_POST['message_id']) || !isset($_POST['reaction_type'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$message_id = intval($_POST['message_id']);
$reaction_type = trim($_POST['reaction_type']);
$user_id = $_SESSION['user_id'];

// Validate reaction type
$allowed_reactions = ['like', 'love', 'haha', 'wow', 'sad', 'angry'];
if (!in_array($reaction_type, $allowed_reactions)) {
    echo json_encode(['success' => false, 'message' => 'Invalid reaction type']);
    exit;
}

// Check if message exists and user has access to it
$check_message = $db->prepare("
    SELECT m.* FROM messages m
    JOIN conversation_participants cp ON m.conversation_id = cp.conversation_id
    WHERE m.message_id = ? AND cp.user_id = ?
");
$check_message->bind_param("ii", $message_id, $user_id);
$check_message->execute();
$result = $check_message->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Message not found or you don\'t have access']);
    exit;
}

// Check if user already reacted to this message
$check_reaction = $db->prepare("
    SELECT * FROM message_reactions 
    WHERE message_id = ? AND user_id = ?
");
$check_reaction->bind_param("ii", $message_id, $user_id);
$check_reaction->execute();
$reaction_result = $check_reaction->get_result();

if ($reaction_result->num_rows > 0) {
    // User already reacted, update the reaction
    $existing_reaction = $reaction_result->fetch_assoc();
    
    if ($existing_reaction['reaction_type'] === $reaction_type) {
        // Remove reaction if clicking the same one
        $remove_reaction = $db->prepare("
            DELETE FROM message_reactions 
            WHERE reaction_id = ?
        ");
        $remove_reaction->bind_param("i", $existing_reaction['reaction_id']);
        
        if ($remove_reaction->execute()) {
            echo json_encode([
                'success' => true, 
                'action' => 'removed',
                'message' => 'Reaction removed'
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Failed to remove reaction: ' . $db->error
            ]);
        }
    } else {
        // Update to new reaction type
        $update_reaction = $db->prepare("
            UPDATE message_reactions 
            SET reaction_type = ?, created_at = NOW() 
            WHERE reaction_id = ?
        ");
        $update_reaction->bind_param("si", $reaction_type, $existing_reaction['reaction_id']);
        
        if ($update_reaction->execute()) {
            echo json_encode([
                'success' => true, 
                'action' => 'updated',
                'message' => 'Reaction updated'
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Failed to update reaction: ' . $db->error
            ]);
        }
    }
} else {
    // Add new reaction
    $add_reaction = $db->prepare("
        INSERT INTO message_reactions (message_id, user_id, reaction_type, created_at) 
        VALUES (?, ?, ?, NOW())
    ");
    $add_reaction->bind_param("iis", $message_id, $user_id, $reaction_type);
    
    if ($add_reaction->execute()) {
        echo json_encode([
            'success' => true, 
            'action' => 'added',
            'message' => 'Reaction added'
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Failed to add reaction: ' . $db->error
        ]);
    }
}
?>