<?php
// blog-details.php

require_once 'connection.php';

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

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_name = filter_var($_POST['user_name'], FILTER_SANITIZE_STRING);
    $comment = filter_var($_POST['comment'], FILTER_SANITIZE_STRING);
    $rating = filter_var($_POST['rating'], FILTER_SANITIZE_NUMBER_INT);

    if ($user_name && $comment && $rating) {
        $insert_comment_query = "INSERT INTO comments (post_id, user_name, comment, rating) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insert_comment_query);
        mysqli_stmt_bind_param($stmt, "issi", $post_id, $user_name, $comment, $rating);
        
        if (mysqli_stmt_execute($stmt)) {
            header("Location: blog-details.php?id=$post_id&success=1");
            exit();
        } else {
            $error = "Error adding comment.";
        }
    } else {
        $error = "All fields are required.";
    }
}

// Fetch comments
$comments_query = "SELECT * FROM comments WHERE post_id = $post_id ORDER BY created_at DESC";
$comments_result = mysqli_query($conn, $comments_query);
$comments = mysqli_fetch_all($comments_result, MYSQLI_ASSOC);

// Fetch average rating
$average_rating_query = "SELECT AVG(rating) as avg_rating FROM comments WHERE post_id = $post_id";
$average_rating_result = mysqli_query($conn, $average_rating_query);
$average_rating = mysqli_fetch_assoc($average_rating_result)['avg_rating'];

// Assuming ingredients and instructions are stored in the blog post details
$ingredients = isset($post['ingredients']) ? $post['ingredients'] : 'No ingredients listed.';
$instructions = isset($post['instructions']) ? $post['instructions'] : 'No instructions available.';
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

        .post-section {
            padding: 1.5rem;
            background: #fafafa;
            border-radius: 8px;
            margin-top: 1.5rem;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        .comment-section {
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

        .rating {
            color: #ffa534;
            font-size: 1rem;
        }

        .rating-display {
            font-size: 1.5rem;
            color: #ff6f61;
            margin-top: 10px;
        }

        .comment-form input, .comment-form textarea, .comment-form select {
            width: 100%;
            padding: 10px;
            margin: 5px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }

        .comment-form button {
            background: #ff6f61;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s;
        }

        .comment-form button:hover {
            background: #e85a4f;
        }

        .back-button {
            display: block;
            width: 200px;
            margin: 2rem auto;
            padding: 12px;
            background-color: #444;
            color: white;
            font-size: 1.1rem;
            text-align: center;
            border-radius: 4px;
            text-decoration: none;
        }

        .back-button:hover {
            background-color: #333;
        }

        .ingredients, .instructions {
            margin-top: 2rem;
            padding: 1rem;
            background: #fafafa;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
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

        <!-- Ingredients Section -->
        <div class="ingredients">
            <h2>Ingredients</h2>
            <p><?php echo nl2br(htmlspecialchars($ingredients)); ?></p>
        </div>

        <!-- Instructions Section -->
        <div class="instructions">
            <h2>Instructions</h2>
            <p><?php echo nl2br(htmlspecialchars($instructions)); ?></p>
        </div>

        <!-- Average Rating -->
        <?php if ($average_rating): ?>
            <h2 class="rating-display">Average Rating: <?php echo round($average_rating, 2); ?>/5 ⭐</h2>
        <?php endif; ?>

        <!-- Comment Section -->
        <div class="comment-section">
            <h2>Leave a Comment</h2>
            <form method="POST" class="comment-form">
                <input type="text" name="user_name" placeholder="Your Name" required>
                <textarea name="comment" placeholder="Write your comment..." required></textarea>
                <select name="rating" required>
                    <option value="5">⭐⭐⭐⭐⭐</option>
                    <option value="4">⭐⭐⭐⭐</option>
                    <option value="3">⭐⭐⭐</option>
                    <option value="2">⭐⭐</option>
                    <option value="1">⭐</option>
                </select>
                <button type="submit">Submit</button>
            </form>

            <h2>Comments</h2>
            <?php if (!empty($comments)): ?>
                <?php foreach ($comments as $comment): ?>
                    <div class="comment">
                        <strong><?php echo htmlspecialchars($comment['user_name']); ?></strong>
                        <span class="rating">⭐ <?php echo htmlspecialchars($comment['rating']); ?></span>
                        <p><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No comments yet. Be the first to comment!</p>
            <?php endif; ?>
        </div>

        <a href="user-dashboard.php" class="back-button">Back to Blog</a>
    </div>
</body>
</html>
