<?php
require_once 'includes/messages/unread_count.php';
$unread_count = get_unread_messages_count($db, $_SESSION['user_id']);

// Fetch user profile image
$profile_query = "SELECT profile_image FROM users WHERE user_id = ?";
$profile_stmt = $db->prepare($profile_query);
$profile_stmt->bind_param("i", $_SESSION['user_id']);
$profile_stmt->execute();
$user_profile = $profile_stmt->get_result()->fetch_assoc();
$profile_image = $user_profile['profile_image'] ?? 'assets/images/default-avatar.png';
?>
<link href='https://fonts.googleapis.com/css?family=MuseoModerno' rel='stylesheet'>
<div class="col-auto px-0">
    <div class="messages-nav">
        <a href="index.php" class="spheria-logo" style="text-decoration: none;">
            <img src="assets/images/logo.jpeg" alt="Spheria" width="40">
            <span class="logo-text">Spheria</span>
        </a>
        <nav class="nav-icons">
            <a href="index.php" class="nav-icon <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                <?php if(basename($_SERVER['PHP_SELF']) == 'index.php'): ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"> 
                        <path fill="#fff" fill-rule="evenodd" d="M2.335 7.875c-.54 1.127-.35 2.446.03 5.083l.278 1.937c.487 3.388.731 5.081 1.906 6.093S7.447 22 10.894 22h2.212c3.447 0 5.17 0 6.345-1.012s1.419-2.705 1.906-6.093l.279-1.937c.38-2.637.57-3.956.029-5.083s-1.691-1.813-3.992-3.183l-1.385-.825C14.2 2.622 13.154 2 12 2s-2.199.622-4.288 1.867l-1.385.825c-2.3 1.37-3.451 2.056-3.992 3.183M12 18.75a.75.75 0 0 1-.75-.75v-3a.75.75 0 0 1 1.5 0v3a.75.75 0 0 1-.75.75" clip-rule="evenodd" stroke-width="0.5" stroke="#fff" /> 
                    </svg>
                <?php else: ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                        <path fill="#fff" d="M11.25 18a.75.75 0 0 0 1.5 0v-3a.75.75 0 0 0-1.5 0z" stroke-width="0.5" stroke="#fff" />
                        <path fill="#fff" fill-rule="evenodd" d="M12 1.25c-.725 0-1.387.2-2.11.537c-.702.327-1.512.81-2.528 1.415l-1.456.867c-1.119.667-2.01 1.198-2.686 1.706C2.523 6.3 2 6.84 1.66 7.551c-.342.711-.434 1.456-.405 2.325c.029.841.176 1.864.36 3.146l.293 2.032c.237 1.65.426 2.959.707 3.978c.29 1.05.702 1.885 1.445 2.524c.742.64 1.63.925 2.716 1.062c1.056.132 2.387.132 4.066.132h2.316c1.68 0 3.01 0 4.066-.132c1.086-.137 1.974-.422 2.716-1.061c.743-.64 1.155-1.474 1.445-2.525c.281-1.02.47-2.328.707-3.978l.292-2.032c.185-1.282.332-2.305.36-3.146c.03-.87-.062-1.614-.403-2.325S21.477 6.3 20.78 5.775c-.675-.508-1.567-1.039-2.686-1.706l-1.456-.867c-1.016-.605-1.826-1.088-2.527-1.415c-.724-.338-1.386-.537-2.111-.537M8.096 4.511c1.057-.63 1.803-1.073 2.428-1.365c.609-.284 1.047-.396 1.476-.396s.867.112 1.476.396c.625.292 1.37.735 2.428 1.365l1.385.825c1.165.694 1.986 1.184 2.59 1.638c.587.443.91.809 1.11 1.225c.199.416.282.894.257 1.626c-.026.75-.16 1.691-.352 3.026l-.28 1.937c-.246 1.714-.422 2.928-.675 3.845c-.247.896-.545 1.415-.977 1.787c-.433.373-.994.593-1.925.71c-.951.119-2.188.12-3.93.12h-2.213c-1.743 0-2.98-.001-3.931-.12c-.93-.117-1.492-.337-1.925-.71c-.432-.372-.73-.891-.977-1.787c-.253-.917-.43-2.131-.676-3.845l-.279-1.937c-.192-1.335-.326-2.277-.352-3.026c-.025-.732.058-1.21.258-1.626s.521-.782 1.11-1.225c.603-.454 1.424-.944 2.589-1.638z" clip-rule="evenodd" stroke-width="0.5" stroke="#fff" />
                    </svg>
                <?php endif; ?>
                <span>Feed</span>
            </a>
            <a href="search.php" class="nav-icon <?php echo basename($_SERVER['PHP_SELF']) == 'search.php' ? 'active' : ''; ?>">
                <?php if(basename($_SERVER['PHP_SELF']) == 'search.php'): ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"> 
                        <path fill="#fff" fill-rule="evenodd" d="M21.788 21.788a.723.723 0 0 0 0-1.022L18.122 17.1a9.157 9.157 0 1 0-1.022 1.022l3.666 3.666a.723.723 0 0 0 1.022 0" clip-rule="evenodd" stroke-width="0.5" stroke="#fff" /> 
                    </svg>
                <?php else: ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                        <g fill="none" stroke="#fff" stroke-width="1.5">
                            <circle cx="11.5" cy="11.5" r="9.5" />
                            <path stroke-linecap="round" d="M18.5 18.5L22 22" />
                        </g>
                    </svg>
                <?php endif; ?>
                <span>Search</span>
            </a>
            <a href="spheres.php" class="nav-icon <?php echo basename($_SERVER['PHP_SELF']) == 'spheres.php' ? 'active' : ''; ?>">
                <?php if(basename($_SERVER['PHP_SELF']) == 'spheres.php'): ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                        <path fill="#fffcfc" fill-rule="evenodd" d="M2 12c0 5.523 4.477 10 10 10h9.25a.75.75 0 0 0 0-1.5h-3.98A9.99 9.99 0 0 0 22 12c0-5.523-4.477-10-10-10S2 6.477 2 12m10-3a1.5 1.5 0 1 0 0-3a1.5 1.5 0 0 0 0 3m0 9a1.5 1.5 0 1 0 0-3a1.5 1.5 0 0 0 0 3m-4.5-7.5a1.5 1.5 0 1 1 0 3a1.5 1.5 0 0 1 0-3M18 12a1.5 1.5 0 1 0-3 0a1.5 1.5 0 0 0 3 0" clip-rule="evenodd" stroke-width="0.5" stroke="#fffcfc" />
                    </svg>
                <?php else: ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                        <g fill="none" stroke="#fffcfc" stroke-width="1.5">
                            <circle cx="12" cy="12" r="10" />
                            <path stroke-linecap="round" d="M12 22h10" />
                            <path d="M13.5 7.5a1.5 1.5 0 1 1-3 0a1.5 1.5 0 0 1 3 0Zm0 9a1.5 1.5 0 1 1-3 0a1.5 1.5 0 0 1 3 0Zm-6-6a1.5 1.5 0 1 1 0 3a1.5 1.5 0 0 1 0-3Zm9 0a1.5 1.5 0 1 1 0 3a1.5 1.5 0 0 1 0-3Z" />
                        </g>
                    </svg>
                <?php endif; ?>
                <span>Spheres</span>
            </a>
            <a href="inbox.php" class="nav-icon <?php echo basename($_SERVER['PHP_SELF']) == 'inbox.php' ? 'active' : ''; ?>">
                <?php if(basename($_SERVER['PHP_SELF']) == 'inbox.php'): ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"> 
                        <path fill="#fff" fill-rule="evenodd" d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2S2 6.477 2 12c0 1.6.376 3.112 1.043 4.453c.178.356.237.763.134 1.148l-.595 2.226a1.3 1.3 0 0 0 1.591 1.592l2.226-.596a1.63 1.63 0 0 1 1.149.133A9.96 9.96 0 0 0 12 22m-4-8.75a.75.75 0 0 0 0 1.5h5.5a.75.75 0 0 0 0-1.5zm-.75-2.75A.75.75 0 0 1 8 9.75h8a.75.75 0 0 1 0 1.5H8a.75.75 0 0 1-.75-.75" clip-rule="evenodd" stroke-width="0.5" stroke="#fff" /> 
                    </svg>
                <?php else: ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                        <path fill="#fff" fill-rule="evenodd" d="M12 2.75A9.25 9.25 0 0 0 2.75 12c0 1.481.348 2.879.965 4.118c.248.498.343 1.092.187 1.677l-.596 2.225a.55.55 0 0 0 .673.674l2.227-.596a2.38 2.38 0 0 1 1.676.187A9.2 9.2 0 0 0 12 21.25a9.25 9.25 0 0 0 0-18.5M1.25 12C1.25 6.063 6.063 1.25 12 1.25S22.75 6.063 22.75 12S17.937 22.75 12 22.75c-1.718 0-3.344-.404-4.787-1.122a.9.9 0 0 0-.62-.08l-2.226.595c-1.524.408-2.918-.986-2.51-2.51l.596-2.226a.9.9 0 0 0-.08-.62A10.7 10.7 0 0 1 1.25 12m6-1.5A.75.75 0 0 1 8 9.75h8a.75.75 0 0 1 0 1.5H8a.75.75 0 0 1-.75-.75m0 3.5a.75.75 0 0 1 .75-.75h5.5a.75.75 0 0 1 0 1.5H8a.75.75 0 0 1-.75-.75" clip-rule="evenodd" stroke-width="0.5" stroke="#fff" />
                    </svg>
                <?php endif; ?>
                <span>Inbox</span>
                <?php if ($unread_count > 0): ?>
                <span class="badge bg-danger rounded-pill"><?= $unread_count ?></span>
                <?php endif; ?>
            </a>
            <a href="create.php" class="nav-icon <?php echo basename($_SERVER['PHP_SELF']) == 'create.php' ? 'active' : ''; ?>">
                <?php if(basename($_SERVER['PHP_SELF']) == 'create.php'): ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"> 
                        <path fill="#fff" fill-rule="evenodd" d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2S2 6.477 2 12s4.477 10 10 10m.75-13a.75.75 0 0 0-1.5 0v2.25H9a.75.75 0 0 0 0 1.5h2.25V15a.75.75 0 0 0 1.5 0v-2.25H15a.75.75 0 0 0 0-1.5h-2.25z" clip-rule="evenodd" stroke-width="0.5" stroke="#fff" /> 
                    </svg>
                <?php else: ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                        <g fill="none" stroke="#fff" stroke-width="1.5">
                            <circle cx="12" cy="12" r="10" />
                            <path stroke-linecap="round" d="M15 12h-3m0 0H9m3 0V9m0 3v3" />
                        </g>
                    </svg>
                <?php endif; ?>
                <span>Create</span>
            </a>
            <a type="button" class="nav-icon notifications-btn">
                <?php if(basename($_SERVER['PHP_SELF']) == 'notifications.php'): ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"> 
                        <path fill="#fff" d="M12 22c-4.714 0-7.071 0-8.536-1.465C2 19.072 2 16.714 2 12s0-7.071 1.464-8.536C4.93 2 7.286 2 12 2c1.399 0 2.59 0 3.612.038a4.5 4.5 0 0 0 6.35 6.35C22 9.41 22 10.601 22 12c0 4.714 0 7.071-1.465 8.535C19.072 22 16.714 22 12 22" stroke-width="0.5" stroke="#fff" /> 
                        <path fill="#fff" d="M22 5a3 3 0 1 1-6 0a3 3 0 0 1 6 0" stroke-width="0.5" stroke="#fff" /> 
                    </svg>
                <?php else: ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                        <path fill="#fff" d="M11.943 1.25c-2.309 0-4.118 0-5.53.19c-1.444.194-2.584.6-3.479 1.494c-.895.895-1.3 2.035-1.494 3.48c-.19 1.411-.19 3.22-.19 5.529v.114c0 2.309 0 4.118.19 5.53c.194 1.444.6 2.584 1.494 3.479c.895.895 2.035 1.3 3.48 1.494c1.411.19 3.22.19 5.529.19h.114c2.309 0 4.118 0 5.53-.19c1.444-.194 2.584-.6 3.479-1.494c.895-.895 1.3-2.035 1.494-3.48c.19-1.411.19-3.22.19-5.529V10.5a.75.75 0 0 0-1.5 0V12c0 2.378-.002 4.086-.176 5.386c-.172 1.279-.5 2.05-1.069 2.62c-.57.569-1.34.896-2.619 1.068c-1.3.174-3.008.176-5.386.176s-4.086-.002-5.386-.176c-1.279-.172-2.05-.5-2.62-1.069c-.569-.57-.896-1.34-1.068-2.619c-.174-1.3-.176-3.008-.176-5.386s.002-4.086.176-5.386c.172-1.279.5-2.05 1.069-2.62c.57-.569 1.34-.896 2.619-1.068c1.3-.174 3.008-.176 5.386-.176h1.5a.75.75 0 0 0 0-1.5z" stroke-width="0.5" stroke="#fff" />
                        <path fill="#fff" fill-rule="evenodd" d="M19 1.25a3.75 3.75 0 1 0 0 7.5a3.75 3.75 0 0 0 0-7.5M16.75 5a2.25 2.25 0 1 1 4.5 0a2.25 2.25 0 0 1-4.5 0" clip-rule="evenodd" stroke-width="0.5" stroke="#fff" />
                    </svg>
                <?php endif; ?>
                <span>Notifications</span>
                <?php if (isset($notification_count) && $notification_count > 0): ?>
                    <span class="badge bg-danger rounded-pill"><?= $notification_count ?></span>
                <?php endif; ?>
            </a>
            <a href="settings.php" class="nav-icon <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
                <?php if(basename($_SERVER['PHP_SELF']) == 'settings.php'): ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                        <path fill="#fffcfc" fill-rule="evenodd" d="M12.428 2c-1.114 0-2.129.6-4.157 1.802l-.686.406C5.555 5.41 4.542 6.011 3.985 7c-.557.99-.557 2.19-.557 4.594v.812c0 2.403 0 3.605.557 4.594s1.57 1.59 3.6 2.791l.686.407C10.299 21.399 11.314 22 12.428 22s2.128-.6 4.157-1.802l.686-.407c2.028-1.2 3.043-1.802 3.6-2.791c.557-.99.557-2.19.557-4.594v-.812c0-2.403 0-3.605-.557-4.594s-1.572-1.59-3.6-2.792l-.686-.406C14.555 2.601 13.542 2 12.428 2m-3.75 10a3.75 3.75 0 1 1 7.5 0a3.75 3.75 0 0 1-7.5 0" clip-rule="evenodd" stroke-width="0.5" stroke="#fffcfc" />
                    </svg>
                <?php else: ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                        <g fill="none" stroke="#fffcfc" stroke-width="2">
                            <path d="M7.843 3.802C9.872 2.601 10.886 2 12 2s2.128.6 4.157 1.802l.686.406c2.029 1.202 3.043 1.803 3.6 2.792c.557.99.557 2.19.557 4.594v.812c0 2.403 0 3.605-.557 4.594s-1.571 1.59-3.6 2.791l-.686.407C14.128 21.399 13.114 22 12 22s-2.128-.6-4.157-1.802l-.686-.407c-2.029-1.2-3.043-1.802-3.6-2.791C3 16.01 3 14.81 3 12.406v-.812C3 9.19 3 7.989 3.557 7s1.571-1.59 3.6-2.792z" opacity="1" />
                            <circle cx="12" cy="12" r="3" />
                        </g>
                    </svg>
                <?php endif; ?>
                <span>Settings</span>
            </a>
            <a href="profile.php" class="nav-icon <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
                <img src="<?= $profile_image ?>" alt="Profile" class="rounded-circle" style="width: 24px; height: 24px; object-fit: cover;">
                <span>Profile</span>
            </a>
            <a href="javascript:void(0);" onclick="confirmLogout();" class="nav-icon mt-auto">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                    <path fill="#fff" d="M12 3.25a.75.75 0 0 1 0 1.5a7.25 7.25 0 0 0 0 14.5a.75.75 0 0 1 0 1.5a8.75 8.75 0 1 1 0-17.5" stroke-width="0.5" stroke="#fff" />
                    <path fill="#fff" d="M16.47 9.53a.75.75 0 0 1 1.06-1.06l3 3a.75.75 0 0 1 0 1.06l-3 3a.75.75 0 1 1-1.06-1.06l1.72-1.72H10a.75.75 0 0 1 0-1.5h8.19z" stroke-width="0.5" stroke="#fff" />
                </svg>
                <span>Logout</span>
            </a>
            
            <script>
            function confirmLogout() {
                if (confirm("Are you sure to Logout?")) {
                    window.location.href = "includes/auth/logout.php";
                }
            }
            </script>
        </nav>
    </div>
</div>
<!-- Add this in the head section -->
<link rel="stylesheet" href="assets/css/notifications.css">
<!-- Add this after your sidebar content -->
<?php include 'includes/notifications/notifications_popup.php'; ?>
<!-- Add this before closing body tag -->
<script src="assets/js/notifications.js"></script>

<!-- Mobile Header -->
<div class="mobile-header d-flex justify-content-between align-items-center px-3 py-2 d-md-none">
    <a href="index.php" class="mobile-header-logo">
        <img src="assets/images/logo.jpeg" alt="Spheria" width="36" class="mobile-logo">
        <span class="logo-text" style="text-decoration: none;">Spheria</span>
    </a>
    <div class="message-notification-mobile">
        <a href="inbox.php" class="mobile-header-icon <?php echo basename($_SERVER['PHP_SELF']) == 'inbox.php' ? 'active' : ''; ?>">
            <?php if(basename($_SERVER['PHP_SELF']) == 'inbox.php'): ?>
                <svg xmlns="http://www.w3.org/2000/svg" width="28px" height="28px" viewBox="0 0 24 24"> 
                    <path fill="#fff" fill-rule="evenodd" d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2S2 6.477 2 12c0 1.6.376 3.112 1.043 4.453c.178.356.237.763.134 1.148l-.595 2.226a1.3 1.3 0 0 0 1.591 1.592l2.226-.596a1.63 1.63 0 0 1 1.149.133A9.96 9.96 0 0 0 12 22m-4-8.75a.75.75 0 0 0 0 1.5h5.5a.75.75 0 0 0 0-1.5zm-.75-2.75A.75.75 0 0 1 8 9.75h8a.75.75 0 0 1 0 1.5H8a.75.75 0 0 1-.75-.75" clip-rule="evenodd" stroke-width="0.5" stroke="#fff" /> 
                </svg>
            <?php else: ?>
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24">
                    <path fill="#fff" fill-rule="evenodd" d="M12 2.75A9.25 9.25 0 0 0 2.75 12c0 1.481.348 2.879.965 4.118c.248.498.343 1.092.187 1.677l-.596 2.225a.55.55 0 0 0 .673.674l2.227-.596a2.38 2.38 0 0 1 1.676.187A9.2 9.2 0 0 0 12 21.25a9.25 9.25 0 0 0 0-18.5M1.25 12C1.25 6.063 6.063 1.25 12 1.25S22.75 6.063 22.75 12S17.937 22.75 12 22.75c-1.718 0-3.344-.404-4.787-1.122a.9.9 0 0 0-.62-.08l-2.226.595c-1.524.408-2.918-.986-2.51-2.51l.596-2.226a.9.9 0 0 0-.08-.62A10.7 10.7 0 0 1 1.25 12m6-1.5A.75.75 0 0 1 8 9.75h8a.75.75 0 0 1 0 1.5H8a.75.75 0 0 1-.75-.75m0 3.5a.75.75 0 0 1 .75-.75h5.5a.75.75 0 0 1 0 1.5H8a.75.75 0 0 1-.75-.75" clip-rule="evenodd" stroke-width="0.5" stroke="#fff" />
                </svg>
            <?php endif; ?>
        </a>
        <a type="button" class="mobile-header-icon nav-icon notifications-btn <?php echo basename($_SERVER['PHP_SELF']) == 'notifications.php' ? 'active' : ''; ?>">
            <?php if(basename($_SERVER['PHP_SELF']) == 'notifications.php'): ?>
                <svg xmlns="http://www.w3.org/2000/svg" width="28px" height="28px" viewBox="0 0 24 24"> 
                    <path fill="#fff" d="M12 22c-4.714 0-7.071 0-8.536-1.465C2 19.072 2 16.714 2 12s0-7.071 1.464-8.536C4.93 2 7.286 2 12 2c1.399 0 2.59 0 3.612.038a4.5 4.5 0 0 0 6.35 6.35C22 9.41 22 10.601 22 12c0 4.714 0 7.071-1.465 8.535C19.072 22 16.714 22 12 22" stroke-width="0.5" stroke="#fff" /> 
                    <path fill="#fff" d="M22 5a3 3 0 1 1-6 0a3 3 0 0 1 6 0" stroke-width="0.5" stroke="#fff" /> 
                </svg>
            <?php else: ?>
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24">
                    <path fill="#fff" d="M11.943 1.25c-2.309 0-4.118 0-5.53.19c-1.444.194-2.584.6-3.479 1.494c-.895.895-1.3 2.035-1.494 3.48c-.19 1.411-.19 3.22-.19 5.529v.114c0 2.309 0 4.118.19 5.53c.194 1.444.6 2.584 1.494 3.479c.895.895 2.035 1.3 3.48 1.494c1.411.19 3.22.19 5.529.19h.114c2.309 0 4.118 0 5.53-.19c1.444-.194 2.584-.6 3.479-1.494c.895-.895 1.3-2.035 1.494-3.48c.19-1.411.19-3.22.19-5.529V10.5a.75.75 0 0 0-1.5 0V12c0 2.378-.002 4.086-.176 5.386c-.172 1.279-.5 2.05-1.069 2.62c-.57.569-1.34.896-2.619 1.068c-1.3.174-3.008.176-5.386.176s-4.086-.002-5.386-.176c-1.279-.172-2.05-.5-2.62-1.069c-.569-.57-.896-1.34-1.068-2.619c-.174-1.3-.176-3.008-.176-5.386s.002-4.086.176-5.386c.172-1.279.5-2.05 1.069-2.62c.57-.569 1.34-.896 2.619-1.068c1.3-.174 3.008-.176 5.386-.176h1.5a.75.75 0 0 0 0-1.5z" stroke-width="0.5" stroke="#fff" />
                    <path fill="#fff" fill-rule="evenodd" d="M19 1.25a3.75 3.75 0 1 0 0 7.5a3.75 3.75 0 0 0 0-7.5M16.75 5a2.25 2.25 0 1 1 4.5 0a2.25 2.25 0 0 1-4.5 0" clip-rule="evenodd" stroke-width="0.5" stroke="#fff" />
                </svg>
            <?php endif; ?>
            <span>Notifications</span>
            <?php
            // Add notification count if you have one
            if (isset($notification_count) && $notification_count > 0): ?>
                <span class="badge bg-danger rounded-pill"><?= $notification_count ?></span>
            <?php endif; ?>
        </a>
    </div>
</div>
<!-- End Mobile Header -->

<!-- Mobile Footer Navigation -->
<nav class="mobile-footer-nav d-flex justify-content-around align-items-center d-md-none">
    <a href="index.php" class="nav-icon <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
        <?php if(basename($_SERVER['PHP_SELF']) == 'index.php'): ?>
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24"> 
                <path fill="#fff" fill-rule="evenodd" d="M2.335 7.875c-.54 1.127-.35 2.446.03 5.083l.278 1.937c.487 3.388.731 5.081 1.906 6.093S7.447 22 10.894 22h2.212c3.447 0 5.17 0 6.345-1.012s1.419-2.705 1.906-6.093l.279-1.937c.38-2.637.57-3.956.029-5.083s-1.691-1.813-3.992-3.183l-1.385-.825C14.2 2.622 13.154 2 12 2s-2.199.622-4.288 1.867l-1.385.825c-2.3 1.37-3.451 2.056-3.992 3.183M12 18.75a.75.75 0 0 1-.75-.75v-3a.75.75 0 0 1 1.5 0v3a.75.75 0 0 1-.75.75" clip-rule="evenodd" stroke-width="0.5" stroke="#fff" /> 
            </svg>
        <?php else: ?>
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24">
                <path fill="#fff" d="M11.25 18a.75.75 0 0 0 1.5 0v-3a.75.75 0 0 0-1.5 0z" stroke-width="0.5" stroke="#fff" />
                <path fill="#fff" fill-rule="evenodd" d="M12 1.25c-.725 0-1.387.2-2.11.537c-.702.327-1.512.81-2.528 1.415l-1.456.867c-1.119.667-2.01 1.198-2.686 1.706C2.523 6.3 2 6.84 1.66 7.551c-.342.711-.434 1.456-.405 2.325c.029.841.176 1.864.36 3.146l.293 2.032c.237 1.65.426 2.959.707 3.978c.29 1.05.702 1.885 1.445 2.524c.742.64 1.63.925 2.716 1.062c1.056.132 2.387.132 4.066.132h2.316c1.68 0 3.01 0 4.066-.132c1.086-.137 1.974-.422 2.716-1.061c.743-.64 1.155-1.474 1.445-2.525c.281-1.02.47-2.328.707-3.978l.292-2.032c.185-1.282.332-2.305.36-3.146c.03-.87-.062-1.614-.403-2.325S21.477 6.3 20.78 5.775c-.675-.508-1.567-1.039-2.686-1.706l-1.456-.867c-1.016-.605-1.826-1.088-2.527-1.415c-.724-.338-1.386-.537-2.111-.537M8.096 4.511c1.057-.63 1.803-1.073 2.428-1.365c.609-.284 1.047-.396 1.476-.396s.867.112 1.476.396c.625.292 1.37.735 2.428 1.365l1.385.825c1.165.694 1.986 1.184 2.59 1.638c.587.443.91.809 1.11 1.225c.199.416.282.894.257 1.626c-.026.75-.16 1.691-.352 3.026l-.28 1.937c-.246 1.714-.422 2.928-.675 3.845c-.247.896-.545 1.415-.977 1.787c-.433.373-.994.593-1.925.71c-.951.119-2.188.12-3.93.12h-2.213c-1.743 0-2.98-.001-3.931-.12c-.93-.117-1.492-.337-1.925-.71c-.432-.372-.73-.891-.977-1.787c-.253-.917-.43-2.131-.676-3.845l-.279-1.937c-.192-1.335-.326-2.277-.352-3.026c-.025-.732.058-1.21.258-1.626s.521-.782 1.11-1.225c.603-.454 1.424-.944 2.589-1.638z" clip-rule="evenodd" stroke-width="0.5" stroke="#fff" />
            </svg>
        <?php endif; ?>
        <span>Home</span>
    </a>
    <a href="search.php" class="nav-icon <?php echo basename($_SERVER['PHP_SELF']) == 'search.php' ? 'active' : ''; ?>">
        <?php if(basename($_SERVER['PHP_SELF']) == 'search.php'): ?>
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24"> 
                <path fill="#fff" fill-rule="evenodd" d="M21.788 21.788a.723.723 0 0 0 0-1.022L18.122 17.1a9.157 9.157 0 1 0-1.022 1.022l3.666 3.666a.723.723 0 0 0 1.022 0" clip-rule="evenodd" stroke-width="0.5" stroke="#fff" /> 
            </svg>
        <?php else: ?>
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24">
                <g fill="none" stroke="#fff" stroke-width="1.5">
                    <circle cx="11.5" cy="11.5" r="9.5" />
                    <path stroke-linecap="round" d="M18.5 18.5L22 22" />
                </g>
            </svg>
        <?php endif; ?>
        <span>Search</span>
    </a>
    <a href="spheres.php" class="nav-icon <?php echo basename($_SERVER['PHP_SELF']) == 'spheres.php' ? 'active' : ''; ?>">
        <?php if(basename($_SERVER['PHP_SELF']) == 'spheres.php'): ?>
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24"> 
                <path fill="#fff" d="M12 2c1.845 0 3.33 0 4.54.088L13.1 7.25H8.4L11.9 2zM3.464 3.464c1.253-1.252 3.158-1.433 6.632-1.46L6.599 7.25H2.104c.147-1.764.503-2.928 1.36-3.786" stroke-width="0.5" stroke="#fff" /> 
                <path fill="#fff" fill-rule="evenodd" d="M2 12c0-1.237 0-2.311.026-3.25h19.948C22 9.689 22 10.763 22 12c0 4.714 0 7.071-1.465 8.535C19.072 22 16.714 22 12 22s-7.071 0-8.536-1.465C2 19.072 2 16.714 2 12m11.014.585C14.338 13.44 15 13.867 15 14.5s-.662 1.06-1.986 1.915c-1.342.866-2.013 1.299-2.514.98c-.5-.317-.5-1.176-.5-2.895s0-2.578.5-2.896s1.172.115 2.514.981" clip-rule="evenodd" stroke-width="0.5" stroke="#fff" /> 
                <path fill="#fff" d="M21.896 7.25c-.147-1.764-.503-2.928-1.36-3.786c-.598-.597-1.344-.95-2.337-1.16L14.9 7.25z" stroke-width="0.5" stroke="#fff" /> 
            </svg>
        <?php else: ?>
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24">
                <g fill="none" stroke="#fff" stroke-width="1.5">
                    <path d="M2 12c0-4.714 0-7.071 1.464-8.536C4.93 2 7.286 2 12 2s7.071 0 8.535 1.464C22 4.93 22 7.286 22 12s0 7.071-1.465 8.535C19.072 22 16.714 22 12 22s-7.071 0-8.536-1.465C2 19.072 2 16.714 2 12Z" />
                    <path stroke-linecap="round" d="M21.5 8h-19m8-5.5L7 8m10-5.5L13.5 8m1.5 6.5c0-.633-.662-1.06-1.986-1.915c-1.342-.866-2.013-1.299-2.514-.98c-.5.317-.5 1.176-.5 2.895s0 2.578.5 2.896s1.172-.115 2.514-.981C14.338 15.56 15 15.133 15 14.5Z" />
                </g>
            </svg>
        <?php endif; ?>
        <span>Spheres</span>
    </a>
    <a href="create.php" class="nav-icon <?php echo basename($_SERVER['PHP_SELF']) == 'create.php' ? 'active' : ''; ?>">
        <?php if(basename($_SERVER['PHP_SELF']) == 'create.php'): ?>
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24"> 
                <path fill="#fff" fill-rule="evenodd" d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2S2 6.477 2 12s4.477 10 10 10m.75-13a.75.75 0 0 0-1.5 0v2.25H9a.75.75 0 0 0 0 1.5h2.25V15a.75.75 0 0 0 1.5 0v-2.25H15a.75.75 0 0 0 0-1.5h-2.25z" clip-rule="evenodd" stroke-width="0.5" stroke="#fff" /> 
            </svg>
        <?php else: ?>
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24">
                <g fill="none" stroke="#fff" stroke-width="1.5">
                    <circle cx="12" cy="12" r="10" />
                    <path stroke-linecap="round" d="M15 12h-3m0 0H9m3 0V9m0 3v3" />
                </g>
            </svg>
        <?php endif; ?>
        <span>Create</span>
    </a>
    <a href="profile.php" class="nav-icon <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
        <img src="<?= $profile_image ?>" alt="Profile" class="rounded-circle" style="width: 28px; height: 28px; object-fit: cover;">
        <span>Profile</span>
    </a>
    <a href="settings.php" class="nav-icon <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
        <?php if(basename($_SERVER['PHP_SELF']) == 'settings.php'): ?>
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24">
                <path fill="#fffcfc" fill-rule="evenodd" d="M12.428 2c-1.114 0-2.129.6-4.157 1.802l-.686.406C5.555 5.41 4.542 6.011 3.985 7c-.557.99-.557 2.19-.557 4.594v.812c0 2.403 0 3.605.557 4.594s1.57 1.59 3.6 2.791l.686.407C10.299 21.399 11.314 22 12.428 22s2.128-.6 4.157-1.802l.686-.407c2.028-1.2 3.043-1.802 3.6-2.791c.557-.99.557-2.19.557-4.594v-.812c0-2.403 0-3.605-.557-4.594s-1.572-1.59-3.6-2.792l-.686-.406C14.555 2.601 13.542 2 12.428 2m-3.75 10a3.75 3.75 0 1 1 7.5 0a3.75 3.75 0 0 1-7.5 0" clip-rule="evenodd" stroke-width="0.5" stroke="#fffcfc" />
            </svg>
        <?php else: ?>
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24">
                <g fill="none" stroke="#fffcfc" stroke-width="2">
                    <path d="M7.843 3.802C9.872 2.601 10.886 2 12 2s2.128.6 4.157 1.802l.686.406c2.029 1.202 3.043 1.803 3.6 2.792c.557.99.557 2.19.557 4.594v.812c0 2.403 0 3.605-.557 4.594s-1.571 1.59-3.6 2.791l-.686.407C14.128 21.399 13.114 22 12 22s-2.128-.6-4.157-1.802l-.686-.407c-2.029-1.2-3.043-1.802-3.6-2.791C3 16.01 3 14.81 3 12.406v-.812C3 9.19 3 7.989 3.557 7s1.571-1.59 3.6-2.792z" opacity="1" />
                    <circle cx="12" cy="12" r="3" />
                </g>
            </svg>
        <?php endif; ?>
        <span>Settings</span>
    </a>
</nav>
<!-- End Mobile Footer Navigation -->