<?php

require_once("../config/session.php");
require_once("../config/database.php");

if(!isset($_GET['id'])){
    header("Location: history.php");
    exit();
}

$id = $_GET['id'];

/* ========= Attendance Session ========= */

$session = $conn->prepare("
SELECT *
FROM attendance_sessions
WHERE id=?
");

$session->execute([$id]);

$info = $session->fetch();

if(!$info){
    die("Attendance Record Not Found.");
}

include("../includes/header.php");
include("../includes/sidebar.php");
include("../includes/navbar.php");
?>

<div class="card">

<div class="card-header bg-primary text-white">

<h4>Attendance Details</h4>

</div>

<div class="card-body">

<div class="row">

<div class="col-md-4 mb-3">
<b>Date:</b><br>
<?= date("d-m-Y", strtotime($info['class_date'])) ?>
</div>

<div class="col-md-4 mb-3">
<b>Time:</b><br>
<?= date("h:i A", strtotime($info['class_time'])) ?>
</div>

<div class="col-md-4 mb-3">
<b>Department:</b><br>
<?= htmlspecialchars($info['department'] ?? 'Computer Science and Technology') ?>
</div>

<div class="col-md-4 mb-3">
<b>Semester:</b><br>
<?= htmlspecialchars($info['semester']) ?>
</div>

<div class="col-md-4 mb-3">
<b>Section:</b><br>
<?= htmlspecialchars($info['section'] ?? '-') ?>
</div>

<div class="col-md-4 mb-3">
<b>Shift:</b><br>
<?= htmlspecialchars($info['shift'] ?? '-') ?>
</div>

<div class="col-md-6 mb-3">
<b>Course Name:</b><br>
<?= htmlspecialchars($info['course_name']) ?>
</div>

<div class="col-md-3 mb-3">
<b>Course Code:</b><br>
<?= htmlspecialchars($info['course_code']) ?>
</div>

<div class="col-md-3 mb-3">
<b>Teacher:</b><br>
<?= htmlspecialchars($info['teacher_name']) ?>
</div>

</div>

<hr>

<?php

$stmt = $conn->prepare("
SELECT
    students.roll,
    students.full_name,
    attendance_details.status

FROM attendance_details

INNER JOIN students
ON students.id = attendance_details.student_id

WHERE attendance_details.session_id = ?

ORDER BY CAST(students.roll AS UNSIGNED) ASC
");

$stmt->execute([$id]);

$attendance = $stmt->fetchAll();

$present = 0;
$absent = 0;

?>

<div class="table-responsive">

<table class="table table-bordered table-hover align-middle text-center">

<thead class="table-dark">

<tr>

<th>Roll</th>

<th>Student Name</th>

<th>Status</th>

</tr>

</thead>

<tbody>

<?php foreach($attendance as $row){ ?>

<tr>

<td><?= htmlspecialchars($row['roll']) ?></td>

<td class="text-start"><?= htmlspecialchars($row['full_name']) ?></td>

<td>

<?php

if($row['status']=="Present"){

    echo '<span class="badge bg-success">Present</span>';
    $present++;

}else{

    echo '<span class="badge bg-danger">Absent</span>';
    $absent++;

}

?>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

<hr>

<div class="row">

<div class="col-md-4">

<div class="card border-success">

<div class="card-body text-center">

<h5 class="text-success">Present</h5>

<h2><?= $present ?></h2>

</div>

</div>

</div>

<div class="col-md-4">

<div class="card border-danger">

<div class="card-body text-center">

<h5 class="text-danger">Absent</h5>

<h2><?= $absent ?></h2>

</div>

</div>

</div>

<div class="col-md-4">

<div class="card border-primary">

<div class="card-body text-center">

<h5 class="text-primary">Total Students</h5>

<h2><?= count($attendance) ?></h2>

</div>

</div>

</div>

</div>

<br>

<a href="history.php" class="btn btn-secondary">
    <i class="fa-solid fa-arrow-left"></i>
    Back to History
</a>

</div>

</div>

<?php include("../includes/footer.php"); ?>