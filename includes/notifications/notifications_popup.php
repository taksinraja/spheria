<?php
require_once __DIR__ . '/get_notifications.php';
$notifications = get_notifications($db, $_SESSION['user_id']);
?>

<div class="notifications-popup" id="notificationsPopup">
    <div class="notifications-header">
        <h4>Notifications</h4>
        <button type="button" class="close-notifications" id="closeNotifications">
            <i class="fas fa-times"></i>
        </button>
    </div>
    
    <div class="notifications-tabs">
        <div class="notifications-tab active" data-tab="all">All</div>
        <div class="notifications-tab" data-tab="requests">Requests</div>
        <div class="notifications-tab" data-tab="mentions">Mentions</div>
    </div>

    <div class="notifications-content">
        <?php if (!empty($notifications)): ?>
            <?php foreach ($notifications as $group => $group_notifications): ?>
                <div class="notification-group">
                    <div class="notification-group-header"><?= $group ?></div>
                    <?php foreach ($group_notifications as $notification): ?>
                        <div class="notification-item">
                            <div class="notification-avatar">
                                <img src="<?= $notification['profile_image'] ?? 'assets/images/default-avatar.png' ?>" alt="User">
                            </div>
                            <div class="notification-content">
                                <span class="notification-text">
                                    <strong><?= htmlspecialchars($notification['username']) ?></strong>
                                    <?php
                                    switch ($notification['type']) {
                                        case 'follow':
                                            echo 'started following you.';
                                            break;
                                        case 'like':
                                            echo 'liked your post.';
                                            break;
                                        case 'comment':
                                            echo 'commented on your post.';
                                            break;
                                        case 'mention':
                                            echo 'mentioned you in a comment.';
                                            break;
                                    }
                                    ?>
                                </span>
                                <div class="notification-time"><?= $notification['time_ago'] ?></div>
                            </div>
                            <?php if ($notification['type'] === 'follow'): ?>
                                <button class="notification-action" data-user-id="<?= $notification['from_user_id'] ?>">
                                    Follow
                                </button>
                            <?php elseif ($notification['post_preview']): ?>
                                <div class="notification-post-preview">
                                    <img src="<?= $notification['post_preview'] ?>" alt="Post preview">
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="notification-group">
                <div class="notification-item">
                    <p class="text-center">No notifications yet</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>