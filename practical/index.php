<?php
require_once("../config/session.php");
require_once("../config/database.php");

include("../includes/header.php");
include("../includes/sidebar.php");
include("../includes/navbar.php");

$subjectsStmt = $conn->query("SELECT * FROM subjects ORDER BY id ASC");
$subjects = $subjectsStmt ? $subjectsStmt->fetchAll() : [];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold"><i class="fa-solid fa-book-bookmark text-primary me-2"></i> Practical Job & Sessional Management</h3>
    <a href="add_subject.php" class="btn btn-success btn-sm"><i class="fa-solid fa-plus me-1"></i> Add New Subject</a>
</div>

<div class="card border-0 shadow-sm mb-4" style="border-radius:15px; background-color: #1e293b !important; color: #f8fafc !important;">
    <div class="card-body">
        <div class="row g-3">
            <?php if(count($subjects) > 0): ?>
                <?php foreach($subjects as $sub): ?>
                <div class="col-md-4 col-lg-3">
                    <div class="border rounded p-3 text-center" style="background-color: #0f172a !important; border-color: rgba(255, 255, 255, 0.1) !important;">
                        <div class="fw-bold text-primary fs-5"><?= htmlspecialchars($sub['subject_code']) ?></div>
                        <div class="small text-muted text-truncate mb-2" style="color: #94a3b8 !important;"><?= htmlspecialchars($sub['subject_name']) ?></div>
                        <a href="manage_tasks.php?subject_id=<?= $sub['id'] ?>" class="btn btn-sm btn-outline-primary w-100 mb-1">
                            <i class="fa-solid fa-list-check me-1"></i> Manage Tasks
                        </a>
                        <a href="delete_subject.php?id=<?= $sub['id'] ?>" class="btn btn-sm text-danger p-0" onclick="return confirm('Delete this subject?')">Delete Subject</a>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-muted text-center py-3">No subjects created yet. Click "Add New Subject" to begin.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include("../includes/footer.php"); ?>