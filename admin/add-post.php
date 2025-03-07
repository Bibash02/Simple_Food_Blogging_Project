<?php
include "connection.php"; // Ensure your connection.php uses PDO

if (isset($_POST['submit'])) {
    // Fixing case sensitivity issue in $_POST
    $title = $_POST['title'];
    $content = $_POST['description']; // Assuming content is the full description
    $ingredients = $_POST['ingredients']; // Get ingredients from the form
    $instructions = $_POST['instructions']; // Get instructions from the form
    $author = "Admin"; // Change dynamically based on session or user login
    $category = "Uncategorized"; // Change based on user input
    $user_id = 16; // Set dynamically based on the logged-in user
    $tags = ""; // Add a field for tags if needed
    $created_at = date("Y-m-d H:i:s");

    // File upload handling for featured image
    if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] == 0) {
        $image_name = time() . "_" . $_FILES['featured_image']['name'];
        $target_dir = "../uploads/blog/";
        $target_file = $target_dir . basename($image_name);

        // Move uploaded file to uploads directory
        if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $target_file)) {
            $featured_image = $image_name;
            $image_url = $target_file; // Store the file path
        } else {
            $featured_image = "";
            $image_url = "";
        }
    } else {
        $featured_image = "";
        $image_url = "";
    }

    // Insert into database
    $sql = "INSERT INTO blog_posts (title, content, author, image_url, created_at, category, user_id, tags, featured_image, ingredients, instructions) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $pdo->prepare($sql);
    
    // Bind parameters
    $stmt->bindValue(1, $title);
    $stmt->bindValue(2, $content);
    $stmt->bindValue(3, $author);
    $stmt->bindValue(4, $image_url);
    $stmt->bindValue(5, $created_at);
    $stmt->bindValue(6, $category);
    $stmt->bindValue(7, $user_id);
    $stmt->bindValue(8, $tags);
    $stmt->bindValue(9, $featured_image);
    $stmt->bindValue(10, $ingredients); // Bind ingredients
    $stmt->bindValue(11, $instructions); // Bind instructions

    if ($stmt->execute()) {
        echo "<p class='message success'>Blog post added successfully!</p>";
        header("location: admin-dashboard.php");
        exit();
    } else {
        echo "<p class='message error'>Error adding blog post: " . implode(", ", $stmt->errorInfo()) . "</p>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Add New Recipe - Bites of Brilliance</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
        .form-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 1.5rem;
        }

        .form-col {
            flex: 1;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
        }

        input[type="text"],
        input[type="date"],
        input[type="number"],
        textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }

        textarea {
            min-height: 150px;
        }

        .textarea-tall {
            min-height: 300px;
        }

        .btn-submit {
            background: #ff6f61;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
        }

        .btn-submit:hover {
            background: #ff5945;
        }

        .message {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 4px;
            color: white;
        }

        .message.success {
            background: #28a745;
        }

        .message.error {
            background: #dc3545;
        }

        .image-preview {
            margin-top: 1rem;
            border: 1px dashed #ddd;
            padding: 10px;
            text-align: center;
            display: none;
        }

        .image-preview img {
            max-width: 100%;
            max-height: 200px;
        }

        h1 {
            color: #ff6f61;
            margin-bottom: 1.5rem;
        }

        .hint {
            font-size: 0.85rem;
            color: #777;
            margin-top: 0.3rem;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h1>Add New Post</h1>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Post Title</label>
                <input type="text" id="title" name="title" required>
            </div>

            <div class="form-row">
                <div class="form-col">
                    <label for="post_date">Date</label>
                    <input type="date" id="post_date" name="post_date" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="form-col">
                    <label for="cook_time">Cook Time (minutes)</label>
                    <input type="number" id="cook_time" name="cook_time" min="1">
                </div>
                <div class="form-col">
                    <label for="serving_size">Serving Size</label>
                    <input type="number" id="serving_size" name="serving_size" min="1">
                </div>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" required></textarea>
                <div class="hint">Briefly describe your recipe, including its origins or special features</div>
            </div>

            <div class="form-group">
                <label for="ingredients">Ingredients</label>
                <textarea id="ingredients" name="ingredients" class="textarea-tall" required></textarea>
                <div class="hint">List all ingredients with measurements, one per line</div>
            </div>

            <div class="form-group">
                <label for="instructions">Instructions</label>
                <textarea id="instructions" name="instructions" class="textarea-tall" required></textarea>
                <div class="hint">Write detailed step-by-step cooking instructions</div>
            </div>

            <div class="form-group">
                <label for="featured_image">Featured Image</label>
                <input type="file" id="featured_image" name="featured_image" accept="image/*">
            </div>

            <input type="submit" name="submit" value="Publish Recipe" class="btn-submit">
        </form>
    </div>
</body>
</html>
