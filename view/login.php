<!-- login.php -->
<?php
ob_start();
session_start();
require_once '../include/connection.php';

// Add debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Login handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email']) && isset($_POST['password'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];


    
    // Debug: Print submitted data
    error_log("Login attempt - Email: " . $email);
    
    if (!empty($email) && !empty($password)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Debug: Print user data
            error_log("User found: " . print_r($user, true));
            
            if ($user && password_verify($password, $user['password'])) {
                // Only handle image upload if the user doesn't already have a profile image
                if (empty($user['profile_image']) && isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
                    // Create upload directory if it doesn't exist
                    $upload_dir = '../FoodBlog/uploads/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    $allowed = array('jpg', 'jpeg', 'png', 'gif');
                    $filename = $_FILES['profile_image']['name'];
                    $file_ext = pathinfo($filename, PATHINFO_EXTENSION);
                    
                    // Check if file extension is allowed
                    if(in_array(strtolower($file_ext), $allowed)) {
                        // Generate unique filename
                        $new_filename = $user['id'] . '_' . time() . '.' . $file_ext;
                        $destination = $upload_dir . $new_filename;
                        
                        // Move uploaded file to destination
                        if(move_uploaded_file($_FILES['profile_image']['tmp_name'], $destination)) {
                            // Update user profile image in database
                            $update_stmt = $pdo->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
                            $update_stmt->execute([$new_filename, $user['id']]);
                            $_SESSION['image_upload_success'] = "Profile image updated successfully";
                        } else {
                            $_SESSION['image_upload_error'] = "Error uploading file";
                            error_log("Error moving uploaded file to destination");
                        }
                    } else {
                        $_SESSION['image_upload_error'] = "Invalid file type. Only JPG, JPEG, PNG and GIF are allowed";
                        error_log("Invalid file extension: " . $file_ext);
                    }
                }
                
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_role'] = $user['role'];
                if (!empty($user['profile_image'])) {
                    $_SESSION['user_image'] = $user['profile_image'];
                }
                
                session_write_close();
                // Debug: Print session
                error_log("Session set: " . print_r($_SESSION, true));
                
                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header("Location: /FoodBlog/admin/admin-dashboard.php");
                } else {
                    header("Location: /FoodBlog/user/user-dashboard.php"); 
                }
                exit();
            } else {
                $_SESSION['login_error'] = "Invalid email or password";
                error_log("Password verification failed");
            }
        } catch(PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            $_SESSION['login_error'] = "An error occurred during login";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Login - Bites of Brilliance</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
        }

        .login-container {
            display: flex;
            min-height: 100vh;
            background-color: #f4f4f4;
        }

        .login-carousel {
            flex: 1;
            position: relative;
            overflow: hidden;
            display: none;
        }

        @media (min-width: 768px) {
            .login-carousel {
                display: block;
            }
        }

        .carousel-item {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 0.5s ease-in-out;
        }

        .carousel-item.active {
            opacity: 1;
        }

        .carousel-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .login-form {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: white;
            padding: 2rem;
        }

        .login-wrapper {
            width: 100%;
            max-width: 400px;
        }

        .login-form h2 {
            text-align: center;
            margin-bottom: 1.5rem;
            color: #333;
            font-size: 1.8rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
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

        .btn-login {
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

        .btn-login:hover {
            background-color: #ff5945;
        }

        .btn-login:active {
            transform: scale(0.98);
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

        .error-message {
            color: #dc3545;
            text-align: center;
            margin-bottom: 1rem;
            padding: 0.5rem;
            background-color: rgba(220, 53, 69, 0.1);
            border-radius: 4px;
            font-size: 0.9rem;
        }

        .success-message {
            color: #28a745;
            text-align: center;
            margin-bottom: 1rem;
            padding: 0.5rem;
            background-color: rgba(40, 167, 69, 0.1);
            border-radius: 4px;
            font-size: 0.9rem;
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
    <div class="login-container">
        <div class="login-carousel">
            <div class="carousel-item active"><img src="../images/nepal7.jpg" alt="Fresh Ingredients"></div>
            <div class="carousel-item"><img src="../images/nepal8.jpg" alt="Cooking Tools"></div>
            <div class="carousel-item"><img src="../images/nepal9.jpg" alt="Delicious Dishes"></div>
        </div>

        <div class="login-form">
            <div class="login-wrapper">
                <h2>Welcome to Bites of Brilliance</h2>
                
                <?php if (isset($_SESSION['registration_success'])): ?>
                    <div class='success-message'>
                        <?php 
                            echo htmlspecialchars($_SESSION['registration_success']);
                            unset($_SESSION['registration_success']);
                        ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['login_error'])): ?>
                    <div class='error-message'>
                        <?php 
                            echo htmlspecialchars($_SESSION['login_error']);
                            unset($_SESSION['login_error']);
                        ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['image_upload_error'])): ?>
                    <div class='error-message'>
                        <?php 
                            echo htmlspecialchars($_SESSION['image_upload_error']);
                            unset($_SESSION['image_upload_error']);
                        ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['image_upload_success'])): ?>
                    <div class='success-message'>
                        <?php 
                            echo htmlspecialchars($_SESSION['image_upload_success']);
                            unset($_SESSION['image_upload_success']);
                        ?>
                    </div>
                <?php endif; ?>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" 
                               class="form-control" 
                               id="email" 
                               name="email" 
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                               placeholder="Enter your email"
                               autocomplete="email" 
                               required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" 
                               class="form-control" 
                               id="password" 
                               name="password" 
                               placeholder="Enter your password"
                               autocomplete="current-password" 
                               required>
                    </div>
                    
                    <!-- Only show image upload option after verifying email doesn't have an image -->
                    <div class="image-upload" id="imageUploadSection" style="display:none;">
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
                    
                    <button type="submit" class="btn-login">Login</button>
                    <div class="login-links">
                        <a href="registration.php">Register</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Carousel functionality
        const carouselItems = document.querySelectorAll('.carousel-item');
        let currentIndex = 0;

        function changeCarousel() {
            carouselItems[currentIndex].classList.remove('active');
            currentIndex = (currentIndex + 1) % carouselItems.length;
            carouselItems[currentIndex].classList.add('active');
        }

        setInterval(changeCarousel, 5000);

        // Form validation enhancement
        document.querySelector('form').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;

            if (!email || !password) {
                e.preventDefault();
                alert('Please fill in all required fields');
            }
        });

        // Check if user already has an image after entering email
        document.getElementById('email').addEventListener('blur', function() {
            const email = this.value.trim();
            if (email) {
                // Use fetch to check if the user has an image
                fetch('check_user_image.php?email=' + encodeURIComponent(email))
                    .then(response => response.json())
                    .then(data => {
                        if (data.hasImage === false) {
                            // Show image upload section if user doesn't have an image
                            document.getElementById('imageUploadSection').style.display = 'block';
                        } else {
                            // Hide image upload section if user already has an image
                            document.getElementById('imageUploadSection').style.display = 'none';
                        }
                    })
                    .catch(error => {
                        console.error('Error checking user image:', error);
                    });
            }
        });

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
    </script>
</body>
</html>