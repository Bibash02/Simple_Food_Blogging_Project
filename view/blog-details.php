<?php
require_once 'conn.php'; // Adjust path if necessary

// Check if an ID is provided
if (!isset($_GET['id'])) {
    die("No blog post specified.");
}

$post_id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

// Query to fetch the blog post details
$query = "SELECT * FROM blog_posts WHERE id = $post_id";
$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    die("Blog post not found.");
}

$post = mysqli_fetch_assoc($result);

// Fetch comments and ratings for the current blog post
$commentsQuery = "SELECT * FROM comments WHERE post_id = $post_id ORDER BY created_at DESC";
$commentsResult = mysqli_query($conn, $commentsQuery);
$comments = mysqli_fetch_all($commentsResult, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($post['title']); ?> - Bites of Brilliance</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link rel="stylesheet" href="/FoodBlog/assets/shared.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f9f9f9;
        }

        .container {
            max-width: 900px;
            margin: 2rem auto;
            padding: 2rem;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .post-title {
            text-align: center;
            font-size: 2rem;
            color: #333;
            margin-bottom: 1rem;
        }

        .post-image {
            width: 100%;
            height: auto;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        .section-title {
            font-size: 1.5rem;
            color: #ff6f61;
            margin-top: 1.5rem;
            border-bottom: 2px solid #ff6f61;
            padding-bottom: 5px;
        }

        .post-content {
            font-size: 1.1rem;
            line-height: 1.7;
            color: #444;
            margin-bottom: 1rem;
            background: #f5f5f5;
            padding: 1rem;
            border-radius: 6px;
        }

        .back-button {
            display: block;
            width: 200px;
            margin: 2rem auto;
            padding: 12px;
            background-color: #ff6f61;
            color: white;
            font-size: 1.1rem;
            text-align: center;
            border-radius: 4px;
            text-decoration: none;
            transition: 0.3s ease;
        }

        .back-button:hover {
            background-color: #e85a4f;
        }

        /* Comments & Ratings Section */
        .comments-section {
            margin-top: 2rem;
            padding: 1.5rem;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        .comment {
            padding: 1rem;
            background: #f5f5f5;
            border-radius: 6px;
            margin-bottom: 1rem;
            border-left: 5px solid #ff6f61;
        }

        .comment strong {
            font-size: 1.1rem;
            color: #333;
        }

        .comment small {
            color: #666;
        }

        .rating {
            color: #ffa534;
            font-size: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h1>
        
        <?php if (!empty($post['image_url'])): ?>
            <img src="<?php echo htmlspecialchars($post['image_url']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" class="post-image">
        <?php endif; ?>

        <div class="post-content">
            <?php echo nl2br(htmlspecialchars($post['content'])); ?>
        </div>

        <?php if (!empty($post['ingredients'])): ?>
            <h2 class="section-title">Ingredients</h2>
            <div class="post-content">
                <?php echo nl2br(htmlspecialchars($post['ingredients'])); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($post['instructions'])): ?>
            <h2 class="section-title">Instructions</h2>
            <div class="post-content">
                <?php echo nl2br(htmlspecialchars($post['instructions'])); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($post['description'])): ?>
            <h2 class="section-title">Description</h2>
            <div class="post-content">
                <?php echo nl2br(htmlspecialchars($post['description'])); ?>
            </div>
        <?php endif; ?>

        <!-- Comments & Ratings Section -->
        <div class="comments-section">
            <h2 class="section-title">Comments & Ratings</h2>

            <?php if (empty($comments)): ?>
                <p>No comments yet. Be the first to comment!</p>
            <?php else: ?>
                <?php foreach ($comments as $comment): ?>
                    <div class="comment">
                        <strong><?php echo htmlspecialchars($comment['user_name']); ?></strong>
                        <p><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                        <small><?php echo date('M d, Y', strtotime($comment['created_at'])); ?></small>

                        <!-- Show Rating if Available -->
                        <?php if (!empty($comment['rating'])): ?>
                            <div class="rating">
                                Rating: 
                                <?php for ($i = 0; $i < $comment['rating']; $i++): ?>
                                    <i class="fas fa-star"></i>
                                <?php endfor; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <a href="/FoodBlog/view/index.php" class="back-button"><i class="fas fa-arrow-left"></i> Back to Blog</a>
    </div>
</body>
</html>

<?php
// Close the database connection
mysqli_close($conn);
?>
