<!-- Replace the views button with save button in your post template -->
<div class="action-buttons">
    <button class="like-btn <?= $post['user_has_liked'] ? 'liked' : '' ?>" data-post-id="<?= $post['post_id'] ?>">
        <i class="<?= $post['user_has_liked'] ? 'fas' : 'far' ?> fa-heart"></i>
    </button>
    <button class="comment-btn" data-post-id="<?= $post['post_id'] ?>">
        <i class="far fa-comment"></i>
    </button>
    <button class="share-btn" data-post-id="<?= $post['post_id'] ?>">
        <i class="far fa-share-square"></i>
    </button>
    <button class="save-btn <?= $post['user_has_saved'] ? 'saved' : '' ?>" data-post-id="<?= $post['post_id'] ?>">
        <i class="<?= $post['user_has_saved'] ? 'fas' : 'far' ?> fa-bookmark"></i>
    </button>
</div>