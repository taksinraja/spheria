<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth/auth_check.php';

// Check if user ID is provided
if (!isset($_GET['user']) || empty($_GET['user'])) {
    // Redirect to inbox if no user specified
    header("Location: inbox.php");
    exit;
}

$recipient_id = intval($_GET['user']);

// Don't allow messaging yourself
if ($recipient_id == $_SESSION['user_id']) {
    header("Location: inbox.php");
    exit;
}

// Check if recipient exists
$check_user = $db->prepare("SELECT user_id, username, full_name, profile_image FROM users WHERE user_id = ?");
$check_user->bind_param("i", $recipient_id);
$check_user->execute();
$user_result = $check_user->get_result();

if ($user_result->num_rows === 0) {
    // User doesn't exist, redirect to inbox
    header("Location: inbox.php");
    exit;
}

// Get recipient information
$recipient = $user_result->fetch_assoc();

// Check if conversation already exists between these users
$check_conversation = $db->prepare("
    SELECT conversation_id FROM conversations 
    WHERE (user1_id = ? AND user2_id = ?) OR (user1_id = ? AND user2_id = ?)
");
$check_conversation->bind_param("iiii", $_SESSION['user_id'], $recipient_id, $recipient_id, $_SESSION['user_id']);
$check_conversation->execute();
$conversation_result = $check_conversation->get_result();

$conversation_id = 0;
$messages = [];

if ($conversation_result->num_rows > 0) {
    // Conversation exists, get messages
    $conversation = $conversation_result->fetch_assoc();
    $conversation_id = $conversation['conversation_id'];
    
    // Get messages for this conversation
    $messages_query = $db->prepare("
        SELECT m.*, u.username, u.profile_image 
        FROM messages m
        JOIN users u ON m.sender_id = u.user_id
        WHERE m.conversation_id = ?
        ORDER BY m.created_at ASC
    ");
    $messages_query->bind_param("i", $conversation_id);
    $messages_query->execute();
    $messages_result = $messages_query->get_result();
    
    while ($message = $messages_result->fetch_assoc()) {
        $messages[] = $message;
        
        // Mark message as read if it's not from current user
        if ($message['sender_id'] != $_SESSION['user_id'] && $message['is_read'] == 0) {
            $mark_read = $db->prepare("UPDATE messages SET is_read = 1 WHERE message_id = ?");
            $mark_read->bind_param("i", $message['message_id']);
            $mark_read->execute();
        }
    }
    
    // No need to redirect, we'll show the conversation
} else {
    // Create new conversation
    $create_conversation = $db->prepare("
        INSERT INTO conversations (user1_id, user2_id, created_at, updated_at) 
        VALUES (?, ?, NOW(), NOW())
    ");
    $create_conversation->bind_param("ii", $_SESSION['user_id'], $recipient_id);
    
    if ($create_conversation->execute()) {
        $conversation_id = $db->insert_id;
        
        // Add participants (for future group chat support)
        $add_participants = $db->prepare("
            INSERT INTO conversation_participants (conversation_id, user_id) 
            VALUES (?, ?), (?, ?)
        ");
        $add_participants->bind_param("iiii", $conversation_id, $_SESSION['user_id'], $conversation_id, $recipient_id);
        $add_participants->execute();
        
        // No messages yet, but we'll show the empty conversation
    } else {
        // Error creating conversation
        $_SESSION['error'] = "Failed to create conversation. Please try again.";
        header("Location: profile.php?id=" . $recipient_id);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spheria - Messages with <?= htmlspecialchars($recipient['username']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- <link rel="stylesheet" href="assets/css/style.css"> -->
    <link rel="stylesheet" href="assets/css/sidebar.css">
    <link rel="stylesheet" href="assets/css/inbox.css">
    <link rel="stylesheet" href="assets/css/messages.css">
    <link rel="shortcut icon" href="assets/images/logo2.png" type="image/x-icon">

</head>
<body class="bg">
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>

            <div class="col p-0 main-cont">
                <div class="messages-container">
                    <div class="chat-area">
                        <div class="chat-header">
                            <div class="user-info">
                                <a href="inbox.php" class="back-btn me-2 d-flex align-items-center justify-content-center">
                                    <i class="fas fa-arrow-left"></i>
                                </a>
                                <img src="<?= !empty($recipient['profile_image']) ? $recipient['profile_image'] : 'assets/images/default-avatar.png' ?>" 
                                     alt="<?= htmlspecialchars($recipient['username']) ?>"
                                     class="chat-header-avatar">
                                <div>
                                    <h5><?= htmlspecialchars($recipient['full_name'] ?? $recipient['username']) ?></h5>
                                    <span>@<?= htmlspecialchars($recipient['username']) ?></span>
                                </div>
                            </div>
                            <div class="chat-actions">
                                <a href="profile.php?id=<?= $recipient['user_id'] ?>" class="action-btn">
                                    <i class="fas fa-user"></i>
                                </a>
                            </div>
                        </div>
                        
                        <div class="chat-messages" id="chatMessages">
                            <?php if (count($messages) > 0): ?>
                                <?php 
                                require_once 'includes/messages/render_message.php';
                                foreach ($messages as $message): 
                                ?>
                                    <div class="message <?= ($message['sender_id'] == $_SESSION['user_id']) ? 'outgoing' : 'incoming' ?>" data-message-id="<?= $message['message_id'] ?>">
                                        <?php if ($message['sender_id'] != $_SESSION['user_id']): ?>
                                            <div class="message-avatar">
                                                <img src="<?= !empty($message['profile_image']) ? $message['profile_image'] : 'assets/images/default-avatar.png' ?>" alt="<?= htmlspecialchars($message['username']) ?>">
                                            </div>
                                        <?php endif; ?>
                                        <div class="message-bubble">
                                            <?= render_message($message['content']) ?>
                                            <div class="message-time"><?= date('M j, g:i a', strtotime($message['created_at'])) ?></div>
                                            <div class="message-actions">
                                                <div class="message-action reaction-btn" data-message-id="<?= $message['message_id'] ?>">
                                                    <i class="far fa-smile"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="reaction-options" style="display: none;">
                                            <div class="reaction-option" data-reaction="like" data-message-id="<?= $message['message_id'] ?>">👍</div>
                                            <div class="reaction-option" data-reaction="love" data-message-id="<?= $message['message_id'] ?>">❤️</div>
                                            <div class="reaction-option" data-reaction="haha" data-message-id="<?= $message['message_id'] ?>">😂</div>
                                            <div class="reaction-option" data-reaction="wow" data-message-id="<?= $message['message_id'] ?>">😮</div>
                                            <div class="reaction-option" data-reaction="sad" data-message-id="<?= $message['message_id'] ?>">😢</div>
                                            <div class="reaction-option" data-reaction="angry" data-message-id="<?= $message['message_id'] ?>">😡</div>
                                        </div>
                                        <div class="message-reactions" data-message-id="<?= $message['message_id'] ?>"></div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="no-messages">
                                    <p>No messages yet</p>
                                    <p>Start a conversation with <?= htmlspecialchars($recipient['username']) ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="chat-input">
                            <form id="messageForm" enctype="multipart/form-data">
                                <input type="hidden" id="conversationId" value="<?= $conversation_id ?>">
                                <input type="hidden" id="recipientId" value="<?= $recipient_id ?>">
                                <div class="input-group">
                                    <!-- <div class="media-buttons">
                                        <label for="imageUpload" class="media-btn" title="Send image">
                                            <i class="fas fa-image"></i>
                                            <input type="file" id="imageUpload" accept="image/" style="display: none">
                                        </label>
                                        <label for="videoUpload" class="media-btn" title="Send video">
                                            <i class="fas fa-video"></i>
                                            <input type="file" id="videoUpload" accept="video/" style="display: none">
                                        </label>
                                        <label for="audioUpload" class="media-btn" title="Send audio">
                                            <i class="fas fa-microphone"></i>
                                            <input type="file" id="audioUpload" accept="audio/" style="display: none">
                                        </label>
                                    </div> -->
                                    <input type="text" id="messageInput" class="form-control" placeholder="Type a message..." autocomplete="off">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane"></i>
                                    </button>
                                </div>
                                <div id="mediaPreview" class="media-preview" style="display: none;">
                                    <div class="preview-content"></div>
                                    <button type="button" class="btn-close" id="clearMediaPreview">×</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/messages.js"></script>
</body>
</html>