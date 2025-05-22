document.addEventListener('DOMContentLoaded', function() {
    const commentForm = document.querySelector('.comment-form');
    const commentsSection = document.querySelector('.comments-section');

    if (commentForm) {
        commentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('/spheria1/includes/comments/add_comment.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Add new comment to the comments section
                    const newComment = createCommentElement(data.comment);
                    commentsSection.insertBefore(newComment, commentsSection.firstChild);
                    
                    // Clear the input
                    commentForm.reset();
                }
            })
            .catch(error => console.error('Error:', error));
        });
    }

    function createCommentElement(comment) {
        const div = document.createElement('div');
        div.className = 'comment';
        div.innerHTML = `
            <div class="user-info">
                <img src="${comment.profile_image || 'assets/images/default-avatar.png'}" alt="Profile">
                <div>
                    <a href="profile.php?username=${comment.username}">${comment.username}</a>
                    <p>${comment.comment_text}</p>
                </div>
            </div>
        `;
        return div;
    }
});

// Add this to your existing post.js file
document.addEventListener('DOMContentLoaded', function() {
    const likeBtn = document.querySelector('.like-btn');
    const likesCount = document.querySelector('.likes-count');

    if (likeBtn) {
        likeBtn.addEventListener('click', function() {
            const postId = this.dataset.postId;
            
            fetch('/spheria1/includes/posts/toggle_like.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `post_id=${postId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update like button state
                    this.classList.toggle('liked');
                    this.querySelector('i').classList.toggle('far');
                    this.querySelector('i').classList.toggle('fas');
                    
                    // Update likes count
                    likesCount.textContent = `${data.likes_count} likes`;
                }
            })
            .catch(error => console.error('Error:', error));
        });
    }
});

document.addEventListener('DOMContentLoaded', function() {
    // Share button functionality
    const shareBtn = document.querySelector('.share-btn');
    const shareModal = document.querySelector('.share-modal');
    const shareCloseBtn = document.querySelector('.share-close');
    
    if (shareBtn && shareModal) {
        shareBtn.addEventListener('click', function() {
            shareModal.classList.add('active');
        });
        
        shareCloseBtn.addEventListener('click', function() {
            shareModal.classList.remove('active');
        });
        
        shareModal.addEventListener('click', function(e) {
            if (e.target === shareModal) {
                shareModal.classList.remove('active');
            }
        });
        
        // Copy link button
        const copyLinkBtn = document.querySelector('.copy-link-btn');
        const shareLinkInput = document.querySelector('#share-link');
        
        copyLinkBtn.addEventListener('click', function() {
            shareLinkInput.select();
            document.execCommand('copy');
            this.textContent = 'Copied!';
            setTimeout(() => {
                this.textContent = 'Copy';
            }, 2000);
        });
        
        // Share options
        const shareOptions = document.querySelectorAll('.share-option');
        shareOptions.forEach(option => {
            option.addEventListener('click', function(e) {
                e.preventDefault();
                const platform = this.getAttribute('data-platform');
                const url = document.getElementById('share-link').value;
                let shareUrl;
                
                switch(platform) {
                    case 'facebook':
                        shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
                        break;
                    case 'twitter':
                        shareUrl = `https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}&text=Check out this post on Spheria!`;
                        break;
                    case 'whatsapp':
                        shareUrl = `https://api.whatsapp.com/send?text=Check out this post on Spheria! ${encodeURIComponent(url)}`;
                        break;
                    case 'telegram':
                        shareUrl = `https://t.me/share/url?url=${encodeURIComponent(url)}&text=Check out this post on Spheria!`;
                        break;
                }
                
                window.open(shareUrl, '_blank');
            });
        });
    }
    
    // Like button functionality
    const likeBtn = document.querySelector('.like-btn');
    if (likeBtn) {
        likeBtn.addEventListener('click', function() {
            const postId = this.getAttribute('data-post-id');
            const likeIcon = this.querySelector('i');
            const likesCountElement = document.querySelector('.likes-count');
            
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
                        this.classList.add('liked');
                    } else {
                        likeIcon.classList.remove('fas');
                        likeIcon.classList.add('far');
                        this.classList.remove('liked');
                    }
                    
                    // Update likes count
                    likesCountElement.textContent = data.likes_count + ' likes';
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    }
    
    // Comment form submission
    const commentForm = document.querySelector('.comment-form');
    if (commentForm) {
        commentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const postId = this.querySelector('input[name="post_id"]').value;
            const commentInput = this.querySelector('input[name="comment_text"]');
            const commentText = commentInput.value.trim();
            
            if (commentText === '') return;
            
            // Send AJAX request to add comment
            const formData = new FormData(this);
            
            fetch('includes/comments/add_comment.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Clear input
                    commentInput.value = '';
                    
                    // Create new comment element
                    const commentsSection = document.querySelector('.comments-section');
                    const newComment = document.createElement('div');
                    newComment.className = 'comment';
                    newComment.innerHTML = `
                        <div class="user-info">
                            <img src="${data.comment.profile_image}" alt="Profile">
                            <div>
                                <a href="profile.php?username=${data.comment.username}">${data.comment.username}</a>
                                <p>${data.comment.comment_text}</p>
                                <small class="text-muted">Just now</small>
                            </div>
                        </div>
                    `;
                    
                    // Add new comment to the top of the comments section
                    commentsSection.insertBefore(newComment, commentsSection.firstChild);
                    
                    // Update comments count if displayed
                    const commentsCountElement = document.querySelector('.comments-count');
                    if (commentsCountElement) {
                        const currentCount = parseInt(commentsCountElement.textContent);
                        commentsCountElement.textContent = currentCount + 1;
                    }
                    
                    // Show notification
                    showNotification('Comment added successfully!');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Failed to add comment', 'error');
            });
        });
    }
    
    // Comment button focus on input
    const commentBtn = document.querySelector('.comment-btn');
    if (commentBtn) {
        commentBtn.addEventListener('click', function() {
            document.querySelector('.comment-form input[name="comment_text"]').focus();
        });
    }
    
    // Media navigation for multiple media items
    setupMediaNavigation();
    
    // Close button functionality
    const closeBtn = document.querySelector('.btn-close');
    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            window.history.back();
        });
    }
});

// Show notification
function showNotification(message, type = 'success') {
    // Create notification element if it doesn't exist
    if (!document.querySelector('.notification')) {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        document.body.appendChild(notification);
        
        // Add notification styles if not already in CSS
        if (!document.querySelector('style#notification-styles')) {
            const style = document.createElement('style');
            style.id = 'notification-styles';
            style.textContent = `
                .notification {
                    position: fixed;
                    bottom: 20px;
                    right: 20px;
                    padding: 10px 20px;
                    border-radius: 5px;
                    color: white;
                    font-weight: 500;
                    z-index: 1200;
                    transform: translateY(100px);
                    opacity: 0;
                    transition: all 0.3s ease;
                }
                
                .notification.success {
                    background-color: #4CAF50;
                }
                
                .notification.error {
                    background-color: #F44336;
                }
                
                .notification.show {
                    transform: translateY(0);
                    opacity: 1;
                }
            `;
            document.head.appendChild(style);
        }
    }
    
    const notification = document.querySelector('.notification');
    notification.textContent = message;
    notification.className = `notification ${type}`;
    notification.classList.add('show');
    
    // Hide notification after 3 seconds
    setTimeout(() => {
        notification.classList.remove('show');
    }, 3000);
}

// Setup media navigation for multiple media items
function setupMediaNavigation() {
    const mediaItems = document.querySelectorAll('.post-media-item');
    if (!mediaItems || mediaItems.length <= 1) return;
    
    // Check if navigation buttons already exist
    if (document.querySelector('.media-nav-btn')) return;
    
    // Create navigation buttons
    const prevBtn = document.createElement('button');
    prevBtn.className = 'media-nav-btn prev-btn';
    prevBtn.innerHTML = '<i class="fas fa-chevron-left"></i>';
    
    const nextBtn = document.createElement('button');
    nextBtn.className = 'media-nav-btn next-btn';
    nextBtn.innerHTML = '<i class="fas fa-chevron-right"></i>';
    
    // Add buttons to the post media container
    const mediaContainer = document.querySelector('.post-media');
    if (!mediaContainer) return;
    
    mediaContainer.appendChild(prevBtn);
    mediaContainer.appendChild(nextBtn);
    
    // Show first media item, hide others
    mediaItems.forEach((item, index) => {
        if (index === 0) {
            item.classList.add('active');
        } else {
            item.classList.remove('active');
        }
    });
    
    // Add navigation functionality
    let currentIndex = 0;
    
    prevBtn.addEventListener('click', function(e) {
        e.stopPropagation(); // Prevent event bubbling
        mediaItems[currentIndex].classList.remove('active');
        currentIndex = (currentIndex - 1 + mediaItems.length) % mediaItems.length;
        mediaItems[currentIndex].classList.add('active');
        updateNavButtons();
    });
    
    nextBtn.addEventListener('click', function(e) {
        e.stopPropagation(); // Prevent event bubbling
        mediaItems[currentIndex].classList.remove('active');
        currentIndex = (currentIndex + 1) % mediaItems.length;
        mediaItems[currentIndex].classList.add('active');
        updateNavButtons();
    });
    
    function updateNavButtons() {
        // Hide prev button if on first item
        if (currentIndex === 0) {
            prevBtn.classList.add('hidden');
        } else {
            prevBtn.classList.remove('hidden');
        }
        
        // Hide next button if on last item
        if (currentIndex === mediaItems.length - 1) {
            nextBtn.classList.add('hidden');
        } else {
            nextBtn.classList.remove('hidden');
        }
    }
    
    // Initial button state
    updateNavButtons();
}

// Add this to your existing post.js file
document.addEventListener('DOMContentLoaded', function() {
    // Save button functionality
    const saveBtn = document.querySelector('.save-btn');
    if (saveBtn) {
        saveBtn.addEventListener('click', function() {
            const postId = this.getAttribute('data-post-id');
            const saveIcon = this.querySelector('i');
            
            // Send AJAX request to toggle save
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
                        this.classList.add('saved');
                    } else {
                        saveIcon.classList.remove('fas');
                        saveIcon.classList.add('far');
                        this.classList.remove('saved');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    }
});

// Add media navigation functionality
document.addEventListener('DOMContentLoaded', function() {
    // Prevent default behavior for post links to avoid page reload
    const postLinks = document.querySelectorAll('.post-link');
    if (postLinks) {
        postLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const postId = this.getAttribute('data-post-id');
                window.history.pushState({}, '', `/spheria1/post.php?id=${postId}`);
                // Load post content via AJAX if needed
            });
        });
    }

    // Ensure close button doesn't cause unwanted navigation
    const closeBtn = document.querySelector('.btn-close-white');
    if (closeBtn) {
        closeBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            window.history.back();
        });
    }

    // Fix for media navigation
    const mediaItems = document.querySelectorAll('.media-item, .post-media-item');
    const prevBtn = document.querySelector('.prev-btn');
    const nextBtn = document.querySelector('.next-btn');
    
    if (mediaItems && mediaItems.length > 1) {
        let currentIndex = 0;
        
        // Make sure all items except the first are hidden
        mediaItems.forEach((item, index) => {
            if (index === 0) {
                item.classList.add('active');
            } else {
                item.classList.remove('active');
            }
        });
        
        // Function to show specific slide
        function showSlide(index) {
            mediaItems.forEach(item => item.classList.remove('active'));
            mediaItems[index].classList.add('active');
            currentIndex = index;
        }
        
        // Navigation button event handlers with stopPropagation
        if (prevBtn) {
            prevBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                let newIndex = currentIndex - 1;
                if (newIndex < 0) newIndex = mediaItems.length - 1;
                showSlide(newIndex);
            });
        }
        
        if (nextBtn) {
            nextBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                let newIndex = currentIndex + 1;
                if (newIndex >= mediaItems.length) newIndex = 0;
                showSlide(newIndex);
            });
        }
    }
});
