<?php
require_once '../config.php';
require_once '../db.php';

try {
    // Create conversations table
    $db->query("
        CREATE TABLE IF NOT EXISTS conversations (
            conversation_id INT(11) NOT NULL AUTO_INCREMENT,
            user1_id INT(11) NOT NULL,
            user2_id INT(11) NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (conversation_id),
            KEY user1_id (user1_id),
            KEY user2_id (user2_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // Create conversation_participants table
    $db->query("
        CREATE TABLE IF NOT EXISTS conversation_participants (
            id INT(11) NOT NULL AUTO_INCREMENT,
            conversation_id INT(11) NOT NULL,
            user_id INT(11) NOT NULL,
            PRIMARY KEY (id),
            KEY conversation_id (conversation_id),
            KEY user_id (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // Create messages table
    $db->query("
        CREATE TABLE IF NOT EXISTS messages (
            message_id INT(11) NOT NULL AUTO_INCREMENT,
            conversation_id INT(11) NOT NULL,
            sender_id INT(11) NOT NULL,
            content TEXT NOT NULL,
            is_read TINYINT(1) NOT NULL DEFAULT '0',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (message_id),
            KEY conversation_id (conversation_id),
            KEY sender_id (sender_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    echo "Message tables created successfully!";
} catch (Exception $e) {
    echo "Error creating tables: " . $e->getMessage();
}