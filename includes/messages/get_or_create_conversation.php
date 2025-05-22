<?php
session_start();
require_once '../config.php';
require_once '../db.php';
require_once '../auth/auth_check.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if recipient ID is provided
if (!isset($_POST['recipient_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing recipient ID']);
    exit;
}

$recipient_id = intval($_POST['recipient_id']);
$user_id = $_SESSION['user_id'];

// Don't allow sending messages to yourself
if ($recipient_id === $user_id) {
    echo json_encode(['success' => false, 'message' => 'Cannot send message to yourself']);
    exit;
}

// Check if conversation already exists
$check_conversation = $db->prepare("
    SELECT conversation_id FROM conversations 
    WHERE (user1_id = ? AND user2_id = ?) OR (user1_id = ? AND user2_id = ?)
");
$check_conversation->bind_param("iiii", $user_id, $recipient_id, $recipient_id, $user_id);
$check_conversation->execute();
$result = $check_conversation->get_result();

if ($result->num_rows > 0) {
    // Conversation exists, return its ID
    $conversation = $result->fetch_assoc();
    echo json_encode([
        'success' => true,
        'conversation_id' => $conversation['conversation_id'],
        'message' => 'Existing conversation found'
    ]);
} else {
    // Create new conversation
    $create_conversation = $db->prepare("
        INSERT INTO conversations (user1_id, user2_id, created_at, updated_at)
        VALUES (?, ?, NOW(), NOW())
    ");
    $create_conversation->bind_param("ii", $user_id, $recipient_id);
    
    if ($create_conversation->execute()) {
        $conversation_id = $db->insert_id;
        
        // Add participants
        $add_participants = $db->prepare("
            INSERT INTO conversation_participants (conversation_id, user_id)
            VALUES (?, ?), (?, ?)
        ");
        $add_participants->bind_param("iiii", $conversation_id, $user_id, $conversation_id, $recipient_id);
        $add_participants->execute();
        
        echo json_encode([
            'success' => true,
            'conversation_id' => $conversation_id,
            'message' => 'New conversation created'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to create conversation: ' . $db->error
        ]);
    }
}
?>