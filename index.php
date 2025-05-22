<?php
/**
 * Spheria - Main Index Page
 * 
 * This file serves as the main entry point for the Spheria social media platform.
 * It handles user authentication, fetches posts for the feed, and renders the main interface.
 *
 * @package Spheria
 * @version 1.0
 */

// Initialize session and include required files
session_start();
require_once 'includes/config.php';
require_once 'includes/db.php';

// Authentication check - redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get current user data
$user_id = $_SESSION['user_id'];
$user = null;
$posts = [];

try {
    // Fetch current user information
    $user_sql = "SELECT * FROM users WHERE user_id = ?";
    $user_stmt = $db->prepare($user_sql);
    $user_stmt->bind_param("i", $user_id);
    $user_stmt->execute();
    $user = $user_stmt->get_result()->fetch_assoc();
    
    if (!$user) {
        throw new Exception("User not found");
    }
    
    // Fetch posts for the feed with user information
    $feed_sql = "SELECT p.*, u.username, u.profile_image 
                FROM posts p 
                JOIN users u ON p.user_id = u.user_id 
                WHERE p.visibility = 'public' 
                ORDER BY p.created_at DESC 
                LIMIT 20";
    $feed_result = $db->query($feed_sql);
    $posts = $feed_result->fetch_all(MYSQLI_ASSOC);
    
} catch (Exception $e) {
    error_log("Error fetching data: " . $e->getMessage());
    $error_message = "An error occurred while loading the feed. Please try again later.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spheria - Home</title>
    <!-- External CSS libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Application CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/sidebar.css">
    <link rel="stylesheet" href="assets/css/notifications.css">
    <link rel="stylesheet" href="assets/css/stories.css">
    <link rel="stylesheet" href="assets/css/feed.css">
    <link rel="stylesheet" href="assets/css/comments.css">
    <link rel="stylesheet" href="assets/css/share.css">

    <link rel="shortcut icon" href="assets/images/logo2.png" type="image/x-icon">

</head>
<body class="bg" data-user-id="<?= $user_id ?>">
    <div class="container-fluid">
        <div class="row g-0">
            <!-- Left Sidebar -->
            <div class="col-md-3 col-lg-3 col-xl-3">
                <?php include 'includes/sidebar.php'; ?>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-6 col-lg-6 col-xl-6 main-content">
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
                <?php endif; ?>
                
                <!-- Stories Section -->
                <div class="stories-container mb-4">
                    <div class="stories-wrapper">
                        <!-- Stories will be loaded dynamically via JavaScript -->
                    </div>
                </div>
        
                <!-- Posts Feed -->
                <div class="posts-container">
                    <?php if (isset($posts) && count($posts) > 0): ?>
                        <?php foreach ($posts as $post): 
                            // Prepare post data
                            $post_id = $post['post_id'];
                            
                            // Check if user has liked this post
                            $like_check_sql = "SELECT like_id FROM likes WHERE post_id = ? AND user_id = ?";
                            $like_check_stmt = $db->prepare($like_check_sql);
                            $like_check_stmt->bind_param("ii", $post_id, $user_id);
                            $like_check_stmt->execute();
                            $user_has_liked = $like_check_stmt->get_result()->num_rows > 0;
                            
                            // Get likes count
                            $likes_sql = "SELECT COUNT(*) as count FROM likes WHERE post_id = ?";
                            $likes_stmt = $db->prepare($likes_sql);
                            $likes_stmt->bind_param("i", $post_id);
                            $likes_stmt->execute();
                            $likes_count = $likes_stmt->get_result()->fetch_assoc()['count'];
                            
                            // Get comments count
                            $comments_sql = "SELECT COUNT(*) as count FROM comments WHERE post_id = ?";
                            $comments_stmt = $db->prepare($comments_sql);
                            $comments_stmt->bind_param("i", $post_id);
                            $comments_stmt->execute();
                            $comments_count = $comments_stmt->get_result()->fetch_assoc()['count'];
                        ?>
                            <div class="post-card mb-4" data-post-id="<?= $post_id ?>">
                                <!-- Post Header -->
                                <div class="post-header">
                                    <div class="user-info">
                                        <a href="profile.php?username=<?= htmlspecialchars($post['username']) ?>" class="profile-link">
                                            <img src="<?= !empty($post['profile_image']) ? htmlspecialchars($post['profile_image']) : 'assets/images/default-avatar.png' ?>" 
                                                alt="Profile" class="rounded-circle">
                                        </a>
                                        <div class="ms-3 user-name">
                                            <a href="profile.php?username=<?= htmlspecialchars($post['username']) ?>" class="username-link">
                                                <h6 class="mb-0 text-white"><?= htmlspecialchars($post['username']) ?></h6>
                                            </a>
                                            <small class="text-muted"><?= date('M d, Y', strtotime($post['created_at'])) ?></small>
                                        </div>
                                    </div>
                                    <button class="options-btn"><i class="fas fa-ellipsis-h"></i></button>
                                </div>
                                
                                <!-- Post Content -->
                                <div class="post-content">
                                    <?php if (!empty($post['content'])): ?>
                                        <p class="text-white"><?= htmlspecialchars($post['content']) ?></p>
                                    <?php endif; ?>
                                    
                                    <?php
                                    // Fetch media for this post
                                    $media_sql = "SELECT * FROM post_media WHERE post_id = ?";
                                    $media_stmt = $db->prepare($media_sql);
                                    $media_stmt->bind_param("i", $post_id);
                                    $media_stmt->execute();
                                    $media = $media_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                                    
                                    if (count($media) > 0):
                                        // Determine gallery class based on number of media items
                                        $galleryClass = count($media) == 1 ? 'post-gallery single-image' : 'post-gallery';
                                    ?>
                                    <div class="<?= $galleryClass ?>">
                                        <?php foreach ($media as $item):
                                            if ($item['media_type'] == 'image'): ?>
                                                <div class="post-media-link" data-post-id="<?= $post_id ?>">
                                                    <img src="<?= htmlspecialchars($item['file_path']) ?>" alt="Post image">
                                                </div>
                                            <?php elseif ($item['media_type'] == 'video'): ?>
                                                <div class="post-media-link video-container" data-post-id="<?= $post_id ?>">
                                                    <video class="feed-video" playsinline style="width: 100%; height: auto; max-height: 500px; object-fit: contain;">
                                                        <source src="<?= htmlspecialchars($item['file_path']) ?>" type="<?= htmlspecialchars($item['mime_type']) ?>">
                                                    </video>
                                                    <div class="video-controls">
                                                        <button class="play-pause-btn">
                                                            <i class="fas fa-play"></i>
                                                            <i class="fas fa-pause"></i>
                                                        </button>
                                                        <button class="volume-btn">
                                                            <i class="fas fa-volume-up"></i>
                                                            <i class="fas fa-volume-mute"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            <?php endif; 
                                        endforeach; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Post Footer with Action Buttons -->
                                <div class="post-footer">
                                    <div class="post-actions">
                                        <!-- Like Button -->
                                        <button class="post-action like-btn <?= $user_has_liked ? 'liked' : '' ?>" data-post-id="<?= $post_id ?>">
                                            <?php if ($user_has_liked): ?>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="28px" height="28px" viewBox="0 0 24 24">
                                                    <path fill="#f00" d="M2 9.137C2 14 6.02 16.591 8.962 18.911C10 19.729 11 20.5 12 20.5s2-.77 3.038-1.59C17.981 16.592 22 14 22 9.138S16.5.825 12 5.501C7.5.825 2 4.274 2 9.137" stroke-width="0.5" stroke="#f00" />
                                                </svg>
                                            <?php else: ?>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="28px" height="28px" viewBox="0 0 24 24">
                                                    <path fill="#fff" d="m8.962 18.91l.464-.588zM12 5.5l-.54.52a.75.75 0 0 0 1.08 0zm3.038 13.41l.465.59zm-5.612-.588C7.91 17.127 6.253 15.96 4.938 14.48C3.65 13.028 2.75 11.335 2.75 9.137h-1.5c0 2.666 1.11 4.7 2.567 6.339c1.43 1.61 3.254 2.9 4.68 4.024zM2.75 9.137c0-2.15 1.215-3.954 2.874-4.713c1.612-.737 3.778-.541 5.836 1.597l1.08-1.04C10.1 2.444 7.264 2.025 5 3.06C2.786 4.073 1.25 6.425 1.25 9.137zM8.497 19.5c.513.404 1.063.834 1.62 1.16s1.193.59 1.883.59v-1.5c-.31 0-.674-.12-1.126-.385c-.453-.264-.922-.628-1.448-1.043zm7.006 0c1.426-1.125 3.25-2.413 4.68-4.024c1.457-1.64 2.567-3.673 2.567-6.339h-1.5c0 2.198-.9 3.891-2.188 5.343c-1.315 1.48-2.972 2.647-4.488 3.842zM22.75 9.137c0-2.712-1.535-5.064-3.75-6.077c-2.264-1.035-5.098-.616-7.54 1.92l1.08 1.04c2.058-2.137 4.224-2.333 5.836-1.596c1.659.759 2.874 2.562 2.874 4.713zm-8.176 9.185c-.526.415-.995.779-1.448 1.043s-.816.385-1.126.385v1.5c.69 0 1.326-.265 1.883-.59c.558-.326 1.107-.756 1.62-1.16z" stroke-width="0.5" stroke="#fff" />
                                                </svg>
                                            <?php endif; ?>
                                            <span class="likes-count"><?= $likes_count ?></span>
                                        </button>
                                        
                                        <!-- Comment Button -->
                                        <a href="post.php?id=<?= $post['post_id'] ?>" class="post-action comment-btn">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="28px" height="28px" viewBox="0 0 24 24">
                                                <g fill="none">
                                                    <path stroke="#fffefe" stroke-width="1.5" d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2S2 6.477 2 12c0 1.6.376 3.112 1.043 4.453c.178.356.237.763.134 1.148l-.595 2.226a1.3 1.3 0 0 0 1.591 1.592l2.226-.596a1.63 1.63 0 0 1 1.149.133A9.96 9.96 0 0 0 12 22Z" />
                                                    <path fill="#fffefe" d="m10.029 14.943l-.486.57zM12 9.5l-.536.524a.75.75 0 0 0 1.072 0zm1.971 5.442l-.486-.572zM12 15.993v-.75zm-1.485-1.622c-.582-.494-1.166-1.068-1.599-1.66c-.441-.605-.666-1.149-.666-1.602h-1.5c0 .916.435 1.774.955 2.486c.529.725 1.21 1.384 1.838 1.919zM8.25 11.11c0-1.107.495-1.69 1.003-1.881c.518-.193 1.342-.09 2.211.797l1.072-1.049c-1.156-1.18-2.581-1.612-3.808-1.153c-1.235.462-1.978 1.717-1.978 3.286zm6.207 4.405c.628-.534 1.309-1.194 1.838-1.918c.52-.713.955-1.571.955-2.487h-1.5c0 .453-.225.997-.666 1.602c-.433.593-1.017 1.166-1.598 1.66zm2.793-4.405c0-1.57-.743-2.824-1.978-3.286c-1.227-.459-2.652-.028-3.808 1.153l1.072 1.05c.869-.888 1.694-.991 2.21-.798c.51.19 1.004.774 1.004 1.881zm-7.707 4.405c.78.663 1.4 1.23 2.457 1.23v-1.5c-.414 0-.617-.134-1.485-.873zm3.943-1.143c-.869.739-1.072.873-1.486.873v1.5c1.057 0 1.678-.567 2.457-1.23z" />
                                                </g>
                                            </svg>
                                            <span class="comments-count"><?= $comments_count ?></span>
                                        </a>
                                        <!-- Share Button -->
                                        <button class="post-action share-btn" data-post-id="<?= $post_id ?>">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="28px" height="28px" viewBox="0 0 24 24">
                                                <path fill="#dfdfdf" d="M15.03 7.47a.75.75 0 0 0-1.06 0l-2.5 2.5a.75.75 0 1 0 1.06 1.06l1.22-1.22V16a.75.75 0 0 0 1.5 0V9.81l1.22 1.22a.75.75 0 1 0 1.06-1.06z" stroke-width="0.5" stroke="#dfdfdf" />
                                                <path fill="#dfdfdf" fill-rule="evenodd" d="M13.945 1.25h1.11c1.367 0 2.47 0 3.337.117c.9.12 1.658.38 2.26.981c.602.602.86 1.36.982 2.26c.116.867.116 1.97.116 3.337v8.11c0 1.367 0 2.47-.116 3.337c-.122.9-.38 1.658-.982 2.26s-1.36.86-2.26.982c-.867.116-1.97.116-3.337.116h-1.11c-1.367 0-2.47 0-3.337-.116c-.9-.122-1.658-.38-2.26-.982c-.4-.4-.648-.869-.805-1.402c-.951-.001-1.744-.012-2.386-.098c-.764-.103-1.426-.325-1.955-.854s-.751-1.19-.854-1.955c-.098-.73-.098-1.656-.098-2.79V9.447c0-1.133 0-2.058.098-2.79c.103-.763.325-1.425.854-1.954s1.19-.751 1.955-.854c.642-.086 1.435-.097 2.386-.098c.157-.533.406-1.002.805-1.402c.602-.602 1.36-.86 2.26-.981c.867-.117 1.97-.117 3.337-.117M7.25 16.055c0 1.05 0 1.943.053 2.694c-.835-.003-1.455-.018-1.946-.084c-.598-.08-.89-.224-1.094-.428s-.348-.496-.428-1.094c-.083-.619-.085-1.443-.085-2.643v-5c0-1.2.002-2.024.085-2.643c.08-.598.224-.89.428-1.094s.496-.348 1.094-.428c.491-.066 1.111-.08 1.946-.084C7.25 6 7.25 6.895 7.25 7.945zm3.558-13.202c-.734.099-1.122.28-1.399.556c-.277.277-.457.665-.556 1.4C8.752 5.562 8.75 6.564 8.75 8v8c0 1.435.002 2.436.103 3.192c.099.734.28 1.122.556 1.399c.277.277.665.457 1.4.556c.755.101 1.756.103 3.191.103h1c1.435 0 2.436-.002 3.192-.103c.734-.099 1.122-.28 1.399-.556c.277-.277.457-.665.556-1.4c.101-.755.103-1.756.103-3.191V8c0-1.435-.002-2.437-.103-3.192c-.099-.734-.28-1.122-.556-1.399c-.277-.277-.665-.457-1.4-.556c-.755-.101-1.756-.103-3.191-.103h-1c-1.435 0-2.437.002-3.192.103" clip-rule="evenodd" stroke-width="0.5" stroke="#dfdfdf" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Quick Comment Form -->
                                <div class="quick-comment-form">
                                    <form class="d-flex align-items-center p-3" action="javascript:void(0);">
                                        <img src="<?= !empty($user['profile_image']) ? htmlspecialchars($user['profile_image']) : 'assets/images/default-avatar.png' ?>" 
                                             alt="Your Profile" class="rounded-circle me-2" width="32" height="32">
                                        <input type="text" class="form-control comment-input" placeholder="Add a comment..." data-post-id="<?= $post_id ?>">
                                        <button type="submit" class="btn btn-link text-primary post-comment-btn">Post</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center text-white p-5">
                            <p>No posts to display. Follow some users to see their posts!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        
            <!-- Right Sidebar -->
            <div class="col-md-3 col-lg-3 col-xl-3">
                <?php include 'includes/right-sidebar.php'; ?>
            </div>
        </div>
    </div>
    
    <!-- Share Modal -->
    <div class="share-modal">
        <div class="share-container">
            <div class="share-header">
                <h5>Share Post</h5>
                <button class="share-close" aria-label="Close"><i class="fas fa-times"></i></button>
            </div>
            <!-- Followers section -->
            <div class="share-search-container">
                <div class="share-search-input">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" id="follower-search" placeholder="Search">
                </div>
            </div>
            
            <div class="share-followers-container">
                <!-- Followers will be loaded dynamically -->
            </div>
            
            <!-- Social sharing options -->
            <div class="share-social-options">
                <div class="share-option-row">
                    <a href="#" class="share-option-item" data-platform="link">
                        <div class="share-icon link">
                            <i class="fas fa-link"></i>
                        </div>
                        <span>Copy Link</span>
                    </a>
                    <a href="#" class="share-option-item" data-platform="facebook">
                        <div class="share-icon facebook">
                            <i class="fab fa-facebook-f"></i>
                        </div>
                        <span>Facebook</span>
                    </a>
                    <a href="#" class="share-option-item" data-platform="messenger">
                        <div class="share-icon messenger">
                            <i class="fab fa-facebook-messenger"></i>
                        </div>
                        <span>Messenger</span>
                    </a>
                </div>
                <div class="share-option-row">
                    <a href="#" class="share-option-item" data-platform="twitter">
                        <div class="share-icon twitter">
                            <i class="fab fa-twitter"></i>
                        </div>
                        <span>Twitter</span>
                    </a>
                    <a href="#" class="share-option-item" data-platform="whatsapp">
                        <div class="share-icon whatsapp">
                            <i class="fab fa-whatsapp"></i>
                        </div>
                        <span>WhatsApp</span>
                    </a>
                    <a href="#" class="share-option-item" data-platform="telegram">
                        <div class="share-icon telegram">
                            <i class="fab fa-telegram-plane"></i>
                        </div>
                        <span>Telegram</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Comment Modal -->
    <div class="comment-modal">
        <div class="comment-container">
            <div class="comment-header">
                <h5>Comments</h5>
                <button class="comment-close" aria-label="Close"><i class="fas fa-times"></i></button>
            </div>
            
            <div class="comments-container">
                <!-- Comments will be loaded dynamically -->
            </div>
            
            <!-- Comment Form -->
            <div class="comment-form-container">
                <form id="comment-form" class="d-flex align-items-center p-3" action="javascript:void(0);">
                    <img src="<?= !empty($user['profile_image']) ? $user['profile_image'] : 'assets/images/default-avatar.png' ?>" 
                         alt="Your Profile" class="rounded-circle me-2" width="32" height="32">
                    <input type="text" class="form-control" id="comment-input" placeholder="Add a comment...">
                    <button type="submit" class="btn btn-primary ms-2">Post</button>
                </form>
            </div>
        </div>
    </div>
    <script src="assets/js/post-modal.js" defer></script>

    <!-- Notifications Panel -->
    <div id="notificationsPanel" class="notifications-panel">
        <div class="notifications-header">
            <h5>Notifications</h5>
            <div class="notification-tabs">
                <button class="tab-btn active" data-tab="all">All</button>
                <button class="tab-btn" data-tab="mentions">Mentions</button>
            </div>
        </div>
        <div class="notifications-content">
            <!-- Notifications will be loaded dynamically -->
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Application Scripts -->
    <script src="assets/js/like.js"></script>
    <script src="assets/js/stories.js"></script>
    <script src="assets/js/share.js"></script>
    <script src="assets/js/comments.js"></script>
    <script src="assets/js/video-player.js"></script>
    
    <!-- Notifications Script -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const notificationIcon = document.querySelector('.nav-icon[href="#notifications"]');
        const notificationsPanel = document.getElementById('notificationsPanel');
        
        if (notificationIcon && notificationsPanel) {
            notificationIcon.addEventListener('click', function(e) {
                e.preventDefault();
                notificationsPanel.classList.toggle('active');
            });
        
            // Close notifications panel when clicking outside
            document.addEventListener('click', function(e) {
                if (notificationsPanel && !notificationsPanel.contains(e.target) && 
                    notificationIcon && !notificationIcon.contains(e.target)) {
                    notificationsPanel.classList.remove('active');
                }
            });
        
            // Handle notification tabs
            const tabButtons = document.querySelectorAll('.notification-tabs .tab-btn');
            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    // Add tab switching logic here
                    const tabType = this.dataset.tab;
                    console.log('Switching to tab:', tabType);
                    // Implement tab content switching functionality
                });
            });
        }
    });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
    
            // Comment Modal Functionality
            document.querySelectorAll('.comment-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const postId = this.closest('.post-card').dataset.postId;
                    openCommentModal(postId);
                });
            });
            
            // Close comment modal when clicking the close button
            const commentCloseBtn = document.querySelector('.comment-close');
            if (commentCloseBtn) {
                commentCloseBtn.addEventListener('click', function() {
                    document.querySelector('.comment-modal').classList.remove('active');
                });
            }
            
            // Close comment modal when clicking outside
            document.addEventListener('click', function(e) {
                const commentModal = document.querySelector('.comment-modal');
                if (commentModal && !commentModal.contains(e.target) && 
                    !e.target.closest('.comment-btn')) {
                    commentModal.classList.remove('active');
                }
            });
            
            // Comment form submission
            const commentForm = document.getElementById('comment-form');
            if (commentForm) {
                commentForm.addEventListener('submit', submitComment);
            }
        });

        function openCommentModal(postId) {
            const commentModal = document.querySelector('.comment-modal');
            commentModal.classList.add('active');
            
            // Load comments for this post
            loadComments(postId);
            
            // Set the current post ID for comment submission
            document.getElementById('comment-form').dataset.postId = postId;
        }

        function loadComments(postId) {
            // AJAX request to fetch comments
            fetch(`includes/get_comments.php?post_id=${postId}`)
                .then(response => response.json())
                .then(data => {
                    const commentsContainer = document.querySelector('.comments-container');
                    commentsContainer.innerHTML = '';
                    
                    if (data.length === 0) {
                        commentsContainer.innerHTML = '<p class="no-comments">No comments yet. Be the first to share your thoughts!</p>';
                        return;
                    }
                    
                    data.forEach(comment => {
                        const commentElement = createCommentElement(comment);
                        commentsContainer.appendChild(commentElement);
                    });
                })
                .catch(error => {
                    console.error('Error loading comments:', error);
                });
        }

        function createCommentElement(comment) {
            const commentDiv = document.createElement('div');
            commentDiv.className = 'comment-item';
            
            commentDiv.innerHTML = `
                <div class="comment-user-info">
                    <img src="${comment.profile_image || 'assets/images/default-avatar.png'}" alt="${comment.username}" class="rounded-circle">
                    <div>
                        <h6>${comment.username}</h6>
                        <small class="text-secondary">${comment.created_at}</small>
                    </div>
                </div>
                <p class="comment-text">${comment.comment_text}</p>
            `;
            
            return commentDiv;
        }

        function submitComment(event) {
            event.preventDefault();
            
            const form = document.getElementById('comment-form');
            const postId = form.dataset.postId;
            const commentInput = document.getElementById('comment-input');
            const comment = commentInput.value.trim();
            
            if (!comment) return;
            
            // AJAX request to submit comment
            fetch('includes/add_comment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `post_id=${postId}&content=${encodeURIComponent(comment)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Clear input
                    commentInput.value = '';
                    
                    // Reload comments
                    loadComments(postId);
                    
                    // Update comment count in the feed
                    const commentCountElement = document.querySelector(`.post-card[data-post-id="${postId}"] .comments-count`);
                    if (commentCountElement) {
                        commentCountElement.textContent = parseInt(commentCountElement.textContent) + 1;
                    }
                }
            })
            .catch(error => {
                console.error('Error submitting comment:', error);
            });
        }
    </script>
</body>
</html>