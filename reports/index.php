<?php

require_once("../config/session.php");
require_once("../config/database.php");

include("../includes/header.php");
include("../includes/sidebar.php");
include("../includes/navbar.php");

$sql = "
SELECT *
FROM attendance_sessions
ORDER BY class_date DESC, id DESC
";

$params = [];

if (!empty($_GET['semester'])) {
    $sql = "SELECT * FROM attendance_sessions WHERE semester = ? ORDER BY class_date DESC, id DESC";
    $params[] = $_GET['semester'];
}

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$reports = $stmt->fetchAll();

?>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">

<div class="card-header bg-primary text-white py-3 px-4 d-flex justify-content-between align-items-center">
    <h4 class="mb-0 fw-bold"><i class="fa-solid fa-clipboard-list me-2"></i> Attendance Reports</h4>
</div>

<div class="card-body p-4">

<form method="GET" class="mb-4">

<div class="row align-items-end g-3">

<div class="col-md-4">

<label class="form-label fw-semibold text-secondary">Filter by Semester</label>

<select name="semester" class="form-select rounded-3 py-2">

<option value="">All Semester</option>

<option value="1st Semester" <?= (($_GET['semester'] ?? '') == '1st Semester') ? 'selected' : '' ?>>1st Semester</option>
<option value="2nd Semester" <?= (($_GET['semester'] ?? '') == '2nd Semester') ? 'selected' : '' ?>>2nd Semester</option>
<option value="3rd Semester" <?= (($_GET['semester'] ?? '') == '3rd Semester') ? 'selected' : '' ?>>3rd Semester</option>
<option value="4th Semester" <?= (($_GET['semester'] ?? '') == '4th Semester') ? 'selected' : '' ?>>4th Semester</option>
<option value="5th Semester" <?= (($_GET['semester'] ?? '') == '5th Semester') ? 'selected' : '' ?>>5th Semester</option>
<option value="6th Semester" <?= (($_GET['semester'] ?? '') == '6th Semester') ? 'selected' : '' ?>>6th Semester</option>
<option value="7th Semester" <?= (($_GET['semester'] ?? '') == '7th Semester') ? 'selected' : '' ?>>7th Semester</option>
<option value="8th Semester" <?= (($_GET['semester'] ?? '') == '8th Semester') ? 'selected' : '' ?>>8th Semester</option>

</select>

</div>

<div class="col-md-2">

<button class="btn btn-primary w-100 py-2 rounded-3 fw-semibold">
    <i class="fa-solid fa-filter me-1"></i> Search
</button>

</div>

</div>

</form>

<!-- Desktop View (Table Layout) -->
<div class="table-responsive d-none d-md-block">
<table class="table table-hover align-middle border">
<thead class="table-dark text-uppercase fs-7">
<tr>
<th class="py-3 px-3">Date</th>
<th class="py-3">Semester & Dept</th>
<th class="py-3">Sec / Shift</th>
<th class="py-3">Subject Name</th>
<th class="py-3">Teacher Name</th>
<th class="py-3 text-center" width="260">Action</th>
</tr>
</thead>
<tbody>
<?php if(count($reports) > 0): foreach($reports as $row){ ?>
<tr>
<td class="px-3 fw-semibold text-white"><?= date("d M, Y", strtotime($row['class_date'])) ?></td>
<td>
    <span class="badge bg-light text-dark border px-2 py-1 mb-1"><?= htmlspecialchars($row['semester']) ?></span><br>
    <small class="text-muted" style="font-size: 11px;"><?= htmlspecialchars($row['department'] ?? 'Computer Science and Technology') ?></small>
</td>
<td>
    <span class="badge bg-info text-dark px-2 py-1">Sec: <?= htmlspecialchars($row['section'] ?? 'N/A') ?></span>
    <span class="badge bg-secondary px-2 py-1"><?= htmlspecialchars($row['shift'] ?? 'N/A') ?></span>
</td>
<td class="fw-bold text-primary"><?= htmlspecialchars($row['course_name']) ?></td>
<td class="fw-bold text-white"><i class="fa-solid fa-user-tie text-white-50 me-1"></i> <?= htmlspecialchars($row['teacher_name']) ?></td>
<td class="text-center">
<div class="d-flex justify-content-center gap-1">
<a href="view.php?id=<?= $row['id']; ?>" class="btn btn-info btn-sm text-white px-2"><i class="fa-solid fa-eye"></i> View</a>
<a href="../attendance/edit.php?id=<?= $row['id']; ?>" class="btn btn-warning btn-sm px-2 text-dark"><i class="fa-solid fa-pen-to-square"></i> Edit</a>
<a href="../attendance/pdf.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm px-2"><i class="fa-solid fa-file-pdf"></i> PDF</a>
<a href="../attendance/delete.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm px-2" onclick="return confirm('Are you sure you want to delete this report?');"><i class="fa-solid fa-trash"></i> Delete</a>
</div>
</td>
</tr>
<?php } else: ?>
<tr><td colspan="6" class="text-center py-4 text-muted">No attendance reports found.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>

<!-- Mobile View (Stacked Card Layout) -->
<div class="d-block d-md-none">
<?php if(count($reports) > 0): ?>
    <?php foreach($reports as $row): ?>
    <div class="card mb-3 border-0 shadow-sm rounded-3 bg-dark text-white p-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
                <span class="badge bg-primary px-2 py-1"><?= htmlspecialchars($row['semester']) ?></span>
                <span class="badge bg-info text-dark px-2 py-1">Sec: <?= htmlspecialchars($row['section'] ?? 'N/A') ?></span>
                <span class="badge bg-secondary px-2 py-1"><?= htmlspecialchars($row['shift'] ?? 'N/A') ?></span>
            </div>
            <span class="text-white small fw-semibold"><i class="fa-regular fa-calendar-days me-1"></i> <?= date("d M, Y", strtotime($row['class_date'])) ?></span>
        </div>
        
        <div class="small text-muted mb-1"><?= htmlspecialchars($row['department'] ?? 'Computer Science and Technology') ?></div>
        <h5 class="fw-bold text-info mb-1">Subject: <?= htmlspecialchars($row['course_name']) ?></h5>
        
        <p class="mb-3 text-white small">
            <i class="fa-solid fa-chalkboard-user text-info me-1"></i> Teacher: <span class="fw-bold text-white"><?= htmlspecialchars($row['teacher_name']) ?></span>
        </p>

        <div class="row g-2 pt-2 border-top border-secondary">
            <div class="col-6">
                <a href="view.php?id=<?= $row['id']; ?>" class="btn btn-info btn-sm text-white w-100"><i class="fa-solid fa-eye me-1"></i> View</a>
            </div>
            <div class="col-6">
                <a href="../attendance/edit.php?id=<?= $row['id']; ?>" class="btn btn-warning btn-sm text-dark fw-semibold w-100"><i class="fa-solid fa-pen-to-square me-1"></i> Edit</a>
            </div>
            <div class="col-6">
                <a href="../attendance/pdf.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm w-100"><i class="fa-solid fa-file-pdf me-1"></i> PDF</a>
            </div>
            <div class="col-6">
                <a href="../attendance/delete.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm w-100" onclick="return confirm('Are you sure?');"><i class="fa-solid fa-trash me-1"></i> Delete</a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="text-center text-muted py-4">No attendance reports found.</div>
<?php endif; ?>
</div>

</div>

</div>

<?php include("../includes/footer.php"); ?>