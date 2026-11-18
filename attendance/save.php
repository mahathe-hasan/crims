<?php

require_once("../config/session.php");
require_once("../config/database.php");

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: index.php");
    exit();
}

/* ========= Attendance Session ========= */

$department  = trim($_POST['department'] ?? 'Computer Science and Technology');
$semester    = $_POST['semester'];
$section     = trim($_POST['section'] ?? '');
$shift       = trim($_POST['shift'] ?? '');
$course_name = trim($_POST['course_name']);
$course_code = trim($_POST['course_code']);
$teacher     = trim($_POST['teacher_name']);
$class_date  = $_POST['class_date'];
$class_time  = $_POST['class_time'];

$present = $_POST['present'] ?? [];

/* ========= Duplicate Check ========= */

$check = $conn->prepare("
SELECT id
FROM attendance_sessions
WHERE
class_date = ?
AND semester = ?
AND course_code = ?
AND class_time = ?
");

$check->execute([
    $class_date,
    $semester,
    $course_code,
    $class_time
]);

if ($check->rowCount() > 0) {
    die("Attendance Already Taken.");
}

try {

    // Transaction Start
    $conn->beginTransaction();

    /* ========= Save Attendance Session ========= */

    $session = $conn->prepare("
    INSERT INTO attendance_sessions
    (
        department,
        semester,
        section,
        shift,
        course_name,
        course_code,
        teacher_name,
        class_date,
        class_time
    )
    VALUES (?,?,?,?,?,?,?,?,?)
    ");

    $session->execute([
        $department,
        $semester,
        $section,
        $shift,
        $course_name,
        $course_code,
        $teacher,
        $class_date,
        $class_time
    ]);

    $session_id = $conn->lastInsertId();

    /* ========= Get Students ========= */

    $students = $conn->prepare("
    SELECT id
    FROM students
    WHERE semester = ?
    ORDER BY CAST(roll AS UNSIGNED) ASC
    ");

    $students->execute([$semester]);

    /* ========= Save Attendance Details ========= */

    $save = $conn->prepare("
    INSERT INTO attendance_details
    (
        session_id,
        student_id,
        status
    )
    VALUES
    (
        ?, ?, ?
    )
    ");

    foreach ($students as $student) {

        $status = in_array($student['id'], $present)
            ? "Present"
            : "Absent";

        $save->execute([
            $session_id,
            $student['id'],
            $status
        ]);
    }

    // Transaction Complete
    $conn->commit();

    header("Location: history.php?success=1");
    exit();

} catch (Exception $e) {

    // Error হলে সব Rollback হবে
    $conn->rollBack();

    die("Attendance Save Failed : " . $e->getMessage());
}

?>