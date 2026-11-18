<?php

require_once("../config/session.php");
require_once("../config/database.php");

if(!isset($_GET['id'])){
    header("Location: index.php");
    exit();
}

$id = (int)$_GET['id'];

$stmt = $conn->prepare("DELETE FROM students WHERE id=?");
$stmt->execute([$id]);

header("Location: index.php?deleted=1");
exit();

?>