<!-- registration.php -->
<?php
session_start();
require_once '../include/connection.php';

// Create upload directory if it doesn't exist
$upload_dir = '../uploads/blog/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate input
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $full_name = htmlspecialchars($_POST['full_name']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = htmlspecialchars($_POST['role']); // Add role validation

    // Validate inputs
    $errors = [];
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    if (empty($full_name)) {
        $errors[] = "Full name is required";
    }
    if (strlen($password) < 4) {
        $errors[] = "Password must be at least 4 characters long";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    if (!in_array($role, ['user', 'admin'])) {
        $errors[] = "Invalid role selected";
    }

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        $errors[] = "Email already exists";
    }

    // Validate image if uploaded
    $profile_image = null;
    if(isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $allowed = array('jpg', 'jpeg', 'png', 'gif');
        $filename = $_FILES['profile_image']['name'];
        $file_ext = pathinfo($filename, PATHINFO_EXTENSION);
        
        // Check file extension
        if(!in_array(strtolower($file_ext), $allowed)) {
            $errors[] = "Invalid file type. Only JPG, JPEG, PNG and GIF are allowed";
        }
        
        // Check file size (2MB max)
        if($_FILES['profile_image']['size'] > 2 * 1024 * 1024) {
            $errors[] = "File size too large. Maximum size is 2MB";
        }
    }

    // If no errors, proceed with registration
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Handle image upload
            if(isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
                $temp = explode(".", $_FILES["profile_image"]["name"]);
                $newfilename = uniqid() . '.' . end($temp);
                $profile_image = $newfilename;
                
                // Move the uploaded file
                move_uploaded_file($_FILES["profile_image"]["tmp_name"], $upload_dir . $newfilename);
            }

            // Insert user into database with role and image
            $stmt = $pdo->prepare("INSERT INTO users (email, password, full_name, role, profile_image) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$email, $hashed_password, $full_name, $role, $profile_image]);
            
            $pdo->commit();

            // Redirect to login page with success message
            $_SESSION['registration_success'] = "Account created successfully. Please log in.";
            header("Location: login.php");
            exit();
        } catch(PDOException $e) {
            $pdo->rollBack();
            $errors[] = "Registration failed: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register - Bites of Brilliance</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
        /* Similar styling to login page */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .register-container {
            background-color: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #666;
            font-weight: 500;
        }
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        .form-control:focus {
            outline: none;
            border-color: #ff6f61;
            box-shadow: 0 0 0 2px rgba(255, 111, 97, 0.1);
        }
        .btn-register {
            width: 100%;
            padding: 0.75rem;
            background-color: #ff6f61;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: background-color 0.3s ease, transform 0.1s ease;
        }
        .btn-register:hover {
            background-color: #ff5945;
        }
        .btn-register:active {
            transform: scale(0.98);
        }
        .error-message {
            color: #dc3545;
            margin-bottom: 1rem;
            padding: 0.5rem;
            background-color: rgba(220, 53, 69, 0.1);
            border-radius: 4px;
        }
        .login-links {
            text-align: center;
            margin-top: 1.5rem;
        }
        .login-links a {
            color: #666;
            text-decoration: none;
            margin: 0 0.5rem;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }
        .login-links a:hover {
            color: #ff6f61;
        }
        /* Add styles for select dropdown */
        select.form-control {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 1em;
        }
        
        /* Image upload styling */
        .image-upload {
            margin-bottom: 1.5rem;
        }
        .image-upload-label {
            display: block;
            margin-bottom: 0.5rem;
            color: #666;
            font-weight: 500;
        }
        .image-preview {
            width: 100px;
            height: 100px;
            margin: 0.5rem 0;
            border: 1px dashed #ddd;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .image-preview img {
            max-width: 100%;
            max-height: 100%;
            object-fit: cover;
        }
        .image-upload-note {
            color: #666;
            font-size: 0.8rem;
            margin-top: 0.25rem;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>Create an Account</h2>
        <?php
        // Display errors if any
        if (!empty($errors)) {
            echo "<div class='error-message'>";
            foreach ($errors as $error) {
                echo "<p>$error</p>";
            }
            echo "</div>";
        }
        ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" class="form-control" id="full_name" name="full_name" 
                       value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>" 
                       required>
            </div>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" 
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                       required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>
            <div class="form-group">
                <label for="role">Role</label>
                <select class="form-control" id="role" name="role" required>
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div class="image-upload">
                <label class="image-upload-label" for="profile_image">Profile Image (Optional)</label>
                <div class="image-preview" id="imagePreview">
                    <span class="fas fa-user" style="font-size: 2rem; color: #ddd;"></span>
                </div>
                <input type="file" 
                       class="form-control" 
                       id="profile_image" 
                       name="profile_image" 
                       accept="image/jpeg,image/png,image/gif">
                <div class="image-upload-note">Supported formats: JPG, PNG, GIF (Max 2MB)</div>
            </div>
            <button type="submit" class="btn-register">Register</button>
            <div class="login-links">
                <a href="login.php">Already have an account? Login</a>
            </div>
        </form>
    </div>

    <script>
        // Image preview functionality
        document.getElementById('profile_image').addEventListener('change', function(e) {
            const preview = document.getElementById('imagePreview');
            preview.innerHTML = '';
            
            if (this.files && this.files[0]) {
                const file = this.files[0];
                
                // Check file size (2MB max)
                if (file.size > 2 * 1024 * 1024) {
                    alert('File is too large. Maximum size is 2MB.');
                    this.value = ''; // Clear the input
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    preview.appendChild(img);
                }
                reader.readAsDataURL(file);
            } else {
                // Reset to default if no file selected
                preview.innerHTML = '<span class="fas fa-user" style="font-size: 2rem; color: #ddd;"></span>';
            }
        });

        // Password matching validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match');
            }
        });
    </script>
</body>
</html>