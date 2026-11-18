<?php

require_once("../config/session.php");
require_once("../config/database.php");

if(isset($_POST['title'])){

    $title = trim($_POST['title']);
    $category = trim($_POST['category']);
    $notice_date = $_POST['notice_date'];
    $description = trim($_POST['description']);
    $is_urgent = isset($_POST['is_urgent']) ? 1 : 0;

$stmt = $conn->prepare("INSERT INTO notices (notice_date, category, title, description, is_urgent) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$notice_date, $category, $title, $description, $is_urgent]);

    $stmt = $conn->prepare("
        INSERT INTO notices
        (title, category, notice_date, description)
        VALUES
        (?, ?, ?, ?)
    ");

    $stmt->execute([
        $title,
        $category,
        $notice_date,
        $description
    ]);

    header("Location: index.php?success=1");
    exit();

}

header("Location: add.php");
exit();

?>