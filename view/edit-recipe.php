<?php
session_start();

// Check if user is not logged in
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

require_once 'conn.php';

// Check if recipe ID is provided
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "No recipe specified";
    header("Location: view_recipes.php");
    exit();
}

$recipe_id = $_GET['id'];

// Fetch recipe details
try {
    $stmt = $pdo->prepare("SELECT * FROM recipes WHERE id = ? AND created_by = ?");
    $stmt->execute([$recipe_id, $_SESSION['id']]);
    $recipe = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$recipe) {
        $_SESSION['error'] = "Recipe not found or you don't have permission to edit it";
        header("Location: view_recipes.php");
        exit();
    }
} catch(PDOException $e) {
    die("Error fetching recipe: " . $e->getMessage());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $cuisine_type = trim($_POST['cuisine_type']);
    $description = trim($_POST['description']);
    $ingredients = trim($_POST['ingredients']);
    $instructions = trim($_POST['instructions']);

    // Validation
    $errors = [];
    if (empty($title)) $errors[] = "Title is required";
    if (empty($cuisine_type)) $errors[] = "Cuisine type is required";
    if (empty($description)) $errors[] = "Description is required";
    if (empty($ingredients)) $errors[] = "Ingredients are required";
    if (empty($instructions)) $errors[] = "Instructions are required";

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("UPDATE recipes SET 
                title = ?, 
                cuisine_type = ?, 
                description = ?, 
                ingredients = ?, 
                instructions = ?,
                updated_at = CURRENT_TIMESTAMP 
                WHERE id = ? AND created_by = ?");
            
            $stmt->execute([
                $title,
                $cuisine_type,
                $description,
                $ingredients,
                $instructions,
                $recipe_id,
                $_SESSION['id']
            ]);

            $_SESSION['message'] = "Recipe updated successfully!";
            header("Location: view_recipes.php");
            exit();
        } catch(PDOException $e) {
            $errors[] = "Error updating recipe: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Recipe - Bites of Brilliance</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .edit-form-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-radius: 8px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .form-group input[type="text"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }

        .form-group textarea {
            min-height: 150px;
        }

        .btn-submit {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        .btn-submit:hover {
            opacity: 0.9;
        }

        .error-list {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .error-list ul {
            margin: 0;
            padding-left: 20px;
        }
    </style>
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="container">
                <a href="index.php" class="navbar-brand">Bites of Brilliance</a>
                <div class="navbar-nav">
                    <a href="index.php">Home</a>
                    <a href="view_recipes.php">View Recipes</a>
                    <a href="blog.php">Add Recipe</a>
                    <a href="logout.php">Logout</a>
                </div>
            </div>
        </nav>
    </header>

    <div class="edit-form-container">
        <h1>Edit Recipe</h1>

        <?php if (!empty($errors)): ?>
            <div class="error-list">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="title">Recipe Title</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($recipe['title']); ?>" required>
            </div>

            <div class="form-group">
                <label for="cuisine_type">Cuisine Type</label>
                <select id="cuisine_type" name="cuisine_type" required>
                    <option value="">Select Cuisine Type</option>
                    <option value="Italian" <?php echo $recipe['cuisine_type'] === 'Italian' ? 'selected' : ''; ?>>Italian</option>
                    <option value="Mexican" <?php echo $recipe['cuisine_type'] === 'Mexican' ? 'selected' : ''; ?>>Mexican</option>
                    <option value="Chinese" <?php echo $recipe['cuisine_type'] === 'Chinese' ? 'selected' : ''; ?>>Chinese</option>
                    <option value="Indian" <?php echo $recipe['cuisine_type'] === 'Indian' ? 'selected' : ''; ?>>Indian</option>
                    <option value="American" <?php echo $recipe['cuisine_type'] === 'American' ? 'selected' : ''; ?>>American</option>
                    <option value="Other" <?php echo $recipe['cuisine_type'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" required><?php echo htmlspecialchars($recipe['description']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="ingredients">Ingredients</label>
                <textarea id="ingredients" name="ingredients" required><?php echo htmlspecialchars($recipe['ingredients']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="instructions">Instructions</label>
                <textarea id="instructions" name="instructions" required><?php echo htmlspecialchars($recipe['instructions']); ?></textarea>
            </div>

            <button type="submit" class="btn-submit">Update Recipe</button>
        </form>
    </div>

    <footer>
        <div class="social-links">
            <a href="https://www.facebook.com"><i class="fab fa-facebook-f"></i></a>
            <a href="https://www.twitter.com"><i class="fab fa-twitter"></i></a>
            <a href="https://www.instagram.com"><i class="fab fa-instagram"></i></a>
        </div>
        <span>Bites of Brilliance Food Blog</span>
    </footer>
</body>
</html>