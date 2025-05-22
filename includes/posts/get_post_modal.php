<?php
session_start();
require_once '../config.php';
require_once '../db.php';

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'No post ID provided']);
    exit;
}

$post_id = $_GET['id'];

try {
    // Get post details with user info
    $sql = "SELECT p.*, u.username, u.profile_image, u.full_name,
            COUNT(DISTINCT l.like_id) as likes_count,
            COUNT(DISTINCT c.comment_id) as comments_count,
            EXISTS(SELECT 1 FROM likes WHERE post_id = p.post_id AND user_id = ?) as user_has_liked
            FROM posts p
            JOIN users u ON p.user_id = u.user_id
            LEFT JOIN likes l ON p.post_id = l.post_id
            LEFT JOIN comments c ON p.post_id = c.post_id
            WHERE p.post_id = ?
            GROUP BY p.post_id";
    
    $stmt = $db->prepare($sql);
    $stmt->bind_param("ii", $_SESSION['user_id'], $post_id);
    $stmt->execute();
    $post = $stmt->get_result()->fetch_assoc();

    // Get post media (removed ORDER BY media_order)
    $media_sql = "SELECT * FROM post_media WHERE post_id = ?";
    $media_stmt = $db->prepare($media_sql);
    $media_stmt->bind_param("i", $post_id);
    $media_stmt->execute();
    $media = $media_stmt->get_result()->fetch_assoc();

    // Get comments with user info
    $comments_sql = "SELECT c.*, u.username, u.profile_image 
                    FROM comments c
                    JOIN users u ON c.user_id = u.user_id
                    WHERE c.post_id = ?
                    ORDER BY c.created_at DESC";
    $comments_stmt = $db->prepare($comments_sql);
    $comments_stmt->bind_param("i", $post_id);
    $comments_stmt->execute();
    $comments = $comments_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Include the post modal template
    include '../templates/post_modal.php';

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
<!-- Inside your post modal template -->
<button class="save-post-btn <?php echo $is_saved ? 'saved' : ''; ?>" 
        data-post-id="<?php echo $post['post_id']; ?>">
    <i class="<?php echo $is_saved ? 'fas' : 'far'; ?> fa-bookmark"></i>
</button>