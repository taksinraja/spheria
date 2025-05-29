<?php
require_once '../config.php';
require_once '../db.php';

try {
    // Create user_encryption_keys table
    $db->query("
        CREATE TABLE IF NOT EXISTS user_encryption_keys (
            user_id INT(11) NOT NULL,
            public_key TEXT NOT NULL,
            private_key TEXT NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (user_id),
            FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // Create conversation_keys table
    $db->query("
        CREATE TABLE IF NOT EXISTS conversation_keys (
            conversation_id INT(11) NOT NULL,
            encryption_key TEXT NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (conversation_id),
            FOREIGN KEY (conversation_id) REFERENCES conversations(conversation_id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // Modify messages table to store encrypted content
    $db->query("
        ALTER TABLE messages 
        ADD COLUMN encrypted_content TEXT NULL AFTER content,
        ADD COLUMN iv VARCHAR(24) NULL AFTER encrypted_content,
        ADD COLUMN tag VARCHAR(24) NULL AFTER iv;
    ");

    echo "Encryption tables created successfully!";
} catch (Exception $e) {
    echo "Error creating encryption tables: " . $e->getMessage();
}