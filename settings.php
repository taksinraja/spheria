<?php
session_start();
require_once 'includes/auth/auth_check.php';
require_once 'includes/config.php';
require_once 'includes/db.php';

// Get user data
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE user_id = ?";
$stmt = $db->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Check for success/error messages
$success_message = '';
$error_message = '';

if (isset($_SESSION['success'])) {
    $success_message = $_SESSION['success'];
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    $error_message = $_SESSION['error'];
    unset($_SESSION['error']);
}

// Get active tab from URL parameter, default to 'edit_profile'
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'edit_profile';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spheria - Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/sidebar.css">
    <link rel="stylesheet" href="assets/css/settings.css">
    <link rel="shortcut icon" href="assets/images/logo2.png" type="image/x-icon">
</head>
<body class="bg">
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <div class="col main-content">
                <div class="settings-container">
                    <h2 class="mb-4">Settings</h2>
                    
                    <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
                    <?php endif; ?>
                    
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
                    <?php endif; ?>
                    
                    <div class="settings-layout">
                        <!-- Settings Navigation -->
                        <div class="settings-nav">
                            <ul>
                                <li class="<?= $active_tab == 'edit_profile' ? 'active' : '' ?>">
                                    <a href="?tab=edit_profile">
                                        <i class="fas fa-user"></i> Edit Profile
                                    </a>
                                </li>
                                <li class="<?= $active_tab == 'account_info' ? 'active' : '' ?>">
                                    <a href="?tab=account_info">
                                        <i class="fas fa-info-circle"></i> Account Information
                                    </a>
                                </li>
                                <li class="<?= $active_tab == 'change_password' ? 'active' : '' ?>">
                                    <a href="?tab=change_password">
                                        <i class="fas fa-lock"></i> Change Password
                                    </a>
                                </li>
                                <li class="<?= $active_tab == 'privacy' ? 'active' : '' ?>">
                                    <a href="?tab=privacy">
                                        <i class="fas fa-shield-alt"></i> Privacy
                                    </a>
                                </li>
                                <li class="<?= $active_tab == 'notifications' ? 'active' : '' ?>">
                                    <a href="?tab=notifications">
                                        <i class="fas fa-bell"></i> Notifications
                                    </a>
                                </li>
                                <li class="<?= $active_tab == 'login_activity' ? 'active' : '' ?>">
                                    <a href="?tab=login_activity">
                                        <i class="fas fa-sign-in-alt"></i> Login Activity
                                    </a>
                                </li>
                            </ul>
                        </div>
                        
                        <!-- Settings Content -->
                        <div class="settings-content">
                            <!-- Edit Profile Tab -->
                            <?php if ($active_tab == 'edit_profile'): ?>
                                <div class="settings-card">
                                    <div class="settings-header">
                                        <h4>Edit Profile</h4>
                                    </div>
                                    
                                    <div class="profile-image-container">
                                        <img src="<?= !empty($user['profile_image']) ? htmlspecialchars($user['profile_image']) : 'assets/images/default-avatar.png' ?>" alt="Profile Picture">
                                        <form action="includes/profile/update_image.php" method="POST" enctype="multipart/form-data" id="profileImageForm">
                                            <input type="file" name="profile_image" id="profileImageInput" hidden accept="image/*">
                                            <button type="button" class="btn btn-sm btn-outline-primary btn-change-image" onclick="document.getElementById('profileImageInput').click()">
                                                Change Profile Picture
                                            </button>
                                        </form>
                                    </div>
                                    
                                    <form action="includes/profile/update_profile.php" method="POST">
                                        <div class="form-group">
                                            <label for="username">Username</label>
                                            <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
                                            <div class="form-text">
                                                You can change your username only once within 14 days.
                                            </div>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="fullname">Full Name</label>
                                            <input type="text" class="form-control" id="fullname" name="fullname" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="bio">Bio</label>
                                            <textarea class="form-control" id="bio" name="bio" rows="3"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                                            <div class="form-text">
                                                <span id="bio-counter">0</span>/150
                                            </div>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="email">Email</label>
                                            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="phone">Phone Number</label>
                                            <input type="tel" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="gender">Gender</label>
                                            <select class="form-control" id="gender" name="gender">
                                                <option value="">Prefer not to say</option>
                                                <option value="male" <?= (isset($user['gender']) && $user['gender'] == 'male') ? 'selected' : '' ?>>Male</option>
                                                <option value="female" <?= (isset($user['gender']) && $user['gender'] == 'female') ? 'selected' : '' ?>>Female</option>
                                                <option value="custom" <?= (isset($user['gender']) && $user['gender'] == 'custom') ? 'selected' : '' ?>>Custom</option>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="website">Website</label>
                                            <input type="url" class="form-control" id="website" name="website" value="<?= htmlspecialchars($user['website'] ?? '') ?>">
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary">Submit</button>
                                    </form>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Account Information Tab -->
                            <?php if ($active_tab == 'account_info'): ?>
                                <div class="settings-card">
                                    <div class="settings-header">
                                        <h4>Account Information</h4>
                                    </div>
                                    
                                    <div class="account-info-list">
                                        <div class="info-item">
                                            <div class="info-label">Username</div>
                                            <div class="info-value"><?= htmlspecialchars($user['username']) ?></div>
                                        </div>
                                        
                                        <div class="info-item">
                                            <div class="info-label">Full Name</div>
                                            <div class="info-value"><?= htmlspecialchars($user['full_name'] ?? 'Not provided') ?></div>
                                        </div>
                                        
                                        <div class="info-item">
                                            <div class="info-label">Email</div>
                                            <div class="info-value"><?= htmlspecialchars($user['email']) ?></div>
                                        </div>
                                        
                                        <div class="info-item">
                                            <div class="info-label">Phone Number</div>
                                            <div class="info-value"><?= htmlspecialchars($user['phone'] ?? 'Not provided') ?></div>
                                        </div>
                                        
                                        <div class="info-item">
                                            <div class="info-label">Gender</div>
                                            <div class="info-value">
                                                <?php 
                                                if (isset($user['gender']) && !empty($user['gender'])) {
                                                    if ($user['gender'] == 'male') echo 'Male';
                                                    elseif ($user['gender'] == 'female') echo 'Female';
                                                    elseif ($user['gender'] == 'custom') echo 'Custom';
                                                } else {
                                                    echo 'Prefer not to say';
                                                }
                                                ?>
                                            </div>
                                        </div>
                                        
                                        <div class="info-item">
                                            <div class="info-label">Website</div>
                                            <div class="info-value">
                                                <?php if (isset($user['website']) && !empty($user['website'])): ?>
                                                    <a href="<?= htmlspecialchars($user['website']) ?>" target="_blank"><?= htmlspecialchars($user['website']) ?></a>
                                                <?php else: ?>
                                                    Not provided
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        
                                        <div class="info-item">
                                            <div class="info-label">Bio</div>
                                            <div class="info-value"><?= htmlspecialchars($user['bio'] ?? 'Not provided') ?></div>
                                        </div>
                                        
                                        <div class="info-item">
                                            <div class="info-label">Date Joined</div>
                                            <div class="info-value"><?= isset($user['created_at']) ? date('F j, Y', strtotime($user['created_at'])) : 'Unknown' ?></div>
                                        </div>
                                        
                                        <div class="info-item">
                                            <div class="info-label">Account Type</div>
                                            <div class="info-value"><?= isset($user['account_type']) && $user['account_type'] == 'premium' ? 'Premium' : 'Standard' ?></div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Change Password Tab -->
                            <?php if ($active_tab == 'change_password'): ?>
                                <div class="settings-card">
                                    <div class="settings-header">
                                        <h4>Change Password</h4>
                                    </div>
                                    
                                    <form action="includes/auth/update_password.php" method="POST">
                                        <div class="form-group">
                                            <label for="current_password">Current Password</label>
                                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="new_password">New Password</label>
                                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                                            <div class="form-text">
                                                Password must be at least 8 characters long and include a mix of letters, numbers, and symbols.
                                            </div>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="confirm_password">Confirm New Password</label>
                                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary">Change Password</button>
                                    </form>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Privacy Tab -->
                            <?php if ($active_tab == 'privacy'): ?>
                                <div class="settings-card">
                                    <div class="settings-header">
                                        <h4>Privacy Settings</h4>
                                    </div>
                                    
                                    <form action="includes/profile/update_privacy.php" method="POST">
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" id="private_account" name="private_account" <?= isset($user['is_private']) && $user['is_private'] ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="private_account">
                                                Private Account
                                            </label>
                                            <div class="form-text">When enabled, only approved followers can see your posts</div>
                                        </div>
                                        
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" id="show_activity" name="show_activity" <?= isset($user['show_activity']) && $user['show_activity'] ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="show_activity">
                                                Show Activity Status
                                            </label>
                                            <div class="form-text">Allow others to see when you're active on Spheria</div>
                                        </div>
                                        
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" id="show_read_receipts" name="show_read_receipts" <?= isset($user['show_read_receipts']) && $user['show_read_receipts'] ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="show_read_receipts">
                                                Show Read Receipts
                                            </label>
                                            <div class="form-text">Let people know when you've seen their messages</div>
                                        </div>
                                        
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" id="mentions_control" name="mentions_control" <?= isset($user['mentions_control']) && $user['mentions_control'] ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="mentions_control">
                                                Restrict Mentions
                                            </label>
                                            <div class="form-text">Only allow mentions from people you follow</div>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary">Save Privacy Settings</button>
                                    </form>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Notifications Tab -->
                            <?php if ($active_tab == 'notifications'): ?>
                                <div class="settings-card">
                                    <div class="settings-header">
                                        <h4>Notification Settings</h4>
                                    </div>
                                    
                                    <form action="includes/profile/update_notifications.php" method="POST">
                                        <h5 class="settings-subheader">Push Notifications</h5>
                                        
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" id="notify_likes" name="notify_likes" <?= isset($user['notify_likes']) && $user['notify_likes'] ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="notify_likes">
                                                Likes
                                            </label>
                                            <div class="form-text">Receive notifications when someone likes your post</div>
                                        </div>
                                        
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" id="notify_comments" name="notify_comments" <?= isset($user['notify_comments']) && $user['notify_comments'] ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="notify_comments">
                                                Comments
                                            </label>
                                            <div class="form-text">Receive notifications when someone comments on your post</div>
                                        </div>
                                        
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" id="notify_follows" name="notify_follows" <?= isset($user['notify_follows']) && $user['notify_follows'] ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="notify_follows">
                                                New Followers
                                            </label>
                                            <div class="form-text">Receive notifications when someone follows you</div>
                                        </div>
                                        
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" id="notify_messages" name="notify_messages" <?= isset($user['notify_messages']) && $user['notify_messages'] ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="notify_messages">
                                                Direct Messages
                                            </label>
                                            <div class="form-text">Receive notifications for new messages</div>
                                        </div>
                                        
                                        <h5 class="settings-subheader">Email Notifications</h5>
                                        
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" id="email_security" name="email_security" <?= isset($user['email_security']) && $user['email_security'] ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="email_security">
                                                Security Emails
                                            </label>
                                            <div class="form-text">Receive emails about security and login activity</div>
                                        </div>
                                        
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" id="email_product" name="email_product" <?= isset($user['email_product']) && $user['email_product'] ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="email_product">
                                                Product Emails
                                            </label>
                                            <div class="form-text">Receive emails about new features and updates</div>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary">Save Notification Settings</button>
                                    </form>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Login Activity Tab -->
                            <?php if ($active_tab == 'login_activity'): ?>
                                <div class="settings-card">
                                    <div class="settings-header">
                                        <h4>Login Activity</h4>
                                    </div>
                                    
                                    <div class="login-activity-list">
                                        <p class="text-muted">This is where you can see your recent login activity and verify that it was you.</p>
                                        
                                        <!-- This would typically be populated from a database table of login history -->
                                        <div class="login-item">
                                            <div class="login-device">
                                                <i class="fas fa-desktop"></i>
                                                <div class="device-info">
                                                    <div class="device-name">Windows PC - Chrome</div>
                                                    <div class="device-location">San Francisco, CA</div>
                                                </div>
                                            </div>
                                            <div class="login-time">Active now</div>
                                        </div>
                                        
                                        <div class="login-item">
                                            <div class="login-device">
                                                <i class="fas fa-mobile-alt"></i>
                                                <div class="device-info">
                                                    <div class="device-name">iPhone - Safari</div>
                                                    <div class="device-location">San Francisco, CA</div>
                                                </div>
                                            </div>
                                            <div class="login-time">2 days ago</div>
                                        </div>
                                        
                                        <div class="mt-4">
                                            <form action="includes/auth/logout.php" method="POST">
                                                <button type="submit" class="btn btn-outline-danger">Log Out of All Sessions</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    // Handle profile image upload
    document.getElementById('profileImageInput').addEventListener('change', function() {
        if (this.files && this.files[0]) {
            document.getElementById('profileImageForm').submit();
        }
    });
    
    // Bio character counter
    const bioTextarea = document.getElementById('bio');
    const bioCounter = document.getElementById('bio-counter');
    
    if (bioTextarea && bioCounter) {
        bioTextarea.addEventListener('input', function() {
            const count = this.value.length;
            bioCounter.textContent = count;
            
            if (count > 150) {
                bioCounter.style.color = 'red';
            } else {
                bioCounter.style.color = '';
            }
        });
        
        // Initialize counter
        bioCounter.textContent = bioTextarea.value.length;
    }
    </script>
</body>
</html>