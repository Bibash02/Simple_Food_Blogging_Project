<?php
$servername = "localhost";  // Change if using a different host
$username = "root";         // Change if using a different MySQL username
$password = "";             // Change if your MySQL has a password
$database = "bites_of_brilliance";  // Ensure this matches your actual database name

$conn = mysqli_connect($servername, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>
