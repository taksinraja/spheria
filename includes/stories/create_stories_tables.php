<?php
require_once '../config.php';
require_once '../db.php';

// Create stories table
$sql = "CREATE TABLE IF NOT EXISTS stories (
    story_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    content TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
)";

if (!$db->query($sql)) {
    die("Error creating stories table: " . $db->error);
}

// Create story_media table
$sql = "CREATE TABLE IF NOT EXISTS story_media (
    media_id INT PRIMARY KEY AUTO_INCREMENT,
    story_id INT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    media_type ENUM('image', 'video') NOT NULL,
    file_size INT NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (story_id) REFERENCES stories(story_id) ON DELETE CASCADE
)";

if (!$db->query($sql)) {
    die("Error creating story_media table: " . $db->error);
}

echo "Stories tables created successfully.";