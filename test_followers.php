<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "Please log in first";
    exit;
}

$user_id = $_SESSION['user_id'];

// Create followers table if it doesn't exist with correct column names
$db->query("CREATE TABLE IF NOT EXISTS followers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    following_id INT NOT NULL,
    follower_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_follow (following_id, follower_id)
)");

// Display top users
echo "<h2>Top Users</h2>";

try {
    // Get users
    $sql = "SELECT user_id, username, full_name, profile_image 
            FROM users 
            WHERE user_id != ? 
            ORDER BY created_at DESC 
            LIMIT 10";
    
    $stmt = $db->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo "<ul style='list-style: none; padding: 0;'>";
        while ($row = $result->fetch_assoc()) {
            $profile_image = !empty($row['profile_image']) ? $row['profile_image'] : 'assets/images/default-avatar.png';
            $full_name = !empty($row['full_name']) ? $row['full_name'] : '';
            $other_user_id = $row['user_id'];
            
            // Check if already following - using following_id instead of user_id
            $follow_check = $db->prepare("SELECT id FROM followers WHERE following_id = ? AND follower_id = ?");
            $follow_check->bind_param("ii", $user_id, $other_user_id);
            $follow_check->execute();
            $is_following = $follow_check->get_result()->num_rows > 0;
            
            echo "<li style='margin-bottom: 15px; display: flex; align-items: center;'>";
            echo "<img src='{$profile_image}' style='width: 50px; height: 50px; border-radius: 50%; margin-right: 10px;'>";
            echo "<div style='flex-grow: 1;'>";
            echo "<div><strong>{$row['username']}</strong></div>";
            echo "<div>{$full_name}</div>";
            echo "</div>";
            
            // Add follow/unfollow button
            if ($is_following) {
                echo "<form method='post' style='margin-left: 10px;'>";
                echo "<input type='hidden' name='unfollow_id' value='{$other_user_id}'>";
                echo "<button type='submit' style='background-color: #333; color: white; border: none; padding: 5px 10px; border-radius: 5px;'>Unfollow</button>";
                echo "</form>";
            } else {
                echo "<form method='post' style='margin-left: 10px;'>";
                echo "<input type='hidden' name='follow_id' value='{$other_user_id}'>";
                echo "<button type='submit' style='background-color: #a970ff; color: white; border: none; padding: 5px 10px; border-radius: 5px;'>Follow</button>";
                echo "</form>";
            }
            
            echo "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No users found</p>";
    }
    
    // Process follow/unfollow actions - using following_id instead of user_id
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['follow_id'])) {
            $follow_id = $_POST['follow_id'];
            $db->query("INSERT IGNORE INTO followers (following_id, follower_id) VALUES ($user_id, $follow_id)");
            echo "<script>window.location.reload();</script>";
        } elseif (isset($_POST['unfollow_id'])) {
            $unfollow_id = $_POST['unfollow_id'];
            $db->query("DELETE FROM followers WHERE following_id = $user_id AND follower_id = $unfollow_id");
            echo "<script>window.location.reload();</script>";
        }
    }
    
    // Display current followers - using following_id instead of user_id
    echo "<h2>Your Followers</h2>";
    $followers_sql = "SELECT u.user_id, u.username, u.full_name, u.profile_image 
                     FROM users u 
                     JOIN followers f ON u.user_id = f.follower_id 
                     WHERE f.following_id = ?";
    
    $followers_stmt = $db->prepare($followers_sql);
    $followers_stmt->bind_param("i", $user_id);
    $followers_stmt->execute();
    $followers_result = $followers_stmt->get_result();
    
    if ($followers_result->num_rows > 0) {
        echo "<ul style='list-style: none; padding: 0;'>";
        while ($follower = $followers_result->fetch_assoc()) {
            $profile_image = !empty($follower['profile_image']) ? $follower['profile_image'] : 'assets/images/default-avatar.png';
            $full_name = !empty($follower['full_name']) ? $follower['full_name'] : '';
            
            echo "<li style='margin-bottom: 15px; display: flex; align-items: center;'>";
            echo "<img src='{$profile_image}' style='width: 50px; height: 50px; border-radius: 50%; margin-right: 10px;'>";
            echo "<div>";
            echo "<div><strong>{$follower['username']}</strong></div>";
            echo "<div>{$full_name}</div>";
            echo "</div>";
            echo "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>You don't have any followers yet.</p>";
    }
    
} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>