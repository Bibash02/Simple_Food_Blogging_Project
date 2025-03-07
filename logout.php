<?php
session_start(); // Start session

// Destroy all session variables
session_unset();
session_destroy();

// Redirect to login page
header("Location: /FoodBlog/view/login.php");
exit();
?>
