<?php

require_once("../config/session.php");
require_once("../config/database.php");

include("../includes/header.php");
include("../includes/sidebar.php");
include("../includes/navbar.php");

$stmt = $conn->query("
SELECT
    s.id,
    s.student_id,
    s.full_name,
    s.roll,

    COUNT(ad.id) AS total_class,

    SUM(
        CASE
            WHEN ad.status='Present'
            THEN 1
            ELSE 0
        END
    ) AS total_present

FROM students s

LEFT JOIN attendance_details ad

ON s.id=ad.student_id

GROUP BY s.id

ORDER BY s.roll ASC
");

$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="d-flex justify-content-between align-items-center mb-4">

    <h3>
        <i class="fa-solid fa-chart-column text-primary"></i>
        Attendance Analytics
    </h3>

</div>
<?php

$total_students = count($students);

$excellent = 0;
$average = 0;
$poor = 0;

foreach($students as $s){

    $total = $s['total_class'];
    $present = $s['total_present'];

    $percent = ($total>0)
        ? round(($present/$total)*100)
        : 0;

    if($percent >= 80){
        $excellent++;
    }elseif($percent >= 60){
        $average++;
    }else{
        $poor++;
    }
}

?>

<div class="row mb-4">

<div class="col-md-3 mb-3">
<div class="card shadow-sm border-0">
<div class="card-body text-center">
<h6>Total Students</h6>
<h2><?= $total_students ?></h2>
</div>
</div>
</div>

<div class="col-md-3 mb-3">
<div class="card shadow-sm border-0 bg-success text-white">
<div class="card-body text-center">
<h6>Excellent</h6>
<h2><?= $excellent ?></h2>
</div>
</div>
</div>

<div class="col-md-3 mb-3">
<div class="card shadow-sm border-0 bg-warning">
<div class="card-body text-center">
<h6>Average</h6>
<h2><?= $average ?></h2>
</div>
</div>
</div>

<div class="col-md-3 mb-3">
<div class="card shadow-sm border-0 bg-danger text-white">
<div class="card-body text-center">
<h6>Poor</h6>
<h2><?= $poor ?></h2>
</div>
</div>
</div>

</div>

<div class="card">

<div class="card-body">
    <div class="mb-3">

<input
type="text"
id="searchAttendance"
class="form-control"
placeholder="🔍 Search Student by Name or Roll">

</div>

<table class="table table-bordered table-hover align-middle">

<thead class="table-primary">

<tr>

<th>Roll</th>

<th>Student Name</th>

<th>Total Class</th>

<th>Present</th>

<th>Attendance %</th>

</tr>

</thead>

<tbody>

<?php foreach($students as $row){

$total = $row['total_class'];

$present = $row['total_present'];

$percent = ($total>0)
? round(($present/$total)*100)
:0;

?>

<tr>

<td><?= $row['roll']; ?></td>

<td><?= $row['full_name']; ?></td>

<td><?= $total; ?></td>

<td><?= $present; ?></td>

<td>

<?php

if($percent >= 80){

    $badge = "success";
    $text = "Excellent";

}elseif($percent >= 60){

    $badge = "warning";
    $text = "Average";

}else{

    $badge = "danger";
    $text = "Poor";

}

?>

<span class="badge bg-<?= $badge; ?>">
    <?= $percent; ?>%
</span>

<div class="progress mt-2" style="height:8px;">

    <div
        class="progress-bar bg-<?= $badge; ?>"
        style="width:<?= $percent; ?>%;">

    </div>

</div>

<small class="text-muted">

<?= $text; ?>

</small>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>

<?php include("../includes/footer.php"); ?>