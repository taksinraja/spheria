<?php
require_once __DIR__ . '/../encryption/MessageEncryption.php';

/**
 * Renders a message based on its content type
 * Supports text messages and shared posts
 * 
 * @param string $content The message content
 * @param string $encrypted_content The encrypted message content (optional)
 * @param string $iv The initialization vector for decryption (optional)
 * @param string $tag The authentication tag for decryption (optional)
 * @return string The rendered HTML for the message
 */
function render_message($content, $encrypted_content = null, $iv = null, $tag = null) {
    global $db;
    
    // If message is encrypted, decrypt it first
    if ($encrypted_content && $iv && $tag) {
        try {
            // Get conversation key
            $get_key = $db->prepare("
                SELECT ck.encryption_key 
                FROM conversation_keys ck
                JOIN messages m ON m.conversation_id = ck.conversation_id
                WHERE m.encrypted_content = ?
            ");
            $get_key->bind_param("s", $encrypted_content);
            $get_key->execute();
            $key_result = $get_key->get_result();
            
            if ($key_result->num_rows > 0) {
                $conversation_key = $key_result->fetch_assoc()['encryption_key'];
                
                // Decrypt the message
                $encryption = new MessageEncryption();
                $decrypted_content = $encryption->decrypt($encrypted_content, $conversation_key, $iv, $tag);
                
                // Use decrypted content
                $content = $decrypted_content;
            }
        } catch (Exception $e) {
            // If decryption fails, show original content
            return '<p>' . nl2br(htmlspecialchars($content)) . '</p>';
        }
    }
    
    // Check if content is JSON (for special message types)
    $decoded = json_decode($content, true);
    
    if (json_last_error() === JSON_ERROR_NONE && isset($decoded['type'])) {
        // Handle different message types
        switch ($decoded['type']) {
            case 'shared_post':
                return render_shared_post($decoded['post_id']);
            case 'story_reply':
                if (isset($decoded['story_id']) && isset($decoded['content'])) {
                    return render_story_reply($decoded['story_id'], $decoded['content']);
                }
                break;
            default:
                return htmlspecialchars($content);
        }
    }
    
    // Check for shared post format [shared_post:123]
    if (preg_match('/^\[shared_post:(\d+)\]$/', $content, $matches)) {
        return render_shared_post($matches[1]);
    }
    
    // Regular text message
    return '<p>' . nl2br(htmlspecialchars($content)) . '</p>';
}

/**
 * Renders a shared post in a message
 * 
 * @param int $post_id The ID of the shared post
 * @return string The rendered HTML for the shared post
 */
function render_shared_post($post_id) {
    global $db;
    
    // Get post data
    $post_query = $db->prepare("
        SELECT p.*, u.username, u.profile_image 
        FROM posts p
        JOIN users u ON p.user_id = u.user_id
        WHERE p.post_id = ?
    ");
    $post_query->bind_param("i", $post_id);
    $post_query->execute();
    $post = $post_query->get_result()->fetch_assoc();
    
    if (!$post) {
        return '<div class="shared-post-error">This post is no longer available</div>';
    }
    
    // Get first media item if exists
    $media_query = $db->prepare("
        SELECT * FROM post_media 
        WHERE post_id = ? 
        ORDER BY media_id ASC 
        LIMIT 1
    ");
    $media_query->bind_param("i", $post_id);
    $media_query->execute();
    $media = $media_query->get_result()->fetch_assoc();
    
    // Build shared post HTML
    $html = '<div class="shared-post">';
    $html .= '<div class="shared-post-header">';
    $html .= '<i class="fas fa-share"></i> <span>Shared post</span>';
    $html .= '</div>';
    $html .= '<div class="shared-post-content">';
    
    // Post author info
    $html .= '<div class="shared-post-author">';
    $html .= '<img src="' . (!empty($post['profile_image']) ? htmlspecialchars($post['profile_image']) : 'assets/images/default-avatar.png') . '" alt="Profile" class="rounded-circle">';
    $html .= '<span>' . htmlspecialchars($post['username']) . '</span>';
    $html .= '</div>';
    
    // Post text content
    if (!empty($post['content'])) {
        $html .= '<p class="shared-post-text">' . htmlspecialchars($post['content']) . '</p>';
    }
    
    // Post media (if exists)
    if ($media) {
        if ($media['media_type'] == 'image') {
            $html .= '<div class="shared-post-media">';
            $html .= '<img src="' . htmlspecialchars($media['file_path']) . '" alt="Post image">';
            $html .= '</div>';
        } elseif ($media['media_type'] == 'video') {
            $html .= '<div class="shared-post-media video-container">';
            $html .= '<video controls preload="metadata" poster="assets/images/video-placeholder.jpg">';
            $html .= '<source src="' . htmlspecialchars($media['file_path']) . '" type="video/mp4">';
            $html .= 'Your browser does not support the video tag.';
            $html .= '</video>';
            $html .= '</div>';
        }
    }
    
    // View post link - Fix to redirect to the user profile with the post
    $html .= '<a href="profile.php?username=' . htmlspecialchars($post['username']) . '&highlight=' . htmlspecialchars($post['post_id']) . '" class="view-post-link" target="_blank">View Post</a>';
    
    $html .= '</div>'; // End shared-post-content
    $html .= '</div>'; // End shared-post
    
    return $html;
}

/**
 * Renders a story reply message
 * @param int $story_id The ID of the story being replied to
 * @param string $content The reply content
 * @return string The rendered HTML
 */
function render_story_reply($story_id, $content) {
    global $db;
    
    // Get story information
    $stmt = $db->prepare("
        SELECT s.*, u.username, u.profile_image 
        FROM stories s
        JOIN users u ON s.user_id = u.user_id
        WHERE s.story_id = ?
    ");
    $stmt->bind_param("i", $story_id);
    $stmt->execute();
    $story = $stmt->get_result()->fetch_assoc();
    
    $html = '<div class="story-reply-message">';
    
    // Add story reference
    $html .= '<div class="story-reference">';
    $html .= '<i class="fas fa-reply"></i> ';
    $html .= '<span>Replying to ';
    if ($story) {
        $html .= htmlspecialchars($story['username']) . '\'s story';
    } else {
        $html .= 'a story';
    }
    $html .= '</span>';
    $html .= '</div>';
    
    // Add the actual reply content
    $html .= '<p class="reply-content">' . nl2br(htmlspecialchars($content)) . '</p>';
    
    $html .= '</div>';
    return $html;
}
?>