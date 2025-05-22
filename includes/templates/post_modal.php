<div class="post-modal">
    <div class="post-container">
        <div class="post-media">
            <?php if (isset($media) && $media): ?>
                <?php if ($media['media_type'] == 'image'): ?>
                    <img src="<?= htmlspecialchars($media['file_path']) ?>" alt="Post">
                <?php elseif ($media['media_type'] == 'video'): ?>
                    <video controls>
                        <source src="<?= htmlspecialchars($media['file_path']) ?>" type="<?= $media['mime_type'] ?>">
                    </video>
                <?php endif; ?>
            <?php else: ?>
                <div class="no-media-message">
                    <p>No media available for this post</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="post-details">
            <div class="post-header">
                <div class="user-info">
                    <img src="<?= !empty($post['profile_image']) ? htmlspecialchars($post['profile_image']) : 'assets/images/default-avatar.png' ?>" alt="Profile">
                    <a href="profile.php?username=<?= htmlspecialchars($post['username']) ?>"><?= htmlspecialchars($post['username']) ?></a>
                </div>
                <button class="btn-close btn-close-white"></button>
            </div>

            <div class="post-content">
                <div class="post-caption">
                    <div class="user-info">
                        <img src="<?= !empty($post['profile_image']) ? htmlspecialchars($post['profile_image']) : 'assets/images/default-avatar.png' ?>" alt="Profile">
                        <div>
                            <a href="profile.php?username=<?= htmlspecialchars($post['username']) ?>"><?= htmlspecialchars($post['username']) ?></a>
                            <p><?= !empty($post['content']) ? htmlspecialchars($post['content']) : '' ?></p>
                        </div>
                    </div>
                </div>

                <div class="comments-section">
                    <?php if (count($comments) > 0): ?>
                        <?php foreach ($comments as $comment): ?>
                            <div class="comment">
                                <div class="user-info">
                                    <img src="<?= !empty($comment['profile_image']) ? htmlspecialchars($comment['profile_image']) : 'assets/images/default-avatar.png' ?>" alt="Profile">
                                    <div>
                                        <a href="profile.php?username=<?= htmlspecialchars($comment['username']) ?>"><?= htmlspecialchars($comment['username']) ?></a>
                                        <p><?= htmlspecialchars($comment['comment_text']) ?></p>
                                        <small class="text-muted"><?= date('M d, Y', strtotime($comment['created_at'])) ?></small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-comments">
                            <p class="text-center text-muted">No comments yet. Be the first to comment!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="post-actions">
                <div class="action-buttons-container">
                    <div class="action-buttons">
                        <button class="like-btn <?= $post['user_has_liked'] ? 'liked' : '' ?>" data-post-id="<?= $post_id ?>">
                            <i class="<?= $post['user_has_liked'] ? 'fas' : 'far' ?> fa-heart"></i>
                        </button>
                        <button class="comment-btn">
                            <i class="far fa-comment"></i>
                        </button>
                        <button class="share-btn" data-post-id="<?= $post_id ?>">
                            <i class="far fa-share-square"></i>
                        </button>
                        <button class="save-btn <?= $user_has_saved ? 'saved' : '' ?>" data-post-id="<?= $post_id ?>">
                            <i class="<?= $user_has_saved ? 'fas' : 'far' ?> fa-bookmark"></i>
                        </button>
                    </div>
                </div>
                <div class="post-stats">
                    <div class="likes-count">
                        <strong><?= $post['likes_count'] ?></strong> likes
                    </div>
                    <div class="post-date">
                        <?= date('F j, Y', strtotime($post['created_at'])) ?>
                    </div>
                </div>
                <form class="comment-form" id="comment-form">
                    <input type="hidden" name="post_id" value="<?= $post_id ?>">
                    <input type="text" name="comment_text" placeholder="Add a comment..." required>
                    <button type="submit">Post</button>
                </form>
            </div>
        </div>
    </div>
</div>