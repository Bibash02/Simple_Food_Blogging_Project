<?php
session_start();
require_once '../include/connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /FoodBlog/view/login.php");
    exit();
}

// Fetch user data from the database
$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Determine profile image path
if (!empty($user['profile_image'])) {
    $relativePath = $user['profile_image'];
    $fullFilePath = $_SERVER['DOCUMENT_ROOT'] . '/FoodBlog/' . $relativePath;
    
    if (file_exists($fullFilePath)) {
        $profileImage = '/FoodBlog/' . $relativePath;
    } else {
        $profileImage = '/FoodBlog/images/hero.png'; // Default image
    }
} else {
    $profileImage = 'https://via.placeholder.com/80';
}

// Handle logout request
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: /FoodBlog/view/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>User Profile - Bites of Brilliance</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            background-color: #f4f4f4;
        }
        .profile-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .profile-container h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }
        .profile-details {
            margin-bottom: 20px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .profile-details p {
            margin: 10px 0;
            font-size: 1.1rem;
            color: #555;
        }
        .btn-back, .btn-logout {
            display: block;
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 4px;
            text-align: center;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }
        .btn-back {
            background-color: #ff6f61;
            color: white;
            margin-bottom: 10px;
        }
        .btn-back:hover {
            background-color: #ff5945;
        }
        .btn-logout {
            background-color: #444;
            color: white;
            cursor: pointer;
        }
        .btn-logout:hover {
            background-color: #222;
        }
        .user-info {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 20px;
            text-align: center;
        }
        .user-info img {
            border-radius: 50%;
            margin-bottom: 10px;
            width: 80px;
            height: 80px;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <div class="user-info">
            <img src="<?php echo htmlspecialchars($profileImage); ?>" alt="User Avatar">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
        </div>
        <h2>User Profile</h2>
        <div class="profile-details">
            <p><strong>Name:</strong> <?php echo htmlspecialchars($user['full_name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><strong>Role:</strong> <?php echo htmlspecialchars($user['role']); ?></p>
        </div>
        <a href="user-dashboard.php" class="btn-back">Back to Dashboard</a>

        <!-- Logout Button -->
        <form method="POST">
            <button type="submit" name="logout" class="btn-logout">Logout</button>
        </form>
    </div>
</body>
</html>
