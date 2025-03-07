<?php
session_start();
require_once '../include/connection.php';

// Debugging session
error_log("Admin Dashboard - Current session: " . print_r($_SESSION, true));

function validateAdminSession($pdo) {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
        return false;
    }
    
    // Double-check against database
    try {
        $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ? AND email = ?");
        $stmt->execute([$_SESSION['user_id'], $_SESSION['user_email']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($user && $user['role'] === 'admin');
    } catch(PDOException $e) {
        error_log("Admin validation error: " . $e->getMessage());
        return false;
    }
}

// Validate admin session
if (!validateAdminSession($pdo)) {
    session_unset();
    session_destroy();
    header("Location: /FoodBlog/view/login.php");
    exit();
}

// ---------------------
// Fetch Ratings Data
// ---------------------
$ratingsData = [];
try {
    // Calculate the total sum of ratings from the comments table
    $stmt = $pdo->query("SELECT SUM(rating) AS total_rating, COUNT(*) AS rating_count FROM comments");
    $ratingsData = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalRatings = $ratingsData['total_rating'] ?? 0; // Total sum of all ratings
    $averageRating = $ratingsData['rating_count'] > 0 ? $totalRatings / $ratingsData['rating_count'] : 0; // Calculate average if count is greater than 0
} catch(PDOException $e) {
    error_log("Error fetching ratings data: " . $e->getMessage());
    $totalRatings = 0;
    $averageRating = 0;
}

// ---------------------
// Fetch Other Statistics
// ---------------------
$postsCount = 0;
$usersCount = 0;
$recentPosts = [];

try {
    // Get total blog posts
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM blog_posts");
    $postsCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    // Get total users
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $usersCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    // Get recent blog posts with author info
    $stmt = $pdo->prepare("SELECT bp.*, u.full_name as author_name 
                           FROM blog_posts bp
                           LEFT JOIN users u ON bp.user_id = u.id
                           ORDER BY bp.created_at DESC 
                           LIMIT 5");
    if ($stmt->execute()) {
        $recentPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        error_log("Error fetching recent posts: " . implode(", ", $stmt->errorInfo()));
    }
} catch(PDOException $e) {
    error_log("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admin Dashboard - Bites of Brilliance</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
        /* Global resets */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }
        /* Sidebar Styles */
        .sidebar {
            width: 250px;
            background-color: #333;
            color: white;
            padding: 1rem;
        }
        .sidebar-brand {
            font-size: 1.5rem;
            color: #ff6f61;
            text-align: center;
            padding: 1rem 0;
            margin-bottom: 2rem;
        }
        .sidebar-menu {
            list-style: none;
        }
        .sidebar-menu li {
            margin-bottom: 0.5rem;
        }
        .sidebar-menu a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .sidebar-menu a:hover {
            background-color: #ff6f61;
        }
        .sidebar-menu i {
            margin-right: 0.75rem;
        }
        /* Main Content Styles */
        .main-content {
            flex: 1;
            padding: 2rem;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        .user-info {
            display: flex;
            align-items: center;
        }
        .user-info img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 1rem;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stat-card h3 {
            color: #666;
            margin-bottom: 0.5rem;
        }
        .stat-card .number {
            font-size: 2rem;
            color: #ff6f61;
            font-weight: bold;
        }
        .content-section {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        .btn-add {
            background: #ff6f61;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }
        .btn-add:hover {
            background: #ff5945;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f8f8;
            color: #333;
        }
        .action-buttons a {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            text-decoration: none;
            color: white;
            margin-right: 0.5rem;
            font-size: 0.875rem;
        }
        .btn-edit {
            background-color: #3498db;
        }
        .btn-edit:hover {
            background-color: #2980b9;
        }
        .btn-delete {
            background-color: #e74c3c;
        }
        .btn-delete:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-brand">
                Bites of Brilliance
            </div>
            <ul class="sidebar-menu">
                <li><a href="/FoodBlog/admin/admin-dashboard.php"><i class="fas fa-home"></i>Dashboard</a></li>
                <li><a href="/FoodBlog/admin/blog-posts.php"><i class="fas fa-blog"></i>Blog Posts</a></li>
                <li><a href="/FoodBlog/admin/users.php"><i class="fas fa-users"></i>Users</a></li>
                <li><a href="/FoodBlog/admin/comments.php"><i class="fas fa-comments"></i>Comments</a></li>
                <li><a href="/FoodBlog/admin/logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a></li>
            </ul>
        </div>
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Dashboard</h1>
                <div class="user-info">
                    <img src="/FoodBlog/images/hero.png" alt="User Avatar">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                </div>
            </div>
            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Posts</h3>
                    <div class="number"><?php echo $postsCount ?? 0; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Total Users</h3>
                    <div class="number"><?php echo $usersCount ?? 0; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Total Ratings</h3>
                    <div class="number"><?php echo number_format($totalRatings, 2); ?></div> <!-- Show total ratings -->
                </div>
                <div class="stat-card">
                    <h3>Average Rating</h3>
                    <div class="number"><?php echo $averageRating ? number_format($averageRating, 1) : 'N/A'; ?>/5</div>
                </div>
            </div>
            <!-- Recent Blog Posts Section -->
            <div class="content-section">
                <div class="content-header">
                    <h2>Recent Blog Posts</h2>
                    <a href="add-post.php" class="btn-add">Add New Post</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recentPosts)): ?>
                            <?php foreach($recentPosts as $post): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($post['title'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($post['author_name'] ?? 'Unknown'); ?></td>
                                    <td><?php echo isset($post['created_at']) ? date('M d, Y', strtotime($post['created_at'])) : 'N/A'; ?></td>
                                    <td class="action-buttons">
                                        <a href="view-post.php?id=<?php echo htmlspecialchars($post['id']); ?>" class="btn-view" title="View Post">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="edit-post.php?id=<?php echo htmlspecialchars($post['id']); ?>" class="btn-edit">Edit</a>
                                        <a href="delete-post.php?id=<?php echo htmlspecialchars($post['id']); ?>" class="btn-delete" onclick="return confirm('Are you sure?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align: center;">No recent blog posts found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
