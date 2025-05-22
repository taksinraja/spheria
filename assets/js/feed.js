document.addEventListener('DOMContentLoaded', function() {
    // Like button functionality
    setupLikeButtons();
    
    // Comment functionality
    setupCommentForms();
    
    // Share functionality
    setupShareButtons();
    
    // Save post functionality
    setupSaveButtons();
});

function setupLikeButtons() {
    const likeButtons = document.querySelectorAll('.like-btn');
    
    likeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const postId = this.getAttribute('data-post-id');
            const likeIcon = this.querySelector('i');
            const likesCountElement = this.querySelector('.likes-count');
            
            // Send AJAX request to toggle like
            fetch('includes/posts/toggle_like.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `post_id=${postId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update UI based on like status
                    if (data.liked) {
                        likeIcon.classList.remove('far');
                        likeIcon.classList.add('fas');
                        button.classList.add('liked');
                    } else {
                        likeIcon.classList.remove('fas');
                        likeIcon.classList.add('far');
                        button.classList.remove('liked');
                    }
                    
                    // Update likes count
                    likesCountElement.textContent = data.likes_count;
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    });
}

function setupCommentForms() {
    const commentForms = document.querySelectorAll('.quick-comment-form form');
    
    commentForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const input = this.querySelector('.comment-input');
            const postId = input.getAttribute('data-post-id');
            const commentText = input.value.trim();
            
            if (commentText === '') return;
            
            // Send AJAX request to add comment
            const formData = new FormData();
            formData.append('post_id', postId);
            formData.append('comment_text', commentText);
            
            fetch('includes/comments/add_comment.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Clear input
                    input.value = '';
                    
                    // Update comments count
                    const commentsCountElement = document.querySelector(`.post-card[data-post-id="${postId}"] .comments-count`);
                    const currentCount = parseInt(commentsCountElement.textContent);
                    commentsCountElement.textContent = currentCount + 1;
                    
                    // Show success message
                    showNotification('Comment added successfully!');
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    });
}

function setupShareButtons() {
    const shareButtons = document.querySelectorAll('.share-btn, .post-action[data-action="share"]');
    const shareModal = document.querySelector('.share-modal');
    
    if (!shareModal) return;

    const closeBtn = shareModal.querySelector('.share-close');
    const shareOptions = shareModal.querySelectorAll('.share-option-item');
    const followerSearch = shareModal.querySelector('#follower-search');
    const followersContainer = shareModal.querySelector('.share-followers-container');
    
    // Load followers when modal opens
    async function loadFollowers() {
        try {
            console.log('Loading followers...');
            followersContainer.innerHTML = '<div class="p-3 text-center text-white">Loading followers...</div>';
            
            const response = await fetch('includes/users/get_followers.php');
            const data = await response.json();
            
            console.log('Followers data:', data);
            
            if (data.success) {
                if (data.followers && data.followers.length > 0) {
                    let followersHTML = '';
                    
                    data.followers.forEach(follower => {
                        followersHTML += `
                            <div class="follower-item" data-user-id="${follower.user_id}">
                                <div class="follower-avatar">
                                    <img src="${follower.profile_image || 'assets/images/default-avatar.png'}" alt="${follower.username}">
                                </div>
                                <div class="follower-info">
                                    <div class="follower-name">${follower.username}</div>
                                    <div class="follower-username">${follower.full_name || ''}</div>
                                </div>
                            </div>
                        `;
                    });
                    
                    followersContainer.innerHTML = followersHTML;
                    
                    // Add click event to follower items
                    const followerItems = followersContainer.querySelectorAll('.follower-item');
                    followerItems.forEach(item => {
                        item.addEventListener('click', async function() {
                            const userId = this.getAttribute('data-user-id');
                            const postId = shareModal.getAttribute('data-post-id');
                            
                            try {
                                const formData = new FormData();
                                formData.append('post_id', postId);
                                formData.append('user_id', userId);
                                
                                const response = await fetch('includes/messages/share_post_to_user.php', {
                                    method: 'POST',
                                    body: formData
                                });
                                
                                const data = await response.json();
                                if (data.success) {
                                    showNotification('Post shared successfully!');
                                    shareModal.classList.remove('active');
                                } else {
                                    showNotification('Error sharing post: ' + (data.message || 'Unknown error'), 'error');
                                    console.error('Share error:', data);
                                }
                            } catch (error) {
                                console.error('Error sharing post:', error);
                                showNotification('Error sharing post', 'error');
                            }
                        });
                    });
                } else {
                    followersContainer.innerHTML = '<div class="p-3 text-center text-white">No followers found</div>';
                }
            } else {
                followersContainer.innerHTML = `<div class="p-3 text-center text-white">Error: ${data.message || 'Could not load followers'}</div>`;
            }
        } catch (error) {
            console.error('Error loading followers:', error);
            followersContainer.innerHTML = '<div class="p-3 text-center text-white">Error loading followers</div>';
        }
    }
    
    // Filter followers based on search input
    followerSearch.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const followerItems = followersContainer.querySelectorAll('.follower-item');
        
        followerItems.forEach(item => {
            const name = item.querySelector('.follower-name').textContent.toLowerCase();
            const username = item.querySelector('.follower-username').textContent.toLowerCase();
            
            if (name.includes(searchTerm) || username.includes(searchTerm)) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
    });

    // Handle share options
    shareOptions.forEach(option => {
        option.addEventListener('click', async function(e) {
            e.preventDefault();
            const platform = this.getAttribute('data-platform');
            const postId = shareModal.getAttribute('data-post-id');
            const postUrl = `${window.location.origin}/spheria1/post.php?id=${postId}`;
            
            if (platform === 'link') {
                // Copy link to clipboard
                navigator.clipboard.writeText(postUrl).then(() => {
                    showNotification('Link copied to clipboard!');
                    shareModal.classList.remove('active');
                }).catch(err => {
                    console.error('Could not copy text: ', err);
                    showNotification('Error copying link', 'error');
                });
                return;
            }
            
            let shareUrl;
            switch(platform) {
                case 'facebook':
                    shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(postUrl)}`;
                    break;
                case 'messenger':
                    shareUrl = `https://www.facebook.com/dialog/send?link=${encodeURIComponent(postUrl)}&app_id=YOUR_FACEBOOK_APP_ID&redirect_uri=${encodeURIComponent(window.location.href)}`;
                    break;
                case 'twitter':
                    shareUrl = `https://twitter.com/intent/tweet?url=${encodeURIComponent(postUrl)}`;
                    break;
                case 'whatsapp':
                    shareUrl = `https://api.whatsapp.com/send?text=${encodeURIComponent(postUrl)}`;
                    break;
                case 'email':
                    shareUrl = `mailto:?subject=Check out this post on Spheria&body=${encodeURIComponent(postUrl)}`;
                    break;
                case 'instagram':
                    // Instagram doesn't have a direct sharing API, so we'll just copy the link
                    navigator.clipboard.writeText(postUrl).then(() => {
                        showNotification('Link copied for Instagram!');
                    });
                    break;
            }
            
            if (shareUrl) {
                window.open(shareUrl, '_blank', 'width=600,height=400');
            }
            
            shareModal.classList.remove('active');
        });
    });

    // Close modal handlers
    closeBtn.addEventListener('click', () => shareModal.classList.remove('active'));
    
    shareModal.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.remove('active');
        }
    });

    // Share button click handler
    shareButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const postId = this.closest('.post-card').getAttribute('data-post-id');
            
            if (!postId) {
                console.error('Could not find post ID');
                return;
            }
            
            shareModal.setAttribute('data-post-id', postId);
            shareModal.classList.add('active');
            
            // Load followers when modal opens
            loadFollowers();
        });
    });
}

function setupSaveButtons() {
    const saveButtons = document.querySelectorAll('.save-btn');
    
    saveButtons.forEach(button => {
        button.addEventListener('click', function() {
            const postId = this.getAttribute('data-post-id');
            const saveIcon = this.querySelector('i');
            
            // Send AJAX request to toggle save status
            fetch('includes/posts/toggle_save.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `post_id=${postId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update UI based on save status
                    if (data.saved) {
                        saveIcon.classList.remove('far');
                        saveIcon.classList.add('fas');
                        button.classList.add('saved');
                        showNotification('Post saved successfully!');
                    } else {
                        saveIcon.classList.remove('fas');
                        saveIcon.classList.add('far');
                        button.classList.remove('saved');
                        showNotification('Post removed from saved items');
                    }
                } else {
                    console.error('Error:', data.message);
                    showNotification('Error saving post', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error saving post', 'error');
            });
        });
    });
}

// Show notification
function showNotification(message, type = 'success') {
    // Create notification element if it doesn't exist
    if (!document.querySelector('.notification')) {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        document.body.appendChild(notification);
    }
    
    const notification = document.querySelector('.notification');
    notification.textContent = message;
    notification.classList.add('show');
    
    // Hide notification after 3 seconds
    setTimeout(() => {
        notification.classList.remove('show');
    }, 3000);
}
// Add this to your existing feed.js file

// Handle follow buttons in right sidebar
document.addEventListener('DOMContentLoaded', function() {
    const followButtons = document.querySelectorAll('.follow-btn');
    
    followButtons.forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.getAttribute('data-user-id');
            
            // Toggle button state for immediate feedback
            if (this.textContent === 'Follow') {
                this.textContent = 'Following';
                this.style.backgroundColor = '#333';
            } else {
                this.textContent = 'Follow';
                this.style.backgroundColor = '#a970ff';
            }
            
            // Send AJAX request to follow/unfollow user
            fetch('includes/users/toggle_follow.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `user_id=${userId}`
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    // Revert button state if request failed
                    if (this.textContent === 'Following') {
                        this.textContent = 'Follow';
                        this.style.backgroundColor = '#a970ff';
                    } else {
                        this.textContent = 'Following';
                        this.style.backgroundColor = '#333';
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    });
});
