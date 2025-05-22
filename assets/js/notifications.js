document.addEventListener('DOMContentLoaded', function() {
    // const notificationBtn = document.querySelector('.notifications-btn');
    const notificationsPopup = document.getElementById('notificationsPopup');
    
    // notificationBtn?.addEventListener('click', function(e) {
    //     e.stopPropagation();
    //     notificationsPopup.style.display = notificationsPopup.style.display === 'block' ? 'none' : 'block';
    //     
    //     // Load notifications if popup is being opened
    //     if (notificationsPopup.style.display === 'block') {
    //         loadNotifications();
    //     }
    // });
    
    document.querySelectorAll('.notifications-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            notificationsPopup.style.display = notificationsPopup.style.display === 'block' ? 'none' : 'block';
            
            // Load notifications if popup is being opened
            if (notificationsPopup.style.display === 'block') {
                loadNotifications();
            }
        });
    });
    
    // Close popup when clicking outside
    document.addEventListener('click', function(e) {
        if (notificationsPopup && !notificationsPopup.contains(e.target) && !e.target.classList.contains('notifications-btn')) {
            notificationsPopup.style.display = 'none';
        }
    });
    
    // Handle tab switching
    const tabs = document.querySelectorAll('.notifications-tab');
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            tabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            loadNotifications(this.dataset.tab);
        });
    });
    
    // Handle follow button clicks
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('notification-action')) {
            const userId = e.target.dataset.userId;
            toggleFollow(userId, e.target);
        }
    });
    
    async function loadNotifications(tab = 'all') {
        try {
            const response = await fetch(`/spheria1/includes/notifications/get_notifications.php?tab=${tab}`);
            const data = await response.json();
            
            if (data.success) {
                document.querySelector('.notifications-content').innerHTML = data.html;
            }
        } catch (error) {
            console.error('Error loading notifications:', error);
        }
    }
    
    async function toggleFollow(userId, button) {
        try {
            const response = await fetch('/spheria1/includes/users/toggle_follow.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `user_id=${userId}`
            });
            
            const data = await response.json();
            if (data.success) {
                button.classList.toggle('following');
                button.textContent = data.following ? 'Following' : 'Follow';
            }
        } catch (error) {
            console.error('Error toggling follow:', error);
        }
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const closeNotificationsBtn = document.getElementById('closeNotifications');
    const notificationsPopup = document.getElementById('notificationsPopup');
    
    if (closeNotificationsBtn && notificationsPopup) {
        closeNotificationsBtn.addEventListener('click', function() {
            notificationsPopup.style.display = 'block';
        });
    }
});