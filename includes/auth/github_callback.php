<?php
session_start();
require_once '../config.php';
require_once '../db.php';

// GitHub OAuth Configuration
$clientID = 'YOUR_GITHUB_CLIENT_ID';
$clientSecret = 'YOUR_GITHUB_CLIENT_SECRET';
$redirectUri = 'http://localhost/spheria1/includes/auth/github_callback.php';

try {
    // Exchange authorization code for access token
    $tokenUrl = 'https://github.com/login/oauth/access_token';
    $params = [
        'client_id' => $clientID,
        'client_secret' => $clientSecret,
        'code' => $_GET['code'],
        'redirect_uri' => $redirectUri
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $tokenUrl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $token_data = json_decode($response, true);
    $access_token = $token_data['access_token'];
    
    // Get user data
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.github.com/user');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: token ' . $access_token,
        'User-Agent: Spheria'
    ]);
    
    $user_data = json_decode(curl_exec($ch), true);
    curl_close($ch);
    
    // Get user email
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.github.com/user/emails');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: token ' . $access_token,
        'User-Agent: Spheria'
    ]);
    
    $emails = json_decode(curl_exec($ch), true);
    curl_close($ch);
    
    // Find primary email
    $email = '';
    foreach ($emails as $email_data) {
        if ($email_data['primary']) {
            $email = $email_data['email'];
            break;
        }
    }
    
    if (empty($email)) {
        $email = $emails[0]['email'];
    }
    
    $name = $user_data['name'] ?? $user_data['login'];
    $picture = $user_data['avatar_url'];
    
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
    $_SESSION['error'] = "GitHub authentication failed: " . $e->getMessage();
    header("Location: ../../login.php");
    exit;
}
?>