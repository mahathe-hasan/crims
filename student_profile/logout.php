<?php
session_start();
unset($_SESSION['student_portal_id']);
header("Location: login.php");
exit;
?>