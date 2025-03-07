<?php
// config.php - Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'food_blog');

// connection.php - Database connection
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch(PDOException $e) {
    error_log("Connection failed: " . $e->getMessage());
    die("Connection failed. Please try again later.");
}

// functions.php - Helper functions
function createBlogPost($pdo, $data, $userId) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO blog_posts (
                title, 
                content, 
                user_id, 
                tags, 
                status,
                created_at
            ) VALUES (
                :title, 
                :content, 
                :user_id, 
                :tags,
                :status, 
                NOW()
            )
        ");

        $stmt->execute([
            'title' => $data['title'],
            'content' => $data['content'],
            'user_id' => $userId,
            'tags' => $data['tags'],
            'status' => 'published'
        ]);

        return $pdo->lastInsertId();
    } catch(PDOException $e) {
        error_log("Error creating blog post: " . $e->getMessage());
        return false;
    }
}

function createRecipe($pdo, $data, $userId) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO recipes (
                title,
                category,
                description,
                ingredients,
                instructions,
                prep_time,
                cook_time,
                servings,
                difficulty,
                dietary_options,
                user_id,
                status,
                created_at
            ) VALUES (
                :title,
                :category,
                :description,
                :ingredients,
                :instructions,
                :prep_time,
                :cook_time,
                :servings,
                :difficulty,
                :dietary_options,
                :user_id,
                :status,
                NOW()
            )
        ");

        // Convert dietary options array to JSON
        $dietaryOptions = isset($data['dietary']) ? json_encode($data['dietary']) : '[]';

        $stmt->execute([
            'title' => $data['title'],
            'category' => $data['category'],
            'description' => $data['description'],
            'ingredients' => $data['ingredients'],
            'instructions' => $data['instructions'],
            'prep_time' => $data['prep_time'],
            'cook_time' => $data['cook_time'],
            'servings' => $data['servings'],
            'difficulty' => $data['difficulty'],
            'dietary_options' => $dietaryOptions,
            'user_id' => $userId,
            'status' => 'published'
        ]);

        return $pdo->lastInsertId();
    } catch(PDOException $e) {
        error_log("Error creating recipe: " . $e->getMessage());
        return false;
    }
}