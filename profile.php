<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth/auth_check.php';

// Get user data - support both ID and username methods
$user_id = null;
$profile_username = null;

if (isset($_GET['id'])) {
    // Method 1: Access by ID
    $user_id = intval($_GET['id']);
} elseif (isset($_GET['username'])) {
    // Method 2: Access by username
    $profile_username = $_GET['username'];
    
    // Get user ID from username
    $username_stmt = $db->prepare("SELECT user_id FROM users WHERE username = ?");
    $username_stmt->bind_param("s", $profile_username);
    $username_stmt->execute();
    $username_result = $username_stmt->get_result();
    
    if ($username_result->num_rows > 0) {
        $user_id = $username_result->fetch_assoc()['user_id'];
    }
} else {
    // Default to current user if no parameters provided
    $user_id = $_SESSION['user_id'];
}

// If user ID is still null (invalid username), default to current user
if ($user_id === null) {
    $user_id = $_SESSION['user_id'];
}

$is_own_profile = ($user_id == $_SESSION['user_id']);

try {
    // Get user info
    $user_sql = "SELECT * FROM users WHERE user_id = ?";
    $user_stmt = $db->prepare($user_sql);
    $user_stmt->bind_param("i", $user_id);
    $user_stmt->execute();
    $user = $user_stmt->get_result()->fetch_assoc();
    
    if (!$user) {
        // User not found, redirect to own profile
        header("Location: profile.php");
        exit;
    }
    
    // Get posts count
    $posts_count_sql = "SELECT COUNT(*) as count FROM posts WHERE user_id = ?";
    $posts_count_stmt = $db->prepare($posts_count_sql);
    $posts_count_stmt->bind_param("i", $user_id);
    $posts_count_stmt->execute();
    $posts_count = $posts_count_stmt->get_result()->fetch_assoc()['count'];
    
    // Get followers count
    $followers_sql = "SELECT COUNT(*) as count FROM followers f 
                     JOIN users u ON f.follower_id = u.user_id 
                     WHERE f.following_id = ?";
    $followers_stmt = $db->prepare($followers_sql);
    $followers_stmt->bind_param("i", $user_id);
    $followers_stmt->execute();
    $followers_count = $followers_stmt->get_result()->fetch_assoc()['count'];
    
    // Get following count
    $following_sql = "SELECT COUNT(*) as count FROM followers f 
                     JOIN users u ON f.following_id = u.user_id 
                     WHERE f.follower_id = ?";
    $following_stmt = $db->prepare($following_sql);
    $following_stmt->bind_param("i", $user_id);
    $following_stmt->execute();
    $following_count = $following_stmt->get_result()->fetch_assoc()['count'];
    
    // Check if current user is following this profile
    $is_following = false;
    if (!$is_own_profile) {
        $follow_check_sql = "SELECT * FROM followers WHERE follower_id = ? AND following_id = ?";
        $follow_check_stmt = $db->prepare($follow_check_sql);
        $follow_check_stmt->bind_param("ii", $_SESSION['user_id'], $user_id);
        $follow_check_stmt->execute();
        $is_following = ($follow_check_stmt->get_result()->num_rows > 0);
    }
    
    // Get user posts
    $posts_sql = "SELECT p.*, COUNT(l.like_id) as likes_count 
                 FROM posts p 
                 LEFT JOIN likes l ON p.post_id = l.post_id 
                 WHERE p.user_id = ? 
                 GROUP BY p.post_id 
                 ORDER BY p.created_at DESC";
    $posts_stmt = $db->prepare($posts_sql);
    $posts_stmt->bind_param("i", $user_id);
    $posts_stmt->execute();
    $posts_result = $posts_stmt->get_result();
    $posts = [];
    while ($post = $posts_result->fetch_assoc()) {
        $posts[] = $post;
    }
} catch (Exception $e) {
    error_log("Error fetching data: " . $e->getMessage());
}

// Check for success message
$success_message = '';
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $success_message = 'Post created successfully!';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spheria - <?= htmlspecialchars($user['username']) ?>'s Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="post.css">
    <link rel="stylesheet" href="assets/css/post-modal.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/sidebar.css">
    <link rel="stylesheet" href="assets/css/profile.css">
    <link rel="shortcut icon" href="assets/images/logo2.png" type="image/x-icon">
    <!-- <link rel="stylesheet" href="post.css"> -->
    <!-- <link rel="stylesheet" href="assets/css/post-modal.css"> -->
</head>
<body class="bg">
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>

            <div class="col main-content">
                <?php if (!empty($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <div class="profile-container">
                    <!-- Profile Header -->
                    <div class="profile-header">
                        <div class="cover-photo">
                            <img src="<?= !empty($user['cover_image']) ? htmlspecialchars($user['cover_image']) : 'assets/images/default-cover.png' ?>" alt="Cover Photo">
                            <?php if ($is_own_profile): ?>
                                <form id="coverPhotoForm" action="includes/profile/update.php" method="POST" enctype="multipart/form-data">
                                    <input type="file" name="cover_image" id="coverPhotoInput" hidden accept="image/*">
                                    <button type="button" class="edit-cover" onclick="document.getElementById('coverPhotoInput').click()">
                                        <i class="fas fa-camera"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                        
                        <div class="profile-info">
                            <div class="profile-picture">
                                <img src="<?= !empty($user['profile_image']) ? $user['profile_image'] : 'assets/images/default-avatar.png' ?>" alt="Profile Picture">
                                <?php if ($is_own_profile): ?>
                                <form id="profileImageForm" action="includes/profile/update.php" method="POST" enctype="multipart/form-data">
                                    <input type="file" name="profile_image" id="profileImageInput" hidden accept="image/*">
                                    <button type="button" class="btn btn-dark btn-sm edit-profile-pic" onclick="document.getElementById('profileImageInput').click()">
                                        <i class="fas fa-camera"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                            
                            <div class="profile-details">
                                <h2><?= htmlspecialchars($user['full_name'] ?? $user['username']) ?></h2>
                                <p class="username">@<?= htmlspecialchars($user['username'] ?? '') ?></p>
                                <p class="bio"><?= htmlspecialchars($user['bio'] ?? 'No bio yet.') ?></p>
                                
                                <?php if (!$is_own_profile): ?>
                                <div class="profile-actions mb-3">
                                    <button id="followBtn" class=" <?= $is_following ? 'btn-following' : 'btn-follow' ?>" data-user-id="<?= $user_id ?>">
                                        <?= $is_following ? 'Following' : 'Follow' ?>
                                    </button>
                                    <button class="btn btn-outline-light ms-2" onclick="window.location.href='messages.php?user=<?= $user_id ?>'">
                                        <i class="fas fa-envelope me-1"></i> Message
                                    </button>
                                </div>
                                <?php endif; ?>
                                
                                <div class="profile-stats">
                                    <div class="stat">
                                        <span class="count"><?= $posts_count ?? 0 ?></span>
                                        <span class="label">Posts</span>
                                    </div>
                                    <div class="stat followers-stat" data-user-id="<?= $user_id ?>">
                                        <span class="count"><?= $followers_count ?? 0 ?></span>
                                        <span class="label">Followers</span>
                                    </div>
                                    <div class="stat following-stat" data-user-id="<?= $user_id ?>">
                                        <span class="count"><?= $following_count ?? 0 ?></span>
                                        <span class="label">Following</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="profile-content">
                        <div class="profile-tabs">
                            <button class="tab-btn active" data-tab="posts">Posts</button>
                            <button class="tab-btn" data-tab="spheres">Spheres</button>
                            <?php if ($is_own_profile): ?>
                            <button class="tab-btn" data-tab="saved">Saved</button>
                            <!-- Removed the Edit Profile tab button -->
                            <?php endif; ?>
                        </div>

                        <div class="tab-content">
                            <!-- In the posts tab, replace the "by @username" with a comment icon -->
                            <div id="posts" class="tab-pane active">
                                <?php if (count($posts) > 0): ?>
                                    <div class="posts-grid">
                                        <?php foreach ($posts as $post): ?>
                                            <?php
                                            // Get all post media (removing ORDER BY media_order ASC)
                                            $media_sql = "SELECT * FROM post_media WHERE post_id = ?";
                                            $media_stmt = $db->prepare($media_sql);
                                            $media_stmt->bind_param("i", $post['post_id']);
                                            $media_stmt->execute();
                                            $media_result = $media_stmt->get_result();
                                            $media_items = [];
                                            while ($media = $media_result->fetch_assoc()) {
                                                $media_items[] = $media;
                                            }
                                            
                                            // Get comments count
                                            $comments_sql = "SELECT COUNT(*) as count FROM comments WHERE post_id = ?";
                                            $comments_stmt = $db->prepare($comments_sql);
                                            $comments_stmt->bind_param("i", $post['post_id']);
                                            $comments_stmt->execute();
                                            $comments_count = $comments_stmt->get_result()->fetch_assoc()['count'];
                                            ?>
                                            <div class="post-item" data-post-id="<?php echo $post['post_id']; ?>">
                                            <?php if (count($media_items) > 0): ?>
                                                        <?php if ($media_items[0]['media_type'] == 'image'): ?>
                                                            <img src="<?php echo $media_items[0]['file_path']; ?>" alt="Post">
                                                            <?php if (count($media_items) > 1): ?>
                                                                <div class="multiple-indicator">
                                                                    <i class="fas fa-clone"></i>
                                                                </div>
                                                            <?php endif; ?>
                                                        <?php elseif ($media_items[0]['media_type'] == 'video'): ?>
                                                            <video>
                                                                <source src="<?php echo $media_items[0]['file_path']; ?>" type="video/mp4">
                                                            </video>
                                                            <div class="video-overlay">
                                                                <i class="fas fa-play"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <div class="text-post">
                                                            <p><?php echo substr(htmlspecialchars($post['content']), 0, 100); ?><?php echo (strlen($post['content']) > 100) ? '...' : ''; ?></p>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div class="post-info">
                                                        <div class="post-info-like-comment-icon">
                                                            <span><i class="fas fa-heart"></i> <?php echo $post['likes_count']; ?></span>
                                                            <span><i class="far fa-comment"></i> <?php echo $comments_count; ?></span>
                                                        </div>
                                                        <?php if ($is_own_profile): ?>
                                                            <button class="btn btn-danger btn-sm delete-post" data-post-id="<?php echo $post['post_id']; ?>">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="post-link" data-post-id="<?php echo $post['post_id']; ?>"></div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="no-posts">
                                        <p>No posts yet.</p>
                                        <?php if ($is_own_profile): ?>
                                        <a href="create.php" class="btn btn-primary">Create your first post</a>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <!-- Add Spheres Tab Content -->
                            <div id="spheres" class="tab-pane">
                                <?php
                                // Get user's video posts
                                $spheres_sql = "SELECT p.*, pm.file_path, COUNT(l.like_id) as likes_count,
                                              COUNT(c.comment_id) as comments_count 
                                              FROM posts p 
                                              JOIN post_media pm ON p.post_id = pm.post_id 
                                              LEFT JOIN likes l ON p.post_id = l.post_id 
                                              LEFT JOIN comments c ON p.post_id = c.post_id 
                                              WHERE p.user_id = ? AND pm.media_type = 'video'
                                              GROUP BY p.post_id 
                                              ORDER BY p.created_at DESC";
                                $spheres_stmt = $db->prepare($spheres_sql);
                                $spheres_stmt->bind_param("i", $user_id);
                                $spheres_stmt->execute();
                                $spheres_result = $spheres_stmt->get_result();
                                $spheres = [];
                                while ($sphere = $spheres_result->fetch_assoc()) {
                                    $spheres[] = $sphere;
                                }
                                ?>
                                
                                <?php if (count($spheres) > 0): ?>
                                    <div class="spheres-grid">
                                        <?php foreach ($spheres as $sphere): ?>
                                            <div class="sphere-item" data-post-id="<?php echo $sphere['post_id']; ?>">
                                                <video>
                                                    <source src="<?php echo $sphere['file_path']; ?>" type="video/mp4">
                                                </video>
                                                <div class="video-overlay">
                                                    <i class="fas fa-play"></i>
                                                </div>
                                                <div class="sphere-info">
                                                    <span><i class="fas fa-heart"></i> <?php echo $sphere['likes_count']; ?></span>
                                                    <span><i class="far fa-comment"></i> <?php echo $sphere['comments_count']; ?></span>
                                                </div>
                                                <?php if ($is_own_profile): ?>
                                                    <button class="btn btn-danger btn-sm delete-post" data-post-id="<?php echo $sphere['post_id']; ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                <?php endif; ?>
                                                <div class="sphere-link" onclick="window.location.href='spheres.php?sphere=<?php echo $sphere['post_id']; ?>'"></div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="no-spheres">
                                        <p>No spheres yet.</p>
                                        <?php if ($is_own_profile): ?>
                                        <a href="create.php" class="btn btn-primary">Create your first sphere</a>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($is_own_profile): ?>
                            <!-- Saved Posts Tab -->
                            <div id="saved" class="tab-pane">
                                <?php
                                // Get saved posts
                                $saved_posts_sql = "SELECT p.*, u.username, u.profile_image, COUNT(l.like_id) as likes_count 
                                                  FROM saved_posts sp
                                                  JOIN posts p ON sp.post_id = p.post_id
                                                  JOIN users u ON p.user_id = u.user_id
                                                  LEFT JOIN likes l ON p.post_id = l.post_id 
                                                  WHERE sp.user_id = ? 
                                                  GROUP BY p.post_id 
                                                  ORDER BY sp.created_at DESC";
                                $saved_posts_stmt = $db->prepare($saved_posts_sql);
                                $saved_posts_stmt->bind_param("i", $_SESSION['user_id']);
                                $saved_posts_stmt->execute();
                                $saved_posts_result = $saved_posts_stmt->get_result();
                                $saved_posts = [];
                                while ($saved_post = $saved_posts_result->fetch_assoc()) {
                                    $saved_posts[] = $saved_post;
                                }
                                ?>
                                
                                <?php if (count($saved_posts) > 0): ?>
                                    <div class="posts-grid">
                                        <?php foreach ($saved_posts as $post): ?>
                                            <?php
                                            // Get all post media instead of just one
                                            $media_sql = "SELECT * FROM post_media WHERE post_id = ?";
                                            $media_stmt = $db->prepare($media_sql);
                                            $media_stmt->bind_param("i", $post['post_id']);
                                            $media_stmt->execute();
                                            $media_result = $media_stmt->get_result();
                                            $media_items = [];
                                            while ($media = $media_result->fetch_assoc()) {
                                                $media_items[] = $media;
                                            }
                                            ?>
                                            <div class="post-item" data-post-id="<?php echo $post['post_id']; ?>">
                                                <?php if (count($media_items) > 0): ?>
                                                            <?php if ($media_items[0]['media_type'] == 'image'): ?>
                                                                <img src="<?php echo $media_items[0]['file_path']; ?>" alt="Post">
                                                                <?php if (count($media_items) > 1): ?>
                                                                    <div class="multiple-indicator">
                                                                        <i class="fas fa-clone"></i>
                                                                    </div>
                                                                <?php endif; ?>
                                                            <?php elseif ($media_items[0]['media_type'] == 'video'): ?>
                                                                <video>
                                                                    <source src="<?php echo $media_items[0]['file_path']; ?>" type="video/mp4">
                                                                </video>
                                                                <div class="video-overlay">
                                                                    <i class="fas fa-play"></i>
                                                                </div>
                                                            <?php endif; ?>
                                                        <?php else: ?>
                                                            <div class="text-post">
                                                                <p><?php echo substr(htmlspecialchars($post['content']), 0, 100); ?><?php echo (strlen($post['content']) > 100) ? '...' : ''; ?></p>
                                                            </div>
                                                        <?php endif; ?>
                                                        <div class="post-info">
                                                            <span><i class="fas fa-heart"></i> <?php echo $post['likes_count']; ?></span>
                                                            <span class="saved-by">by @<?php echo htmlspecialchars($post['username']); ?></span>
                                                        </div>
                                                 <!-- Change this line to make the entire post item clickable -->
                                                 <div class="post-link" data-post-id="<?php echo $post['post_id']; ?>"></div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="no-posts">
                                        <p>No saved posts yet.</p>
                                        <p>Browse and save posts you like to see them here.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- <div id="edit" class="tab-pane">
                                <div class="edit-profile-form">
                                    <h3>Edit Profile</h3>
                                    <form action="includes/profile/update.php" method="POST">
                                        <div class="mb-3">
                                            <label for="fullName" class="form-label">Full Name</label>
                                            <input type="text" class="form-control" id="fullName" name="full_name" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label for="username" class="form-label">Username</label>
                                            <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($user['username'] ?? '') ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label for="bio" class="form-label">Bio</label>
                                            <textarea class="form-control" id="bio" name="bio" rows="3"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>">
                                        </div>
                                        <button type="submit" class="btn btn-primary">Save Changes</button>
                                    </form>
                                </div>
                            </div> -->
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php if (!$is_own_profile): ?>
    <script>
    const followBtn = document.getElementById('followBtn');
    
    if (followBtn) {
        followBtn.addEventListener('click', function() {
            const userId = this.dataset.userId;
            console.log('Following user ID:', userId); // Debug
            
            // Disable button during request
            followBtn.disabled = true;
            
            fetch('/spheria1/includes/profile/follow.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'user_id=' + userId
            })
            .then(response => {
                console.log('Response status:', response.status); // Debug
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data); // Debug
                
                if (data.success) {
                    if (data.action === 'followed') {
                        followBtn.textContent = 'Following';
                        followBtn.classList.remove('btn-follow');
                        followBtn.classList.add('btn-following');
                        
                        // Update followers count
                        const followersCountEl = document.querySelector('.followers-stat .count');
                        if (followersCountEl) {
                            followersCountEl.textContent = parseInt(followersCountEl.textContent) + 1;
                        }
                    } else {
                        followBtn.textContent = 'Follow';
                        followBtn.classList.remove('btn-following');
                        followBtn.classList.add('btn-follow');
                        
                        // Update followers count
                        const followersCountEl = document.querySelector('.followers-stat .count');
                        if (followersCountEl) {
                            followersCountEl.textContent = Math.max(0, parseInt(followersCountEl.textContent) - 1);
                        }
                    }
                } else {
                    alert('Failed to follow/unfollow: ' + (data.message || 'Unknown error'));
                }
                
                // Re-enable button
                followBtn.disabled = false;
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while processing your request');
                
                // Re-enable button
                followBtn.disabled = false;
            });
        });
    }
    </script>
    <?php endif; ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add event listeners to all delete buttons
            document.querySelectorAll('.delete-post').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    if (confirm('Are you sure you want to delete this post?')) {
                        const postId = this.getAttribute('data-post-id');
                        
                        fetch('/spheria1/includes/posts/delete_post.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: 'post_id=' + postId
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Remove the post from the DOM
                                const postElement = this.closest('.post-item');
                                postElement.remove();
                            } else {
                                alert('Failed to delete post: ' + (data.message || 'Unknown error'));
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('An error occurred while deleting the post');
                        });
                    }
                });
            });
        });
        </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/profile.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

<!-- Followers Modal -->
<div class="modal fade" id="followersModal" tabindex="-1" aria-labelledby="followersModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header">
                <h5 class="modal-title" id="followersModalLabel">Followers</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="followers-list">
                    <!-- Followers will be loaded here dynamically -->
                    <div class="text-center">
                        <div class="spinner-border text-light" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Following Modal -->
<div class="modal fade" id="followingModal" tabindex="-1" aria-labelledby="followingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header">
                <h5 class="modal-title" id="followingModalLabel">Following</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="following-list">
                    <!-- Following users will be loaded here dynamically -->
                    <div class="text-center">
                        <div class="spinner-border text-light" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Followers click event
    const followersStats = document.querySelector('.followers-stat');
    if (followersStats) {
        followersStats.addEventListener('click', function() {
            const userId = this.dataset.userId;
            loadFollowers(userId);
            const followersModal = new bootstrap.Modal(document.getElementById('followersModal'));
            followersModal.show();
        });
    }
    
    // Following click event
    const followingStats = document.querySelector('.following-stat');
    if (followingStats) {
        followingStats.addEventListener('click', function() {
            const userId = this.dataset.userId;
            loadFollowing(userId);
            const followingModal = new bootstrap.Modal(document.getElementById('followingModal'));
            followingModal.show();
        });
    }
    
    // Load followers function
    function loadFollowers(userId) {
        const followersList = document.querySelector('.followers-list');
        followersList.innerHTML = '<div class="text-center"><div class="spinner-border text-light" role="status"><span class="visually-hidden">Loading...</span></div></div>';
        
        fetch(`includes/profile/get_followers.php?user_id=${userId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.followers.length > 0) {
                        followersList.innerHTML = '';
                        data.followers.forEach(follower => {
                            followersList.innerHTML += `
                                <div class="user-item d-flex align-items-center mb-3">
                                    <img src="${follower.profile_image || 'assets/images/default-avatar.png'}" alt="${follower.username}" class="rounded-circle me-3" width="50" height="50">
                                    <div class="user-info flex-grow-1">
                                        <h6 class="mb-0">${follower.full_name || follower.username}</h6>
                                        <small class="text-secondary">@${follower.username}</small>
                                    </div>
                                    <a href="profile.php?id=${follower.user_id}" class="btn btn-sm btn-outline-light">View</a>
                                </div>
                            `;
                        });
                    } else {
                        followersList.innerHTML = '<p class="text-center">No followers yet.</p>';
                    }
                } else {
                    followersList.innerHTML = '<p class="text-center text-danger">Error loading followers.</p>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                followersList.innerHTML = '<p class="text-center text-danger">Error loading followers.</p>';
            });
    }
    
    // Load following function
    function loadFollowing(userId) {
        const followingList = document.querySelector('.following-list');
        followingList.innerHTML = '<div class="text-center"><div class="spinner-border text-light" role="status"><span class="visually-hidden">Loading...</span></div></div>';
        
        fetch(`includes/profile/get_following.php?user_id=${userId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.following.length > 0) {
                        followingList.innerHTML = '';
                        data.following.forEach(following => {
                            followingList.innerHTML += `
                                <div class="user-item d-flex align-items-center mb-3">
                                    <img src="${following.profile_image || 'assets/images/default-avatar.png'}" alt="${following.username}" class="rounded-circle me-3" width="50" height="50">
                                    <div class="user-info flex-grow-1">
                                        <h6 class="mb-0">${following.full_name || following.username}</h6>
                                        <small class="text-secondary">@${following.username}</small>
                                    </div>
                                    <a href="profile.php?id=${following.user_id}" class="btn btn-sm btn-outline-light">View</a>
                                </div>
                            `;
                        });
                    } else {
                        followingList.innerHTML = '<p class="text-center">Not following anyone yet.</p>';
                    }
                } else {
                    followingList.innerHTML = '<p class="text-center text-danger">Error loading following users.</p>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                followingList.innerHTML = '<p class="text-center text-danger">Error loading following users.</p>';
            });
    }

    // Add this to your profile.php page in a script tag
    document.addEventListener('DOMContentLoaded', function() {
        // Check if there's a highlight parameter in the URL
        const urlParams = new URLSearchParams(window.location.search);
        const highlightPostId = urlParams.get('highlight');
        
        if (highlightPostId) {
            // Find the post with the matching ID
            const postElement = document.querySelector(`[data-post-id="${highlightPostId}"]`);
            
            if (postElement) {
                // Scroll to the post
                postElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                
                // Add a highlight effect
                postElement.classList.add('highlighted-post');
                
                // Remove the highlight after a few seconds
                setTimeout(() => {
                    postElement.classList.remove('highlighted-post');
                }, 3000);
            }
        }
    });
});
</script>

<style>
    /* Followers/Following Stats Styling */
.stat {
    cursor: pointer;
    transition: all 0.2s ease;
}

/* .stat:hover {
    background-color: rgba(255, 255, 255, 0.1);
    border-radius: 5px;
} */

/* User Item Styling in Modals */
.user-item {
    padding: 10px;
    border-radius: 8px;
    transition: all 0.2s ease;
}

.user-item:hover {
    background-color: rgba(255, 255, 255, 0.05);
}

/* Add this to your CSS */
.highlighted-post {
    animation: highlight-pulse 3s ease-in-out;
}

@keyframes highlight-pulse {
    0%, 100% {
        box-shadow: 0 0 0 rgba(169, 112, 255, 0);
    }
    50% {
        box-shadow: 0 0 20px rgba(169, 112, 255, 0.7);
    }
}
</style>