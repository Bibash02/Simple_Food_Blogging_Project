<!-- user-page comment -->
<?php
session_start();
require_once '../include/connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Get the post_id from the URL
if (!isset($_GET['post_id'])) {
    header("Location: user-dashboard.php");
    exit();
}

$postId = intval($_GET['post_id']);

try {
    // Initialize $comments as an empty array to prevent errors
    $comments = [];

    // Fetch the blog post details
    $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id = ?");
    $stmt->execute([$postId]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$post) {
        header("Location: user-dashboard.php");
        exit();
    }

    // Handle the comment submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $comment = trim($_POST['comment']);

        if (!empty($comment)) {
            $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, comment, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$postId, $userId, $comment]);
            header("Location: comment.php?post_id=$postId&success=1");
            exit();
        } else {
            $error = "Comment cannot be empty.";
        }
    }

    // Fetch existing approved comments
    $stmt = $pdo->prepare("
        SELECT 
            comments.*, 
            users.full_name AS user_name 
        FROM comments 
        JOIN users ON comments.user_id = users.id 
        WHERE post_id = ? 
        AND status = 'approved'
        ORDER BY created_at DESC
    ");
    $stmt->execute([$postId]);
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch user's pending comments
    $stmt = $pdo->prepare("
        SELECT 
            comments.*, 
            users.full_name AS user_name 
        FROM comments 
        JOIN users ON comments.user_id = users.id 
        WHERE post_id = ? 
        AND user_id = ? 
        AND status = 'pending'
        ORDER BY created_at DESC
    ");
    $stmt->execute([$postId, $userId]);
    $pendingComments = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Comment on Post - Bites of Brilliance</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
        /* Styling */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 80%;
            margin: auto;
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-top: 2rem;
        }

        h1 {
            margin-bottom: 1rem;
        }

        .error {
            color: red;
            margin-bottom: 1rem;
        }

        .success {
            color: green;
        }

        .comment-form textarea {
            width: 100%;
            padding: 0.75rem;
            margin-bottom: 1rem;
            border-radius: 4px;
            border: 1px solid #ddd;
        }

        .comment-form button {
            padding: 0.5rem 1rem;
            background-color: #ff6f61;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .comment-form button:hover {
            background-color: #ff5945;
        }

        .comment {
            margin-bottom: 1rem;
            border-bottom: 1px solid #ddd;
            padding-bottom: 0.5rem;
        }

        .comment-meta {
            font-size: 0.875rem;
            color: #666;
        }

        .comment-body {
            font-size: 1rem;
            margin-top: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Comment on: <?php echo htmlspecialchars($post['title']); ?></h1>

        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['success'])): ?>
            <div class="success">Comment added successfully!</div>
        <?php endif; ?>

        <form class="comment-form" method="POST">
            <textarea name="comment" rows="5" placeholder="Write your comment here..."></textarea>
            <button type="submit">Submit Comment</button>
        </form>

        <h2>Comments</h2>
        
        <?php if (!empty($comments) && is_array($comments) && count($comments) > 0): ?>
            <?php foreach ($comments as $comment): ?>
                <div class="comment">
                    <div class="comment-meta">
                        <strong><?php echo htmlspecialchars($comment['user_name']); ?></strong> 
                        on <?php echo date('M d, Y', strtotime($comment['created_at'])); ?>
                    </div>
                    <div class="comment-body"><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No comments yet. Be the first to comment!</p>
        <?php endif; ?>
    </div>
</body>
</html>
