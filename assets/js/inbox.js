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
                            </div>
                        </div>
                    `;
                    chatMessages.insertAdjacentHTML('beforeend', messageHtml);
                    scrollToBottom();
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
                            </div>
                        </div>
                    `;
                    chatMessages.insertAdjacentHTML('beforeend', messageHtml);
                });
                scrollToBottom();
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
    chatItems.forEach(item => {
        item.addEventListener('click', function() {
            const conversationId = this.dataset.conversationId;
            window.location.href = `inbox.php?conversation=${conversationId}`;
        });
    });
    
    // Handle user row clicks
    userRows.forEach(row => {
        row.addEventListener('click', function() {
            const userId = this.dataset.userId;
            window.location.href = `messages.php?user=${userId}`;
        });
    });
    
    // Check for new messages every 5 seconds
    if (chatMessages) {
        setInterval(checkNewMessages, 5000);
    }
});