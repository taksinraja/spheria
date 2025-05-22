<?php
session_start();
require_once '../config.php';
require_once '../db.php';
require_once '../auth/auth_check.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$post_id = $_POST['post_id'] ?? null;
$user_id = $_SESSION['user_id'];

try {
    // Start transaction
    $db->begin_transaction();

    // Verify post ownership
    $check_sql = "SELECT user_id FROM posts WHERE post_id = ?";
    $check_stmt = $db->prepare($check_sql);
    $check_stmt->bind_param("i", $post_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $post = $result->fetch_assoc();

    if (!$post || $post['user_id'] != $user_id) {
        throw new Exception("Unauthorized access");
    }

    // Get media files to delete
    $media_sql = "SELECT file_path FROM post_media WHERE post_id = ?";
    $media_stmt = $db->prepare($media_sql);
    $media_stmt->bind_param("i", $post_id);
    $media_stmt->execute();
    $media_result = $media_stmt->get_result();

    // Delete associated files
    while ($media = $media_result->fetch_assoc()) {
        $file_path = $_SERVER['DOCUMENT_ROOT'] . '/spheria1/' . $media['file_path'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }

    // Delete from database tables
    $tables = ['post_media', 'post_tags', 'likes', 'comments', 'saved_posts', 'posts'];
    
    foreach ($tables as $table) {
        $delete_sql = "DELETE FROM $table WHERE post_id = ?";
        $delete_stmt = $db->prepare($delete_sql);
        $delete_stmt->bind_param("i", $post_id);
        $delete_stmt->execute();
    }

    $db->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    if (isset($db)) {
        $db->rollback();
    }
    error_log("Error deleting post: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>