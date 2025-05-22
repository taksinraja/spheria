<?php
require_once '/Applications/XAMPP/xamppfiles/htdocs/spheria1/includes/config.php';
require_once '/Applications/XAMPP/xamppfiles/htdocs/spheria1/includes/db.php';

try {
    // Create search_history table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS search_history (
        search_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        searched_user_id INT NOT NULL,
        search_date DATETIME NOT NULL,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
        FOREIGN KEY (searched_user_id) REFERENCES users(user_id) ON DELETE CASCADE,
        INDEX (user_id),
        INDEX (searched_user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($db->query($sql)) {
        echo "Search history table created successfully!";
    } else {
        echo "Error creating search history table: " . $db->error;
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>