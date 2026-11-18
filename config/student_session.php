<?php
session_start();

// যদি স্টুডেন্ট আইডি সেশনে না থাকে, তবে স্টুডেন্ট লগইন পেজে পাঠিয়ে দেবে
if (!isset($_SESSION['student_id'])) {
    header("Location: ../student/login.php");
    exit();
}
?>