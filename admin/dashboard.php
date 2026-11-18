<?php

require_once("../config/session.php");
require_once("../config/database.php");

/* ===========================
    Dashboard Statistics
=========================== */

// Total Students
$totalStudents = $conn->query("SELECT COUNT(*) FROM students")->fetchColumn();

// Today's Attendance
$today = date("Y-m-d");

$stmt = $conn->prepare("
SELECT id
FROM attendance_sessions
WHERE class_date=?
ORDER BY id DESC
LIMIT 1
");

$stmt->execute([$today]);

$session = $stmt->fetch(PDO::FETCH_ASSOC);

$presentToday = 0;
$absentToday = 0;
$attendanceRate = 0;

if($session){

    $session_id = $session['id'];

    $presentToday = $conn->prepare("
    SELECT COUNT(*)
    FROM attendance_details
    WHERE session_id=?
    AND status='Present'
    ");

    $presentToday->execute([$session_id]);

    $presentToday = $presentToday->fetchColumn();

    $absentToday = $conn->prepare("
    SELECT COUNT(*)
    FROM attendance_details
    WHERE session_id=?
    AND status='Absent'
    ");

    $absentToday->execute([$session_id]);

    $absentToday = $absentToday->fetchColumn();

    if(($presentToday+$absentToday)>0){

        $attendanceRate = round(
            ($presentToday/($presentToday+$absentToday))*100,
            1
        );

    }

}
/* ===========================
    Recent Attendance
=========================== */

$recentAttendance = $conn->query("
SELECT
    course_name,
    semester,
    class_date,
    teacher_name
FROM attendance_sessions
ORDER BY id DESC
LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
/* ===========================
    Semester Overview
=========================== */

$semesterData = $conn->query("
SELECT
semester,
COUNT(*) as total
FROM students
GROUP BY semester
ORDER BY semester ASC
")->fetchAll(PDO::FETCH_ASSOC);

include("../includes/header.php");
include("../includes/sidebar.php");
include("../includes/navbar.php");

?>

<div class="row g-4">

    <!-- Total Students -->
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-lg text-white"
            style="background:linear-gradient(135deg,#2563eb,#1d4ed8); border-radius:18px;">

            <div class="card-body d-flex justify-content-between align-items-center">

                <div>
                    <h2><?= $totalStudents ?></h2>
                    <p class="mb-0">Total Students</p>
                </div>

                <i class="fa-solid fa-user-graduate fa-3x opacity-75"></i>

            </div>

        </div>
    </div>

    <!-- Present -->
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-lg text-white"
            style="background:linear-gradient(135deg,#16a34a,#15803d); border-radius:18px;">

            <div class="card-body d-flex justify-content-between align-items-center">

                <div>
                    <h2><?= $presentToday ?></h2>
                    <p class="mb-0">Present Today</p>
                </div>

                <i class="fa-solid fa-circle-check fa-3x opacity-75"></i>

            </div>

        </div>
    </div>

    <!-- Absent -->
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-lg text-white"
            style="background:linear-gradient(135deg,#dc2626,#b91c1c); border-radius:18px;">

            <div class="card-body d-flex justify-content-between align-items-center">

                <div>
                    <h2><?= $absentToday ?></h2>
                    <p class="mb-0">Absent Today</p>
                </div>

                <i class="fa-solid fa-user-xmark fa-3x opacity-75"></i>

            </div>

        </div>
    </div>

    <!-- Attendance -->
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-lg text-white"
            style="background:linear-gradient(135deg,#7c3aed,#5b21b6); border-radius:18px;">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center">

                    <div>
                        <h2><?= $attendanceRate ?>%</h2>
                        <p class="mb-2">Attendance Rate</p>
                    </div>

                    <i class="fa-solid fa-chart-line fa-3x opacity-75"></i>

                </div>

                <div class="progress" style="height:8px;">
                    <div class="progress-bar bg-light"
                        style="width:<?= $attendanceRate ?>%">
                    </div>
                </div>

            </div>

        </div>
    </div>

</div>

<div class="card border-0 shadow-lg my-4"
    style="border-radius:20px;
    background:linear-gradient(135deg,#0d6efd,#3b82f6);
    color:white;">

    <div class="card-body p-4">

        <div class="row align-items-center">

            <div class="col-md-8">

                <h2 class="fw-bold">
                    Welcome,
                    <?= $_SESSION['admin_name']; ?> 👋
                </h2>

                <p class="mb-1 fs-5">
                    Class Record & Information Management System
                </p>

                <small>

                    Dhaka Government Polytechnic Institute

                    <br>

                    <div class="mt-2">

    <p class="mb-1">
        <strong>Date :</strong>
        <?= date("d F Y"); ?>
    </p>

    <p class="mb-0">
        <strong>Time :</strong>
        <?= date("h:i A"); ?>
    </p>

</div>

                </small>

            </div>

            <div class="col-md-4 text-end d-none d-md-block">

                <i class="fa-solid fa-school fa-5x opacity-50"></i>

            </div>

        </div>

    </div>

</div>

<!-- Calendar Button (Welcome কার্ডের নিচে যুক্ত করা হলো) -->
<div class="mb-4">
    <a href="../attendance/calendar.php" class="btn btn-dark w-100 py-3 shadow-sm fw-bold d-flex align-items-center justify-content-center gap-2" style="border-radius: 14px; background: linear-gradient(135deg, #1e293b, #0f172a); border: none;">
        <i class="fa-solid fa-calendar-days fa-lg text-success"></i>
        <span>View Attendance Calendar</span>
    </a>
</div>

<div class="row mb-4 g-3">

<div class="col-6 col-md-3">

<a href="../students/add.php"
class="btn btn-primary w-100 py-3 shadow-sm rounded-4">

<i class="fa-solid fa-user-plus fa-lg"></i>

<br>

<span class="small fw-semibold">Add Student</span>

</a>

</div>

<div class="col-6 col-md-3">

<a href="../attendance/index.php"
class="btn btn-success w-100 py-3 shadow-sm rounded-4">

<i class="fa-solid fa-calendar-check fa-lg"></i>

<br>

<span class="small fw-semibold">Take Attendance</span>

</a>

</div>

<div class="col-6 col-md-3">

<a href="../attendance/history.php"
class="btn btn-warning text-white w-100 py-3 shadow-sm rounded-4">

<i class="fa-solid fa-clock-rotate-left fa-lg"></i>

<br>

<span class="small fw-semibold">Attendance History</span>

</a>

</div>

<div class="col-6 col-md-3">

<a href="../reports/index.php"
class="btn btn-danger w-100 py-3 shadow-sm rounded-4">

<i class="fa-solid fa-file-pdf fa-lg"></i>

<br>

<span class="small fw-semibold">Reports</span>

</a>

</div>

</div>

<div class="card border-0 shadow-lg mt-4" style="border-radius:18px;">

    <div class="card-header bg-white border-0 pt-4">
        <h4 class="fw-bold mb-0">
            <i class="fa-solid fa-clock-rotate-left text-primary"></i>
            Recent Attendance
        </h4>
    </div>

    <div class="card-body">

        <?php if(count($recentAttendance)>0){ ?>

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead class="table-primary">

                        <tr>
                            <th>Course</th>
                            <th>Semester</th>
                            <th>Date</th>
                            <th>Teacher</th>
                        </tr>

                    </thead>

                    <tbody>

                    <?php foreach($recentAttendance as $row){ ?>

                        <tr>

                            <td><?= htmlspecialchars($row['course_name']) ?></td>

                            <td><?= htmlspecialchars($row['semester']) ?></td>

                            <td><?= date("d M Y",strtotime($row['class_date'])) ?></td>

                            <td><?= htmlspecialchars($row['teacher_name']) ?></td>

                        </tr>

                    <?php } ?>

                    </tbody>

                </table>

            </div>

        <?php } else { ?>

            <div class="alert alert-info mb-0">
                No attendance records found.
            </div>

        <?php } ?>

    </div>

</div>

<div class="card border-0 shadow-lg mt-4 mb-4" style="border-radius:18px;">

    <div class="card-header bg-white border-0">
        <h4 class="fw-bold mb-0">
            <i class="fa-solid fa-chart-column text-success"></i>
            Semester Overview
        </h4>
    </div>

    <div class="card-body">

        <?php if(count($semesterData)>0){ ?>

            <?php foreach($semesterData as $row){ ?>

                <div class="mb-3">

                    <div class="d-flex justify-content-between">

                        <strong><?= htmlspecialchars($row['semester']) ?></strong>

                        <span><?= $row['total'] ?> Students</span>

                    </div>

                    <div class="progress mt-2" style="height:10px;">

                        <div
                            class="progress-bar bg-success"
                            style="width:<?= min($row['total']*5,100) ?>%">
                        </div>

                    </div>

                </div>

            <?php } ?>

        <?php }else{ ?>

            <div class="alert alert-warning mb-0">
                No semester data found.
            </div>

        <?php } ?>

    </div>

</div>

<?php

include("../includes/footer.php");

?>