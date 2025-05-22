/**
 * Share functionality for Spheria social media platform
 * Handles post sharing functionality including the share modal and sharing options
 */

document.addEventListener('DOMContentLoaded', function() {
    // Share Modal Elements
    const shareModal = document.querySelector('.share-modal');
    const shareContainer = document.querySelector('.share-container');
    const shareCloseBtn = document.querySelector('.share-close');
    const followerSearch = document.getElementById('follower-search');
    const followersContainer = document.querySelector('.share-followers-container');
    const shareOptions = document.querySelectorAll('.share-option-item');
    
    // Share button click event
    document.querySelectorAll('.share-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const postId = this.dataset.postId;
            openShareModal(postId);
        });
    });
    
    // Close share modal when clicking the close button
    if (shareCloseBtn) {
        shareCloseBtn.addEventListener('click', function() {
            closeShareModal();
        });
    }
    
    // Close share modal when clicking outside
    document.addEventListener('click', function(e) {
        if (shareModal && !shareContainer.contains(e.target) && 
            !e.target.closest('.share-btn')) {
            closeShareModal();
        }
    });
    
    // Handle follower search
    if (followerSearch) {
        followerSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            filterFollowers(searchTerm);
        });
    }
    
    // Handle share option clicks
    if (shareOptions) {
        shareOptions.forEach(option => {
            option.addEventListener('click', function(e) {
                e.preventDefault();
                const platform = this.dataset.platform;
                const postId = shareModal.dataset.postId;
                handleShare(platform, postId);
            });
        });
    }
    
    /**
     * Opens the share modal for a specific post
     * @param {string} postId - The ID of the post to share
     */
    function openShareModal(postId) {
        shareModal.dataset.postId = postId;
        shareModal.classList.add('active');
        
        // Load users for sharing instead of followers
        loadUsers('');
    }
    
    /**
     * Closes the share modal
     */
    function closeShareModal() {
        shareModal.classList.remove('active');
    }
    
    /**
     * Loads users for the share modal
     * @param {string} search - The search term for filtering users
     */
    function loadUsers(search) {
        // AJAX request to fetch users
        fetch(`includes/users/search_users.php?q=${search}`)
            .then(response => response.json())
            .then(data => {
                followersContainer.innerHTML = '';
                
                if (!data.success || data.users.length === 0) {
                    followersContainer.innerHTML = '<p class="no-followers">No users found to share with.</p>';
                    return;
                }
                
                data.users.forEach(user => {
                    // Fix: Add missing full_name property if not present
                    if (!user.full_name) {
                        user.full_name = user.username || '';
                    }
                    const userElement = createUserElement(user, shareModal.dataset.postId);
                    followersContainer.appendChild(userElement);
                });
            })
            .catch(error => {
                console.error('Error loading users:', error);
                followersContainer.innerHTML = '<p class="error">Error loading users. Please try again.</p>';
            });
    }
    
    /**
     * Creates a user element for the share modal
     * @param {Object} user - The user data
     * @param {string} postId - The ID of the post being shared
     * @returns {HTMLElement} - The user element
     */
    function createUserElement(user, postId) {
        const userDiv = document.createElement('div');
        userDiv.className = 'follower-item';
        
        userDiv.innerHTML = `
            <div class="follower-info">
                <img src="${user.profile_image || 'assets/images/default-avatar.png'}" alt="${user.username}" class="rounded-circle">
                <div class="follower-details">
                    <h6>${user.username}</h6>
                    <small>${user.full_name || ''}</small>
                </div>
            </div>
            <button class="share-with-btn" data-user-id="${user.user_id}" data-post-id="${postId}">Share</button>
        `;
        
        // Add event listener to share button
        const shareBtn = userDiv.querySelector('.share-with-btn');
        shareBtn.addEventListener('click', function() {
            const userId = this.dataset.userId;
            // Update button text immediately
            this.textContent = 'Sharing...';
            this.disabled = true;
            shareWithUser(userId, postId);
        });
        
        return userDiv;
    }
    
    /**
     * Filters followers based on search term
     * @param {string} searchTerm - The search term
     */
    function filterFollowers(searchTerm) {
        // If we have a search input, load filtered users
        if (searchTerm.length > 0) {
            loadUsers(searchTerm);
            return;
        }
        
        // Otherwise just filter the existing elements
        const followerItems = followersContainer.querySelectorAll('.follower-item');
        
        followerItems.forEach(item => {
            const username = item.querySelector('h6').textContent.toLowerCase();
            const fullName = item.querySelector('small').textContent.toLowerCase();
            
            if (username.includes(searchTerm) || fullName.includes(searchTerm)) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
    }
    
    /**
     * Shares a post with a specific user
     * @param {string} userId - The ID of the user to share with
     * @param {string} postId - The ID of the post to share
     */
    function shareWithUser(userId, postId) {
        // AJAX request to share post
        fetch('includes/posts/share_post.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `shared_with=${userId}&post_id=${postId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                const shareBtn = document.querySelector(`.share-with-btn[data-user-id="${userId}"][data-post-id="${postId}"]`);
                if (shareBtn) {
                    shareBtn.textContent = 'Shared';
                    shareBtn.disabled = true;
                    shareBtn.classList.add('shared');
                }
                
                // Update share count if available
                const countElement = document.querySelector(`.share-btn[data-post-id="${postId}"] .shares-count`);
                if (countElement && data.shares_count) {
                    countElement.textContent = data.shares_count;
                }
            } else {
                console.error('Error sharing post:', data.message);
            }
        })
        .catch(error => {
            console.error('Error sharing post:', error);
        });
    }
    
    /**
     * Handles sharing to external platforms
     * @param {string} platform - The platform to share to
     * @param {string} postId - The ID of the post to share
     */
    function handleShare(platform, postId) {
        // Get the post URL
        const postUrl = `${window.location.origin}/post.php?id=${postId}`;
        
        switch (platform) {
            case 'link':
                // Copy link to clipboard
                navigator.clipboard.writeText(postUrl)
                    .then(() => {
                        alert('Link copied to clipboard!');
                    })
                    .catch(err => {
                        console.error('Could not copy text: ', err);
                    });
                break;
                
            case 'facebook':
                window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(postUrl)}`, '_blank');
                break;
                
            case 'messenger':
                window.open(`https://www.facebook.com/dialog/send?link=${encodeURIComponent(postUrl)}&app_id=YOURAPPID&redirect_uri=${encodeURIComponent(window.location.href)}`, '_blank');
                break;
                
            case 'twitter':
                window.open(`https://twitter.com/intent/tweet?url=${encodeURIComponent(postUrl)}&text=Check out this post on Spheria!`, '_blank');
                break;
                
            case 'whatsapp':
                window.open(`https://api.whatsapp.com/send?text=${encodeURIComponent('Check out this post on Spheria! ' + postUrl)}`, '_blank');
                break;
                
            case 'telegram':
                window.open(`https://t.me/share/url?url=${encodeURIComponent(postUrl)}&text=${encodeURIComponent('Check out this post on Spheria!')}`, '_blank');
                break;
                
            default:
                console.error('Unknown platform:', platform);
        }
        
        // Close the share modal after sharing
        closeShareModal();
    }
});