<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth/auth_check.php';

// Fetch all sphere videos with user information
$sql = "SELECT p.*, pm.file_path, pm.media_type, u.username, u.profile_image,
        (SELECT COUNT(*) FROM likes WHERE post_id = p.post_id) as likes_count,
        (SELECT COUNT(*) FROM comments WHERE post_id = p.post_id) as comments_count,
        EXISTS(SELECT 1 FROM likes WHERE post_id = p.post_id AND user_id = ?) as is_liked,
        EXISTS(SELECT 1 FROM saved_posts WHERE post_id = p.post_id AND user_id = ?) as is_saved,
        EXISTS(SELECT 1 FROM followers WHERE follower_id = ? AND following_id = p.user_id) as is_following
        FROM posts p 
        JOIN post_media pm ON p.post_id = pm.post_id 
        JOIN users u ON p.user_id = u.user_id 
        WHERE pm.media_type = 'video' 
        ORDER BY p.created_at DESC";
$stmt = $db->prepare($sql);
$stmt->bind_param("iii", $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$spheres = [];
while ($row = $result->fetch_assoc()) {
    $spheres[] = $row;
}
// Debug information
// error_log("Number of spheres found: " . count($spheres));

// Inside the foreach loop, before the video element
// error_log("Processing sphere: " . print_r($sphere, true));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spheria - Spheres</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/sidebar.css">
    <link rel="stylesheet" href="assets/css/spheres.css">
    <link rel="shortcut icon" href="assets/images/logo2.png" type="image/x-icon">
</head>

<body class="bg">
    <div class="container-fluid p-0">
        <div class="row g-0">
            <?php include 'includes/sidebar.php'; ?>

            <div class="col main-content p-0">
                <div class="sphere-container">
                    <?php foreach ($spheres as $sphere): ?>
                        <div class="sphere-item">
                            <!-- // Update the video container HTML structure first -->
                            <div class="video-container">
                                <video class="sphere-video" loop playsinline>
                                    <source src="/spheria1/<?php echo $sphere['file_path']; ?>" type="video/mp4">
                                </video>
                                <div class="video-controls">
                                    <div class="play-pause-btn">
                                        <i class="fas fa-play"></i>
                                    </div>
                                    <div class="volume-btn">
                                        <i class="fas fa-volume-up"></i>
                                    </div>
                                </div>
                            </div>
                            
                            
                            <div class="sphere-info">
                                <div class="user-info">
                                    <div class="user-profile" onclick="window.location.href='profile.php?username=<?php echo htmlspecialchars($sphere['username']); ?>'">
                                        <img src="<?php echo $sphere['profile_image'] ?? 'assets/images/default-avatar.png'; ?>" alt="Profile">
                                        <div class="user-details">
                                            <span class="username"><?php echo htmlspecialchars($sphere['username']); ?></span>
                                            <span class="location"><?php echo htmlspecialchars($sphere['location'] ?? ''); ?></span>
                                        </div>
                                    </div>
                                    <?php if ($sphere['user_id'] !== $_SESSION['user_id']): ?>
                                        <button class="follow-button <?php echo $sphere['is_following'] ? 'following' : ''; ?>" 
                                               data-user-id="<?php echo $sphere['user_id']; ?>">
                                            <?php echo $sphere['is_following'] ? 'Following' : 'Follow'; ?>
                                        </button>
                                    <?php endif; ?>
                                </div>
                                <div class="content-section">
                                    <p class="caption"><?php echo htmlspecialchars($sphere['content']); ?></p>
                                    <?php if (!empty($sphere['tags'])): ?>
                                        <div class="tags">
                                            <?php foreach (explode(',', $sphere['tags']) as $tag): ?>
                                                <span class="tag">#<?php echo trim(htmlspecialchars($tag)); ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="sphere-actions">
                                <div class="action-item like-btn <?php echo $sphere['is_liked'] ? 'active' : ''; ?>" 
                                     data-post-id="<?php echo $sphere['post_id']; ?>">
                                    <i class="fa-heart <?php echo $sphere['is_liked'] ? 'fas' : 'fas'; ?>"></i>
                                    <div class="likes-count"><?php echo number_format($sphere['likes_count']); ?></div>
                                </div>
                                <div class="action-item comment-btn" data-post-id="<?php echo $sphere['post_id']; ?>">
                                    <i class="fas fa-comment"></i>
                                    <div class="comments-count"><?php echo number_format($sphere['comments_count']); ?></div>
                                </div>
                                <div class="action-item share-btn" data-post-id="<?php echo $sphere['post_id']; ?>">
                                    <i class="fas fa-share"></i>
                                    <div class="shares-count"><?php echo number_format($sphere['share_count']); ?></div>
                                </div>
                                <div class="action-item save-btn <?php echo $sphere['is_saved'] ? 'active' : ''; ?>" 
                                     data-post-id="<?php echo $sphere['post_id']; ?>">
                                    <i class="fas fa-bookmark"></i>
                                    <div class="saves-count"><?php echo number_format($sphere['saves_count'] ?? 0); ?></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Fix for comment modal
        const commentModal = document.getElementById('commentModal');
        if (commentModal) {
            commentModal.classList.remove('show'); // Remove show class on page load
            
            // Close modal when clicking outside
            window.addEventListener('click', function(event) {
                if (event.target === commentModal) {
                    commentModal.classList.remove('show');
                }
            });
        }
    
        // Update video playback logic
        document.addEventListener('DOMContentLoaded', function() {
            const videoContainers = document.querySelectorAll('.video-container');
            
            videoContainers.forEach(container => {
                const video = container.querySelector('video');
                const playPauseBtn = container.querySelector('.play-pause-btn');
                const volumeBtn = container.querySelector('.volume-btn');
                const playIcon = '<i class="fas fa-play"></i>';
                const pauseIcon = '<i class="fas fa-pause"></i>';
                const volumeOnIcon = '<i class="fas fa-volume-up"></i>';
                const volumeOffIcon = '<i class="fas fa-volume-mute"></i>';
                
                // Initial state - autoplay with sound
                video.muted = false;  // Sound on by default
                volumeBtn.innerHTML = volumeOnIcon;
                document.documentElement.setAttribute('data-user-interacted', 'true');
                
                function updatePlayPauseState() {
                    if (video.paused) {
                        container.classList.remove('playing');
                        playPauseBtn.innerHTML = playIcon;
                    } else {
                        container.classList.add('playing');
                        playPauseBtn.innerHTML = pauseIcon;
                    }
                }
    
                function updateVolumeState() {
                    volumeBtn.innerHTML = video.muted ? volumeOnIcon : volumeOffIcon ;
                }
                
                // Play video when it's in view
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            video.play().then(() => {
                                updatePlayPauseState();
                            }).catch(() => {
                                updatePlayPauseState();
                            });
                        } else {
                            video.pause();
                            updatePlayPauseState();
                        }
                    });
                }, { threshold: 0.5 });
                
                observer.observe(container);
                
                // Handle play/pause button click
                playPauseBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    if (video.paused) {
                        video.play();
                    } else {
                        video.pause();
                    }
                    updatePlayPauseState();
                });
    
                // Handle volume button click
                volumeBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    video.muted = !video.muted;
                    updateVolumeState();
                });
                
                // Update states on video events
                video.addEventListener('play', updatePlayPauseState);
                video.addEventListener('pause', updatePlayPauseState);
                video.addEventListener('volumechange', updateVolumeState);
                
                // Start playing the first visible video
                if (isElementInViewport(container)) {
                    video.play().catch(() => {
                        console.log('Autoplay prevented');
                    });
                }
            });
            
            // Helper function to check if element is in viewport
            function isElementInViewport(el) {
                const rect = el.getBoundingClientRect();
                return (
                    rect.top >= 0 &&
                    rect.left >= 0 &&
                    rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
                    rect.right <= (window.innerWidth || document.documentElement.clientWidth)
                );
            }
        });
    });
    </script>
    <script>
    // Add this after your existing video control JavaScript
    document.querySelectorAll('.follow-button').forEach(button => {
        button.addEventListener('click', async function() {
            const userId = this.dataset.userId;
            try {
                const response = await fetch('/spheria1/includes/users/follow.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `user_id=${userId}`
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.classList.toggle('following');
                    this.textContent = this.classList.contains('following') ? 'Following' : 'Follow';
                }
            } catch (error) {
                console.error('Error:', error);
            }
        });
    });
    </script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Like functionality
        document.querySelectorAll('.like-btn').forEach(btn => {
            btn.addEventListener('click', async function() {
                const postId = this.dataset.postId;
                const countElement = this.querySelector('.likes-count');
                const currentCount = parseInt(countElement.textContent.replace(/,/g, ''));
                
                // Optimistic UI update
                this.classList.toggle('active');
                countElement.textContent = this.classList.contains('active') ? 
                    numberWithCommas(currentCount + 1) : 
                    numberWithCommas(currentCount - 1);

                try {
                    const response = await fetch('/spheria1/includes/posts/like_post.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `post_id=${postId}`
                    });
                    const data = await response.json();
                    if (data.success) {
                        countElement.textContent = numberWithCommas(data.likes_count);
                    }
                } catch (error) {
                    // Revert on error
                    this.classList.toggle('active');
                    countElement.textContent = numberWithCommas(currentCount);
                    console.error('Error:', error);
                }
            });
        });

        // Helper function for number formatting
        function numberWithCommas(x) {
            return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }

        // Save functionality
        document.querySelectorAll('.save-btn').forEach(btn => {
            btn.addEventListener('click', async function() {
                const postId = this.dataset.postId;
                const countElement = this.querySelector('.saves-count');
                const currentCount = parseInt(countElement.textContent.replace(/,/g, '') || '0');
                
                // Optimistic UI update
                this.classList.toggle('active');
                countElement.textContent = this.classList.contains('active') ? 
                    numberWithCommas(currentCount + 1) : 
                    numberWithCommas(currentCount - 1);

                try {
                    const response = await fetch('/spheria1/includes/posts/save_post.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `post_id=${postId}`
                    });
                    const data = await response.json();
                    if (data.success) {
                        showToast(data.action === 'save' ? 'Post saved!' : 'Post unsaved');
                    } else {
                        throw new Error('Failed to save post');
                    }
                } catch (error) {
                    // Revert on error
                    this.classList.toggle('active');
                    countElement.textContent = numberWithCommas(currentCount);
                    showToast('Failed to save post');
                    console.error('Error:', error);
                }
            });
        });

        // Share functionality with toast notification
        // document.querySelectorAll('.share-btn').forEach(btn => {
        //     btn.addEventListener('click', async function() {
        //         const postId = this.dataset.postId;
        //         const shareUrl = `${window.location.origin}/spheria1/post.php?id=${postId}`;
                
        //         if (navigator.share) {
        //             try {
        //                 await navigator.share({
        //                     title: 'Check out this post on Spheria',
        //                     url: shareUrl
        //                 });
        //                 showToast('Post shared successfully!');
        //             } catch (error) {
        //                 if (error.name !== 'AbortError') {
        //                     showToast('Failed to share post');
        //                 }
        //             }
        //         } else {
        //             navigator.clipboard.writeText(shareUrl)
        //                 .then(() => showToast('Link copied to clipboard!'))
        //                 .catch(() => showToast('Failed to copy link'));
        //         }
        //     });
        // });

        // Toast notification function
        function showToast(message) {
            const toast = document.createElement('div');
            toast.className = 'toast-notification';
            toast.textContent = message;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.classList.add('show');
                setTimeout(() => {
                    toast.classList.remove('show');
                    setTimeout(() => toast.remove(), 300);
                }, 2000);
            }, 100);
        }
    });
    </script>
    <!-- Add this before closing body tag -->
    <div class="comment-modal" id="commentModal">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Comments</h5>
                <button class="close-btn">&times;</button>
            </div>
            <div class="comments-container"></div>
            <div class="comment-input">
                <textarea placeholder="Add a comment..."></textarea>
                <button class="post-comment-btn">Post</button>
            </div>
        </div>
    </div>

    <script>
    // Add this to your existing JavaScript
    document.querySelectorAll('.comment-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const postId = this.dataset.postId;
            const modal = document.getElementById('commentModal');
            const commentsContainer = modal.querySelector('.comments-container');
            const textarea = modal.querySelector('textarea');
            const postButton = modal.querySelector('.post-comment-btn');
            
            // Show modal
            modal.classList.add('show');
            
            // Clear previous comments and textarea
            commentsContainer.innerHTML = '';
            textarea.value = '';
            
            // Load comments
            loadComments(postId);
            
            // Set up post button
            postButton.onclick = () => postComment(postId, textarea.value);
            
            // Close button functionality
            modal.querySelector('.close-btn').onclick = () => {
                modal.classList.remove('show');
            };
        });
    });

    async function loadComments(postId) {
        try {
            const response = await fetch(`/spheria1/includes/posts/get_comments.php?post_id=${postId}`);
            const data = await response.json();
            if (data.success) {
                const container = document.querySelector('.comments-container');
                container.innerHTML = data.comments.map(comment => `
                    <div class="comment">
                        <img src="${comment.profile_image || 'assets/images/default-avatar.png'}" alt="Profile">
                        <div class="comment-content">
                            <div class="comment-header">
                                <span class="username">@${comment.username}</span>
                                <span class="time">${comment.created_at}</span>
                            </div>
                            <p>${comment.comment_text}</p>
                        </div>
                    </div>
                `).join('');
            }
        } catch (error) {
            console.error('Error loading comments:', error);
        }
    }

    async function postComment(postId, comment) {
        if (!comment.trim()) return;
        
        try {
            const response = await fetch('/spheria1/includes/posts/comment_post.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `post_id=${postId}&comment=${encodeURIComponent(comment)}`
            });
            
            const data = await response.json();
            if (data.success) {
                // Update comment count
                const countElement = document.querySelector(`.comment-btn[data-post-id="${postId}"] .comments-count`);
                countElement.textContent = data.comments_count;
                
                // Clear textarea and reload comments
                document.querySelector('.comment-modal textarea').value = '';
                loadComments(postId);
                
                showToast('Comment posted successfully!');
            }
        } catch (error) {
            console.error('Error posting comment:', error);
            showToast('Failed to post comment');
        }
    }
    </script>

    <!-- Share Modal -->
    <div class="share-modal" id="shareModal">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Share with</h5>
                <button class="close-btn">&times;</button>
            </div>
            <div class="search-box">
                <input type="text" placeholder="Search users..." id="userSearch">
            </div>
            <div class="users-list"></div>
        </div>
    </div>

    <script>
    // Add this to your existing JavaScript
    // Replace the existing share button JavaScript with this updated version
    document.querySelectorAll('.share-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const postId = this.dataset.postId;
            const modal = document.getElementById('shareModal');
            const searchInput = modal.querySelector('#userSearch');
            const usersList = modal.querySelector('.users-list');
            
            modal.classList.add('show');
            searchInput.value = '';
            usersList.innerHTML = '';
            
            // Load initial users list
            loadUsers('');
            
            // Search functionality
            let searchTimeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => loadUsers(this.value), 300);
            });
            
            // Close modal
            modal.querySelector('.close-btn').onclick = () => {
                modal.classList.remove('show');
            };
            
            // Close modal when clicking outside
            window.onclick = function(event) {
                if (event.target === modal) {
                    modal.classList.remove('show');
                }
            };
            
            async function loadUsers(search) {
                try {
                    const response = await fetch(`/spheria1/includes/users/search_users.php?q=${search}`);
                    const data = await response.json();
                    if (data.success) {
                        usersList.innerHTML = data.users.map(user => `
                            <div class="user-item" data-user-id="${user.user_id}">
                                <img src="${user.profile_image || 'assets/images/default-avatar.png'}" alt="Profile">
                                <span>@${user.username}</span>
                                <button class="share-with-btn" onclick="sharePost(${postId}, ${user.user_id}, this)">Share</button>
                            </div>
                        `).join('');
                    }
                } catch (error) {
                    console.error('Error loading users:', error);
                    showToast('Failed to load users');
                }
            }
        });
    });

    // Add this new function for handling the share action
    async function sharePost(postId, sharedWith, button) {
        try {
            // Add sending state
            button.classList.add('sending');
            button.innerHTML = 'Sent <i class="fas fa-paper-plane"></i>';
    
            const response = await fetch('/spheria1/includes/posts/share_post.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `post_id=${postId}&shared_with=${sharedWith}`
            });
            
            const result = await response.json();
            if (result.success) {
                // Update share count
                const countElement = document.querySelector(`.share-btn[data-post-id="${postId}"] .shares-count`);
                countElement.textContent = result.shares_count;
                
                // Disable the share button and update text
                button.disabled = true;
                button.textContent = 'Shared';
                button.style.backgroundColor = '#666';
                
                showToast('Video shared successfully!');
                
                // Close modal after short delay
                setTimeout(() => {
                    const modal = document.getElementById('shareModal');
                    modal.classList.remove('show');
                }, 1000);
            } else {
                throw new Error(result.message || 'Failed to share video');
            }
        } catch (error) {
            console.error('Error sharing post:', error);
            showToast(error.message || 'Failed to share video');
        }
    };
    </script>
    <script>
            document.addEventListener('DOMContentLoaded', function() {
            const videoContainers = document.querySelectorAll('.video-container');
            
            videoContainers.forEach(container => {
                const video = container.querySelector('video');
                const playPauseBtn = container.querySelector('.play-pause-btn');
                const volumeBtn = container.querySelector('.volume-btn');
                const playIcon = '<i class="fas fa-play"></i>';
                const pauseIcon = '<i class="fas fa-pause"></i>';
                const volumeOnIcon = '<i class="fas fa-volume-up"></i>';
                const volumeOffIcon = '<i class="fas fa-volume-mute"></i>';
                
                // // Initial state
                // video.pause();
                // video.muted = false;
                
                function updatePlayPauseState() {
                    if (video.paused) {
                        container.classList.remove('playing');
                        playPauseBtn.innerHTML = playIcon;
                    } else {
                        container.classList.add('playing');
                        playPauseBtn.innerHTML = pauseIcon;
                    }
                }
        
                function updateVolumeState() {
                    volumeBtn.innerHTML = video.muted ? volumeOffIcon : volumeOnIcon;
                }
                
                // Play video when it's in viewr
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting && document.documentElement.hasAttribute('data-user-interacted')) {
                            video.play().catch(() => {
                                updatePlayPauseState();
                            });
                        } else {
                            video.pause();
                        }
                        updatePlayPauseState();
                    });
                }, { threshold: 0.5 });
                
                observer.observe(container);
                
                // Handle play/pause button click
                playPauseBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    document.documentElement.setAttribute('data-user-interacted', 'true');
                    if (video.paused) {
                        video.play();
                    } else {
                        video.pause();
                    }
                    updatePlayPauseState();
                });
        
                // Handle volume button click
                volumeBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    video.muted = !video.muted;
                    updateVolumeState();
                });
                
                // Update states on video events
                video.addEventListener('play', updatePlayPauseState);
                video.addEventListener('pause', updatePlayPauseState);
                video.addEventListener('volumechange', updateVolumeState);
            });
        });
    </script>
</body>
</html>