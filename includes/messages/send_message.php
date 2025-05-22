<?php
session_start();
require_once '../config.php';
require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conversation_id = $_POST['conversation_id'];
    $message = $_POST['message'];
    $sender_id = $_SESSION['user_id'];
    
    // Handle media upload
    $media_path = null;
    if (isset($_FILES['media']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['media'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'video/mp4', 'audio/mpeg', 'audio/wav'];
        
        if (in_array($file['type'], $allowed_types)) {
            $upload_dir = '../../uploads/messages/';
            $filename = uniqid() . '_' . $file['name'];
            $filepath = $upload_dir . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                $media_path = 'uploads/messages/' . $filename;
            }
        }
    }
    
    // Insert message with media
    $insert_message = $db->prepare("
        INSERT INTO messages (conversation_id, sender_id, content, media_url, created_at) 
        VALUES (?, ?, ?, ?, NOW())
    ");
    $insert_message->bind_param("iiss", $conversation_id, $sender_id, $message, $media_path);
    
    if ($insert_message->execute()) {
        echo json_encode(['success' => true, 'message_id' => $db->insert_id]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to send message']);
    }
}
?>