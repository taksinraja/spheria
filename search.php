<?php 
require_once 'includes/config.php';
require_once 'includes/db.php';
session_start();

// Get trending creators
$trending_sql = "SELECT u.user_id, u.username, u.full_name, u.profile_image, 
                COUNT(f.follower_id) as followers_count 
                FROM users u 
                LEFT JOIN followers f ON u.user_id = f.following_id 
                GROUP BY u.user_id 
                ORDER BY followers_count DESC 
                LIMIT 6";
$trending_result = $db->query($trending_sql);
$trending_creators = [];
if ($trending_result) {
    while ($creator = $trending_result->fetch_assoc()) {
        $trending_creators[] = $creator;
    }
}

// Get recent searches if user is logged in
$recent_searches = [];
if (isset($_SESSION['user_id'])) {
    $recent_sql = "SELECT s.*, u.username, u.profile_image 
                  FROM search_history s 
                  JOIN users u ON s.searched_user_id = u.user_id 
                  WHERE s.user_id = ? 
                  ORDER BY s.search_date DESC 
                  LIMIT 5";
    $recent_stmt = $db->prepare($recent_sql);
    if ($recent_stmt) {
        $recent_stmt->bind_param("i", $_SESSION['user_id']);
        $recent_stmt->execute();
        $recent_result = $recent_stmt->get_result();
        
        while ($search = $recent_result->fetch_assoc()) {
            $recent_searches[] = $search;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spheria - Search</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/sidebar.css">
    <link rel="stylesheet" href="assets/css/search.css">
    <link rel="shortcut icon" href="assets/images/logo2.png" type="image/x-icon">
</head>
<body class="bg">
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>

            <!-- Main Content -->
            <div class="col main-content">
                <div class="search-container">
                    <h1 class="mb-4 text-white">Search Creators</h1>
                    
                    <!-- Search Input -->
                    <div class="search-input mb-4">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input type="text" id="searchInput" class="form-control form-control-lg" 
                                   placeholder="Search creators, platforms, content categories...">
                        </div>
                    </div>

                    <!-- Search Results -->
                    <div id="searchResults" class="search-results mb-4" style="display: none;">
                        <h6 class="text-muted mb-3">Search Results</h6>
                        <div id="resultsContainer" class="results-container">
                            <!-- Search results will be populated here -->
                        </div>
                    </div>

                    <!-- Recent Searches -->
                    <?php if (!empty($recent_searches)): ?>
                    <div class="recent-search mb-5">
                        <h6 class="text-muted mb-3">Recent searches</h6>
                        <div class="d-flex flex-wrap">
                            <?php foreach ($recent_searches as $search): ?>
                            <div class="recent-search-item" data-user-id="<?= $search['searched_user_id'] ?>">
                                <img src="<?= !empty($search['profile_image']) ? $search['profile_image'] : 'assets/images/default-avatar.png' ?>" class="rounded-circle" width="24" height="24">
                                <span><?= htmlspecialchars($search['username']) ?></span>
                                <i class="fas fa-times remove-search" data-search-id="<?= $search['search_id'] ?>"></i>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Trending Creators -->
                    <div class="trending-creators">
                        <h6 class="text-muted">Trending Creators</h6>
                        <div class="row row-cols-1 row-cols-md-3 g-4 mt-2">
                            <?php foreach ($trending_creators as $creator): ?>
                            <div class="col mb-2 tranding-card">
                                <div class="creator-card h-100">
                                    <div class="d-flex align-items-center mb-2">
                                        <img src="<?= !empty($creator['profile_image']) ? $creator['profile_image'] : 'assets/images/default-avatar.png' ?>" class="rounded-circle me-3" width="50" height="50">
                                        <div>
                                            <h6 class="mb-0 text-white"><?= htmlspecialchars($creator['full_name'] ?? $creator['username']) ?></h6>
                                            <small class="text-muted">@<?= htmlspecialchars($creator['username']) ?></small>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <div class="text-muted small">
                                            <i class="fas fa-users me-1"></i> <?= number_format($creator['followers_count']) ?> followers
                                        </div>
                                        <a href="profile.php?id=<?= $creator['user_id'] ?>" class="btn btn-sm view-btn">View Profile</a>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/search.js"></script>
</body>
</html>