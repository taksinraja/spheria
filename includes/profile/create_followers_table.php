<?php
require_once '../config.php';
require_once '../db.php';

try {
    // Check if the database connection is working
    if ($db->connect_error) {
        die("Connection failed: " . $db->connect_error);
    }
    
    // Create followers table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS followers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        follower_id INT NOT NULL,
        following_id INT NOT NULL,
        followed_at DATETIME NOT NULL,
        FOREIGN KEY (follower_id) REFERENCES users(user_id) ON DELETE CASCADE,
        FOREIGN KEY (following_id) REFERENCES users(user_id) ON DELETE CASCADE,
        UNIQUE KEY unique_follow (follower_id, following_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($db->query($sql)) {
        echo "Followers table created successfully!";
    } else {
        echo "Error creating followers table: " . $db->error;
    }
    
    // Check if the table was created and has the correct structure
    $check_table = $db->query("SHOW TABLES LIKE 'followers'");
    if ($check_table->num_rows > 0) {
        echo "<br>Followers table exists.";
        
        // Check table structure
        $structure = $db->query("DESCRIBE followers");
        echo "<br>Table structure:<br>";
        while ($row = $structure->fetch_assoc()) {
            echo $row['Field'] . " - " . $row['Type'] . "<br>";
        }
    } else {
        echo "<br>WARNING: Followers table was not created!";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>