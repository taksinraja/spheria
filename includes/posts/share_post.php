<?php
session_start();
require_once '../config.php';
require_once '../db.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Check if required parameters are provided
if (!isset($_POST['shared_with']) || !isset($_POST['post_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$sender_id = $_SESSION['user_id'];
$recipient_id = intval($_POST['shared_with']);
$post_id = intval($_POST['post_id']);

// Validate post exists
$check_post = $db->prepare("SELECT post_id FROM posts WHERE post_id = ?");
$check_post->bind_param("i", $post_id);
$check_post->execute();
if ($check_post->get_result()->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Post not found']);
    exit;
}

// Check if conversation exists between these users
$check_conversation = $db->prepare("
    SELECT conversation_id FROM conversations 
    WHERE (user1_id = ? AND user2_id = ?) OR (user1_id = ? AND user2_id = ?)
");
$check_conversation->bind_param("iiii", $sender_id, $recipient_id, $recipient_id, $sender_id);
$check_conversation->execute();
$conversation_result = $check_conversation->get_result();

$conversation_id = 0;

if ($conversation_result->num_rows > 0) {
    // Conversation exists
    $conversation = $conversation_result->fetch_assoc();
    $conversation_id = $conversation['conversation_id'];
} else {
    // Create new conversation
    $create_conversation = $db->prepare("
        INSERT INTO conversations (user1_id, user2_id, created_at, updated_at) 
        VALUES (?, ?, NOW(), NOW())
    ");
    $create_conversation->bind_param("ii", $sender_id, $recipient_id);
    
    if ($create_conversation->execute()) {
        $conversation_id = $db->insert_id;
        
        // Add participants
        $add_participants = $db->prepare("
            INSERT INTO conversation_participants (conversation_id, user_id) 
            VALUES (?, ?), (?, ?)
        ");
        $add_participants->bind_param("iiii", $conversation_id, $sender_id, $conversation_id, $recipient_id);
        $add_participants->execute();
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create conversation']);
        exit;
    }
}

// Create a special message format for shared posts
$message_content = json_encode([
    'type' => 'shared_post',
    'post_id' => $post_id
]);

// Insert message
$insert_message = $db->prepare("
    INSERT INTO messages (conversation_id, sender_id, content, created_at) 
    VALUES (?, ?, ?, NOW())
");
$insert_message->bind_param("iis", $conversation_id, $sender_id, $message_content);

if ($insert_message->execute()) {
    // Update conversation timestamp
    $update_conversation = $db->prepare("
        UPDATE conversations SET updated_at = NOW() WHERE conversation_id = ?
    ");
    $update_conversation->bind_param("i", $conversation_id);
    $update_conversation->execute();
    
    // Get share count for this post
    $count_shares = $db->prepare("
        SELECT COUNT(*) as count FROM messages 
        WHERE content LIKE ?
    ");
    $share_pattern = '%"type":"shared_post","post_id":' . $post_id . '%';
    $count_shares->bind_param("s", $share_pattern);
    $count_shares->execute();
    $shares_count = $count_shares->get_result()->fetch_assoc()['count'];
    
    echo json_encode([
        'success' => true, 
        'message' => 'Post shared successfully',
        'shares_count' => $shares_count
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to share post']);
}
?>