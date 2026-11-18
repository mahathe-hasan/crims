<?php

require_once("../config/session.php");
require_once("../config/database.php");

if(!isset($_GET['id'])){
    header("Location: index.php");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM notices WHERE id=?");
$stmt->execute([$_GET['id']]);

$notice = $stmt->fetch();

if(!$notice){
    header("Location: index.php");
    exit();
}

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

$badgeClass = getAdminBadgeClass($notice['category']);

include("../includes/header.php");
include("../includes/sidebar.php");
include("../includes/navbar.php");

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

<div class="card border-0 shadow-lg" style="border-radius:18px;">

    <div class="card-header bg-primary text-white">

        <h4 class="mb-0">
            <i class="fa-solid fa-eye"></i>
            View Notice
        </h4>

    </div>

    <div class="card-body">

        <table class="table">

            <tr>
                <th width="180">Title</th>
                <td><?= htmlspecialchars($notice['title']) ?></td>
            </tr>

            <tr>
                <th>Category</th>
                <td>
                    <span class="notice-badge <?= $badgeClass ?>">
                        <i class="fa-solid fa-circle me-1" style="font-size: 5px;"></i>
                        <?= htmlspecialchars($notice['category']) ?>
                    </span>
                </td>
            </tr>

            <tr>
                <th>Date</th>
                <td><?= date("d F Y", strtotime($notice['notice_date'])) ?></td>
            </tr>

            <tr>
                <th>Description</th>
                <td><?= nl2br(htmlspecialchars($notice['description'])) ?></td>
            </tr>

        </table>

        <a href="index.php" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i>
            Back
        </a>

    </div>

</div>

<?php include("../includes/footer.php"); ?>