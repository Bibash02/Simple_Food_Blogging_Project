<?php
// settings.php
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
        $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
        $newsletter = isset($_POST['newsletter']) ? 1 : 0;
        $theme = $_POST['theme'];

        $stmt = $pdo->prepare("UPDATE user_settings SET email_notifications = ?, newsletter = ?, theme = ? WHERE user_id = ?");
        $stmt->execute([$email_notifications, $newsletter, $theme, $_SESSION['user_id']]);
        
        $success_message = "Settings updated successfully!";
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

// Get current settings
try {
    $stmt = $pdo->prepare("SELECT * FROM user_settings WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Error fetching settings: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Settings - Bites of Brilliance</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .settings-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .settings-section {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }

        .settings-section:last-child {
            border-bottom: none;
        }

        .switch-container {
            display: flex;
            align-items: center;
            margin: 1rem 0;
        }

        .switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
            margin-right: 1rem;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked + .slider {
            background-color: #ff6b6b;
        }

        input:checked + .slider:before {
            transform: translateX(26px);
        }

        .theme-select {
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-top: 0.5rem;
        }

        .btn-save {
            background: #ff6b6b;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
        }

        .btn-save:hover {
            background: #ff5252;
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

    <div class="settings-container">
        <h1>Settings</h1>

        <?php if ($success_message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <form method="POST" action="settings.php">
            <div class="settings-section">
                <h2>Notifications</h2>
                <div class="switch-container">
                    <label class="switch">
                        <input type="checkbox" name="email_notifications" <?php echo $settings['email_notifications'] ? 'checked' : ''; ?>>
                        <span class="slider"></span>
                    </label>
                    <span>Email Notifications</span>
                </div>

                <div class="switch-container">
                    <label class="switch">
                        <input type="checkbox" name="newsletter" <?php echo $settings['newsletter'] ? 'checked' : ''; ?>>
                        <span class="slider"></span>
                    </label>
                    <span>Subscribe to Newsletter</span>
                </div>
            </div>

            <div class="settings-section">
                <h2>Appearance</h2>
                <label for="theme">Theme</label>
                <select name="theme" id="theme" class="theme-select">
                    <option value="light" <?php echo $settings['theme'] === 'light' ? 'selected' : ''; ?>>Light</option>
                    <option value="dark" <?php echo $settings['theme'] === 'dark' ? 'selected' : ''; ?>>Dark</option>
                    <option value="system" <?php echo $settings['theme'] === 'system' ? 'selected' : ''; ?>>System Default</option>
                </select>
            </div>

            <button type="submit" class="btn-save">Save Settings</button>
        </form>
    </div>
</body>
</html>