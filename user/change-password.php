<?php
session_start();
require_once 'conn.php'; // Ensure this file initializes a PDO connection as $pdo

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = trim($_POST['current_password']);
    $newPassword = trim($_POST['new_password']);
    $confirmPassword = trim($_POST['confirm_password']);

    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $error = "All fields are required.";
    } elseif ($newPassword !== $confirmPassword) {
        $error = "New passwords do not match.";
    } else {
        try {
            // Fetch current password from DB
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($currentPassword, $user['password'])) {
                // Hash the new password
                $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

                // Update password in database
                $updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $updateStmt->execute([$hashedPassword, $_SESSION['user_id']]);

                // Redirect to dashboard with success message
                $_SESSION['success'] = "Password changed successfully!";
                header("Location: user-dashboard.php");
                exit();
            } else {
                $error = "Current password is incorrect.";
            }
        } catch (PDOException $e) {
            error_log("Error: " . $e->getMessage());
            $error = "Something went wrong. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Change Password</h1>

        <?php if (isset($_SESSION['success'])): ?>
            <p class="success"><?php echo htmlspecialchars($_SESSION['success']); ?></p>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form method="POST">
            <label for="current_password">Current Password</label>
            <input type="password" name="current_password" required><br>

            <label for="new_password">New Password</label>
            <input type="password" name="new_password" required><br>

            <label for="confirm_password">Confirm New Password</label>
            <input type="password" name="confirm_password" required><br>

            <button type="submit" class="btn">Change Password</button>
        </form>
    </div>
</body>
</html>
