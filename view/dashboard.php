<?php
// dashboard.php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'connection.php';


try {
    // Get complete user details
    $stmt = $pdo->prepare("SELECT id, full_name, email, username, created_at FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get saved recipes
    $stmt = $pdo->prepare("SELECT * FROM saved_recipes WHERE id = ? ORDER BY saved_date DESC LIMIT 5");
    $stmt->execute([$_SESSION['user_id']]);
    $savedRecipes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get recent activity
    $stmt = $pdo->prepare("SELECT * FROM user_activity WHERE user_id = ? ORDER BY activity_date DESC LIMIT 5");
    $stmt->execute([$_SESSION['user_id']]);
    $recentActivity = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Error fetching user data: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - Bites of Brilliance</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .dashboard-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 20px;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .welcome-text {
            font-size: 1.5rem;
            color: #333;
        }

        .profile-section {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .profile-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-top: 1rem;
        }

        .profile-item {
            padding: 1rem;
            background: #f8f8f8;
            border-radius: 4px;
        }

        .profile-label {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 0.5rem;
        }

        .profile-value {
            font-size: 1.1rem;
            color: #333;
            font-weight: 500;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .stat-card i {
            font-size: 2rem;
            color: #ff6b6b;
            margin-bottom: 1rem;
        }

        .stat-number {
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
        }

        .stat-label {
            color: #666;
            margin-top: 0.5rem;
        }

        .content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .dashboard-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 1.5rem;
        }

        .dashboard-card h3 {
            color: #333;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #ff6b6b;
        }

        .recipe-list, .activity-list {
            list-style: none;
            padding: 0;
        }

        .recipe-item, .activity-item {
            padding: 1rem;
            border-bottom: 1px solid #eee;
            transition: background-color 0.3s;
        }

        .recipe-item:last-child, .activity-item:last-child {
            border-bottom: none;
        }

        .recipe-name {
            color: #333;
            font-weight: 500;
        }

        .recipe-cuisine {
            color: #666;
            font-size: 0.9rem;
        }

        .activity-date {
            color: #999;
            font-size: 0.8rem;
        }

        .edit-profile-btn, .settings-btn {
            background: #ff6b6b;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s;
            margin-left: 1rem;
        }

        .edit-profile-btn:hover, .settings-btn:hover {
            background: #ff5252;
        }

        .button-group {
            display: flex;
            align-items: center;
        }

        @media (max-width: 768px) {
            .profile-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-container {
                grid-template-columns: 1fr;
            }
            
            .content-grid {
                grid-template-columns: 1fr;
            }

            .button-group {
                flex-direction: column;
                gap: 1rem;
            }

            .edit-profile-btn, .settings-btn {
                width: 100%;
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Include the existing navigation -->
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="navbar-brand">Bites of Brilliance</a>
            <div class="navbar-nav">
                <a href="index.php">home</a>
                <a href="list.html">recipes</a>
                <a href="blog.php">Blog</a>
                <a href="dashboard.php" class="active">Dashboard</a>
            </div>
        </div>
    </nav>

    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1 class="welcome-text">Welcome back, <?php echo htmlspecialchars($user['full_name']); ?>!</h1>
            <div class="button-group">
                <a href="edit-profile.php"><button class="edit-profile-btn"><i class="fas fa-user-edit"></i> Edit Profile</button></a>
                <a href="settings.php"><button class="settings-btn"><i class="fas fa-cog"></i> Settings</button></a>
            </div>
        </div>

        <div class="profile-section">
            <h2>Profile Information</h2>
            <div class="profile-grid">
                <div class="profile-item">
                    <div class="profile-label">User ID</div>
                    <div class="profile-value">#<?php echo htmlspecialchars($user['id']); ?></div>
                </div>
                <div class="profile-item">
                    <div class="profile-label">Username</div>
                    <div class="profile-value"><?php echo htmlspecialchars($user['full_name']); ?></div>
                </div>
                <div class="profile-item">
                    <div class="profile-label">Email</div>
                    <div class="profile-value"><?php echo htmlspecialchars($user['email']); ?></div>
                </div>
                <div class="profile-item">
                    <div class="profile-label">Member Since</div>
                    <div class="profile-value"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></div>
                </div>
            </div>
        </div>

        <!-- Rest of the dashboard content remains the same -->
        <!-- Stats Container -->
        <div class="stats-container">
            <!-- ... (same as before) ... -->
        </div>

        <!-- Content Grid -->
        <div class="content-grid">
            <!-- ... (same as before) ... -->
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Edit Profile button handler
        const editProfileBtn = document.querySelector('.edit-profile-btn');
        editProfileBtn.addEventListener('click', function() {
            window.location.href = 'edit-profile.php';
        });

        // Settings button handler
        const settingsBtn = document.querySelector('.settings-btn');
        settingsBtn.addEventListener('click', function() {
            window.location.href = 'settings.php';
        });

        // Add hover effects to recipe items
        const recipeItems = document.querySelectorAll('.recipe-item');
        recipeItems.forEach(item => {
            item.addEventListener('mouseenter', function() {
                this.style.backgroundColor = '#f8f8f8';
            });
            item.addEventListener('mouseleave', function() {
                this.style.backgroundColor = 'transparent';
            });
        });

        // Update profile information periodically
        function updateProfile() {
            fetch('get_user_profile.php')
                .then(response => response.json())
                .then(data => {
                    // Update profile information here
                })
                .catch(error => console.error('Error:', error));
        }

        // Update profile every 5 minutes
        setInterval(updateProfile, 300000);
    });
    </script>
</body>
</html>