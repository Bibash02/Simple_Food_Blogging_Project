<?php
// search.php
session_start();
require_once 'connection.php';

// Function to sanitize search input
function sanitizeSearch($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

// Handle the search request
if (isset($_GET['search'])) {
    $searchTerm = sanitizeSearch($_GET['search']);
    
    try {
        // Prepare the search query
        $query = "SELECT 
                    r.s_id,
                    r.s_title,
                    r.s_description,
                    r.s_cuisine_type,
                    r.s_image_url,
                    r.s_likes_count
                 FROM search r
                 WHERE 
                    r.s_title LIKE :search 
                    OR r.s_description LIKE :search 
                    OR r.s_cuisine_type LIKE :search
                 ORDER BY r.s_likes_count DESC";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute(['search' => "%$searchTerm%"]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch(PDOException $e) {
        die("Search failed: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Search Results - Bites of Brilliance</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Search Results for "<?php echo $searchTerm; ?>"</h1>
        
        <?php if (empty($results)): ?>
            <div class="no-results">
                <p>No recipes found matching your search. Try different keywords or browse our categories.</p>
            </div>
        <?php else: ?>
            <div class="design-content">
                <?php foreach ($results as $search): ?>
                    <div class="design-item">
                        <div class="design-img">
                            <img src="<?php echo htmlspecialchars($search['s_image_url']); ?>" alt="<?php echo htmlspecialchars($search['s_title']); ?>">
                            <span><i class="far fa-heart"></i> <?php echo $search['s_likes_count']; ?></span>
                            <span><?php echo htmlspecialchars($search['s_cuisine_type']); ?></span>
                        </div>
                        <div class="design-title">
                            <a href="recipe-details.php?id=<?php echo $search['id']; ?>">
                                <?php echo htmlspecialchars($search['title']); ?>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <div class="back-link">
            <a href="index.php">Back to Home</a>
        </div>
    </div>
</body>
</html>