<?php
session_start();
require_once 'connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if recipe ID is provided
if (!isset($_GET['id']) || empty($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Recipe ID is missing in the URL or is invalid.");
}

$recipe_id = $_GET['id'];
$success_message = '';
$error_message = '';

// Fetch recipe data
try {
    $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id = ?");
    $stmt->execute([$recipe_id]);
    $recipe = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$recipe) {
        die("Error: Recipe not found in the database.");
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $category = $_POST['category'];
    $content= trim($_POST['content']);
    

    // Basic validation
    if (empty($title)) {
        $error_message = "Title is required.";
    } else {
        try {
            // Handle image upload if a new image is provided
            $image_url = isset($recipe['image_url']) ? $recipe['image_url'] : ''; // Keep existing image by default

            // Define the target directory
            $target_dir = __DIR__ . '/FoodBlog/uploads/'; // Path to the uploads directory

            // Ensure the uploads directory exists
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true); // Create uploads directory if it doesn't exist
            }

            if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
                $file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
                $new_filename = uniqid('blog', true) . '.' . $file_extension;
                $target_file = $target_dir . $new_filename;

                // Validate file type
                if (!in_array($file_extension, ["jpg", "jpeg", "png"])) {
                    throw new Exception("Only JPG, JPEG, PNG files are allowed.");
                }

                // Validate file size (5MB limit)
                if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
                    throw new Exception("File size must be less than 5MB.");
                }

                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    $image_url = $target_file;
                } else {
                    throw new Exception("Failed to upload image.");
                }
            }

            // Update recipe in the database
            $stmt = $pdo->prepare("UPDATE blog_posts SET 
                title = ?, 
                category = ?, 
                content = ?, 
                image_url = ?
                
                WHERE id = ?"); // Use id here

            $stmt->execute([
                $title,
                $category,
                $content,
                $image_url,
                $recipe_id // Make sure to bind the correct recipe ID
            ]);

            $success_message = "Recipe updated successfully!";

            // Refresh recipe data
            $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id = ?");
            $stmt->execute([$recipe_id]);
            $recipe = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $error_message = "Error updating recipe: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Edit Recipe - Bites of Brilliance</title>
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
            min-height: 100vh;
        }

        .container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .header h1 {
            color: #333;
        }

        .btn-back {
            color: #666;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: bold;
        }

        input[type="text"],
        select,
        textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }

        textarea {
            min-height: 150px;
            resize: vertical;
        }

        .current-image {
            margin: 1rem 0;
        }

        .current-image img {
            max-width: 200px;
            border-radius: 4px;
        }

        .buttons {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn-save {
            background: #ff6f61;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
        }

        .btn-save:hover {
            background: #ff5945;
        }

        .btn-cancel {
            background: #666;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 1rem;
        }

        .btn-cancel:hover {
            background: #555;
        }

        .message {
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Edit Post</h1>
            <a href="admin-dashboard.php" class="btn-back">
                <i class="fas fa-arrow-left"></i>
                Back to Post
            </a>
        </div>

        <?php if ($success_message): ?>
            <div class="message success">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="message error">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($recipe['title'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="category">Category</label>
                <input type="text" name="category" value="<?php echo htmlspecialchars($recipe['category'] ?? ''); ?>" >
            </div>

            <div class="form-group">
                <label for="content">content</label>
                <textarea id="content" name="content" required><?php echo htmlspecialchars($recipe['content'] ?? ''); ?></textarea>
            </div>


            <div class="form-group">
                <label for="image">Image (Optional)</label>
                <input type="file" id="image" name="image" accept="image/*">
                <?php if (!empty($recipe['image_url'])): ?>
                    <div class="current-image">
                        <p>Current Image:</p>
                        <img src="<?php echo htmlspecialchars($recipe['image_url']); ?>" alt="Current Recipe Image">
                    </div>
                <?php endif; ?>
            </div>

            <div class="buttons">
                <button type="submit" class="btn-save">Save Changes</button>
                <a href="admin-dashboard.php" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>
