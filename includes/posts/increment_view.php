<?php
session_start();
require_once '../config.php';
require_once '../db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['post_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$post_id = $_POST['post_id'];

try {
    // Check if views table exists, if not create it
    $check_table = "SHOW TABLES LIKE 'post_views'";
    $table_exists = $db->query($check_table)->num_rows > 0;
    
    if (!$table_exists) {
        $create_table = "CREATE TABLE post_views (
            view_id INT AUTO_INCREMENT PRIMARY KEY,
            post_id INT NOT NULL,
            user_id INT,
            ip_address VARCHAR(45),
            viewed_at DATETIME NOT NULL,
            FOREIGN KEY (post_id) REFERENCES posts(post_id) ON DELETE CASCADE
        )";
        $db->query($create_table);
    }
    
    // Record the view
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $ip_address = $_SERVER['REMOTE_ADDR'];
    
    $sql = "INSERT INTO post_views (post_id, user_id, ip_address, viewed_at) VALUES (?, ?, ?, NOW())";
    $stmt = $db->prepare($sql);
    
    if ($user_id) {
        $stmt->bind_param("iis", $post_id, $user_id, $ip_address);
    } else {
        $null_id = null;
        $stmt->bind_param("iis", $post_id, $null_id, $ip_address);
    }
    
    $stmt->execute();
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    error_log("Error incrementing view: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}