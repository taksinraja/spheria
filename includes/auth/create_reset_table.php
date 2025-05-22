<?php
// Use absolute path to ensure config and db files are found
require_once '/Applications/XAMPP/xamppfiles/htdocs/spheria1/includes/config.php';
require_once '/Applications/XAMPP/xamppfiles/htdocs/spheria1/includes/db.php';

try {
    // Create password_reset table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS password_reset (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        token VARCHAR(64) NOT NULL,
        expires_at DATETIME NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
        INDEX (token)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($db->query($sql)) {
        echo "Password reset table created successfully!";
    } else {
        echo "Error creating password reset table: " . $db->error;
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>