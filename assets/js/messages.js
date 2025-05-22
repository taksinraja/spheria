document.addEventListener('DOMContentLoaded', function() {
    const chatMessages = document.getElementById('chatMessages');
    const messageForm = document.getElementById('messageForm');
    const messageInput = document.getElementById('messageInput');
    const userSearchInput = document.getElementById('userSearchInput');
    const searchResults = document.getElementById('searchResults');
    const chatItems = document.querySelectorAll('.chat-item');
    const userRows = document.querySelectorAll('.user-row');
    
    // Scroll to bottom of messages
    function scrollToBottom() {
        if (chatMessages) {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
    }
    
    // Initial scroll
    scrollToBottom();
    
    // Send message
    if (messageForm) {
        messageForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const message = messageInput.value.trim();
            if (!message) return;
            
            const conversationId = document.getElementById('conversationId').value;
            const recipientId = document.getElementById('recipientId').value;
            
            // Clear input
            messageInput.value = '';
            
            // Send message to server
            fetch('includes/messages/send_message.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `conversation_id=${conversationId}&recipient_id=${recipientId}&message=${encodeURIComponent(message)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Add message to UI
                    const messageHtml = `
                        <div class="message outgoing" data-message-id="${data.message_id}">
                            <div class="message-bubble">
                                <div class="message-text">${message}</div>
                                <div class="message-time">Just now</div>
                                <div class="message-actions">
                                    <div class="message-action reaction-btn" data-message-id="${data.message_id}">
                                        <i class="far fa-smile"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="reaction-options" style="display: none;">
                                <div class="reaction-option" data-reaction="like" data-message-id="${data.message_id}">👍</div>
                                <div class="reaction-option" data-reaction="love" data-message-id="${data.message_id}">❤️</div>
                                <div class="reaction-option" data-reaction="haha" data-message-id="${data.message_id}">😂</div>
                                <div class="reaction-option" data-reaction="wow" data-message-id="${data.message_id}">😮</div>
                                <div class="reaction-option" data-reaction="sad" data-message-id="${data.message_id}">😢</div>
                                <div class="reaction-option" data-reaction="angry" data-message-id="${data.message_id}">😡</div>
                            </div>
                            <div class="message-reactions" data-message-id="${data.message_id}"></div>
                        </div>
                    `;
                    chatMessages.insertAdjacentHTML('beforeend', messageHtml);
                    scrollToBottom();
                    
                    // Setup reactions for the new message
                    setupMessageReactions();
                } else {
                    console.error('Failed to send message:', data.message);
                    alert('Failed to send message. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error sending message:', error);
                alert('An error occurred while sending your message. Please try again.');
            });
        });
    }
    
    // Check for new messages periodically
    function checkNewMessages() {
        const conversationId = document.getElementById('conversationId')?.value;
        if (!conversationId) return;
        
        let lastMessageId = 0;
        const messages = document.querySelectorAll('.message[data-message-id]');
        if (messages.length > 0) {
            lastMessageId = messages[messages.length - 1].dataset.messageId;
        }
        
        fetch(`includes/messages/check_new_messages.php?conversation_id=${conversationId}&last_message_id=${lastMessageId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.messages.length > 0) {
                // Add new messages to UI
                data.messages.forEach(message => {
                    const messageHtml = `
                        <div class="message incoming" data-message-id="${message.message_id}">
                            <div class="message-avatar">
                                <img src="${message.profile_image}" alt="${message.username}">
                            </div>
                            <div class="message-bubble">
                                <div class="message-text">${message.content}</div>
                                <div class="message-time">${formatMessageTime(message.created_at)}</div>
                                <div class="message-actions">
                                    <div class="message-action reaction-btn" data-message-id="${message.message_id}">
                                        <i class="far fa-smile"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="reaction-options" style="display: none;">
                                <div class="reaction-option" data-reaction="like" data-message-id="${message.message_id}">👍</div>
                                <div class="reaction-option" data-reaction="love" data-message-id="${message.message_id}">❤️</div>
                                <div class="reaction-option" data-reaction="haha" data-message-id="${message.message_id}">😂</div>
                                <div class="reaction-option" data-reaction="wow" data-message-id="${message.message_id}">😮</div>
                                <div class="reaction-option" data-reaction="sad" data-message-id="${message.message_id}">😢</div>
                                <div class="reaction-option" data-reaction="angry" data-message-id="${message.message_id}">😡</div>
                            </div>
                            <div class="message-reactions" data-message-id="${message.message_id}"></div>
                        </div>
                    `;
                    chatMessages.insertAdjacentHTML('beforeend', messageHtml);
                });
                scrollToBottom();
                setupMessageReactions();
            }
        })
        .catch(error => {
            console.error('Error checking for new messages:', error);
        });
    }
    
    // Format message time
    function formatMessageTime(timestamp) {
        const date = new Date(timestamp);
        return date.toLocaleString('en-US', {
            month: 'short',
            day: 'numeric',
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        });
    }
    
    // Start checking for new messages every 5 seconds
    if (chatMessages) {
        setInterval(checkNewMessages, 5000);
    }
    
    // Search for users
    if (userSearchInput) {
        userSearchInput.addEventListener('input', function() {
            const query = this.value.trim();
            
            if (query.length < 2) {
                searchResults.style.display = 'none';
                return;
            }
            
            fetch(`includes/messages/search_users.php?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.users.length > 0) {
                    let resultsHtml = '';
                    data.users.forEach(user => {
                        resultsHtml += `
                            <div class="search-result-item" data-user-id="${user.user_id}">
                                <img src="${user.profile_image}" alt="${user.username}">
                                <div class="user-info">
                                    <h6>${user.full_name}</h6>
                                    <span>@${user.username}</span>
                                </div>
                            </div>
                        `;
                    });
                    searchResults.innerHTML = resultsHtml;
                    searchResults.style.display = 'block';
                    
                    // Add click event to search results
                    document.querySelectorAll('.search-result-item').forEach(item => {
                        item.addEventListener('click', function() {
                            const userId = this.dataset.userId;
                            window.location.href = `messages.php?user=${userId}`;
                        });
                    });
                } else {
                    searchResults.innerHTML = '<div class="p-3 text-center text-light">No users found</div>';
                    searchResults.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error searching for users:', error);
                searchResults.innerHTML = '<div class="p-3 text-center text-danger">Error searching for users</div>';
                searchResults.style.display = 'block';
            });
        });
        
        // Hide search results when clicking outside
        document.addEventListener('click', function(e) {
            if (!userSearchInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.style.display = 'none';
            }
        });
    }
    
    // Handle chat item clicks
    if (chatItems) {
        chatItems.forEach(item => {
            item.addEventListener('click', function() {
                const conversationId = this.dataset.conversationId;
                window.location.href = `inbox.php?conversation=${conversationId}`;
            });
        });
    }
    
    // Handle user row clicks (recent users)
    if (userRows) {
        userRows.forEach(row => {
            row.addEventListener('click', function() {
                const userId = this.dataset.userId;
                window.location.href = `messages.php?user=${userId}`;
            });
        });
    }
    
    // Handle message input focus
    if (messageInput) {
        messageInput.addEventListener('focus', function() {
            // Mark messages as read when user focuses on input
            const conversationId = document.getElementById('conversationId')?.value;
            if (conversationId) {
                fetch(`includes/messages/mark_read.php?conversation_id=${conversationId}`, {
                    method: 'POST'
                }).catch(error => {
                    console.error('Error marking messages as read:', error);
                });
            }
        });
        
        // Auto-focus message input when conversation is loaded
        if (chatMessages && chatMessages.children.length > 0) {
            messageInput.focus();
        }
    }
    
    // Handle emoji picker (if implemented)
    const emojiButton = document.getElementById('emojiButton');
    if (emojiButton && messageInput) {
        emojiButton.addEventListener('click', function() {
            // Toggle emoji picker visibility
            const emojiPicker = document.getElementById('emojiPicker');
            if (emojiPicker) {
                emojiPicker.classList.toggle('show');
            }
        });
    }
    
    // Initialize reactions
    setupMessageReactions();
});

// Handle message reactions
function setupMessageReactions() {
    // Add reaction button to messages
    document.querySelectorAll('.message').forEach(message => {
        if (!message.querySelector('.message-actions')) {
            const messageBubble = message.querySelector('.message-bubble');
            const messageId = message.dataset.messageId;
            
            // Create reaction button
            const actionsDiv = document.createElement('div');
            actionsDiv.className = 'message-actions';
            actionsDiv.innerHTML = `
                <div class="message-action reaction-btn" data-message-id="${messageId}">
                    <i class="far fa-smile"></i>
                </div>
            `;
            
            // Create reaction options
            const reactionOptions = document.createElement('div');
            reactionOptions.className = 'reaction-options';
            reactionOptions.style.display = 'none';
            reactionOptions.innerHTML = `
                <div class="reaction-option" data-reaction="like" data-message-id="${messageId}">👍</div>
                <div class="reaction-option" data-reaction="love" data-message-id="${messageId}">❤️</div>
                <div class="reaction-option" data-reaction="haha" data-message-id="${messageId}">😂</div>
                <div class="reaction-option" data-reaction="wow" data-message-id="${messageId}">😮</div>
                <div class="reaction-option" data-reaction="sad" data-message-id="${messageId}">😢</div>
                <div class="reaction-option" data-reaction="angry" data-message-id="${messageId}">😡</div>
            `;
            
            // Create reactions container
            const reactionsContainer = document.createElement('div');
            reactionsContainer.className = 'message-reactions';
            reactionsContainer.dataset.messageId = messageId;
            
            // Add elements to message
            messageBubble.appendChild(actionsDiv);
            message.appendChild(reactionOptions);
            message.appendChild(reactionsContainer);
            
            // Load existing reactions
            loadMessageReactions(messageId);
        }
    });
    
    // Remove any existing event listeners to prevent duplicates
    document.querySelectorAll('.reaction-btn').forEach(btn => {
        const newBtn = btn.cloneNode(true);
        btn.parentNode.replaceChild(newBtn, btn);
    });
    
    // Add event listeners to reaction buttons
    document.querySelectorAll('.reaction-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const messageId = this.dataset.messageId;
            const message = document.querySelector(`.message[data-message-id="${messageId}"]`);
            const reactionOptions = message.querySelector('.reaction-options');
            
            // Toggle reaction options
            if (reactionOptions.style.display === 'flex') {
                reactionOptions.style.display = 'none';
            } else {
                // Hide all other reaction options first
                document.querySelectorAll('.reaction-options').forEach(el => {
                    el.style.display = 'none';
                });
                reactionOptions.style.display = 'flex';
            }
        });
    });
    
    // Handle reaction selection
    document.querySelectorAll('.reaction-option').forEach(option => {
        option.addEventListener('click', function(e) {
            e.stopPropagation();
            const messageId = this.dataset.messageId;
            const reactionType = this.dataset.reaction;
            
            // Hide reaction options
            document.querySelectorAll('.reaction-options').forEach(el => {
                el.style.display = 'none';
            });
            
            // Send reaction to server
            addMessageReaction(messageId, reactionType);
        });
    });
}

// Load reactions for a message
function loadMessageReactions(messageId) {
    fetch(`includes/messages/get_reactions.php?message_id=${messageId}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const reactionsContainer = document.querySelector(`.message-reactions[data-message-id="${messageId}"]`);
            if (!reactionsContainer) {
                console.error('Reactions container not found for message ID:', messageId);
                return;
            }
            
            reactionsContainer.innerHTML = '';
            
            // Display reaction counts
            for (const [type, count] of Object.entries(data.counts)) {
                if (count > 0) { // Only show reactions with a count
                    const isUserReaction = data.user_reaction === type;
                    const reactionEmoji = getReactionEmoji(type);
                    
                    const reactionEl = document.createElement('div');
                    reactionEl.className = `reaction-count ${isUserReaction ? 'active' : ''}`;
                    reactionEl.dataset.reaction = type;
                    reactionEl.dataset.messageId = messageId;
                    reactionEl.innerHTML = `${reactionEmoji} ${count}`;
                    
                    reactionEl.addEventListener('click', function() {
                        addMessageReaction(messageId, type);
                    });
                    
                    reactionsContainer.appendChild(reactionEl);
                }
            }
            
            // Make sure the container is visible if it has reactions
            if (Object.keys(data.counts).length > 0 && 
                Object.values(data.counts).some(count => count > 0)) {
                reactionsContainer.style.display = 'flex';
            } else {
                reactionsContainer.style.display = 'none';
            }
        } else {
            console.error('Failed to load reactions:', data.message);
        }
    })
    .catch(error => {
        console.error('Error loading reactions:', error);
    });
}

// Add or update a reaction
function addMessageReaction(messageId, reactionType) {
    fetch('includes/messages/react_to_message.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `message_id=${messageId}&reaction_type=${reactionType}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload reactions to update UI
            loadMessageReactions(messageId);
        } else {
            console.error('Failed to add reaction:', data.message);
        }
    })
    .catch(error => {
        console.error('Error adding reaction:', error);
    });
}

// Get emoji for reaction type
function getReactionEmoji(type) {
    const emojis = {
        'like': '👍',
        'love': '❤️',
        'haha': '😂',
        'wow': '😮',
        'sad': '😢',
        'angry': '😡'
    };
    return emojis[type] || '👍';
}

// Initialize reactions when page loads
document.addEventListener('DOMContentLoaded', function() {
    setupMessageReactions();
    
    // Set up observer to handle dynamically added messages
    const chatMessages = document.getElementById('chatMessages');
    if (chatMessages) {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.addedNodes.length) {
                    setupMessageReactions();
                }
            });
        });
        
        observer.observe(chatMessages, { childList: true });
    }
});

// Add these event listeners for media uploads
document.getElementById('imageUpload').addEventListener('change', handleMediaUpload);
document.getElementById('videoUpload').addEventListener('change', handleMediaUpload);
document.getElementById('audioUpload').addEventListener('change', handleMediaUpload);
document.getElementById('clearMediaPreview').addEventListener('click', clearMediaPreview);

let selectedMedia = null;

function handleMediaUpload(event) {
    const file = event.target.files[0];
    if (!file) return;

    selectedMedia = file;
    const preview = document.getElementById('mediaPreview');
    const previewContent = preview.querySelector('.preview-content');
    previewContent.innerHTML = '';

    if (file.type.startsWith('image/')) {
        const img = document.createElement('img');
        img.src = URL.createObjectURL(file);
        previewContent.appendChild(img);
    } else if (file.type.startsWith('video/')) {
        const video = document.createElement('video');
        video.src = URL.createObjectURL(file);
        video.controls = true;
        previewContent.appendChild(video);
    } else if (file.type.startsWith('audio/')) {
        const audio = document.createElement('audio');
        audio.src = URL.createObjectURL(file);
        audio.controls = true;
        previewContent.appendChild(audio);
    }

    preview.style.display = 'block';
}

function clearMediaPreview() {
    selectedMedia = null;
    const preview = document.getElementById('mediaPreview');
    preview.style.display = 'none';
    preview.querySelector('.preview-content').innerHTML = '';
    document.getElementById('imageUpload').value = '';
    document.getElementById('videoUpload').value = '';
    document.getElementById('audioUpload').value = '';
}

// Update your existing form submission handler
document.getElementById('messageForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const messageInput = document.getElementById('messageInput');
    const message = messageInput.value.trim();
    const conversationId = document.getElementById('conversationId').value;
    const recipientId = document.getElementById('recipientId').value;

    if (!message && !selectedMedia) return;

    const formData = new FormData();
    formData.append('conversation_id', conversationId);
    formData.append('recipient_id', recipientId);
    formData.append('message', message);
    
    if (selectedMedia) {
        formData.append('media', selectedMedia);
    }

    try {
        const response = await fetch('includes/messages/send_message.php', {
            method: 'POST',
            body: formData
        });

        if (response.ok) {
            messageInput.value = '';
            clearMediaPreview();
            // Refresh messages or append new message
            // Your existing code to handle successful message sending
        }
    } catch (error) {
        console.error('Error sending message:', error);
    }
});