<?php

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

$cat = strtolower(trim($notice['category'] ?? 'notice'));
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

<!DOCTYPE html>
<html lang="bn">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($notice['title']) ?> - Notice Details</title>
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
        radial-gradient(at 0% 0%, rgba(37, 99, 235, 0.12) 0px, transparent 45%),
        radial-gradient(at 100% 100%, rgba(13, 202, 240, 0.08) 0px, transparent 45%);
    background-attachment: fixed;
    font-family: 'Plus Jakarta Sans', sans-serif; 
    color: #e2e8f0; 
    min-height: 100vh;
}

h1, h2, h3, h4, h5, h6 {
    font-family: 'Outfit', sans-serif;
}

/* গ্লাস কার্ড স্টাইল */
.glass-card { 
    background: var(--card-surface); 
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border-radius: 20px; 
    border: 1px solid var(--border-glass);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.45); 
    position: relative;
    overflow: hidden;
}

.glass-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: <?= $accentColor ?>;
}

/* মেটা ট্যাগ ও ব্যাজ কালারসমূহ */
.notice-badge {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.03em;
    text-transform: uppercase;
    padding: 6px 14px;
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

/* ডেসক্রিপশন বক্স */
.notice-description {
    font-size: 15px;
    line-height: 1.8;
    color: #cbd5e1;
    background: rgba(10, 15, 29, 0.5);
    border-radius: 14px;
    padding: 20px;
    border: 1px solid rgba(255, 255, 255, 0.05);
}

/* বাটনস */
.btn-action-back {
    color: #94a3b8;
    background: rgba(17, 25, 39, 0.8);
    border: 1px solid var(--border-glass);
    font-weight: 600;
    font-size: 13px;
    padding: 8px 20px;
    border-radius: 50rem;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.25s ease;
}

.btn-action-back:hover {
    background: var(--accent-blue);
    border-color: var(--accent-blue);
    color: #ffffff;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(37, 99, 235, 0.35);
}

.btn-drive {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: #ffffff !important;
    font-weight: 600;
    font-size: 13px;
    padding: 8px 20px;
    border-radius: 50rem;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border: none;
    box-shadow: 0 4px 14px rgba(16, 185, 129, 0.25);
    transition: all 0.25s ease;
}

.btn-drive:hover {
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
}
</style>
</head>

<body>

<div class="container py-4 py-md-5">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-9">
            
            <div class="glass-card p-4 p-md-5">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <span class="notice-badge <?= $badgeClass ?>">
                        <i class="fa-solid fa-circle me-1" style="font-size: 5px;"></i>
                        <?= htmlspecialchars($notice['category']) ?>
                    </span>
                    <span class="text-secondary small">
                        <i class="fa-regular fa-calendar me-1"></i>
                        <?= date("d F Y", strtotime($notice['notice_date'])) ?>
                    </span>
                </div>

                <h3 class="fw-bold text-white mb-4 lh-base">
                    <?= htmlspecialchars($notice['title']) ?>
                </h3>

                <div class="notice-description mb-4">
                    <?= nl2br(htmlspecialchars($notice['description'])) ?>
                </div>

                <div class="d-flex gap-2 flex-wrap pt-3 border-top border-dark-subtle" style="border-color: rgba(255,255,255,0.06) !important;">
                    <?php 
                    $back_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';
                    ?>
                    <a href="<?= $back_url ?>" class="btn-action-back">
                        <i class="fa-solid fa-arrow-left"></i> Back
                    </a>

                    <!-- Google Drive Button for Notice attachments -->
                    <a href="https://drive.google.com/drive/folders/1jFHHTAdlMphqw1ETwdFIRzFvSYqPhCio?usp=sharing" target="_blank" class="btn-drive">
                        <i class="fa-brands fa-google-drive"></i> Open Drive
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>