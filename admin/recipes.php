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
$query = "SELECT * FROM recipes";
$whereConditions = [];

if ($category === 'appetizer') {
    $whereConditions[] = "category = 'appetizer'";
}

if (!empty($whereConditions)) {
    $query .= " WHERE " . implode(' AND ', $whereConditions);
}

$query .= $sort === 'name' ? " ORDER BY title ASC" : " ORDER BY created_at DESC";

// Fetch recipes from database
try {
    $stmt = $pdo->query($query);
    $recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Recipes Management - Bites of Brilliance</title>
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

        .recipes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .recipe-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .recipe-image {
            width: 100%;
            height: 200px;
            background-color: #ddd;
            background-size: cover;
            background-position: center;
        }

        .recipe-content {
            padding: 1rem;
        }

        .recipe-title {
            font-size: 1.25rem;
            margin-bottom: 0.5rem;
            color: #333;
        }

        .recipe-meta {
            display: flex;
            justify-content: space-between;
            color: #666;
            margin-bottom: 1rem;
            font-size: 0.875rem;
        }

        .recipe-actions {
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
        <div class="sidebar">
            <div class="sidebar-brand">Bites of Brilliance</div>
            <ul class="sidebar-menu">
                <li><a href="admin-dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="recipes.php"><i class="fas fa-utensils"></i> Recipes</a></li>
                <li><a href="blog.php"><i class="fas fa-blog"></i> Blog Posts</a></li>
                <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
                <li><a href="comments.php"><i class="fas fa-comments"></i> Comments</a></li>
                <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        <div class="main-content">
            <div class="header">
                <h1>Recipe Management</h1>
                <a href="add-recipe.php" class="btn-add">
                    <i class="fas fa-plus"></i> Add New Recipe
                </a>
            </div>
            <div class="recipes-grid">
                <?php foreach($recipes as $recipe): ?>
                <div class="recipe-card">
                    <div class="recipe-image" style="background-image: url('<?php echo htmlspecialchars($recipe['image_url'] ?? 'images/default-recipe.jpg'); ?>')"></div>
                    <div class="recipe-content">
                        <h3 class="recipe-title"> <?php echo htmlspecialchars($recipe['title']); ?> </h3>
                        <div class="recipe-meta">
                            <span><?php echo htmlspecialchars($recipe['category']); ?></span>
                            <span><?php echo date('M d, Y', strtotime($recipe['created_at'])); ?></span>
                        </div>
                        <p class="post-excerpt">
                            <?php echo htmlspecialchars(substr($recipe['content'] ?? '', 0, 100)) . '...'; ?>
                        </p>
                        <div class="post-actions">
                            <a href="edit-recipe.php?recipe_id=<?php echo htmlspecialchars($recipe['recipe_id']); ?>" class="btn-edit">Edit</a>
                            <a href="view-post.php?recipe_id=<?php echo htmlspecialchars($recipe['recipe_id']); ?>" class="btn-edit">View</a>
                            <a href="delete-recipe.php?recipe_id=<?php echo htmlspecialchars($recipe['recipe_id']); ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this recipe?')">Delete</a>
                        </div>

                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html>