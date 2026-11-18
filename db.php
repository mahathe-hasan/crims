<?php
$host = "localhost";
$user = "your_database_username";
$pass = "your_database_password";
$db   = "crims_db";

// connection create
$conn = new mysqli($host, $user, $pass, $db);

// check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>