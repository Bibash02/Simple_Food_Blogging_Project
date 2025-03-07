<?php
session_start();
include 'connection.php'; // Ensure this file contains database connection code

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $query = "SELECT * FROM users WHERE username='$username'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        
        // Verify password
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];

            // Redirect based on role
            header("Location: FoodBlog/view/index.php");
            exit();
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "User not found!";
    }
}

$blog_query = "SELECT * FROM blog_posts ORDER BY created_at DESC";
$blog_result = mysqli_query($conn, $blog_query);
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
    </style>
</head>
<body>
<header>
    <nav class="navbar">
        <div class="brand">
            <a href="user-dashboard">Bites of Brilliance</a>
        </div>
        <div class="nav-links">
            <a href="user-dashboard.php">Home</a>
            <a href="/FoodBlog/view/recipes.html">Recipes</a>
            <a href="change-password.php">Settings</a>
            <a href="user-profile.php">Profile</a>
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
            <?php 
            if (mysqli_num_rows($blog_result) > 0):
                while ($row = mysqli_fetch_assoc($blog_result)): ?>
                    <div class="col-md-4">
                        <div class="card">
                            <img src="<?php echo htmlspecialchars($row['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($row['title']); ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($row['title']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($row['content']); ?></p>
                                <a href="Blog-Details.php?id=<?php echo $row['id']; ?>" class="btn btn-primary">Read More</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile;
            else: ?>
                <p>No blog posts found.</p>
            <?php endif; ?>
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