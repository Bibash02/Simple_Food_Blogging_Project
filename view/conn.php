<?php
// Database configuration
$host = 'localhost'; // Database host (usually localhost)
$user = 'root'; // Database username
$password = ''; // Database password
$database = 'bites_of_brilliance'; // Database name

// Create connection
$conn = mysqli_connect($host, $user, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set the character set to utf8
mysqli_set_charset($conn, 'utf8');

// Optional: Set the timezone
date_default_timezone_set('Asia/Kathmandu'); // Change to your timezone

?>
