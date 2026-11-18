<?php
require_once("../config/session.php");
require_once("../config/database.php");

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if($id > 0){
    $stmt = $conn->prepare("DELETE FROM routines WHERE id = ?");
    $stmt->execute([$id]);
}
header("Location: index.php");
exit();