<?php
require_once("../config/session.php");
require_once("../config/database.php");

include("../includes/header.php");
include("../includes/sidebar.php");
include("../includes/navbar.php");

$routineStmt = $conn->query("SELECT * FROM routines ORDER BY FIELD(day_name, 'Sunday','Monday','Tuesday','Wednesday','Thursday'), period_no");
$routines = $routineStmt ? $routineStmt->fetchAll() : [];
?>

<div class="container-fluid px-2 px-md-4 py-3 text-white">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0"><i class="fa-solid fa-calendar-days text-primary me-2"></i> Class Routine Management</h3>
        <a href="add_routine.php" class="btn btn-primary btn-sm fw-semibold shadow-sm"><i class="fa-solid fa-plus me-1"></i> Add Routine Slot</a>
    </div>

    <div class="card border-0 shadow-lg rounded-4 overflow-hidden" style="background-color: #1e293b !important; color: #f8fafc !important;">
        <div class="card-body p-3 p-md-4">
            <?php if(count($routines) > 0): ?>
                <!-- Desktop View Table (কম্পিউটার বা বড় স্ক্রিনের জন্য টেবিল) -->
                <div class="table-responsive d-none d-lg-block">
                    <table class="table table-hover align-middle mb-0 text-center" style="color: #f8fafc !important; background-color: #1e293b !important; border-color: rgba(255, 255, 255, 0.15) !important;">
                        <thead style="background-color: #1a2332 !important; color: #f8fafc !important;">
                            <tr style="border-color: rgba(255, 255, 255, 0.15) !important;">
                                <th style="background-color: #1a2332 !important; color: #f8fafc !important; border-color: rgba(255, 255, 255, 0.15) !important;">Day</th>
                                <th style="background-color: #1a2332 !important; color: #f8fafc !important; border-color: rgba(255, 255, 255, 0.15) !important;">Period</th>
                                <th style="background-color: #1a2332 !important; color: #f8fafc !important; border-color: rgba(255, 255, 255, 0.15) !important;">Time</th>
                                <th style="background-color: #1a2332 !important; color: #f8fafc !important; border-color: rgba(255, 255, 255, 0.15) !important;">Subject Details</th>
                                <th style="background-color: #1a2332 !important; color: #f8fafc !important; border-color: rgba(255, 255, 255, 0.15) !important;">Teacher Code</th>
                                <th style="background-color: #1a2332 !important; color: #f8fafc !important; border-color: rgba(255, 255, 255, 0.15) !important;">Room No</th>
                                <th style="background-color: #1a2332 !important; color: #f8fafc !important; border-color: rgba(255, 255, 255, 0.15) !important;">Duration Span</th>
                                <th width="140" style="background-color: #1a2332 !important; color: #f8fafc !important; border-color: rgba(255, 255, 255, 0.15) !important;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($routines as $r): ?>
                            <tr style="border-color: rgba(255, 255, 255, 0.15) !important;">
                                <td class="fw-bold" style="color: #f8fafc !important; border-color: rgba(255, 255, 255, 0.15) !important; background-color: transparent !important;"><?= htmlspecialchars($r['day_name']) ?></td>
                                <td style="color: #f8fafc !important; border-color: rgba(255, 255, 255, 0.15) !important; background-color: transparent !important;">Period <?= htmlspecialchars($r['period_no']) ?></td>
                                <td style="color: #f8fafc !important; border-color: rgba(255, 255, 255, 0.15) !important; background-color: transparent !important;"><?= htmlspecialchars($r['time_slot']) ?></td>
                                <td class="text-start" style="border-color: rgba(255, 255, 255, 0.15) !important; background-color: transparent !important;">
                                    <div><span class="text-white-50 small">Subject Code:</span> <strong class="text-primary"><?= htmlspecialchars($r['subject_code']) ?></strong></div>
                                    <?php if(!empty($r['subject_name'])): ?>
                                        <div class="mt-1"><span class="text-white-50 small">Subject Name:</span> <span class="text-white"><?= htmlspecialchars($r['subject_name']) ?></span></div>
                                    <?php endif; ?>
                                </td>
                                <td style="color: #f8fafc !important; border-color: rgba(255, 255, 255, 0.15) !important; background-color: transparent !important;"><?= htmlspecialchars($r['teacher_code']) ?></td>
                                <td style="color: #f8fafc !important; border-color: rgba(255, 255, 255, 0.15) !important; background-color: transparent !important;"><?= htmlspecialchars($r['room_no']) ?></td>
                                <td style="border-color: rgba(255, 255, 255, 0.15) !important; background-color: transparent !important;"><span class="badge bg-info text-dark"><?= (int)$r['period_span'] ?> Period(s)</span></td>
                                <td style="border-color: rgba(255, 255, 255, 0.15) !important; background-color: transparent !important;">
                                    <a href="edit_routine.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-warning text-dark me-1" title="Edit">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <a href="delete_routine.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this period?')" title="Delete">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile & Tablet View Cards (মোবাইল ও ট্যাবলেটের জন্য কার্ড) -->
                <div class="row g-3 d-lg-none">
                    <?php foreach($routines as $r): ?>
                    <div class="col-12 col-md-6">
                        <div class="card border border-secondary shadow-sm rounded-3 h-100 text-white" style="background-color: #26354a !important;">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-primary fs-6"><?= htmlspecialchars($r['day_name']) ?></span>
                                    <span class="badge bg-info text-dark">Period <?= htmlspecialchars($r['period_no']) ?></span>
                                </div>
                                
                                <div class="mb-2">
                                    <div class=" small text-white-50">Subject Code: <span class="text-white fw-bold"><?= htmlspecialchars($r['subject_code']) ?></span></div>
                                    <?php if(!empty($r['subject_name'])): ?>
                                        <div class="small text-white-50 mt-1">Subject Name: <strong class="text-white"><?= htmlspecialchars($r['subject_name']) ?></strong></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="mb-3 small text-white-50">
                                    <div>Time: <strong class="text-white"><?= htmlspecialchars($r['time_slot']) ?></strong></div>
                                    <div>Teacher Code: <strong class="text-white"><?= htmlspecialchars($r['teacher_code']) ?></strong></div>
                                    <div>Room No: <strong class="text-white"><?= htmlspecialchars($r['room_no']) ?></strong></div>
                                    <div>Duration: <span class="badge bg-secondary mt-1"><?= (int)$r['period_span'] ?> Period(s)</span></div>
                                </div>

                                <div class="d-flex justify-content-end gap-1 border-top border-secondary pt-2 flex-wrap">
                                    <a href="edit_routine.php?id=<?= $r['id'] ?>" class="btn btn-warning btn-sm text-dark fw-bold">
                                        <i class="fa-solid fa-pen-to-square me-1"></i>Edit
                                    </a>
                                    <a href="delete_routine.php?id=<?= $r['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this period?')">
                                        <i class="fa-solid fa-trash me-1"></i>Delete
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

            <?php else: ?>
                <div class="text-center text-muted py-4">No routine entries found.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include("../includes/footer.php"); ?>