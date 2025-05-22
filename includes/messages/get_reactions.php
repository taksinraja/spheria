<?php
session_start();
require_once '../config.php';
require_once '../db.php';
require_once '../auth/auth_check.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if message ID is provided
if (!isset($_GET['message_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing message ID']);
    exit;
}

$message_id = intval($_GET['message_id']);
$user_id = $_SESSION['user_id'];

// Check if user has access to this message
$check_access = $db->prepare("
    SELECT m.* FROM messages m
    JOIN conversation_participants cp ON m.conversation_id = cp.conversation_id
    WHERE m.message_id = ? AND cp.user_id = ?
");
$check_access->bind_param("ii", $message_id, $user_id);
$check_access->execute();
$access_result = $check_access->get_result();

if ($access_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Message not found or you don\'t have access']);
    exit;
}

// Get all reactions for this message
$get_reactions = $db->prepare("
    SELECT mr.*, u.username, u.profile_image 
    FROM message_reactions mr
    JOIN users u ON mr.user_id = u.user_id
    WHERE mr.message_id = ?
    ORDER BY mr.created_at ASC
");
$get_reactions->bind_param("i", $message_id);
$get_reactions->execute();
$reactions_result = $get_reactions->get_result();

$reactions = [];
$reaction_counts = [];
$user_reaction = null;

while ($reaction = $reactions_result->fetch_assoc()) {
    // Count reactions by type
    if (!isset($reaction_counts[$reaction['reaction_type']])) {
        $reaction_counts[$reaction['reaction_type']] = 0;
    }
    $reaction_counts[$reaction['reaction_type']]++;
    
    // Store user's own reaction
    if ($reaction['user_id'] == $user_id) {
        $user_reaction = $reaction['reaction_type'];
    }
    
    $reactions[] = $reaction;
}

echo json_encode([
    'success' => true,
    'reactions' => $reactions,
    'counts' => $reaction_counts,
    'user_reaction' => $user_reaction
]);
?>