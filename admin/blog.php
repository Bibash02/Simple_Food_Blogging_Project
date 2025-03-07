<?php
// blog.php
session_start();
require_once 'connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle blog post operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $stmt = $conn->prepare("INSERT INTO blog_posts (title, content, author_id, status) VALUES (?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['title'],
                    $_POST['content'],
                    $_SESSION['user_id'],
                    $_POST['status']
                ]);
                break;

            case 'edit':
                $stmt = $conn->prepare("UPDATE blog_posts SET title=?, content=?, status=? WHERE id=?");
                $stmt->execute([
                    $_POST['title'],
                    $_POST['content'],
                    $_POST['status'],
                    $_POST['post_id']
                ]);
                break;

            case 'delete':
                $stmt = $conn->prepare("DELETE FROM blog_posts WHERE id=?");
                $stmt->execute([$_POST['post_id']]);
                break;
        }
        header("Location: blog.php");
        exit();
    }
}

// Get blog posts list
$stmt = $conn->query("SELECT bp.*, u.username as author_name 
                     FROM blog_posts bp 
                     LEFT JOIN userss u ON bp.author_id = u.id 
                     ORDER BY bp.created_at DESC");
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Blog Posts - Bites of Brilliance</title>
    <!-- Include your CSS and other head elements -->
</head>
<body>
    <div class="dashboard">
        <!-- Include sidebar -->
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <div class="content-header">
                <h2>Manage Blog Posts</h2>
                <button class="btn" onclick="showAddPostForm()">Add New Post</button>
            </div>

            <div class="content-section">
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Status</th>
                            <th>Views</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($posts as $post): ?>
                        <tr>
                            <td><?= htmlspecialchars($post['title']) ?></td>
                            <td><?= htmlspecialchars($post['author_name']) ?></td>
                            <td><?= ucfirst($post['status']) ?></td>
                            <td><?= $post['views'] ?></td>
                            <td><?= date('d M, Y', strtotime($post['created_at'])) ?></td>
                            <td>
                                <button onclick="editPost(<?= $post['id'] ?>)" class="action-btn edit-btn">Edit</button>
                                <button onclick="deletePost(<?= $post['id'] ?>)" class="action-btn delete-btn">Delete</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Post Modal -->
    <div id="addPostModal" class="modal">
        <div class="modal-content">
            <h2>Add New Blog Post</h2>
            <form method="POST" action="blog.php">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" required>
                </div>
                <div class="form-group">
                    <label>Content</label>
                    <textarea name="content" required></textarea>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" required>
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                    </select>
                </div>
                <button type="submit" class="btn">Add Post</button>
            </form>
        </div>
    </div>

    <script>
        function showAddPostForm() {
            document.getElementById('addPostModal').style.display = 'block';
        }

        function editPost(id) {
            // Implement edit functionality
        }

        function deletePost(id) {
            if (confirm('Are you sure you want to delete this post?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'blog.php';
                
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'delete';
                
                const postInput = document.createElement('input');
                postInput.type = 'hidden';
                postInput.name = 'post_id';
                postInput.value = id;
                
                form.appendChild(actionInput);
                form.appendChild(postInput);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>