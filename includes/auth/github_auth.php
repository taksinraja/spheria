<?php
session_start();
require_once '../config.php';
require_once '../db.php';

// GitHub OAuth Configuration
$clientID = 'YOUR_GITHUB_CLIENT_ID';
$clientSecret = 'YOUR_GITHUB_CLIENT_SECRET';
$redirectUri = 'http://localhost/spheria1/includes/auth/github_callback.php';

// Create GitHub auth URL
$githubAuthUrl = 'https://github.com/login/oauth/authorize';
$params = [
    'client_id' => $clientID,
    'redirect_uri' => $redirectUri,
    'scope' => 'user:email'
];

$authUrl = $githubAuthUrl . '?' . http_build_query($params);
header('Location: ' . $authUrl);
exit;
?>