<?php

/* ===========================
   Bangladesh Time Zone
=========================== */
date_default_timezone_set('Asia/Dhaka');

$host = "localhost";
$dbname = "crims_db";
$user = "root";
$pass = "";

try{

    $conn = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass
    );

    $conn->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);

}catch(PDOException $e){

    die("Database Connection Failed : ".$e->getMessage());

}

?>
