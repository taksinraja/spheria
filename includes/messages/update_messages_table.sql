-- Add encryption columns to messages table
ALTER TABLE messages 
ADD COLUMN IF NOT EXISTS encrypted_content TEXT NULL AFTER content,
ADD COLUMN IF NOT EXISTS iv VARCHAR(24) NULL AFTER encrypted_content,
ADD COLUMN IF NOT EXISTS tag VARCHAR(24) NULL AFTER iv;

-- Create conversation_keys table
CREATE TABLE IF NOT EXISTS conversation_keys (
    conversation_id INT(11) NOT NULL,
    encryption_key TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (conversation_id),
    FOREIGN KEY (conversation_id) REFERENCES conversations(conversation_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; 