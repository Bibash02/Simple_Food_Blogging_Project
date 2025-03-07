<?php
require_once 'conn.php'; // Include database connection

// Fetch blog posts from the database
$query = "SELECT * FROM blog_posts ORDER BY created_at DESC"; // Adjust the query as necessary
$result = mysqli_query($conn, $query);

// Check if the query was successful
if (!$result) {
    die("Database query failed: " . mysqli_error($conn));
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Bites of Brilliance</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" integrity="sha512-+4zCK9k+qNFUR5X+cKL9EIR+ZOhtIloNl9GIKS57V1MyNsYpYcUrUeQc9vNfzsWfV28IaLL3i96P9sdNyeRssA==" crossorigin="anonymous" />
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/FoodBlog/assets/shared.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
        }

        .navbar {
            background-color: #333;
            overflow: hidden;
            display: flex;
            justify-content: space-between;
            padding: 14px 20px;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            padding: 14px 20px;
            display: block;
        }

        .navbar a:hover {
            background-color: #575757;
        }

        .navbar .brand {
            font-weight: bold;
            font-size: 18px;
        }

        .navbar .nav-links {
            display: flex;
        }
    </style>
</head>
<body>
<header>
    <nav class="navbar">
        <div class="brand">
            <a href="#">Bites of Brilliance</a>
        </div>
        <div class="nav-links">
            <a href="/FoodBlog/view/index.php">Home</a>
            <a href="/FoodBlog/view/recipes.html">Recipes</a>
            <a href="/FoodBlog/view/login.php">Login</a>
            <a href="/FoodBlog/view/about.html">About</a>
        </div>
    </nav>
    <div class="banner">
        <div class="container">
            <h1 class="banner-title">
                <span></span> Bites of Brilliance
            </h1>
            <p>explore, cook, and savor delicious recipes from around the world</p>
        </div>
    </div>
</header>

<section class="blog" id="blog">
    <div class="container">
        <div class="title">
            <h2>Latest Food Blogs</h2>
            <p>culinary stories and cooking adventures</p>
        </div>
        
        <div class="row">
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <div class="col-md-4">
                    <div class="card">
                        <img src="<?php echo htmlspecialchars($row['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($row['title']); ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($row['title']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($row['content']); ?></p>
                            <a href="blog-details.php?id=<?php echo $row['id']; ?>" class="btn btn-primary">Read More</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

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

<?php
// Close the database connection
mysqli_close($conn);
?>
