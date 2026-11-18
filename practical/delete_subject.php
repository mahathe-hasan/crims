<?php
require_once("../config/session.php");
require_once("../config/database.php");

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if($id > 0){
    // আগে টাস্ক মুছে ফেলা (যদি Foreign Key Cascade না থাকে)
    $stmt1 = $conn->prepare("DELETE FROM practical_tasks WHERE subject_id = ?");
    $stmt1->execute([$id]);

    // সাবজেক্ট মুছে ফেলা
    $stmt2 = $conn->prepare("DELETE FROM subjects WHERE id = ?");
    $stmt2->execute([$id]);
}

header("Location: index.php");
exit();