<?php
session_start();
require_once '../include/connection.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$userRole = $_SESSION['user_role'] ?? null;

// Redirect to appropriate dashboard if already logged in
if ($isLoggedIn) {
    if ($userRole === 'admin') {
        header("Location: admin-dashboard.php");
        exit();
    } elseif ($userRole === 'user') {
        header("Location: user-dashboard.php");
        exit();
    }
}

// Fetch recipes and blog posts for display
try {
    // Get recent recipes
    $stmt = $pdo->query("SELECT * FROM recipes ORDER BY created_at DESC LIMIT 6");
    $recentRecipes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get blog posts
    $stmt = $pdo->query("SELECT * FROM blog_posts ORDER BY created_at DESC LIMIT 6");
    $blogPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Error: " . $e->getMessage());
    $recentRecipes = [];
    $blogPosts = [];
}

// Your existing HTML content starts here
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Bites of Brilliance</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/shared.css">
    <!-- Your existing CSS remains the same -->
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="brand">
                <a href="#">Bites of Brilliance</a>
            </div>
            <div class="nav-links">
                <a href="index.php?page=home">Home</a>
                <a href="recipes.html?page=recipes">Recipes</a>
                <?php if ($isLoggedIn): ?>
                    <?php if ($userRole === 'admin'): ?>
                        <a href="admin-dashboard.php">Dashboard</a>
                    <?php else: ?>
                        <a href="user-dashboard.php">Dashboard</a>
                    <?php endif; ?>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                <?php endif; ?>
                <a href="about.html?page=about">About</a>
            </div>
        </nav>
        <!-- Rest of your existing header content -->
    </header>

    <!-- Your existing content sections remain the same -->
    
    <!-- Add login check for interaction features -->
    <?php foreach($recentRecipes as $recipe): ?>
        <!-- Your existing recipe card structure -->
        <?php if (!$isLoggedIn): ?>
            <div class="login-prompt">
                <a href="login.php" class="btn btn-primary">Login to view full recipe</a>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>

    <!-- Similar login check for blog posts -->
    <?php foreach($blogPosts as $post): ?>
        <!-- Your existing blog post card structure -->
        <?php if (!$isLoggedIn): ?>
            <div class="login-prompt">
                <a href="login.php" class="btn btn-primary">Login to read more</a>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>

    <section class="design" id="recipes">
      <div class="container">
        <div class="title">
          <h2>Recent Recipes</h2>
          <p>delicious dishes to inspire your cooking</p>
        </div>
    
        <div class="row">
          <!-- Recipe Card 1 -->
          <div class="col-md-4">
            <div class="card">
              <img src="../images/nepal7.jpg" class="card-img-top" alt="Pasta Carbonara">
              <div class="card-body">
                <h5 class="card-title">Aloo Chop Nepal Style</h5>
                <p class="card-text">Aloo chop is a famous and much loved street snack. A spiced potato croquette/fritter that is irresistibly crispy on the outside.</p>
                <a href="Blog-Details.html?id=7" class="btn btn-primary">View Recipe</a>
              </div>
            </div>
          </div>
    
          <!-- Recipe Card 2 -->
          <div class="col-md-4">
            <div class="card">
            <img src="../images/nepal12.jpg" class="card-img-top" alt="Butter Chicken">
              <div class="card-body">
                <h5 class="card-title">Sukuti Sadheko</h5>
                <p class="card-text">Sukuti Sadheko is a truly unique Nepali dish that offers such a wonderful range of flavours and textures.</p>
                <a href="Blog-Details.html?id=8" class="btn btn-primary">View Recipe</a>
              </div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="card">
              <img src="nepal13.jpg" class="card-img-top" alt="Butter Chicken">
              <div class="card-body">
                <h5 class="card-title">Easy Chicken Momo Dumplings</h5>
                <p class="card-text">Nepali momos is traditionally eaten with a tomato based chutney. They are however also enjoyed in various other delicious forms in Nepal.</p>
                <a href="Blog-Details.html?id=9" class="btn btn-primary">View Recipe</a>
              </div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="card">
              <img src="nepal14.jpg" class="card-img-top" alt="Butter Chicken">
              <div class="card-body">
                <h5 class="card-title">Buckwheat Pancake With Ginger and Cinnamon</h5>
                <p class="card-text">Buckwheat Pancakes is an inventive take on the tradition buckwheat crepes/ rotis that is a beloved staple of the Thakali community in Nepal.</p>
                <a href="Blog-Details.html?id=10" class="btn btn-primary">View Recipe</a>
              </div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="card">
              <img src="nepal8.jpg" class="card-img-top" alt="Butter Chicken">
              <div class="card-body">
                <h5 class="card-title">Pork and Spinach Curry (Rayo Saag Ra Sungur Ko Masu)</h5>
                <p class="card-text">Introducing Pork and Spinach Curry (Rayo Saag ra Pork), an authentic Nepali dish originating from the Kirati community that is super easy to make.</p>
                <a href="Blog-Details.html?id=11" class="btn btn-primary">View Recipe</a>
              </div>
            </div>
          </div>
    
          <!-- Recipe Card 3 -->
          <div class="col-md-4">
            <div class="card">
              <img src="nepal10.jpg" class="card-img-top" alt="Indian-style Chicken Curry">
              <div class="card-body">
                <h5 class="card-title">Simple and Easy Chamre (Turmeric fried rice)</h5>
                <p class="card-text">Chamre is a delicious turmeric fried rice from Nepal. This dish is all about simple, wholesome ingredients coming together in a burst of flavor and color.</p>
                <a href="Blog-Details.html?id=12" class="btn btn-primary">View Recipe</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="blog" id="blog">
      <div class="container">
        <div class="title">
          <h2>Latest Food Blogs</h2>
          <p>culinary stories and cooking adventures</p>
        </div>
        
        <div class="row">
          <!-- Blog Card 1 -->
          <div class="col-md-4">
            <div class="card">
              <img src="nepal2.jpg" class="card-img-top" alt="Vegetable Manchurian">
              <div class="card-body">
                <h5 class="card-title">The Best Chicken Thukpa</h5>
                <p class="card-text">Chicken Thukpa, a humble noodle soup, originated in Tibet where "thukpa" refers to any soup or stew with noodles.</p>
                <a href="Blog-Details.html?id=1" class="btn btn-primary">Read More</a>
              </div>
            </div>
          </div>
    
          <!-- Blog Card 2 -->
          <div class="col-md-4">
            <div class="card">
              <img src="nepal3.jpg" class="card-img-top" alt="Home Baking">
              <div class="card-body">
                <h5 class="card-title">The Best Chatpate</h5>
                <p class="card-text">Chatpate is a beloved snack food in Nepal that is a perfect balance of crunch, tang, and spice. Everyone has their own version of chat pate.</p>
                <a href="Blog-Details.html?id=2" class="btn btn-primary">Read More</a>
              </div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="card">
              <img src="nepal1.jpg" class="card-img-top" alt="Home Baking">
              <div class="card-body">
                <h5 class="card-title">Spicy Tomato Jhol MoMo</h5>
                <p class="card-text">Introducing the mouthwatering Spicy Jhol Momo recipe. Jhol Momo is a beloved street food in Kathmandu, Nepal.</p>
                <a href="Blog-Details.html?id=3" class="btn btn-primary">Read More</a>
              </div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="card">
              <img src="nepal4.jpg" class="card-img-top" alt="Home Baking">
              <div class="card-body">
                <h5 class="card-title">Chicken Chilli Nepali Style</h5>
                <p class="card-text">Nepali Chicken Chilli is a classic Indo-Chinese fusion dish that has become super popular in Nepali restaurants.</p>
                <a href="Blog-Details.html?id=4" class="btn btn-primary">Read More</a>
              </div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="card">
              <img src="nepal5.jpg" class="card-img-top" alt="Home Baking">
              <div class="card-body">
                <h5 class="card-title">Easy Potato Curry Nepali Style</h5>
                <p class="card-text">Aloo Dum is a type of potato curry, staple dish in every Nepali household, is made by simmering baby potatoes in a flavourful tomato sauce.</p>
                <a href="Blog-Details.html?id=5" class="btn btn-primary">Read More</a>
              </div>
            </div>
          </div>
          
          <!-- Blog Card 3 -->
          <div class="col-md-4">
            <div class="card">
              <img src="nepal6.jpg" class="card-img-top" alt="Farm to Table">
              <div class="card-body">
                <h5 class="card-title">Aloo ko Achar</h5>
                <p class="card-text">This delightful Nepali dish affectionately known as "Aloo ko Achar" celebrates the humble potato in all its glory.</p>
                <a href="Blog-Details.html?id=6" class="btn btn-primary">Read More</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    
    <footer>
      <div class = "social-links">
        <a href="https://www.facebook.com"><i class="fab fa-facebook-f"></i></a>
        <a href = "https://www.x.com"><i class = "fab fa-twitter"></i></a>
        <a href = "https://www.instagram.com"><i class = "fab fa-instagram"></i></a>
</body>
</html>