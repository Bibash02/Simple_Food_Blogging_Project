<?php
session_start();

// Check if user is not logged in
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

require_once 'conn.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                try {
                    $stmt = $pdo->prepare("INSERT INTO recipes (title, cuisine_type, description, ingredients, instructions, created_by) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $_POST['title'],
                        $_POST['cuisine_type'],
                        $_POST['description'],
                        $_POST['ingredients'],
                        $_POST['instructions'],
                        $_SESSION['id']
                    ]);
                    $_SESSION['message'] = "Recipe created successfully!";
                } catch(PDOException $e) {
                    $_SESSION['error'] = "Error creating recipe: " . $e->getMessage();
                }
                break;

            case 'update':
                try {
                    $stmt = $pdo->prepare("UPDATE recipes SET title = ?, cuisine_type = ?, description = ?, ingredients = ?, instructions = ? WHERE id = ? AND created_by = ?");
                    $stmt->execute([
                        $_POST['title'],
                        $_POST['cuisine_type'],
                        $_POST['description'],
                        $_POST['ingredients'],
                        $_POST['instructions'],
                        $_POST['recipe_id'],
                        $_SESSION['user_id']
                    ]);
                    $_SESSION['message'] = "Recipe updated successfully!";
                } catch(PDOException $e) {
                    $_SESSION['error'] = "Error updating recipe: " . $e->getMessage();
                }
                break;

            case 'delete':
                try {
                    $stmt = $pdo->prepare("DELETE FROM recipes WHERE recipe_id = ? AND created_by = ?");
                    $stmt->execute([$_POST['recipe_id'], $_SESSION['id']]);
                    $_SESSION['message'] = "Recipe deleted successfully!";
                } catch(PDOException $e) {
                    $_SESSION['error'] = "Error deleting recipe: " . $e->getMessage();
                }
                break;
        }
        header("Location: blog.php");
        exit();
    }
}

// Fetch all recipes for the current user
try {
    $stmt = $pdo->prepare("SELECT * FROM recipes WHERE created_by = ? ORDER BY created_at DESC");
    $stmt->execute([$_SESSION['id']]);
    $recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Error fetching recipes: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Recipes - Bites of Brilliance</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        
        .recipe-form {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .recipe-form input[type="text"],
        .recipe-form textarea,
        .recipe-form select {
            width: 100%;
            padding: 8px;
            margin: 8px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .recipe-form button {
            background: #333;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .recipe-form button:hover {
            background: #555;
        }

        .recipe-list {
            max-width: 800px;
            margin: 20px auto;
        }

        .recipe-item {
            background: #fff;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .recipe-actions {
            margin-top: 10px;
        }

        .recipe-actions button {
            margin-right: 10px;
        }

        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }

        .success {
            background: #d4edda;
            color: #155724;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
        }
        footer{
    background: var(--exDark);
    color: #fff;
    text-align: center;
    padding: 2rem 0;
}
    .social-links{
        display: flex;
        justify-content: center;
        margin-bottom: 1.4rem;
    }
    .social-links a{
        border: 2px solid #fff;
        color: #fff;
        display: block;
        width: 40px;
        height: 40px;
        display: flex;
        justify-content: center;
        align-items: center;
        border-radius: 50%;
        text-decoration: none;
        margin: 0 0.3rem;
        transition: all 0.5s ease;
    }

    .social-links a:hover{
        background: #fff;
        color: var(--exDark);
    }
    .footer span{
        margin-top: 1rem;
        display: block;
        font-family: var(--Playfair);
        letter-spacing: 2px;
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
                    <a href="list.html">Recipes</a>
                    <a href="logout.php">Logout</a>
                </div>
            </div>
        </nav>
    </header>

    <div class="container">
        <h1>Manage Your Recipes</h1>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="message success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="message error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <!-- Create Recipe Form -->
        <div class="recipe-form">
            <h2>Add New Recipe</h2>
            <form method="POST" action="blog.php">
                <input type="hidden" name="action" value="create">
                
                <div>
                    <label for="title">Title:</label>
                    <input type="text" id="title" name="title" required>
                </div>

                <div>
                    <label for="cuisine_type">Cuisine Type:</label>
                    <select id="cuisine_type" name="cuisine_type" required>
                        <option value="Italian">Italian</option>
                        <option value="Indian">Indian</option>
                        <option value="Mexican">Mexican</option>
                        <option value="Chinese">Chinese</option>
                        <option value="Japanese">Japanese</option>
                        <option value="Thai">Thai</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div>
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" rows="3" required></textarea>
                </div>

                <div>
                    <label for="ingredients">Ingredients:</label>
                    <textarea id="ingredients" name="ingredients" rows="5" required></textarea>
                </div>

                <div>
                    <label for="instructions">Instructions:</label>
                    <textarea id="instructions" name="instructions" rows="5" required></textarea>
                </div>

                <button type="submit">Add Recipe</button>
            </form>
        </div>

        <!-- Recipe List -->
        <div class="recipe-list">
            <h2>Your Recipes</h2>
            <?php foreach ($recipes as $recipe): ?>
                <div class="recipe-item">
                    <h3><?php echo htmlspecialchars($recipe['title']); ?></h3>
                    <p><strong>Cuisine:</strong> <?php echo htmlspecialchars($recipe['cuisine_type']); ?></p>
                    <p><?php echo htmlspecialchars($recipe['description']); ?></p>
                    
                    <div class="recipe-actions">
                        <button onclick="editRecipe(<?php echo $recipe['id']; ?>)">Edit</button>
                        <form method="POST" action="manage-recipes.php" style="display: inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="recipe_id" value="<?php echo $recipe['id']; ?>">
                            <button type="submit" onclick="return confirm('Are you sure you want to delete this recipe?')">Delete</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <footer>
      <div class = "social-links">
        <a href = "www.facebook.com"><i class = "fab fa-facebook-f"></i></a>
        <a href = "www.x.com"><i class = "fab fa-twitter"></i></a>
        <a href = "www.instagram.com"><i class = "fab fa-instagram"></i></a>
      
      </div>
      <span>Bites of Brilliance Food Blog</span>
    </footer>
      

    <script>
        function editRecipe(recipeId) {
            // In a real application, you would populate a form with the recipe details
            // This is just a placeholder to show how it could work
            alert('Edit recipe ' + recipeId);
            // You could load the recipe details in a modal or redirect to an edit page
        }
    </script>
</body>
</html>