<?php
$servername = "localhost"; // Change this if necessary
$username = "root"; // Your database username
$password = ""; // Your database password
$dbname = "bites_of_brilliance"; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
