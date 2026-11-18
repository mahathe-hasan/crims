<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "config/database.php";

try {
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // নতুন অ্যাডমিনের তথ্য
    $full_name = "Mahathe";
    $username = "123";
    $password = "123";
    $hash = password_hash($password, PASSWORD_DEFAULT);

    // পূর্বের অ্যাডমিনরা অপরিবর্তিত রেখে নতুন অ্যাডমিন ডাটাবেজে যুক্ত করা হচ্ছে
    $stmt = $conn->prepare("INSERT INTO admins (full_name, username, password) VALUES (?, ?, ?)");
    $stmt->execute([$full_name, $username, $hash]);

    echo "<h2 style='color:green;'>New Admin Created Successfully!</h2>";
    echo "<p>Username: <b>Zazamahamad242417</b></p>";
    echo "<p>Now you can <a href='auth/login.php'>Login here</a>.</p>";

} catch (PDOException $e) {
    echo "<h2 style='color:red;'>Database Error: </h2>";
    echo "<p style='color:red;'>" . $e->getMessage() . "</p>";
}
?>