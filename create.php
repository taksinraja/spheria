<?php
session_start();
require_once 'includes/auth/auth_check.php';
require_once 'includes/config.php';
require_once 'includes/db.php';

// Get user data
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE user_id = ?";
$stmt = $db->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Check for error message
$error_message = '';
if (isset($_GET['error']) && isset($_GET['message'])) {
    $error_message = urldecode($_GET['message']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spheria - Create</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/sidebar.css">
    <link rel="stylesheet" href="assets/css/create.css">
    <link rel="shortcut icon" href="assets/images/logo2.png" type="image/x-icon">
</head>
<body class="bg">
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <!-- Main Content -->
            <div class="col main-content">
                <div class="create-container-main">
                <div class="create-container">
                    <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($error_message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>
                    <!-- Update the toggle buttons section -->
                    <div class="upload-toggle">
                        <div class="toggle-buttons">
                            <button class="toggle-btn active" data-type="image">Image</button>
                            <!-- <button class="toggle-btn" data-type="video">Video</button> -->
                            <button class="toggle-btn" data-type="video">Spheres</button>
                            <!-- <button class="toggle-btn" data-type="story">Stories</button> -->
                        </div>
                    </div>

                    <!-- Update the form section -->
                    <form action="includes/posts/create_post.php" method="POST" enctype="multipart/form-data" class="upload-card">
                        <h5>Share your media</h5>
                        
                        <div class="upload-area">
                            <div class="upload-placeholder">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <p>Upload Media</p>
                                <span>Click to browse or drag and drop</span>
                                <input type="file" name="media[]" class="file-input" accept="image/*,video/*" multiple required>
                            </div>
                            <div class="preview-container"></div>
                        </div>
                        
                        <input type="hidden" name="media_type" id="mediaType" value="image">
                        
                        <div class="form-group">
                            <label>Add caption</label>
                            <textarea name="caption" class="form-control" placeholder="Write a caption..." rows="3"></textarea>
                        </div>

                        <div class="form-group">
                            <label>Add tags (optional)</label>
                            <input type="text" name="tags" class="form-control" placeholder="Add tags (comma separated)">
                        </div>

                        <div class="action-buttons">
                            <button type="submit" name="status" value="published" class="btn btn-primary">Share Post</button>
                        </div>
                    </form>
                </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/create.js"></script>
</body>
</html>