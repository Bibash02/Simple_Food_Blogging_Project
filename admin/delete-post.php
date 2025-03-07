<?php 
include "connection.php"; // Ensure this is the correct path to your database connection file

if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // Sanitize the ID input

    // Prepare the SQL statement to delete the post
    $deletePost = $pdo->prepare("DELETE FROM blog_posts WHERE id = ?");
    
    // Execute the statement
    if ($deletePost->execute([$id])) {
        echo "<script>
            alert('Record deleted successfully.');
            window.location.href = 'admin-dashboard.php';
        </script>";
    } else {
        echo "Error deleting record: " . $conn->error;
    }
} else {
    header('Location: admin-dashboard.php');
}

$conn->close();
?>
