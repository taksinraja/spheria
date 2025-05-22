<div class="right-sidebar">
    <!-- Suggestions Section -->
    <div class="suggestions-section">
        <h5 class="section-title">Based on your profile</h5>
        
        <div class="suggestions-list">
            <?php
            // Fetch user suggestions
            $suggestion_sql = "SELECT u.user_id, u.username, u.profile_image 
                              FROM users u 
                              WHERE u.user_id != ? 
                              ORDER BY RAND() 
                              LIMIT 4";
            $suggestion_stmt = $db->prepare($suggestion_sql);
            $suggestion_stmt->bind_param("i", $_SESSION['user_id']);
            $suggestion_stmt->execute();
            $suggestions = $suggestion_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            
            foreach ($suggestions as $suggestion):
                // Check if current user is already following this user
                $follow_check_sql = "SELECT * FROM followers WHERE follower_id = ? AND following_id = ?";
                $follow_check_stmt = $db->prepare($follow_check_sql);
                $follow_check_stmt->bind_param("ii", $_SESSION['user_id'], $suggestion['user_id']);
                $follow_check_stmt->execute();
                $is_following = $follow_check_stmt->get_result()->num_rows > 0;
                
                // Get a random follower of this suggested user
                $follower_sql = "SELECT u.username FROM followers f 
                                JOIN users u ON f.follower_id = u.user_id 
                                WHERE f.following_id = ? 
                                ORDER BY RAND() LIMIT 1";
                $follower_stmt = $db->prepare($follower_sql);
                $follower_stmt->bind_param("i", $suggestion['user_id']);
                $follower_stmt->execute();
                $follower_result = $follower_stmt->get_result();
                $follower = $follower_result->fetch_assoc();
            ?>
                <div class="suggestion-item">
                    <div class="suggestion-user">
                        <a href="profile.php?username=<?= htmlspecialchars($suggestion['username']) ?>" class="suggestion-avatar">
                            <img src="<?= !empty($suggestion['profile_image']) ? $suggestion['profile_image'] : 'assets/images/default-avatar.png' ?>" 
                                 alt="<?= htmlspecialchars($suggestion['username']) ?>" class="rounded-circle">
                        </a>
                        <div class="suggestion-info">
                            <a href="profile.php?username=<?= htmlspecialchars($suggestion['username']) ?>" class="suggestion-username">
                                <?= htmlspecialchars($suggestion['username']) ?>
                            </a>
                            <span class="suggestion-meta">
                                <?php if ($follower): ?>
                                    Followed by <?= htmlspecialchars($follower['username']) ?>
                                <?php else: ?>
                                    Suggested for you
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                    <button class="follow-btn <?= $is_following ? 'following' : '' ?>" data-user-id="<?= $suggestion['user_id'] ?>">
                        <?= $is_following ? 'Following' : 'Follow' ?>
                    </button>
                </div>
            <?php endforeach; ?>
        </div>
        
        <a href="search.php" class="view-more">View More <i class="fas fa-chevron-right"></i></a>
    </div>
    
    <!-- Footer Links -->
    <div class="sidebar-footer">
        <div class="footer-links">
            <a href="#">About</a>
            <a href="#">Help</a>
            <a href="#">Press</a>
            <a href="#">API</a>
            <a href="#">Jobs</a>
            <a href="#">Privacy</a>
            <a href="#">Terms</a>
            <a href="#">Locations</a>
        </div>
        <div class="copyright">
            © 2023 SPHERIA
        </div>
    </div>
</div>

<!-- Add JavaScript for follow button functionality -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get all follow buttons in the sidebar
    const followButtons = document.querySelectorAll('.follow-btn');
    
    // Add click event listener to each button
    followButtons.forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.getAttribute('data-user-id');
            const isFollowing = this.classList.contains('following');
            
            // Create FormData object for proper form submission
            const formData = new FormData();
            formData.append('user_id', userId);
            formData.append('action', isFollowing ? 'unfollow' : 'follow');
            
            // Send AJAX request to follow/unfollow
            fetch('includes/profile/follow.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Toggle button state
                    if (isFollowing) {
                        this.classList.remove('following');
                        this.textContent = 'Follow';
                    } else {
                        this.classList.add('following');
                        this.textContent = 'Following';
                    }
                } else {
                    console.error('Error:', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    });
});
</script>