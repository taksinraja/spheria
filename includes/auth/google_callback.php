<?php
session_start();
require_once '../config.php';
require_once '../db.php';

// Google OAuth Configuration
$clientID = 'YOUR_GOOGLE_CLIENT_ID';
$clientSecret = 'YOUR_GOOGLE_CLIENT_SECRET';
$redirectUri = 'http://localhost/spheria1/includes/auth/google_callback.php';

// Create Google Client
$client = new Google_Client();
$client->setClientId($clientID);
$client->setClientSecret($clientSecret);
$client->setRedirectUri($redirectUri);

try {
    // Exchange authorization code for access token
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $client->setAccessToken($token);
    
    // Get user profile
    $google_oauth = new Google_Service_Oauth2($client);
    $google_account_info = $google_oauth->userinfo->get();
    
    $email = $google_account_info->email;
    $name = $google_account_info->name;
    $picture = $google_account_info->picture;
    
    // Check if user exists
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // User exists, log them in
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        header("Location: ../../index.php");
        exit;
    } else {
        // Create new user
        $username = strtolower(str_replace(' ', '', $name)) . rand(100, 999);
        $password = bin2hex(random_bytes(16)); // Random password
        
        $stmt = $db->prepare("INSERT INTO users (username, email, password, full_name, profile_image) VALUES (?, ?, ?, ?, ?)");
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt->bind_param("sssss", $username, $email, $hashed_password, $name, $picture);
        
        if ($stmt->execute()) {
            $user_id = $db->insert_id;
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;
            header("Location: ../../index.php");
            exit;
        } else {
            throw new Exception("Failed to create user account");
        }
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Google authentication failed: " . $e->getMessage();
    header("Location: ../../login.php");
    exit;
}
?>