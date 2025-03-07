<?php
session_start();
require_once 'connection.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get filter and sort parameters
$category = isset($_GET['category']) ? $_GET['category'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : '';

// Build the query based on filters
$query = "SELECT * FROM blog_posts";
$whereConditions = [];
$orderBy = "";

// Add category filter
if ($category !== '') {
    $whereConditions[] = "category = :category";
}

// Combine where conditions
if (!empty($whereConditions)) {
    $query .= " WHERE " . implode(' AND ', $whereConditions);
}

// Add sorting
if ($sort === 'title') {
    $query .= " ORDER BY title ASC";
} else {
    $query .= " ORDER BY created_at DESC";
}

// Fetch blog posts from database
try {
    $stmt = $pdo->prepare($query);
    if ($category !== '') {
        $stmt->bindParam(':category', $category);
    }
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Blog Posts Management - Bites of Brilliance</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
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

        .filter-controls {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .filter-box {
            display: flex;
            gap: 1rem;
        }

        .filter-box select {
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .btn-add {
            background: #ff6f61;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-add:hover {
            background: #ff5945;
        }

        .posts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .post-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .post-image {
            width: 100%;
            height: 200px;
            background-color: #ddd;
            background-size: cover;
            background-position: center;
        }

        .post-content {
            padding: 1rem;
        }

        .post-title {
            font-size: 1.25rem;
            margin-bottom: 0.5rem;
            color: #333;
        }

        .post-meta {
            display: flex;
            justify-content: space-between;
            color: #666;
            margin-bottom: 1rem;
            font-size: 0.875rem;
        }

        .post-excerpt {
            color: #666;
            font-size: 0.875rem;
            margin-bottom: 1rem;
            line-height: 1.5;
        }

        .post-actions {
            display: flex;
            gap: 0.5rem;
        }

        .btn-edit, .btn-delete {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            color: white;
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

        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 2rem;
        }

        .pagination a {
            padding: 0.5rem 1rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            color: #333;
        }

        .pagination a.active {
            background-color: #ff6f61;
            color: white;
            border-color: #ff6f61;
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
                <li><a href="admin-dashboard.php"><i class="fas fa-home"></i>Dashboard</a></li>
                <li><a href="blog-posts.php"><i class="fas fa-blog"></i>Blog Posts</a></li>
                <li><a href="users.php"><i class="fas fa-users"></i>Users</a></li>
                <li><a href="comments.php"><i class="fas fa-comments"></i>Comments</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Blog Posts Management</h1>
                <a href="add-post.php" class="btn-add">
                    <i class="fas fa-plus"></i> Add New Post
                </a>
            </div>

            <!-- Posts Grid -->
            <div class="posts-grid">
                <?php foreach($posts as $post): ?>
                <div class="post-card">
                    <div class="post-image" style="background-image: url('<?php echo htmlspecialchars($post['image_url'] ?? 'images/default-post.jpg'); ?>')"></div>
                    <div class="post-content">
                        <h3 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h3>
                        <div class="post-meta">
                            <span><?php echo htmlspecialchars($post['category']); ?></span>
                            <span><?php echo date('M d, Y', strtotime($post['created_at'])); ?></span>
                        </div>
                        <p class="post-excerpt"><?php echo htmlspecialchars(substr($post['content'], 0, 100)) . '...'; ?></p>
                        <div class="post-actions">
                           
                        <a href="edit-post.php?id=<?php echo htmlspecialchars($post['id']); ?>" class="btn-edit">Edit</a>
                            <a href="view-post.php?id=<?php echo $post['id']; ?>" class="btn-edit">View</a>
                            <a href="delete-post.php?id=<?php echo $post['id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this post?')">Delete</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            
        </div>
    </div>
</body>
</html>