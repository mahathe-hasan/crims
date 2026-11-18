<?php
require_once("../config/session.php");
require_once("../config/database.php");

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$subject_id = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : 0;

if($id > 0){
    $stmt = $conn->prepare("DELETE FROM practical_tasks WHERE id = ?");
    $stmt->execute([$id]);
}
header("Location: manage_tasks.php?subject_id=" . $subject_id);
exit();