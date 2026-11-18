<?php
if (file_exists("../config/database.php")) {
    require_once("../config/database.php");
} elseif (file_exists("../database/database.php")) {
    require_once("../database/database.php");
} elseif (file_exists("../db.php")) {
    require_once("../db.php");
}

$db_conn = null;
if (isset($conn)) {
    $db_conn = $conn;
} elseif (isset($db)) {
    $db_conn = $db;
} elseif (isset($pdo)) {
    $db_conn = $pdo;
}

$selectedDate = isset($_GET['date']) ? $_GET['date'] : date("Y-m-d");
$notices = [];

if ($db_conn instanceof PDO) {
    $stmt = $db_conn->prepare("SELECT * FROM notices WHERE notice_date = ? ORDER BY id DESC");
    $stmt->execute([$selectedDate]);
    $notices = $stmt->fetchAll(PDO::FETCH_ASSOC);
} elseif ($db_conn) {
    $stmt = mysqli_prepare($db_conn, "SELECT * FROM notices WHERE notice_date = ? ORDER BY id DESC");
    mysqli_stmt_bind_param($stmt, "s", $selectedDate);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $notices = mysqli_fetch_all($res, MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Notice Details - CRIMS</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="icon" type="image/png" href="../assets/images/dgpi_logo.png">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
<style>
:root {
    --bg-dark: #0a0f1d;
    --card-surface: rgba(17, 24, 39, 0.75);
    --border-glass: rgba(255, 255, 255, 0.08);
    --accent-blue: #2563eb;
    --text-heading: #f8fafc;
    --text-muted: #94a3b8;
}

body { 
    background-color: var(--bg-dark); 
    background-image: 
        radial-gradient(at 0% 0%, rgba(37, 99, 235, 0.12) 0px, transparent 40%),
        radial-gradient(at 100% 100%, rgba(13, 202, 240, 0.08) 0px, transparent 40%);
    background-attachment: fixed;
    font-family: 'Plus Jakarta Sans', sans-serif; 
    color: #e2e8f0; 
    min-height: 100vh;
}

h1, h2, h3, h4, h5, h6 {
    font-family: 'Outfit', sans-serif;
}

/* আপগ্রেডেড গ্লাস বক্স ডিজাইন */
.glass-card { 
    background: var(--card-surface); 
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    border-radius: 20px; 
    border: 1px solid var(--border-glass);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35); 
    position: relative;
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.glass-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: var(--card-accent, #2563eb);
    transition: width 0.25s ease;
}

.glass-card:hover {
    transform: translateY(-5px);
    border-color: rgba(255, 255, 255, 0.18);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5), 0 0 20px rgba(37, 99, 235, 0.15);
}

.glass-card:hover::before {
    width: 6px;
}

/* ব্যাক বাটন */
.btn-back {
    color: #94a3b8;
    background: rgba(17, 25, 39, 0.8);
    border: 1px solid var(--border-glass);
    font-weight: 600;
    font-size: 13px;
    padding: 8px 18px;
    border-radius: 50rem;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.25s ease;
}

.btn-back:hover {
    background: var(--accent-blue);
    border-color: var(--accent-blue);
    color: #ffffff;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(37, 99, 235, 0.35);
}

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

/* বাটন ডিজাইন */
.btn-view-notice {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    color: #ffffff !important;
    font-weight: 600;
    font-size: 13px;
    padding: 7px 18px;
    border-radius: 50rem;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35);
    transition: all 0.25s ease;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.btn-view-notice:hover {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(37, 99, 235, 0.5);
    color: #ffffff;
}

.btn-view-notice i {
    transition: transform 0.2s ease;
}

.btn-view-notice:hover i {
    transform: translateX(3px);
}
</style>
</head>
<body>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <h4 class="fw-bold text-white mb-0">
            <i class="fa-solid fa-calendar-day text-primary me-2"></i> Notices for 
            <span class="text-secondary fs-6 fw-normal ms-2">(<?= date("d M, Y", strtotime($selectedDate)) ?>)</span>
        </h4>
        <a href="index.php" class="btn-back">
            <i class="fa-solid fa-arrow-left"></i> Back Home
        </a>
    </div>

    <div class="row g-4">
        <?php if (count($notices) > 0): ?>
            <?php foreach ($notices as $item): ?>
                <?php 
                    $cat = strtolower(trim($item['category'] ?? 'notice'));
                    $badgeClass = 'badge-default';
                    $accentColor = '#2563eb';

                    if (in_array($cat, ['job sheet', 'jobsheet'])) {
                        $badgeClass = 'badge-jobsheet';
                        $accentColor = '#06b6d4';
                    } elseif (in_array($cat, ['class test', 'classtest', 'ct'])) {
                        $badgeClass = 'badge-class-test';
                        $accentColor = '#f97316';
                    } elseif (in_array($cat, ['quiz test', 'quiztest'])) {
                        $badgeClass = 'badge-quiz-test';
                        $accentColor = '#34d399';
                    } elseif ($cat == 'presentation') {
                        $badgeClass = 'badge-presentation';
                        $accentColor = '#c084fc';
                    } elseif ($cat == 'assignment') {
                        $badgeClass = 'badge-assignment';
                        $accentColor = '#818cf8';
                    } elseif ($cat == 'practical') {
                        $badgeClass = 'badge-practical';
                        $accentColor = '#f472b6';
                    } elseif ($cat == 'sessional') {
                        $badgeClass = 'badge-sessional';
                        $accentColor = '#facc15';
                    } elseif ($cat == 'practical exam') {
                        $badgeClass = 'badge-practical-exam';
                        $accentColor = '#c4b5fd';
                    } elseif ($cat == 'mid exam') {
                        $badgeClass = 'badge-mid-exam';
                        $accentColor = '#60a5fa';
                    } elseif ($cat == 'semester final') {
                        $badgeClass = 'badge-semester-final';
                        $accentColor = '#4ade80';
                    } elseif (in_array($cat, ['class starts', 'class-starts'])) {
                        $badgeClass = 'badge-class-starts';
                        $accentColor = '#cbd5e1';
                    } elseif (in_array($cat, ['class off', 'class-off', 'holiday'])) {
                        $badgeClass = 'badge-class-off';
                        $accentColor = '#f87171';
                    } 
                    // নতুন ৭টি ক্যাটাগরির ম্যাপিং
                    elseif ($cat == 'event') {
                        $badgeClass = 'badge-event';
                        $accentColor = '#e879f9';
                    } elseif (in_array($cat, ['urgent notice', 'urgentnotice', 'urgent'])) {
                        $badgeClass = 'badge-urgent-notice';
                        $accentColor = '#fca5a5';
                    } elseif (in_array($cat, ['class related', 'classrelated'])) {
                        $badgeClass = 'badge-class-related';
                        $accentColor = '#38bdf8';
                    } elseif (in_array($cat, ['academic notice', 'academicnotice', 'academic'])) {
                        $badgeClass = 'badge-academic-notice';
                        $accentColor = '#93c5fd';
                    } elseif (in_array($cat, ['general notice', 'generalnotice', 'general'])) {
                        $badgeClass = 'badge-general-notice';
                        $accentColor = '#6ee7b7';
                    } elseif ($cat == 'result') {
                        $badgeClass = 'badge-result';
                        $accentColor = '#fcd34d';
                    } elseif ($cat == 'others') {
                        $badgeClass = 'badge-others';
                        $accentColor = '#d1d5db';
                    }
                ?>
                <div class="col-12 col-md-6">
                    <div class="glass-card p-4 h-100" style="--card-accent: <?= $accentColor ?>;">
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="notice-badge <?= $badgeClass ?>">
                                    <i class="fa-solid fa-circle me-1" style="font-size: 5px;"></i>
                                    <?= htmlspecialchars($item['category'] ?? 'Notice') ?>
                                </span>
                                <small class="text-secondary"><i class="fa-regular fa-calendar me-1"></i> <?= htmlspecialchars($item['notice_date']) ?></small>
                            </div>
                            <h5 class="fw-bold text-white mb-4 lh-base"><?= htmlspecialchars($item['title']) ?></h5>
                        </div>
                        <div class="text-end pt-3 border-top border-dark-subtle" style="border-color: rgba(255,255,255,0.06) !important;">
                            <a href="view.php?id=<?= $item['id'] ?>" class="btn-view-notice">
                                <span>View Details</span>
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <div class="glass-card p-5 border-dashed">
                    <i class="fa-solid fa-circle-exclamation text-warning fs-1 mb-3"></i>
                    <h5 class="text-secondary mb-0">এই তারিখে কোনো নোটিশ পাওয়া যায়নি।</h5>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>