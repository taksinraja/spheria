<?php
session_start();
require_once '../config.php';
require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    // Get privacy settings
    $private_account = isset($_POST['private_account']) ? 1 : 0;
    $show_activity = isset($_POST['show_activity']) ? 1 : 0;
    
    try {
        // Update privacy settings
        $sql = "UPDATE users SET is_private = ?, show_activity = ? WHERE user_id = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("iii", $private_account, $show_activity, $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Privacy settings updated successfully";
        } else {
            $_SESSION['error'] = "Failed to update privacy settings";
        }
        
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
    
    header("Location: /spheria1/settings.php");
    exit();
}
?>