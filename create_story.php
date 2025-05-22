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
    <title>Spheria - Create Story</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/sidebar.css">
    <link rel="stylesheet" href="assets/css/story.css">
</head>
<body class="bg-dark">
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <!-- Main Content -->
            <div class="col main-content">
                <div class="upload-story">
                    <div class="story-upload-container">
                        <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($error_message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php endif; ?>

                        <div class="story-upload-header">
                            <h4>Create Story</h4>
                            <p>Share a photo or video as a story</p>
                        </div>

                        <form action="includes/stories/create_story.php" method="POST" enctype="multipart/form-data">
                            <div class="story-upload-area upload-area">
                                <div class="upload-placeholder">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <p>Upload Media</p>
                                    <span>Click to browse or drag and drop</span>
                                    <input type="file" name="media[]" class="file-input" accept="image/*,video/*" multiple required>
                                </div>
                                <div class="preview-container"></div>
                            </div>
                            
                            <div class="story-form-group">
                                <label>Add caption (optional)</label>
                                <textarea name="caption" class="form-control" placeholder="Write a caption..." rows="3"></textarea>
                            </div>

                            <div class="story-action-buttons">
                                <button type="submit" class="btn btn-primary">Share Story</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/story.js"></script>
</body>
</html>