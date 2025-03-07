<?php
session_start();
require_once '../include/connection.php';

// Assuming the admin's user ID is stored in the session as 'user_id'
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page or show an error
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id']; // Get the admin's user ID from the session

// Fetch the most recent post created by the admin
$post = null; // Initialize variable to hold the post
try {
    $stmt = $pdo->prepare("SELECT bp.*, u.full_name as author_name 
                            FROM blog_posts bp 
                            JOIN users u ON bp.user_id = u.id 
                            WHERE bp.user_id = ? 
                            ORDER BY bp.created_at DESC 
                            LIMIT 1"); // Fetch only the latest post
    $stmt->execute([$user_id]); // Bind the user ID to the query
    $post = $stmt->fetch(PDO::FETCH_ASSOC); // Fetch the single post
} catch (PDOException $e) {
    error_log("Error fetching post: " . $e->getMessage());
    // Optionally, redirect to an error page or show a message
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Blog Post - Bites of Brilliance</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #ff6f61;
            margin-bottom: 20px;
        }
        .post {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .post-title {
            font-size: 1.5em;
            margin-bottom: 10px;
            color: #333;
        }
        .post-meta {
            font-size: 0.9em;
            color: #777;
        }
        .post-content {
            margin-top: 10px;
        }
        .featured-image {
            width: 100%;
            height: auto;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        .back-button {
            display: inline-block;
            margin-bottom: 20px;
            background: #ff6f61;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
        }
        .back-button:hover {
            background: #ff5945;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="admin-dashboard.php" class="back-button">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
        <h1>View Blog Post</h1>

        <?php if (!$post): ?>
            <p>No blog posts available.</p>
        <?php else: ?>
            <div class="post">
                <h2 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h2>
                <div class="post-meta">
                    <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($post['author_name']); ?></span>
                    <span><i class="fas fa-calendar"></i> <?php echo date('F j, Y', strtotime($post['created_at'])); ?></span>
                </div>
                <?php if (!empty($post['featured_image'])): ?>
                    <img src="/FoodBlog/uploads/blog/<?php echo htmlspecialchars($post['featured_image']); ?>" 
                         alt="<?php echo htmlspecialchars($post['title']); ?>" 
                         class="featured-image">
                <?php endif; ?>
                <div class="post-content">
                    <h3>Description</h3>
                    <p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                    <h3>Ingredients</h3>
                    <p><?php echo nl2br(htmlspecialchars($post['ingredients'])); ?></p>
                    <h3>Instructions</h3>
                    <p><?php echo nl2br(htmlspecialchars($post['instructions'])); ?></p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
