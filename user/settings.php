<?php
session_start();
require_once 'connection.php';

// Fetch user data
$userId = $_SESSION['user_id'];
try {
    $stmt = $pdo->prepare("SELECT notifications FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error: " . $e->getMessage());
}

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $notifications = isset($_POST['notifications']) ? 1 : 0;
    try {
        $updateStmt = $pdo->prepare("UPDATE users SET notifications = ? WHERE id = ?");
        $updateStmt->execute([$notifications, $userId]);
        header("Location: user_dashboard.php");
        exit();
    } catch (PDOException $e) {
        error_log("Error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Settings</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Settings</h1>
        <form method="POST">
            <label for="notifications">Receive Notifications</label>
            <input type="checkbox" name="notifications" <?php echo $user['notifications'] ? 'checked' : ''; ?>><br>
            <button type="submit" class="btn">Save Settings</button>
        </form>
    </div>
</body>
</html>
