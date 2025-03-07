<?php
session_start();
require_once 'conn.php'; // Adjust the path as needed to your mysqli connection file

// Check if admin is logged in (adjust condition as necessary)
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: FoodBlog/view/login.php");
    exit();
}

// Handle actions: deletion, approval, and rejection
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $delQuery = "DELETE FROM comments WHERE id = ?";
    
    if ($stmt = mysqli_prepare($conn, $delQuery)) {
        mysqli_stmt_bind_param($stmt, "i", $delete_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    header("Location: comments.php"); // Correct redirection
    exit();
}

if (isset($_GET['approve_id'])) {
    $approve_id = intval($_GET['approve_id']);
    $appQuery = "UPDATE comments SET status = 'approved' WHERE id = ?";
    
    if ($stmt = mysqli_prepare($conn, $appQuery)) {
        mysqli_stmt_bind_param($stmt, "i", $approve_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    header("Location: comments.php"); // Correct redirection
    exit();
}

if (isset($_GET['reject_id'])) {
    $reject_id = intval($_GET['reject_id']);
    $rejQuery = "UPDATE comments SET status = 'rejected' WHERE id = ?";
    
    if ($stmt = mysqli_prepare($conn, $rejQuery)) {
        mysqli_stmt_bind_param($stmt, "i", $reject_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    header("Location: comments.php"); // Correct redirection
    exit();
}

// Fetch comments from the database
$query = "
    SELECT c.id, c.comment, c.created_at, c.status, c.user_name, bp.title AS post_title 
    FROM comments c
    JOIN blog_posts bp ON c.post_id = bp.id
    ORDER BY c.created_at DESC
";
$result = mysqli_query($conn, $query);
if (!$result) {
    die("Database query failed: " . mysqli_error($conn));
}
$comments = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Comments - Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1200px;
            margin: auto;
            padding: 2rem;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            margin-bottom: 1rem;
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
            color: #fff;
            margin-right: 0.5rem;
            font-size: 0.875rem;
        }
        .btn-approve {
            background-color: #2ecc71;
        }
        .btn-approve:hover {
            background-color: #27ae60;
        }
        .btn-reject, .btn-delete {
            background-color: #e74c3c;
        }
        .btn-reject:hover, .btn-delete:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Comments</h1>
        <table>
            <thead>
                <tr>
                    <th>User</th>
                    <th>Post</th>
                    <th>Comment</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($comments)): ?>
                    <?php foreach ($comments as $comment): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($comment['user_name']); ?></td>
                            <td><?php echo htmlspecialchars($comment['post_title']); ?></td>
                            <td><?php echo htmlspecialchars($comment['comment']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($comment['created_at'])); ?></td>
                            <td><strong><?php echo ucfirst($comment['status']); ?></strong></td>
                            <td class="action-buttons">
                                <?php if ($comment['status'] == 'pending'): ?>
                                    <a href="?approve_id=<?php echo $comment['id']; ?>" class="btn-approve" onclick="return confirm('Approve this comment?')">Approve</a>
                                    <a href="?reject_id=<?php echo $comment['id']; ?>" class="btn-reject" onclick="return confirm('Reject this comment?')">Reject</a>
                                <?php endif; ?>
                                <a href="?delete_id=<?php echo $comment['id']; ?>" class="btn-delete" onclick="return confirm('Delete this comment?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center;">No comments found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php
mysqli_close($conn);
?>
