<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth/auth_check.php';

// Get conversations for the current user
$conversations_query = $db->prepare("
    SELECT c.*, 
           u1.username as user1_username, u1.full_name as user1_full_name, u1.profile_image as user1_profile_image,
           u2.username as user2_username, u2.full_name as user2_full_name, u2.profile_image as user2_profile_image,
           m.content as last_message, m.created_at as last_message_time, m.sender_id as last_message_sender
    FROM conversations c
    LEFT JOIN users u1 ON c.user1_id = u1.user_id
    LEFT JOIN users u2 ON c.user2_id = u2.user_id
    LEFT JOIN messages m ON m.message_id = (
        SELECT MAX(message_id) FROM messages 
        WHERE conversation_id = c.conversation_id
    )
    WHERE (c.user1_id = ? AND u2.user_id IS NOT NULL) 
       OR (c.user2_id = ? AND u1.user_id IS NOT NULL)
    ORDER BY c.updated_at DESC
");

$conversations_query->bind_param("ii", $_SESSION['user_id'], $_SESSION['user_id']);
$conversations_query->execute();
$conversations_result = $conversations_query->get_result();

$conversations = [];
while ($conversation = $conversations_result->fetch_assoc()) {
    // Determine which user is the other participant
    if ($conversation['user1_id'] == $_SESSION['user_id']) {
        $conversation['other_user_id'] = $conversation['user2_id'];
        $conversation['other_username'] = $conversation['user2_username'];
        $conversation['other_full_name'] = $conversation['user2_full_name'] ?: $conversation['user2_username'];
        $conversation['other_profile_image'] = $conversation['user2_profile_image'] ?: 'assets/images/default-avatar.png';
    } else {
        $conversation['other_user_id'] = $conversation['user1_id'];
        $conversation['other_username'] = $conversation['user1_username'];
        $conversation['other_full_name'] = $conversation['user1_full_name'] ?: $conversation['user1_username'];
        $conversation['other_profile_image'] = $conversation['user1_profile_image'] ?: 'assets/images/default-avatar.png';
    }
    
    $conversations[] = $conversation;
}

// Get active conversation if specified
$active_conversation = null;
$messages = [];
if (isset($_GET['conversation']) && !empty($_GET['conversation'])) {
    $conversation_id = intval($_GET['conversation']);
    
    // Check if conversation exists and user is a participant
    $check_conversation = $db->prepare("
        SELECT c.*, 
               u1.user_id as user1_id, u1.username as user1_username, u1.full_name as user1_full_name, u1.profile_image as user1_profile_image,
               u2.user_id as user2_id, u2.username as user2_username, u2.full_name as user2_full_name, u2.profile_image as user2_profile_image
        FROM conversations c
        JOIN users u1 ON c.user1_id = u1.user_id
        JOIN users u2 ON c.user2_id = u2.user_id
        WHERE c.conversation_id = ? AND (c.user1_id = ? OR c.user2_id = ?)
    ");
    $check_conversation->bind_param("iii", $conversation_id, $_SESSION['user_id'], $_SESSION['user_id']);
    $check_conversation->execute();
    $conversation_result = $check_conversation->get_result();
    
    if ($conversation_result->num_rows > 0) {
        $active_conversation = $conversation_result->fetch_assoc();
        
        // Determine which user is the other participant
        if ($active_conversation['user1_id'] == $_SESSION['user_id']) {
            $active_conversation['recipient_id'] = $active_conversation['user2_id'];
            $active_conversation['recipient_username'] = $active_conversation['user2_username'];
            $active_conversation['recipient_full_name'] = $active_conversation['user2_full_name'] ?: $active_conversation['user2_username'];
            $active_conversation['recipient_profile_image'] = $active_conversation['user2_profile_image'] ?: 'assets/images/default-avatar.png';
        } else {
            $active_conversation['recipient_id'] = $active_conversation['user1_id'];
            $active_conversation['recipient_username'] = $active_conversation['user1_username'];
            $active_conversation['recipient_full_name'] = $active_conversation['user1_full_name'] ?: $active_conversation['user1_username'];
            $active_conversation['recipient_profile_image'] = $active_conversation['user1_profile_image'] ?: 'assets/images/default-avatar.png';
        }
        
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
    }
}

// Get recent users (users you've messaged recently)
$recent_users_query = $db->prepare("
    SELECT DISTINCT u.user_id, u.username, u.full_name, u.profile_image
    FROM conversations c
    JOIN messages m ON m.conversation_id = c.conversation_id
    JOIN users u ON (
        (c.user1_id = ? AND c.user2_id = u.user_id) OR 
        (c.user2_id = ? AND c.user1_id = u.user_id)
    )
    ORDER BY m.created_at DESC
    LIMIT 10
");
$recent_users_query->bind_param("ii", $_SESSION['user_id'], $_SESSION['user_id']);
$recent_users_query->execute();
$recent_users_result = $recent_users_query->get_result();

$recent_users = [];
while ($user = $recent_users_result->fetch_assoc()) {
    if (empty($user['profile_image'])) {
        $user['profile_image'] = 'assets/images/default-avatar.png';
    }
    $recent_users[] = $user;
}

// Include the rest of your inbox.php file (HTML part)
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spheria - Messages</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/sidebar.css">
    <link rel="stylesheet" href="assets/css/inbox.css">
    <link rel="stylesheet" href="assets/css/messages.css">
    <link rel="shortcut icon" href="assets/images/logo2.png" type="image/x-icon">
</head>
<body class="bg">
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>

            <div class="col">
                <div class="messages-container">
                    <!-- Mobile Chat Toggle Button -->
                    <button class="chat-toggle d-md-none" id="chatToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    
                    <!-- Chat List -->
                    <div class="chat-list" id="chatList">
                        <h4 class="messages-title">Messages</h4>
                        
                        <div class="chat-search">
                            <i class="fas fa-search"></i>
                            <input type="text" id="userSearchInput" placeholder="Search users...">
                            <div id="searchResults" class="search-results" style="display: none;"></div>
                        </div>
                        
                        <div class="recent-users">
                            <?php if (count($recent_users) > 0): ?>
                                <?php foreach ($recent_users as $user): ?>
                                    <div class="user-row" data-user-id="<?= $user['user_id'] ?>">
                                        <img src="<?= $user['profile_image'] ?>" alt="<?= htmlspecialchars($user['username']) ?>">
                                        <span><?= htmlspecialchars($user['username']) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="no-recent-users">No recent users</div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="chat-sections">
                            <h6>CONVERSATIONS</h6>
                            
                            <?php if (count($conversations) > 0): ?>
                                <?php foreach ($conversations as $conversation): ?>
                                    <div class="chat-item <?= (isset($_GET['conversation']) && $_GET['conversation'] == $conversation['conversation_id']) ? 'active' : '' ?>" data-conversation-id="<?= $conversation['conversation_id'] ?>">
                                        <img src="<?= $conversation['other_profile_image'] ?>" alt="<?= htmlspecialchars($conversation['other_username']) ?>">
                                        <div class="chat-info">
                                            <div class="chat-header-2">
                                                <h6><?= htmlspecialchars($conversation['other_full_name']) ?></h6>
                                                <span class="time"><?= $conversation['last_message_time'] ? date('g:i a', strtotime($conversation['last_message_time'])) : '' ?></span>
                                            </div>
                                            <p><?php 
                                                if ($conversation['last_message']) {
                                                    // Check if it's a shared post JSON
                                                    $decoded = json_decode($conversation['last_message'], true);
                                                    if (json_last_error() === JSON_ERROR_NONE && isset($decoded['type']) && $decoded['type'] === 'shared_post') {
                                                        echo 'Shared a post';
                                                    } elseif (preg_match('/^\[shared_post:(\d+)\]$/', $conversation['last_message'])) {
                                                        echo 'Shared a post';
                                                    } else {
                                                        echo htmlspecialchars($conversation['last_message']);
                                                    }
                                                } else {
                                                    echo 'No messages yet';
                                                }
                                            ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="no-conversations">No conversations yet</div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Chat Area -->
                    <?php if ($active_conversation): ?>
                        <div class="chat-area">
                            <div class="chat-header">
                                <div class="user-info">
                                    <img src="<?= $active_conversation['recipient_profile_image'] ?>" alt="<?= htmlspecialchars($active_conversation['recipient_username']) ?>">
                                    <div>
                                        <h5><?= htmlspecialchars($active_conversation['recipient_full_name']) ?></h5>
                                        <span>@<?= htmlspecialchars($active_conversation['recipient_username']) ?></span>
                                    </div>
                                </div>
                                <div class="chat-actions">
                                    <a href="profile.php?id=<?= $active_conversation['recipient_id'] ?>" class="action-btn">
                                        <i class="fas fa-user"></i>
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Inside the chat-messages div -->
                            <div class="chat-messages" id="chatMessages">
                                <?php if (count($messages) > 0): ?>
                                    <?php 
                                    require_once 'includes/messages/render_message.php';
                                    foreach ($messages as $message): 
                                    ?>
                                        <div class="message <?= ($message['sender_id'] == $_SESSION['user_id']) ? 'outgoing' : 'incoming' ?>" 
                                             data-message-id="<?= $message['message_id'] ?>">
                                            <?php if ($message['sender_id'] != $_SESSION['user_id']): ?>
                                                <div class="message-avatar">
                                                    <img src="<?= !empty($message['profile_image']) ? $message['profile_image'] : 'assets/images/default-avatar.png' ?>" 
                                                         alt="<?= htmlspecialchars($message['username']) ?>">
                                                </div>
                                            <?php endif; ?>
                                            <div class="message-bubble">
                                                <?= render_message($message['content']) ?>
                                                <div class="message-time">
                                                    <?= date('M j, g:i a', strtotime($message['created_at'])) ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="no-messages">
                                        <p>No messages yet</p>
                                        <p>Start a conversation with <?= htmlspecialchars($active_conversation['recipient_username']) ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="chat-input">
                                <form id="messageForm">
                                    <input type="hidden" id="conversationId" value="<?= $active_conversation['conversation_id'] ?>">
                                    <input type="hidden" id="recipientId" value="<?= $active_conversation['recipient_id'] ?>">
                                    <div class="input-group">
                                        <input type="text" id="messageInput" class="form-control" placeholder="Type a message..." autocomplete="off">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-paper-plane"></i>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="no-conversation-selected">
                            <div class="empty-state">
                                <i class="fas fa-comments fa-4x"></i>
                                <h4>Your Messages</h4>
                                <p>Send private messages to your friends</p>
                                <p>Use the search box to find people</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/messages.js"></script>
    <script>
        // Toggle chat sidebar on mobile
        document.getElementById('chatToggle')?.addEventListener('click', function() {
            document.getElementById('chatList').classList.toggle('active');
        });
        
        // Handle conversation clicks
        document.querySelectorAll('.chat-item').forEach(item => {
            item.addEventListener('click', function() {
                const conversationId = this.getAttribute('data-conversation-id');
                window.location.href = `inbox.php?conversation=${conversationId}`;
                
                // चैट एरिया को एक्टिव करें और z-index बढ़ाएं
                const chatArea = document.querySelector('.chat-area');
                if (chatArea) {
                    chatArea.classList.add('active');
                }
            });
        });
        
        // पेज लोड होने पर चेक करें कि क्या कोई चैट पहले से सेलेक्ट है
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const conversationId = urlParams.get('conversation');
            
            if (conversationId) {
                // अगर कोई चैट सेलेक्ट है तो चैट एरिया को एक्टिव करें
                const chatArea = document.querySelector('.chat-area');
                if (chatArea) {
                    chatArea.classList.add('active');
                }
            } else {
                // अगर कोई चैट सेलेक्ट नहीं है तो चैट एरिया को डीएक्टिव करें
                const chatArea = document.querySelector('.chat-area');
                if (chatArea) {
                    chatArea.classList.remove('active');
                }
            }
        });
        
        // Handle recent user clicks
        document.querySelectorAll('.user-row').forEach(row => {
            row.addEventListener('click', function() {
                const userId = this.getAttribute('data-user-id');
                window.location.href = 'messages.php?user=' + userId;
            });
        });
        
        // User search functionality
        const searchInput = document.getElementById('userSearchInput');
        const searchResults = document.getElementById('searchResults');
        
        searchInput?.addEventListener('input', function() {
            const query = this.value.trim();
            
            if (query.length < 2) {
                searchResults.style.display = 'none';
                return;
            }
            
            fetch('includes/messages/search_users.php?q=' + encodeURIComponent(query))
                .then(response => response.json())
                .then(data => {
                    searchResults.innerHTML = '';
                    
                    if (data.length > 0) {
                        data.forEach(user => {
                            const userRow = document.createElement('div');
                            userRow.className = 'user-row';
                            userRow.innerHTML = `
                                <img src="${user.profile_image || 'assets/images/default-avatar.png'}" alt="${user.username}">
                                <div>
                                    <strong>${user.username}</strong>
                                    ${user.full_name ? `<div>${user.full_name}</div>` : ''}
                                </div>
                            `;
                            userRow.addEventListener('click', function() {
                                window.location.href = 'messages.php?user=' + user.user_id;
                            });
                            searchResults.appendChild(userRow);
                        });
                        searchResults.style.display = 'block';
                    } else {
                        searchResults.innerHTML = '<div class="p-3">No users found</div>';
                        searchResults.style.display = 'block';
                    }
                });
        });
        
        document.addEventListener('click', function(e) {
            if (!searchInput?.contains(e.target) && !searchResults?.contains(e.target)) {
                searchResults.style.display = 'none';
            }
        });


        
    </script>
</body>
</html>
