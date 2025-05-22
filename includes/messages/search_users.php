<?php
session_start();
require_once '../config.php';
require_once '../db.php';
require_once '../auth/auth_check.php';

// Get search query
$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (empty($query) || strlen($query) < 2) {
    echo json_encode(['success' => false, 'message' => 'Search query too short']);
    exit;
}

// Search for users
$search_query = $db->prepare("
    SELECT user_id, username, full_name, profile_image
    FROM users 
    WHERE (username LIKE ? OR full_name LIKE ?) 
    AND user_id != ?
    LIMIT 10
");
$search_param = "%$query%";
$search_query->bind_param("ssi", $search_param, $search_param, $_SESSION['user_id']);
$search_query->execute();
$search_result = $search_query->get_result();

$users = [];
while ($user = $search_result->fetch_assoc()) {
    // Format profile image
    if (empty($user['profile_image'])) {
        $user['profile_image'] = 'assets/images/default-avatar.png';
    }
    
    // Format full name
    if (empty($user['full_name'])) {
        $user['full_name'] = $user['username'];
    }
    
    $users[] = $user;
}

echo json_encode([
    'success' => true,
    'users' => $users
]);
?>