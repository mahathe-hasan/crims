<?php

require_once("../config/session.php");
require_once("../config/database.php");

$stmt = $conn->query("
SELECT *
FROM notices
ORDER BY notice_date DESC
");

$notices = $stmt->fetchAll();

include("../includes/header.php");
include("../includes/sidebar.php");
include("../includes/navbar.php");

// ক্যাটাগরি অনুযায়ী ডাইনামিক ব্যাজ ক্লাস ফাংশন
function getAdminBadgeClass($catName) {
    $c = strtolower(trim($catName ?? 'notice'));
    if (in_array($c, ['job sheet', 'jobsheet'])) return 'badge-jobsheet';
    if (in_array($c, ['class test', 'classtest', 'ct'])) return 'badge-class-test';
    if (in_array($c, ['quiz test', 'quiztest'])) return 'badge-quiz-test';
    if ($c == 'presentation') return 'badge-presentation';
    if ($c == 'assignment') return 'badge-assignment';
    if ($c == 'practical') return 'badge-practical';
    if ($c == 'sessional') return 'badge-sessional';
    if ($c == 'practical exam') return 'badge-practical-exam';
    if ($c == 'mid exam') return 'badge-mid-exam';
    if ($c == 'semester final') return 'badge-semester-final';
    if (in_array($c, ['class starts', 'class-starts'])) return 'badge-class-starts';
    if (in_array($c, ['class off', 'class-off', 'holiday'])) return 'badge-class-off';
    
    // নতুন ৭টি ক্যাটাগরি
    if ($c == 'event') return 'badge-event';
    if (in_array($c, ['urgent notice', 'urgentnotice', 'urgent'])) return 'badge-urgent-notice';
    if (in_array($c, ['class related', 'classrelated'])) return 'badge-class-related';
    if (in_array($c, ['academic notice', 'academicnotice', 'academic'])) return 'badge-academic-notice';
    if (in_array($c, ['general notice', 'generalnotice', 'general'])) return 'badge-general-notice';
    if ($c == 'result') return 'badge-result';
    if ($c == 'others') return 'badge-others';

    return 'badge-default';
}
?>

<style>
/* ক্যাটাগরি ব্যাজ ডায়নামিক কালারসমূহ */
.notice-badge {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.03em;
    text-transform: uppercase;
    padding: 5px 12px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
}

.badge-default      { background: rgba(37, 99, 235, 0.18); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.35); }
.badge-jobsheet     { background: rgba(6, 182, 212, 0.18); color: #22d3ee; border: 1px solid rgba(6, 182, 212, 0.35); }
.badge-class-test   { background: rgba(249, 115, 22, 0.18); color: #fb923c; border: 1px solid rgba(249, 115, 22, 0.35); }
.badge-quiz-test    { background: rgba(16, 185, 129, 0.18); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.35); }
.badge-presentation { background: rgba(168, 85, 247, 0.18); color: #c084fc; border: 1px solid rgba(168, 85, 247, 0.35); }
.badge-assignment   { background: rgba(99, 102, 241, 0.18); color: #818cf8; border: 1px solid rgba(99, 102, 241, 0.35); }
.badge-practical    { background: rgba(236, 72, 153, 0.18); color: #f472b6; border: 1px solid rgba(236, 72, 153, 0.35); }
.badge-sessional    { background: rgba(234, 179, 8, 0.18); color: #facc15; border: 1px solid rgba(234, 179, 8, 0.35); }
.badge-practical-exam { background: rgba(139, 92, 246, 0.22); color: #c4b5fd; border: 1px solid rgba(139, 92, 246, 0.4); }
.badge-mid-exam     { background: rgba(37, 99, 235, 0.22); color: #60a5fa; border: 1px solid rgba(37, 99, 235, 0.4); }
.badge-semester-final { background: rgba(22, 163, 74, 0.22); color: #4ade80; border: 1px solid rgba(22, 163, 74, 0.4); }
.badge-class-starts { background: rgba(100, 116, 139, 0.18); color: #cbd5e1; border: 1px solid rgba(100, 116, 139, 0.35); }
.badge-class-off    { background: rgba(220, 38, 38, 0.22); color: #f87171; border: 1px solid rgba(220, 38, 38, 0.4); }

/* নতুন ৭টি ক্যাটাগরির ব্যাজ স্টাইল */
.badge-event           { background: rgba(217, 70, 239, 0.18); color: #e879f9; border: 1px solid rgba(217, 70, 239, 0.35); }
.badge-urgent-notice   { background: rgba(239, 68, 68, 0.22); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.4); }
.badge-class-related   { background: rgba(14, 165, 233, 0.18); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.35); }
.badge-academic-notice { background: rgba(59, 130, 246, 0.18); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.35); }
.badge-general-notice  { background: rgba(16, 185, 129, 0.18); color: #6ee7b7; border: 1px solid rgba(16, 185, 129, 0.35); }
.badge-result          { background: rgba(245, 158, 11, 0.18); color: #fcd34d; border: 1px solid rgba(245, 158, 11, 0.35); }
.badge-others          { background: rgba(107, 114, 128, 0.18); color: #d1d5db; border: 1px solid rgba(107, 114, 128, 0.35); }
</style>

<div class="container-fluid px-2 px-md-4 py-3">

<?php if(isset($_GET['success'])){ ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="fa-solid fa-circle-check me-1"></i> Notice added successfully.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php } ?>

<?php if(isset($_GET['updated'])){ ?>
<div class="alert alert-warning alert-dismissible fade show">
    <i class="fa-solid fa-pen-to-square me-1"></i> Notice updated successfully.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php } ?>

<?php if(isset($_GET['deleted'])){ ?>
<div class="alert alert-danger alert-dismissible fade show">
    <i class="fa-solid fa-trash me-1"></i> Notice deleted successfully.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php } ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold mb-0">
        <i class="fa-solid fa-calendar-days text-primary me-2"></i>
        Notice Management
    </h3>
    <a href="add.php" class="btn btn-primary fw-semibold shadow-sm">
        <i class="fa-solid fa-plus me-1"></i> Add Notice
    </a>
</div>

<div class="card border-0 shadow-lg rounded-4 overflow-hidden">
    <div class="card-body p-3 p-md-4">

        <?php if(count($notices) > 0){ ?>
            <!-- Desktop View Table -->
            <div class="table-responsive d-none d-lg-block">
                <table class="table table-hover align-middle mb-0 text-center">
                    <thead class="table-primary">
                        <tr>
                            <th class="text-start">Date</th>
                            <th class="text-start">Category</th>
                            <th class="text-start">Title</th>
                            <th width="200">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($notices as $row){ 
                            $badgeClass = getAdminBadgeClass($row['category']);
                        ?>
                        <tr>
                            <td class="text-start text-nowrap"><?= date("d M Y", strtotime($row['notice_date'])) ?></td>
                            <td class="text-start">
                                <span class="notice-badge <?= $badgeClass ?>">
                                    <i class="fa-solid fa-circle me-1" style="font-size: 5px;"></i>
                                    <?= htmlspecialchars($row['category']) ?>
                                </span>
                            </td>
                            <td class="text-start fw-semibold"><?= htmlspecialchars($row['title']) ?></td>
                            <td>
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="view.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info text-white" title="View">
                                        <i class="fa-solid fa-eye"></i> View
                                    </a>
                                    <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning text-dark fw-bold" title="Edit">
                                        <i class="fa-solid fa-pen-to-square"></i> Edit
                                    </a>
                                    <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this notice?')" title="Delete">
                                        <i class="fa-solid fa-trash"></i> Delete
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile & Tablet View Cards -->
            <div class="row g-3 d-lg-none">
                <?php foreach($notices as $row){ 
                    $badgeClass = getAdminBadgeClass($row['category']);
                ?>
                <div class="col-12 col-md-6">
                    <div class="card border border-secondary shadow-sm rounded-3 h-100 bg-secondary text-white">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="notice-badge <?= $badgeClass ?>">
                                    <i class="fa-solid fa-circle me-1" style="font-size: 5px;"></i>
                                    <?= htmlspecialchars($row['category']) ?>
                                </span>
                                <span class="text-white small fw-bold"><i class="fa-regular fa-calendar me-1"></i><?= date("d M Y", strtotime($row['notice_date'])) ?></span>
                            </div>
                            
                            <h5 class="card-title fw-bold text-white mb-3"><?= htmlspecialchars($row['title']) ?></h5>

                            <div class="d-flex justify-content-end gap-1 border-top border-secondary pt-2 flex-wrap">
                                <a href="view.php?id=<?= $row['id'] ?>" class="btn btn-info btn-sm text-white">
                                    <i class="fa-solid fa-eye me-1"></i>View
                                </a>
                                <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm text-dark fw-bold">
                                    <i class="fa-solid fa-pen-to-square me-1"></i>Edit
                                </a>
                                <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this notice?')">
                                    <i class="fa-solid fa-trash me-1"></i>Delete
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>

        <?php } else { ?>
            <div class="text-center text-muted py-4">
                No notices found.
            </div>
        <?php } ?>

    </div>
</div>

</div>

<?php include("../includes/footer.php"); ?>