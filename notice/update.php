<?php

require_once("../config/session.php");
require_once("../config/database.php");

if(isset($_POST['id'])){

    $id = $_POST['id'];
    $title = trim($_POST['title']);
    $category = trim($_POST['category']);
    $notice_date = $_POST['notice_date'];
    $description = trim($_POST['description']);

    $stmt = $conn->prepare("
        UPDATE notices
        SET
            title = ?,
            category = ?,
            notice_date = ?,
            description = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $title,
        $category,
        $notice_date,
        $description,
        $id
    ]);

    header("Location: index.php?updated=1");
    exit();

}

header("Location: index.php");
exit();

?>