<?php
session_start();
require_once 'connection.php';

// Fetch user data
$userId = $_SESSION['user_id'];
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error: " . $e->getMessage());
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = $_POST['full_name'];
    $bio = $_POST['bio'];

    // Handle profile picture upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $profilePicture = $_FILES['profile_picture'];
        $targetDir = "uploads/";
        $targetFile = $targetDir . basename($profilePicture['name']);
        move_uploaded_file($profilePicture['tmp_name'], $targetFile);
    } else {
        $targetFile = $user['profile_picture']; // Keep the old picture if no new one is uploaded
    }

    try {
        $updateStmt = $pdo->prepare("UPDATE users SET full_name = ?, bio = ?, profile_picture = ? WHERE id = ?");
        $updateStmt->execute([$fullName, $bio, $targetFile, $userId]);
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
    <title>Edit Profile</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Edit Profile</h1>
        <form method="POST" enctype="multipart/form-data">
            <label for="full_name">Full Name</label>
            <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required><br>

            <label for="bio">Bio</label>
            <textarea name="bio" required><?php echo htmlspecialchars($user['bio']); ?></textarea><br>

            <label for="profile_picture">Profile Picture</label>
            <input type="file" name="profile_picture"><br>

            <button type="submit" class="btn">Save Changes</button>
        </form>
    </div>
</body>
</html>
