<?php

require_once("../config/session.php");
require_once("../config/database.php");

if (!isset($_GET['id'])) {
    header("Location: history.php");
    exit;
}

$session_id = (int)$_GET['id'];

try {

    $conn->beginTransaction();

    // Delete attendance details first
    $stmt = $conn->prepare("
        DELETE FROM attendance_details
        WHERE session_id = ?
    ");
    $stmt->execute([$session_id]);

    // Delete attendance session
    $stmt = $conn->prepare("
        DELETE FROM attendance_sessions
        WHERE id = ?
    ");
    $stmt->execute([$session_id]);

    $conn->commit();

    header("Location: history.php?deleted=1");
    exit;

} catch (Exception $e) {

    $conn->rollBack();

    die("Delete Failed: " . $e->getMessage());
}