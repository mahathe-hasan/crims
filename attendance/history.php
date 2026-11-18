<?php

require_once("../config/session.php");
require_once("../config/database.php");

include("../includes/header.php");
include("../includes/sidebar.php");
include("../includes/navbar.php");

$stmt = $conn->query("
SELECT
    attendance_sessions.*,

    (
        SELECT COUNT(*)
        FROM attendance_details
        WHERE attendance_details.session_id = attendance_sessions.id
        AND status='Present'
    ) AS present_count,

    (
        SELECT COUNT(*)
        FROM attendance_details
        WHERE attendance_details.session_id = attendance_sessions.id
        AND status='Absent'
    ) AS absent_count

FROM attendance_sessions

ORDER BY class_date DESC, id DESC
");

$sessions = $stmt->fetchAll();

?>

<div class="container-fluid px-2 px-md-4 py-3">
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden bg-dark text-white">
        <div class="card-header bg-success text-white py-3 px-4">
            <h4 class="mb-0 fw-bold"><i class="fa-solid fa-clock-rotate-left me-2"></i>Attendance History</h4>
        </div>

        <div class="card-body p-3 p-md-4">

            <?php if(isset($_GET['success'])){ ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    Attendance Saved Successfully.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php } ?>

            <?php if(isset($_GET['deleted'])){ ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    Attendance Deleted Successfully.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php } ?>

            <?php
            if(empty($sessions)) {
                echo '<div class="text-center text-muted py-4">No attendance records found.</div>';
            } else {
            ?>
            <!-- Desktop View Table -->
            <div class="table-responsive d-none d-lg-block">
                <table class="table table-dark table-bordered table-hover align-middle text-center mb-0">
                    <thead class="table-secondary text-dark text-nowrap">
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Semester</th>
                            <th>Section / Shift</th>
                            <th class="text-start">Course Name</th>
                            <th>Course Code</th>
                            <th>Teacher</th>
                            <th>Present</th>
                            <th>Absent</th>
                            <th style="min-width: 170px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $i = 1;
                        foreach($sessions as $row){
                        ?>
                        <tr>
                            <td class="fw-bold"><?= $i++ ?></td>
                            <td class="text-nowrap"><?= date("d-m-Y", strtotime($row['class_date'])) ?></td>
                            <td class="text-nowrap"><?= date("h:i A", strtotime($row['class_time'])) ?></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($row['semester']) ?></span></td>
                            <td class="small text-nowrap">
                                Sec: <?= htmlspecialchars($row['section'] ?? '-') ?><br>
                                Shift: <?= htmlspecialchars($row['shift'] ?? '-') ?>
                            </td>
                            <td class="text-start fw-semibold"><?= htmlspecialchars($row['course_name']) ?></td>
                            <td><?= htmlspecialchars($row['course_code']) ?></td>
                            <td><?= htmlspecialchars($row['teacher_name']) ?></td>
                            <td>
                                <span class="badge bg-success px-2 py-1"><?= $row['present_count']; ?></span>
                            </td>
                            <td>
                                <span class="badge bg-danger px-2 py-1"><?= $row['absent_count']; ?></span>
                            </td>
                            <td>
                                <div class="d-flex justify-content-center gap-1 flex-wrap">
                                    <a href="view.php?id=<?= $row['id'] ?>" class="btn btn-info btn-sm text-white" title="View"><i class="fa-solid fa-eye"></i></a>
                                    <a href="../attendance/edit.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm text-dark" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>
                                    <a href="pdf.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" title="PDF"><i class="fa-solid fa-file-pdf"></i></a>
                                    <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-dark btn-sm" onclick="return confirm('Delete this attendance?')" title="Delete"><i class="fa-solid fa-trash me-1"></i>Delete</a>
                                </div>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile & Tablet View Cards -->
            <div class="row g-3 d-lg-none">
                <?php
                $i = 1;
                foreach($sessions as $row){
                ?>
                <div class="col-12 col-md-6">
                    <div class="card border border-secondary shadow-sm rounded-3 h-100 bg-secondary text-white">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-primary">#<?= $i++ ?></span>
                                <span class="text-white-50 small"><i class="fa-regular fa-calendar me-1"></i><?= date("d-m-Y", strtotime($row['class_date'])) ?> (<?= date("h:i A", strtotime($row['class_time'])) ?>)</span>
                            </div>
                            
                            <h5 class="card-title fw-bold text-white mb-1"><?= htmlspecialchars($row['course_name']) ?></h5>
                            <p class="text-white-50 small mb-2">Code: <strong class="text-white"><?= htmlspecialchars($row['course_code'] ?? 'N/A') ?></strong></p>
                            
                            <div class="mb-2 small text-white">
                                <div><strong>Semester:</strong> <?= htmlspecialchars($row['semester']) ?></div>
                                <div><strong>Section:</strong> <?= htmlspecialchars($row['section'] ?? '-') ?> | <strong>Shift:</strong> <?= htmlspecialchars($row['shift'] ?? '-') ?></div>
                                <div><strong>Teacher:</strong> <?= htmlspecialchars($row['teacher_name']) ?></div>
                            </div>

                            <div class="d-flex gap-2 mb-3">
                                <span class="badge bg-success flex-fill py-2 fs-6">Present: <?= $row['present_count']; ?></span>
                                <span class="badge bg-danger flex-fill py-2 fs-6">Absent: <?= $row['absent_count']; ?></span>
                            </div>

                            <div class="d-flex justify-content-end gap-1 border-top border-secondary pt-2 flex-wrap">
                                <a href="view.php?id=<?= $row['id'] ?>" class="btn btn-info btn-sm text-white"><i class="fa-solid fa-eye me-1"></i>View</a>
                                <a href="../attendance/edit.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm text-dark fw-bold"><i class="fa-solid fa-pen-to-square me-1"></i>Edit</a>
                                <a href="pdf.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm"><i class="fa-solid fa-file-pdf me-1"></i>PDF</a>
                                <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-dark btn-sm text-white border border-light" onclick="return confirm('Delete this attendance?')"><i class="fa-solid fa-trash me-1"></i>Delete</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
            <?php } ?>

        </div>
    </div>
</div>

<?php include("../includes/footer.php"); ?>