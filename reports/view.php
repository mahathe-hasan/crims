<?php

require_once("../config/session.php");
require_once("../config/database.php");

if(!isset($_GET['id'])){
    header("Location: index.php");
    exit();
}

$id = (int)$_GET['id'];

/* Attendance Session */

$stmt = $conn->prepare("
SELECT *
FROM attendance_sessions
WHERE id=?
");

$stmt->execute([$id]);

$session = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$session){
    die("Report Not Found");
}

/* Present Student List */

$stmt = $conn->prepare("
SELECT
students.roll,
students.full_name,
attendance_details.status

FROM attendance_details

INNER JOIN students
ON students.id=attendance_details.student_id

WHERE attendance_details.session_id=?
AND attendance_details.status='Present'

ORDER BY students.roll ASC
");

$stmt->execute([$id]);

$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Statistics */

$total = $conn->prepare("
SELECT COUNT(*) FROM attendance_details
WHERE session_id=?
");

$total->execute([$id]);

$total_students = $total->fetchColumn();

$present = $conn->prepare("
SELECT COUNT(*) FROM attendance_details
WHERE session_id=?
AND status='Present'
");

$present->execute([$id]);

$total_present = $present->fetchColumn();

$total_absent = $total_students - $total_present;

include("../includes/header.php");
include("../includes/sidebar.php");
include("../includes/navbar.php");

?>

<div class="card shadow-sm border-0 rounded-4 bg-dark text-white">

<div class="card-header bg-success text-white py-3 px-4">
    <h3 class="mb-0 fw-bold"><i class="fa-solid fa-file-lines me-2"></i> Attendance Report</h3>
</div>

<div class="card-body p-4">

<h4 class="text-center fw-bold text-white">
    Dhaka Government Polytechnic Institute
</h4>

<h6 class="text-center mb-4 text-info fw-semibold">
    Department of Computer Science and Technology (CST)
</h6>

<div class="row g-3 bg-secondary bg-opacity-25 p-3 rounded-3 border border-secondary">

<div class="col-md-4">
    <span class="text-warning small fw-semibold">Department</span><br>
    <strong class="text-white"><?= htmlspecialchars($session['department'] ?? 'Computer Science and Technology') ?></strong>
</div>

<div class="col-md-4">
    <span class="text-warning small fw-semibold">Semester</span><br>
    <strong class="text-white"><?= htmlspecialchars($session['semester']) ?></strong>
</div>

<div class="col-md-4">
    <span class="text-warning small fw-semibold">Section</span><br>
    <strong class="text-white"><?= htmlspecialchars($session['section'] ?? 'N/A') ?></strong>
</div>

<div class="col-md-4">
    <span class="text-warning small fw-semibold">Shift</span><br>
    <strong class="text-white"><?= htmlspecialchars($session['shift'] ?? 'N/A') ?></strong>
</div>

<div class="col-md-4">
    <span class="text-warning small fw-semibold">Course Name</span><br>
    <strong class="text-white"><?= htmlspecialchars($session['course_name']) ?></strong>
</div>

<div class="col-md-4">
    <span class="text-warning small fw-semibold">Course Code</span><br>
    <strong class="text-white"><?= htmlspecialchars($session['course_code']) ?></strong>
</div>

<div class="col-md-4">
    <span class="text-warning small fw-semibold">Teacher</span><br>
    <strong class="text-white"><?= htmlspecialchars($session['teacher_name']) ?></strong>
</div>

<div class="col-md-4">
    <span class="text-warning small fw-semibold">Date</span><br>
    <strong class="text-white"><?= date("d-m-Y", strtotime($session['class_date'])) ?></strong>
</div>

<div class="col-md-4">
    <span class="text-warning small fw-semibold">Time</span><br>
    <strong class="text-white"><?= htmlspecialchars($session['class_time']) ?></strong>
</div>

</div>

<hr class="border-secondary my-4">

<div class="row text-center g-3">

<div class="col-md-4">
    <div class="alert alert-primary mb-0 py-3">
        <h3 class="fw-bold mb-1 text-dark"><?= $total_students ?></h3>
        <span class="fw-semibold text-dark">Total Students</span>
    </div>
</div>

<div class="col-md-4">
    <div class="alert alert-success mb-0 py-3">
        <h3 class="fw-bold mb-1 text-dark"><?= $total_present ?></h3>
        <span class="fw-semibold text-dark">Present</span>
    </div>
</div>

<div class="col-md-4">
    <div class="alert alert-danger mb-0 py-3">
        <h3 class="fw-bold mb-1 text-dark"><?= $total_absent ?></h3>
        <span class="fw-semibold text-dark">Absent</span>
    </div>
</div>

</div>

<h4 class="mt-4 fw-bold text-white">
    Present Student List
</h4>

<!-- টেবিল যেন টেনে দেখা না লাগে, সে জন্য fluid ও responsive করা হয়েছে -->
<div class="table-responsive w-100">
<table class="table table-dark table-striped table-hover align-middle border border-secondary mb-0">
<thead class="table-success text-dark">
<tr>
    <th style="width: 15%;" class="py-3">SL</th>
    <th style="width: 25%;" class="py-3">Roll</th>
    <th style="width: 45%;" class="py-3">Student Name</th>
    <th style="width: 15%;" class="text-center py-3">Status</th>
</tr>
</thead>
<tbody>
<?php
$i = 1;
if(count($students) > 0):
    foreach($students as $row){
?>
<tr>
    <td class="fw-semibold text-white"><?= $i++ ?></td>
    <td class="fw-bold text-warning"><?= htmlspecialchars($row['roll']) ?></td>
    <td class="text-white"><?= htmlspecialchars($row['full_name']) ?></td>
    <td class="text-center">
        <span class="badge bg-success px-3 py-2">Present</span>
    </td>
</tr>
<?php 
    }
else:
?>
<tr>
    <td colspan="4" class="text-center py-4 text-muted">No students present for this session.</td>
</tr>
<?php endif; ?>
</tbody>
</table>
</div>

<div class="d-flex gap-2 mt-4">
    <a href="../reports/index.php" class="btn btn-secondary px-4">
        ← Back
    </a>
    <a href="../attendance/pdf.php?id=<?= $id; ?>" class="btn btn-danger px-4">
        📄 Download PDF
    </a>
</div>

</div>

</div>

<?php include("../includes/footer.php"); ?>