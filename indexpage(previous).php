<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/db.php';

// Check if user is logged in, if not redirect to login page
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get user data if logged in
$user_id = $_SESSION['user_id'];
try {
    $sql = "SELECT * FROM users WHERE user_id = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    
    // Fetch posts for the feed
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
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spheria - Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/sidebar.css">
    <!-- Add this in the head section -->
    <link rel="stylesheet" href="assets/css/notifications.css">
    <link rel="stylesheet" href="assets/css/stories.css">
    <!-- Remove this line -->
    <!-- <link rel="stylesheet" href="assets/css/stories-sidebar.css"> -->
    <link rel="stylesheet" href="assets/css/feed.css">
    <link rel="stylesheet" href="assets/css/comments.css">

    <!-- Add this after the sidebar include -->


    <!-- Add this before closing body tag -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const notificationIcon = document.querySelector('.nav-icon[href="#notifications"]');
        const notificationsPanel = document.getElementById('notificationsPanel');
        
        notificationIcon.addEventListener('click', function(e) {
            e.preventDefault();
            notificationsPanel.classList.toggle('active');
        });
    
        // Close notifications panel when clicking outside
        document.addEventListener('click', function(e) {
            if (!notificationsPanel.contains(e.target) && 
                !notificationIcon.contains(e.target)) {
                notificationsPanel.classList.remove('active');
            }
        });
    
        // Handle notification tabs
        const tabButtons = document.querySelectorAll('.notification-tabs .tab-btn');
        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                tabButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                // Add your tab switching logic here
            });
        });
    });
    </script>
</head>
<!-- Add this to the body tag -->
<body class="bg" data-user-id="<?= $user_id ?>">
    <div class="container-fluid">
        <!-- Main Content -->
        <div class="row g-0">
            <!-- Left Sidebar -->
            <div class="col-md-3 col-lg-3 col-xl-3">
                <?php include 'includes/sidebar.php'; ?>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-6 col-lg-6 col-xl-6 main-content">
                <!-- Stories -->
                <div class="stories-container mb-4">
                    <div class="stories-wrapper">
                        <!-- Stories will be loaded dynamically via JavaScript -->
                    </div>
                </div>
        
                <!-- Posts -->
                <div class="posts-container">
                    <?php if (isset($posts) && count($posts) > 0): ?>
                        <?php foreach ($posts as $post): ?>
                            <?php

                            // Check if user has liked this post
                            $like_check_sql = "SELECT like_id FROM likes WHERE post_id = ? AND user_id = ?";
                            $like_check_stmt = $db->prepare($like_check_sql);
                            $like_check_stmt->bind_param("ii", $post['post_id'], $_SESSION['user_id']);
                            $like_check_stmt->execute();
                            $user_has_liked = $like_check_stmt->get_result()->num_rows > 0;
                            
                            // Get likes count
                            $likes_sql = "SELECT COUNT(*) as count FROM likes WHERE post_id = ?";
                            $likes_stmt = $db->prepare($likes_sql);
                            $likes_stmt->bind_param("i", $post['post_id']);
                            $likes_stmt->execute();
                            $likes_count = $likes_stmt->get_result()->fetch_assoc()['count'];
                            
                            // Get comments count
                            $comments_sql = "SELECT COUNT(*) as count FROM comments WHERE post_id = ?";
                            $comments_stmt = $db->prepare($comments_sql);
                            $comments_stmt->bind_param("i", $post['post_id']);
                            $comments_stmt->execute();
                            $comments_count = $comments_stmt->get_result()->fetch_assoc()['count'];
                            ?>
                            <div class="post-card mb-4" data-post-id="<?= $post['post_id'] ?>">
                                <div class="post-header">
                                    <div class="user-info">
                                        <a href="profile.php?username=<?= htmlspecialchars($post['username']) ?>" class="profile-link">
                                            <img src="<?= !empty($post['profile_image']) ? $post['profile_image'] : 'assets/images/default-avatar.png' ?>" 
                                                alt="Profile" class="rounded-circle">
                                        </a>
                                        <div class="ms-3">
                                            <a href="profile.php?username=<?= htmlspecialchars($post['username']) ?>" class="username-link">
                                                <h6 class="mb-0 text-white"><?= htmlspecialchars($post['username']) ?></h6>
                                            </a>
                                            <small class="text-muted"><?= date('M d, Y', strtotime($post['created_at'])) ?></small>
                                        </div>
                                    </div>
                                    <button class="options-btn"><i class="fas fa-ellipsis-h"></i></button>
                                </div>
                                
                                <div class="post-content">
                                    <?php if (!empty($post['content'])): ?>
                                        <p class="text-white"><?= htmlspecialchars($post['content']) ?></p>
                                    <?php endif; ?>
                                    
                                    <?php
                                    // Fetch media for this post
                                    $media_sql = "SELECT * FROM post_media WHERE post_id = ?";
                                    $media_stmt = $db->prepare($media_sql);
                                    $media_stmt->bind_param("i", $post['post_id']);
                                    $media_stmt->execute();
                                    $media = $media_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                                    
                                    if (count($media) > 0):
                                        // Determine gallery class based on number of media items
                                        $galleryClass = count($media) == 1 ? 'post-gallery single-image' : 'post-gallery';
                                    ?>
                                    <div class="<?= $galleryClass ?>">
                                        <?php foreach ($media as $item):
                                            if ($item['media_type'] == 'image'): ?>
                                                <div class="post-media-link" data-post-id="<?= $post['post_id'] ?>">
                                                    <img src="<?= $item['file_path'] ?>" alt="Post image">
                                                </div>
                                            <?php elseif ($item['media_type'] == 'video'): ?>
                                                <div class="post-media-link video-container" data-post-id="<?= $post['post_id'] ?>">
                                                    <video class="feed-video" playsinline>
                                                        <source src="<?= $item['file_path'] ?>" type="<?= $item['mime_type'] ?>">
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
                                
                                <div class="post-footer">
                                    <div class="post-actions">
                                        <button class="post-action like-btn <?= $user_has_liked ? 'liked' : '' ?>" data-post-id="<?= $post['post_id'] ?>">
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
                                        <a href="post.php?id=<?= $post['post_id'] ?>" class="post-action comment-btn">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="28px" height="28px" viewBox="0 0 24 24">
                                                <g fill="none">
                                                    <path stroke="#fffefe" stroke-width="1.5" d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2S2 6.477 2 12c0 1.6.376 3.112 1.043 4.453c.178.356.237.763.134 1.148l-.595 2.226a1.3 1.3 0 0 0 1.591 1.592l2.226-.596a1.63 1.63 0 0 1 1.149.133A9.96 9.96 0 0 0 12 22Z" />
                                                    <path fill="#fffefe" d="m10.029 14.943l-.486.57zM12 9.5l-.536.524a.75.75 0 0 0 1.072 0zm1.971 5.442l-.486-.572zM12 15.993v-.75zm-1.485-1.622c-.582-.494-1.166-1.068-1.599-1.66c-.441-.605-.666-1.149-.666-1.602h-1.5c0 .916.435 1.774.955 2.486c.529.725 1.21 1.384 1.838 1.919zM8.25 11.11c0-1.107.495-1.69 1.003-1.881c.518-.193 1.342-.09 2.211.797l1.072-1.049c-1.156-1.18-2.581-1.612-3.808-1.153c-1.235.462-1.978 1.717-1.978 3.286zm6.207 4.405c.628-.534 1.309-1.194 1.838-1.918c.52-.713.955-1.571.955-2.487h-1.5c0 .453-.225.997-.666 1.602c-.433.593-1.017 1.166-1.598 1.66zm2.793-4.405c0-1.57-.743-2.824-1.978-3.286c-1.227-.459-2.652-.028-3.808 1.153l1.072 1.05c.869-.888 1.694-.991 2.21-.798c.51.19 1.004.774 1.004 1.881zm-7.707 4.405c.78.663 1.4 1.23 2.457 1.23v-1.5c-.414 0-.617-.134-1.485-.873zm3.943-1.143c-.869.739-1.072.873-1.486.873v1.5c1.057 0 1.678-.567 2.457-1.23z" />
                                                </g>
                                            </svg>
                                            <span class="comments-count"><?= $comments_count ?></span>
                                        </a>
                                        <button class="post-action share-btn" data-post-id="<?= $post['post_id'] ?>">
                                                <!-- <svg xmlns="http://www.w3.org/2000/svg" width="28px" height="28px" viewBox="0 0 24 24"> -->
                                                <svg xmlns="http://www.w3.org/2000/svg" width="28px" height="28px" viewBox="0 0 24 24">
                                                    <path fill="#dfdfdf" d="M15.03 7.47a.75.75 0 0 0-1.06 0l-2.5 2.5a.75.75 0 1 0 1.06 1.06l1.22-1.22V16a.75.75 0 0 0 1.5 0V9.81l1.22 1.22a.75.75 0 1 0 1.06-1.06z" stroke-width="0.5" stroke="#dfdfdf" />
                                                    <path fill="#dfdfdf" fill-rule="evenodd" d="M13.945 1.25h1.11c1.367 0 2.47 0 3.337.117c.9.12 1.658.38 2.26.981c.602.602.86 1.36.982 2.26c.116.867.116 1.97.116 3.337v8.11c0 1.367 0 2.47-.116 3.337c-.122.9-.38 1.658-.982 2.26s-1.36.86-2.26.982c-.867.116-1.97.116-3.337.116h-1.11c-1.367 0-2.47 0-3.337-.116c-.9-.122-1.658-.38-2.26-.982c-.4-.4-.648-.869-.805-1.402c-.951-.001-1.744-.012-2.386-.098c-.764-.103-1.426-.325-1.955-.854s-.751-1.19-.854-1.955c-.098-.73-.098-1.656-.098-2.79V9.447c0-1.133 0-2.058.098-2.79c.103-.763.325-1.425.854-1.954s1.19-.751 1.955-.854c.642-.086 1.435-.097 2.386-.098c.157-.533.406-1.002.805-1.402c.602-.602 1.36-.86 2.26-.981c.867-.117 1.97-.117 3.337-.117M7.25 16.055c0 1.05 0 1.943.053 2.694c-.835-.003-1.455-.018-1.946-.084c-.598-.08-.89-.224-1.094-.428s-.348-.496-.428-1.094c-.083-.619-.085-1.443-.085-2.643v-5c0-1.2.002-2.024.085-2.643c.08-.598.224-.89.428-1.094s.496-.348 1.094-.428c.491-.066 1.111-.08 1.946-.084C7.25 6 7.25 6.895 7.25 7.945zm3.558-13.202c-.734.099-1.122.28-1.399.556c-.277.277-.457.665-.556 1.4C8.752 5.562 8.75 6.564 8.75 8v8c0 1.435.002 2.436.103 3.192c.099.734.28 1.122.556 1.399c.277.277.665.457 1.4.556c.755.101 1.756.103 3.191.103h1c1.435 0 2.436-.002 3.192-.103c.734-.099 1.122-.28 1.399-.556c.277-.277.457-.665.556-1.4c.101-.755.103-1.756.103-3.191V8c0-1.435-.002-2.437-.103-3.192c-.099-.734-.28-1.122-.556-1.399c-.277-.277-.665-.457-1.4-.556c-.755-.101-1.756-.103-3.191-.103h-1c-1.435 0-2.437.002-3.192.103" clip-rule="evenodd" stroke-width="0.5" stroke="#dfdfdf" />
                                                </svg>
                                            </svg>
                                        </button>
                                    </div>
                                    <div>
                                        <?php
                                        // Check if user has saved this post
                                        $save_check_sql = "SELECT save_id FROM saved_posts WHERE post_id = ? AND user_id = ?";
                                        $save_check_stmt = $db->prepare($save_check_sql);
                                        $save_check_stmt->bind_param("ii", $post['post_id'], $_SESSION['user_id']);
                                        $save_check_stmt->execute();
                                        $user_has_saved = $save_check_stmt->get_result()->num_rows > 0;
                                        ?>
                                        <button class="post-action save-btn <?= $user_has_saved ? 'saved' : '' ?>" data-post-id="<?= $post['post_id'] ?>">
                                            <?php if ($user_has_saved): ?>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="28px" height="28px" viewBox="0 0 24 24">
                                                    <path fill="#cacaca" fill-rule="evenodd" d="M21 11.098v4.993c0 3.096 0 4.645-.734 5.321c-.35.323-.792.526-1.263.58c-.987.113-2.14-.907-4.445-2.946c-1.02-.901-1.529-1.352-2.118-1.47a2.2 2.2 0 0 0-.88 0c-.59.118-1.099.569-2.118 1.47c-2.305 2.039-3.458 3.059-4.445 2.945a2.24 2.24 0 0 1-1.263-.579C3 20.736 3 19.188 3 16.091v-4.994C3 6.81 3 4.666 4.318 3.333S7.758 2 12 2s6.364 0 7.682 1.332S21 6.81 21 11.098M8.25 6A.75.75 0 0 1 9 5.25h6a.75.75 0 0 1 0 1.5H9A.75.75 0 0 1 8.25 6" clip-rule="evenodd" stroke-width="0.5" stroke="#cacaca" />
                                                </svg>
                                            <?php else: ?>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="28px" height="28px" viewBox="0 0 24 24">
                                                    <path fill="#fffcfc" d="M9 5.25a.75.75 0 0 0 0 1.5h6a.75.75 0 0 0 0-1.5z" stroke-width="0.5" stroke="#fffcfc" />
                                                    <path fill="#fffcfc" fill-rule="evenodd" d="M11.943 1.25c-2.073 0-3.706 0-4.982.173c-1.31.178-2.355.552-3.176 1.382c-.82.829-1.188 1.882-1.364 3.202c-.171 1.289-.171 2.938-.171 5.034v5.098c0 1.508 0 2.701.096 3.6c.095.888.298 1.689.88 2.225c.466.43 1.056.7 1.686.773c.787.09 1.522-.286 2.247-.8c.733-.518 1.622-1.305 2.744-2.297l.036-.032c.52-.46.872-.77 1.166-.986c.284-.207.457-.282.603-.312a1.5 1.5 0 0 1 .584 0c.146.03.32.105.603.312c.294.215.646.526 1.166.986l.037.032c1.121.992 2.01 1.779 2.743 2.298c.725.513 1.46.889 2.247.799a3 3 0 0 0 1.686-.773c.581-.536.785-1.337.88-2.225c.096-.899.096-2.092.096-3.6v-5.098c0-2.096 0-3.746-.171-5.034c-.176-1.32-.544-2.373-1.364-3.202c-.821-.83-1.866-1.204-3.176-1.382c-1.276-.173-2.909-.173-4.982-.173zM4.85 3.86c.497-.502 1.172-.795 2.312-.95c1.163-.158 2.694-.16 4.837-.16s3.674.002 4.837.16c1.14.155 1.815.448 2.312.95c.498.503.789 1.188.943 2.345c.156 1.178.158 2.727.158 4.893v4.993c0 1.566-.001 2.68-.087 3.488c-.09.83-.253 1.141-.405 1.282c-.234.215-.528.35-.84.385c-.2.023-.534-.054-1.21-.532c-.658-.467-1.487-1.198-2.653-2.23l-.026-.023c-.488-.431-.892-.788-1.249-1.05c-.373-.272-.749-.482-1.192-.571a3 3 0 0 0-1.176 0c-.443.09-.82.299-1.192.572c-.357.26-.761.618-1.249 1.049l-.026.023c-1.166 1.032-1.995 1.763-2.653 2.23c-.676.478-1.01.555-1.21.532a1.5 1.5 0 0 1-.84-.385c-.152-.141-.316-.452-.404-1.282c-.087-.809-.088-1.922-.088-3.488v-4.994c0-2.165.002-3.714.158-4.892c.154-1.157.445-1.842.943-2.345" clip-rule="evenodd" stroke-width="0.5" stroke="#fffcfc" />
                                                </svg>
                                            <?php endif; ?>
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Quick Comment Form -->
                                <div class="quick-comment-form">
                                    <form class="d-flex align-items-center p-3" action="javascript:void(0);">
                                        <img src="<?= !empty($user['profile_image']) ? $user['profile_image'] : 'assets/images/default-avatar.png' ?>" 
                                             alt="Your Profile" class="rounded-circle me-2" width="32" height="32">
                                        <input type="text" class="form-control comment-input" placeholder="Add a comment..." data-post-id="<?= $post['post_id'] ?>" style="::placeholder { color: rgba(255, 255, 255, 0.8); }">
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
                    <a href="#" class="share-option-item" data-platform="whatsapp">
                        <div class="share-icon whatsapp">
                            <i class="fab fa-whatsapp"></i>
                        </div>
                        <span>WhatsApp</span>
                    </a>
                </div>
                <div class="share-option-row">
                    <a href="#" class="share-option-item" data-platform="email">
                        <div class="share-icon email">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <span>Email</span>
                    </a>
                    <a href="#" class="share-option-item" data-platform="threads">
                        <div class="share-icon threads">
                            <i class="fab fa-threads"></i>
                        </div>
                        <span>Threads</span>
                    </a>
                    <a href="#" class="share-option-item" data-platform="twitter">
                        <div class="share-icon twitter">
                            <i class="fab fa-twitter"></i>
                        </div>
                        <span>X</span>
                    </a>
                    <a href="#" class="share-option-item more-options">
                        <div class="share-icon more">
                            <i class="fas fa-ellipsis-h"></i>
                        </div>
                        <span>More</span>
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

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/feed.js"></script>
    <script src="assets/js/stories.js"></script>
    <!-- Add this line to include the post-modal.js file -->
    <script src="assets/js/post-modal.js" defer></script>
        <!-- <script src="assets/js/stories-sidebar.js"></script> -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle video controls
    document.querySelectorAll('.video-container').forEach(container => {
        const video = container.querySelector('video');
        const playPauseBtn = container.querySelector('.play-pause-btn');
        const volumeBtn = container.querySelector('.volume-btn');
        
        // Initialize video state
        video.volume = 1;
        container.classList.add('paused');
        
        // Play/Pause functionality
        playPauseBtn.addEventListener('click', () => {
            if (video.paused) {
                video.play();
                container.classList.remove('paused');
                container.classList.add('playing');
            } else {
                video.pause();
                container.classList.remove('playing');
                container.classList.add('paused');
            }
        });
        
        // Volume control
        volumeBtn.addEventListener('click', () => {
            if (video.volume === 0) {
                video.volume = 1;
                container.classList.remove('muted');
            } else {
                video.volume = 0;
                container.classList.add('muted');
            }
        });
        
        // Show controls on hover
        container.addEventListener('mouseenter', () => {
            container.classList.add('show-controls');
        });
        
        container.addEventListener('mouseleave', () => {
            container.classList.remove('show-controls');
        });
        
        // Pause video when out of viewport
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (!entry.isIntersecting && !video.paused) {
                    video.pause();
                    container.classList.remove('playing');
                    container.classList.add('paused');
                }
            });
        }, { threshold: 0.5 });
        
        observer.observe(container);
    });
});

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