<?php
// edit-profile.php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'connection.php';

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $full_name = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        $username = trim($_POST['username']);
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // Verify current password
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();

        if (password_verify($current_password, $user['password'])) {
            // Start building the update query
            $updates = [];
            $params = [];

            if (!empty($full_name)) {
                $updates[] = "full_name = ?";
                $params[] = $full_name;
            }
            if (!empty($email)) {
                // Check if email is already in use by another user
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $stmt->execute([$email, $_SESSION['user_id']]);
                if ($stmt->fetch()) {
                    throw new Exception("Email already in use");
                }
                $updates[] = "email = ?";
                $params[] = $email;
            }
            if (!empty($username)) {
                // Check if username is already in use by another user
                $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
                $stmt->execute([$username, $_SESSION['user_id']]);
                if ($stmt->fetch()) {
                    throw new Exception("Username already in use");
                }
                $updates[] = "username = ?";
                $params[] = $username;
            }
            if (!empty($new_password)) {
                if ($new_password !== $confirm_password) {
                    throw new Exception("New passwords do not match");
                }
                $updates[] = "password = ?";
                $params[] = password_hash($new_password, PASSWORD_DEFAULT);
            }

            if (!empty($updates)) {
                $params[] = $_SESSION['user_id'];
                $sql = "UPDATE users SET " . implode(", ", $updates) . " WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $success_message = "Profile updated successfully!";
            }
        } else {
            throw new Exception("Current password is incorrect");
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

// Get current user data
try {
    $stmt = $pdo->prepare("SELECT full_name, email, username FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Error fetching user data: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Profile - Bites of Brilliance</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .edit-profile-container {
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

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }

        .divider {
            margin: 2rem 0;
            border-top: 1px solid #eee;
        }

        .btn-submit {
            background: #ff6b6b;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
        }

        .btn-submit:hover {
            background: #ff5252;
        }

        .alert {
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <!-- Include navigation -->
    <nav class="navbar">
        <div class="container">
            <a href="#" class="navbar-brand">Bites of Brilliance</a>
            <div class="navbar-nav">
                <a href="index.php">home</a>
                <a href="list.html">recipes</a>
                <a href="blog.php">Blog</a>
                <a href="dashboard.php">Dashboard</a>
            </div>
        </div>
    </nav>

    <div class="edit-profile-container">
        <h1>Edit Profile</h1>
        
        <?php if ($success_message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <form method="POST" action="edit-profile.php">
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>">
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
            </div>

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>">
            </div>

            <div class="divider"></div>

            <h2>Change Password</h2>
            <div class="form-group">
                <label for="current_password">Current Password</label>
                <input type="password" id="current_password" name="current_password">
            </div>

            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password">
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password">
            </div>

            <button type="submit" class="btn-submit">Update Profile</button>
        </form>
    </div>
</body>
</html>
