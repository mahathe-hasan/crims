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

$todayDate = date("Y-m-d");
if ($db_conn instanceof PDO) {
    $deleteStmt = $db_conn->prepare("DELETE FROM notices WHERE notice_date < ?");
    $deleteStmt->execute([$todayDate]);
} elseif ($db_conn) {
    $deleteStmt = mysqli_prepare($db_conn, "DELETE FROM notices WHERE notice_date < ?");
    mysqli_stmt_bind_param($deleteStmt, "s", $todayDate);
    mysqli_stmt_execute($deleteStmt);
}

$driveUrl = "https://drive.google.com/drive/folders/1jFHHTAdlMphqw1ETwdFIRzFvSYqPhCio?usp=sharing"; 

$month = isset($_GET['month']) ? (int)$_GET['month'] : date("n");
$year  = isset($_GET['year']) ? (int)$_GET['year'] : date("Y");

if ($month < 1) { $month = 12; $year--; }
if ($month > 12) { $month = 1; $year++; }

// --- Google Calendar Official Bangladesh Holiday Feed Parser & Cache ---
function getGoogleBangladeshHolidays($year) {
    $cacheDir = __DIR__ . '/../cache';
    if (!is_dir($cacheDir)) {
        @mkdir($cacheDir, 0777, true);
    }
    $cacheFile = $cacheDir . "/google_holidays_{$year}.json";

    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < 86400 * 30) {
        $cached = json_decode(file_get_contents($cacheFile), true);
        if (!empty($cached)) return $cached;
    }

    $icsUrl = "https://calendar.google.com/calendar/ical/en.bd%23holiday%40group.v.calendar.google.com/public/basic.ics";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $icsUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 6);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $icsData = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $holidays = [];
    if ($httpCode == 200 && !empty($icsData)) {
        $events = explode('BEGIN:VEVENT', $icsData);
        array_shift($events);

        foreach ($events as $event) {
            $date = '';
            $summary = '';

            if (preg_match('/DTSTART;VALUE=DATE:([0-9]{8})/', $event, $mDate)) {
                $d = $mDate[1];
                $date = substr($d, 0, 4) . '-' . substr($d, 4, 2) . '-' . substr($d, 6, 2);
            } elseif (preg_match('/DTSTART:([0-9]{8})/', $event, $mDate)) {
                $d = $mDate[1];
                $date = substr($d, 0, 4) . '-' . substr($d, 4, 2) . '-' . substr($d, 6, 2);
            }

            if (preg_match('/SUMMARY:(.*?)[\r\n]/', $event, $mSum)) {
                $summary = trim(str_replace(['\,', '\;'], [',', ';'], $mSum[1]));
            }

            if (!empty($date) && !empty($summary)) {
                $holidays[$date] = $summary;
            }
        }

        if (!empty($holidays)) {
            @file_put_contents($cacheFile, json_encode($holidays));
        }
    }

    return $holidays;
}

$googleHolidays = getGoogleBangladeshHolidays($year);

$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
$firstDay = date("N", strtotime("$year-$month-01"));

$todayDay   = date("j");
$todayMonth = date("n");
$todayYear  = date("Y");

$noticeData = [];
$totalNoticesCount = 0;
if ($db_conn instanceof PDO) {
    $stmt = $db_conn->prepare("SELECT * FROM notices WHERE MONTH(notice_date) = ? AND YEAR(notice_date) = ? ORDER BY notice_date, id");
    $stmt->execute([$month, $year]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $day = date("j", strtotime($row['notice_date']));
        $noticeData[$day][] = $row;
        $totalNoticesCount++;
    }
} elseif ($db_conn) {
    $stmt = mysqli_prepare($db_conn, "SELECT * FROM notices WHERE MONTH(notice_date) = ? AND YEAR(notice_date) = ? ORDER BY notice_date, id");
    mysqli_stmt_bind_param($stmt, "ii", $month, $year);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($res)) {
        $day = date("j", strtotime($row['notice_date']));
        $noticeData[$day][] = $row;
        $totalNoticesCount++;
    }
}

$todayNotices = [];
if (isset($noticeData[$todayDay])) {
    $todayNotices = $noticeData[$todayDay];
}
$todayNoticesCount = count($todayNotices);

$routineData = [];
$todayClassesCount = 0;
$currentDayName = date("l");

if ($db_conn instanceof PDO) {
    $routineStmt = $db_conn->query("SELECT * FROM routines ORDER BY FIELD(day_name, 'Sunday','Monday','Tuesday','Wednesday','Thursday'), period_no");
    $routinesRaw = $routineStmt ? $routineStmt->fetchAll(PDO::FETCH_ASSOC) : [];
} elseif ($db_conn) {
    $res = mysqli_query($db_conn, "SELECT * FROM routines ORDER BY FIELD(day_name, 'Sunday','Monday','Tuesday','Wednesday','Thursday'), period_no");
    $routinesRaw = $res ? mysqli_fetch_all($res, MYSQLI_ASSOC) : [];
}

foreach ($routinesRaw as $r) {
    $routineData[$r['day_name']][$r['period_no']] = $r;
    if ($r['day_name'] == $currentDayName) {
        $todayClassesCount++;
    }
}

$subjects = [];
if ($db_conn instanceof PDO) {
    $subjectsStmt = $db_conn->query("SELECT * FROM subjects ORDER BY id ASC");
    $subjects = $subjectsStmt ? $subjectsStmt->fetchAll(PDO::FETCH_ASSOC) : [];
} elseif ($db_conn) {
    $res = mysqli_query($db_conn, "SELECT * FROM subjects ORDER BY id ASC");
    $subjects = $res ? mysqli_fetch_all($res, MYSQLI_ASSOC) : [];
}

$prevMonth = $month - 1; 
$prevYear = $year;
if ($prevMonth == 0) { $prevMonth = 12; $prevYear--; }

$nextMonth = $month + 1; 
$nextYear = $year;
if ($nextMonth == 13) { $nextMonth = 1; $nextYear++; }

$weekDays = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'];
$defaultActiveDay = in_array($currentDayName, $weekDays) ? $currentDayName : 'Sunday';

function getCategoryClass($cat) {
    $c = strtolower(trim($cat));
    if (in_array($c, ['job sheet', 'jobsheet'])) return 'cat-job-sheet';
    if (in_array($c, ['class test', 'classtest', 'ct'])) return 'cat-class-test';
    if (in_array($c, ['quiz test', 'quiztest'])) return 'cat-quiz-test';
    if ($c == 'presentation') return 'cat-presentation';
    if ($c == 'assignment') return 'cat-assignment';
    if ($c == 'practical') return 'cat-practical';
    if ($c == 'sessional') return 'cat-sessional';
    if ($c == 'practical exam') return 'cat-practical-exam';
    if ($c == 'mid exam') return 'cat-mid-exam';
    if ($c == 'semester final') return 'cat-semester-final';
    if (in_array($c, ['class starts', 'class-starts'])) return 'cat-class-starts';
    if (in_array($c, ['class off', 'class-off', 'holiday'])) return 'cat-class-off';
    
    // নতুন ক্যাটাগরিগুলো এখানে যোগ করা হলো:
    if ($c == 'event') return 'cat-event';
    if (in_array($c, ['urgent notice', 'urgentnotice', 'urgent'])) return 'cat-urgent-notice';
    if (in_array($c, ['class related', 'classrelated'])) return 'cat-class-related';
    if (in_array($c, ['academic notice', 'academicnotice', 'academic'])) return 'cat-academic-notice';
    if (in_array($c, ['general notice', 'generalnotice', 'general'])) return 'cat-general-notice';
    if ($c == 'result') return 'cat-result';
    if ($c == 'others') return 'cat-others';

    return 'cat-default';
}
?>

<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CRIMS Student Portal</title>

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
    --card-surface-solid: #111827;
    --border-glass: rgba(255, 255, 255, 0.08);
    --border-subtle: #1e293b;
    --accent-blue: #2563eb;
    --accent-blue-hover: #1d4ed8;
    --text-heading: #f8fafc;
    --text-body: #e2e8f0;
    --text-muted: #cbd5e1;
    --primary-gradient: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
    --card-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
}

body { 
    background-color: var(--bg-dark); 
    background-image: 
        radial-gradient(at 0% 0%, rgba(37, 99, 235, 0.12) 0px, transparent 40%),
        radial-gradient(at 100% 100%, rgba(13, 202, 240, 0.06) 0px, transparent 40%);
    background-attachment: fixed;
    font-family: 'Plus Jakarta Sans', sans-serif; 
    color: var(--text-body); 
    padding-bottom: 70px;
    min-height: 100vh;
}

h1, h2, h3, h4, h5, h6 {
    font-family: 'Outfit', sans-serif;
    color: var(--text-heading);
}

.text-muted { color: #cbd5e1 !important; }
.text-secondary { color: #94a3b8 !important; }

.glass-card {
    background: var(--card-surface);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    border: 1px solid var(--border-glass);
    border-radius: 18px;
    box-shadow: var(--card-shadow);
}

.header-card {
    background: linear-gradient(135deg, rgba(30, 58, 138, 0.5) 0%, rgba(17, 24, 39, 0.85) 100%);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(59, 130, 246, 0.25);
    color: #fff; 
    padding: 14px 22px; 
    border-radius: 16px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
}

.portal-logo {
    width: 48px; height: 48px; border-radius: 12px;
    background: #ffffff; padding: 4px; object-fit: contain;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.contact-card {
    background: var(--card-surface);
    backdrop-filter: blur(12px);
    border-radius: 16px;
    padding: 20px;
    box-shadow: var(--card-shadow);
    border: 1px solid var(--border-glass);
    height: 100%;
    text-align: center;
    transition: 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
.contact-card:hover {
    transform: translateY(-4px);
    border-color: rgba(59, 130, 246, 0.4);
    box-shadow: 0 15px 30px rgba(37, 99, 235, 0.15);
}
.contact-avatar {
    width: 60px; height: 60px; border-radius: 50%;
    background: linear-gradient(135deg, #2563eb 0%, #06b6d4 100%);
    color: #fff; font-size: 22px; display: flex; align-items: center; justify-content: center;
    margin: 0 auto 12px auto; font-weight: bold;
    box-shadow: 0 4px 15px rgba(37, 99, 235, 0.35);
}
.social-btns a {
    width: 36px; height: 36px; border-radius: 50%; display: inline-flex;
    align-items: center; justify-content: center; color: #fff; text-decoration: none;
    margin: 0 4px; font-size: 15px; transition: 0.2s ease;
    border: 1px solid rgba(255, 255, 255, 0.1);
}
.social-btns a:hover { transform: translateY(-2px) scale(1.08); filter: brightness(1.1); }
.btn-whatsapp { background: #16a34a; }
.btn-phone { background: #2563eb; }
.btn-facebook { background: #1877f2; }

.stat-box {
    background: var(--card-surface);
    border-radius: 14px;
    padding: 12px 16px;
    box-shadow: var(--card-shadow);
    border: 1px solid var(--border-glass);
    transition: all 0.25s ease;
    text-decoration: none;
    display: flex;
    height: 100%;
    align-items: center;
}
.stat-box:hover {
    transform: translateY(-3px);
    border-color: rgba(59, 130, 246, 0.4);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
}
.stat-icon {
    width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;
    border-radius: 12px; font-size: 16px; flex-shrink: 0;
}

/* ক্যালেন্ডার গ্রিড */
.calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 8px; }

.day-header {
    background: #111827; color: #cbd5e1; text-align: center;
    padding: 10px 4px; font-weight: 700; border-radius: 10px; font-size: 12px; text-transform: uppercase;
    border: 1px solid var(--border-glass);
    letter-spacing: 0.05em;
}
.day-header.weekend { 
    background: rgba(239, 68, 68, 0.12) !important; 
    color: #f87171 !important; 
    border-color: rgba(239, 68, 68, 0.25) !important; 
}

.calendar-day {
    background: rgba(17, 24, 39, 0.65); min-height: 110px; border-radius: 14px; padding: 8px;
    box-shadow: var(--card-shadow); transition: all 0.2s ease; display: flex; flex-direction: column;
    border: 1px solid var(--border-glass);
    position: relative;
    overflow: hidden;
}
.calendar-day.clickable-holiday { cursor: pointer; }
.calendar-day.empty-day { background: transparent; box-shadow: none; border: none; cursor: default; }

.date-number {
    font-size: 13px; font-weight: 700; margin-bottom: 6px; color: #cbd5e1;
    display: inline-block; width: 26px; height: 26px; line-height: 26px; text-align: center; border-radius: 8px;
}

/* আজকের দিন: মোটা ব্লু বর্ডার */
.today { 
    border: 2px solid var(--accent-blue) !important; 
    background: rgba(37, 99, 235, 0.08) !important;
}
.today .date-number { background: var(--accent-blue); color: #fff; box-shadow: 0 2px 8px rgba(37, 99, 235, 0.4); }

/* সরকারি ছুটির লাল ডট */
.holiday-indicator-dot {
    width: 6px;
    height: 6px;
    background-color: #ef4444;
    border-radius: 50%;
    display: inline-block;
    box-shadow: 0 0 6px #ef4444;
}

/* বন্ধের দিন: ব্যাকগ্রাউন্ড হালকা লালচে কিন্তু বর্ডার সম্পূর্ণ সাধারণ নরমাল (কোনো লাল বর্ডার নেই) */
.calendar-day.has-class-off {
    background-color: rgba(220, 38, 38, 0.1) !important;
    border: 1px solid var(--border-glass) !important;
}
.calendar-day.has-class-off.today {
    border: 2px solid var(--accent-blue) !important;
}
.calendar-day.has-class-off .date-number {
    background: #dc2626 !important;
    color: #fff !important;
}

.weekend-day .date-number { color: #f87171 !important; }
.weekend-day.today {
    border: 2px solid var(--accent-blue) !important;
    background: rgba(37, 99, 235, 0.08) !important;
}

.notice-item { 
    font-size: 11px; padding: 4px 6px; margin-bottom: 4px; border-radius: 6px; 
    cursor: pointer; text-decoration: none; display: block; 
    border: 1px solid rgba(255, 255, 255, 0.05);
    transition: transform 0.15s ease;
}
.notice-item:hover { transform: translateX(2px); }
.notice-item span { color: #ffffff; font-weight: 600; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

.more-notice-badge {
    font-size: 10px; font-weight: 700; background: rgba(255, 255, 255, 0.08); color: #cbd5e1; 
    padding: 3px 6px; border-radius: 6px; text-align: center; cursor: pointer; display: block; margin-top: auto;
    border: 1px solid var(--border-glass);
}
.more-notice-badge:hover { background: rgba(255, 255, 255, 0.14); color: #f8fafc; }

/* মোবাইল ভিউ বেল আইকন কনটেইনার ও আইকন ফিক্স */
.mobile-bell-container {
    display: none;
    flex-wrap: wrap;
    gap: 3px;
    align-items: center;
    justify-content: center !important;
    margin-top: 2px !important; /* auto তুলে দিয়ে নির্দিষ্ট অল্প মার্জিন */
    width: 100%;
}

.mobile-bell-icon {
    font-size: 8px;
    width: 16px;
    height: 16px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 5px;
    flex-shrink: 0;
    text-decoration: none !important;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.3);
}

/* Modern Pill-Style Counter Badge */
.mobile-more-badge {
    font-size: 8px;
    height: 16px;
    min-width: 16px;
    padding: 0 3px;
    font-weight: 700;
    border-radius: 5px;
    background: rgba(255, 255, 255, 0.12);
    color: #e2e8f0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid rgba(255, 255, 255, 0.15);
    line-height: 1;
    flex-shrink: 0;
}

/* ক্যাটাগরি ইউনিক কালারসমূহ (Practical Exam আলাদা ভায়োলেট কালারে) */
.cat-job-sheet { background: rgba(6, 182, 212, 0.25) !important; border: 1px solid rgba(6, 182, 212, 0.5) !important; color: #22d3ee !important; }
.cat-class-test { background: rgba(249, 115, 22, 0.25) !important; border: 1px solid rgba(249, 115, 22, 0.5) !important; color: #fb923c !important; }
.cat-quiz-test { background: rgba(16, 185, 129, 0.25) !important; border: 1px solid rgba(16, 185, 129, 0.5) !important; color: #34d399 !important; }
.cat-presentation { background: rgba(168, 85, 247, 0.25) !important; border: 1px solid rgba(168, 85, 247, 0.5) !important; color: #c084fc !important; }
.cat-assignment { background: rgba(99, 102, 241, 0.25) !important; border: 1px solid rgba(99, 102, 241, 0.5) !important; color: #818cf8 !important; }
.cat-practical { background: rgba(236, 72, 153, 0.25) !important; border: 1px solid rgba(236, 72, 153, 0.5) !important; color: #f472b6 !important; }
.cat-sessional { background: rgba(234, 179, 8, 0.25) !important; border: 1px solid rgba(234, 179, 8, 0.5) !important; color: #facc15 !important; }
.cat-practical-exam { background: rgba(139, 92, 246, 0.3) !important; border: 1px solid rgba(139, 92, 246, 0.7) !important; color: #c4b5fd !important; }
.cat-mid-exam { background: rgba(37, 99, 235, 0.25) !important; border: 1px solid rgba(37, 99, 235, 0.5) !important; color: #60a5fa !important; }
.cat-semester-final { background: rgba(22, 163, 74, 0.25) !important; border: 1px solid rgba(22, 163, 74, 0.5) !important; color: #4ade80 !important; }
.cat-class-starts { background: rgba(100, 116, 139, 0.25) !important; border: 1px solid rgba(100, 116, 139, 0.5) !important; color: #cbd5e1 !important; }
.cat-class-off { background: rgba(220, 38, 38, 0.3) !important; border: 1px solid rgba(220, 38, 38, 0.6) !important; color: #f87171 !important; }
.cat-default { background: rgba(37, 99, 235, 0.25) !important; border: 1px solid rgba(37, 99, 235, 0.5) !important; color: #93c5fd !important; }
/* নতুন ক্যাটাগরিগুলোর ইউনিক ও আকর্ষণীয় কালারসমূহ */
.cat-event { background: rgba(217, 70, 239, 0.25) !important; border: 1px solid rgba(217, 70, 239, 0.5) !important; color: #e879f9 !important; }
.cat-urgent-notice { background: rgba(239, 68, 68, 0.3) !important; border: 1px solid rgba(239, 68, 68, 0.7) !important; color: #fca5a5 !important; }
.cat-class-related { background: rgba(14, 165, 233, 0.25) !important; border: 1px solid rgba(14, 165, 233, 0.5) !important; color: #38bdf8 !important; }
.cat-academic-notice { background: rgba(59, 130, 246, 0.25) !important; border: 1px solid rgba(59, 130, 246, 0.5) !important; color: #93c5fd !important; }
.cat-general-notice { background: rgba(16, 185, 129, 0.25) !important; border: 1px solid rgba(16, 185, 129, 0.5) !important; color: #6ee7b7 !important; }
.cat-result { background: rgba(245, 158, 11, 0.25) !important; border: 1px solid rgba(245, 158, 11, 0.5) !important; color: #fcd34d !important; }
.cat-others { background: rgba(107, 114, 128, 0.25) !important; border: 1px solid rgba(107, 114, 128, 0.5) !important; color: #d1d5db !important; }

/* অতি ছোট ও কম্প্যাক্ট ক্যাটাগরি লেজেন্ড (শুধুমাত্র মোবাইল ভিউর জন্য d-md-none) */
.compact-legend-box {
    background: var(--card-surface);
    border: 1px solid var(--border-glass);
    border-radius: 12px;
    padding: 8px 10px;
    margin-top: 14px;
}
.legend-item {
    font-size: 9px;
    font-weight: 600;
    padding: 2px 6px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    gap: 3px;
    line-height: 1.2;
}

.subject-card {
    background: var(--card-surface); border-radius: 16px; padding: 18px; text-align: center;
    border: 1px solid var(--border-glass); cursor: pointer; transition: 0.3s cubic-bezier(0.16, 1, 0.3, 1); height: 100%;
    box-shadow: var(--card-shadow);
}
.subject-card:hover { 
    transform: translateY(-5px); 
    border-color: var(--accent-blue); 
    box-shadow: 0 15px 30px rgba(37, 99, 235, 0.2); 
}

.day-nav-btn { 
    flex: 1 1 auto; min-width: 95px; text-align: center; border-radius: 50rem !important; 
    border-color: var(--border-glass); color: #cbd5e1; background: var(--card-surface);
}
.day-nav-btn.active, .day-nav-btn:hover {
    background: var(--accent-blue) !important;
    color: #fff !important;
    border-color: var(--accent-blue) !important;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
}

.mobile-bottom-nav {
    display: none;
    position: fixed;
    bottom: 0; left: 0; right: 0;
    background: rgba(17, 24, 39, 0.95);
    backdrop-filter: blur(12px);
    box-shadow: 0 -5px 25px rgba(0,0,0,0.5);
    z-index: 1050;
    padding: 10px 0;
    border-top: 1px solid var(--border-glass);
    border-top-left-radius: 20px;
    border-top-right-radius: 20px;
}
.mobile-bottom-nav a {
    color: #cbd5e1;
    text-decoration: none;
    font-size: 11px;
    font-weight: 600;
    display: flex;
    flex-direction: column;
    align-items: center;
    transition: 0.2s;
}
.mobile-bottom-nav a i { font-size: 18px; margin-bottom: 3px; }
.mobile-bottom-nav a.active, .mobile-bottom-nav a:hover { color: #60a5fa; }

.modal-content {
    background: #111827;
    border: 1px solid var(--border-glass);
    color: #e2e8f0;
}
.modal-header {
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

/* মোবাইল ভিউ রেসপনসিভ */
@media (max-width: 768px) {
    .mobile-bottom-nav { display: flex; justify-content: space-around; align-items: center; }
    body { padding-bottom: 85px; }
    .calendar-grid { gap: 4px; }
    .day-header { font-size: 9px; padding: 6px 2px; border-radius: 8px; }
    
    /* মোবাইলে প্রতিটি ঘরের সাইজ ফিক্সড রাখা */
    .calendar-day { 
        min-height: 75px !important; 
        height: auto !important; 
        padding: 5px 3px !important; 
        border-radius: 8px; 
        display: flex !important;
        flex-direction: column !important;
        justify-content: flex-start !important; /* একদম নিচে ঠেলে দেবে না */
        gap: 4px !important; /* তারিখ ও বেল আইকনের মাঝে পারফেক্ট গ্যাপ */
}
    .date-number { font-size: 11px; width: 19px; height: 19px; line-height: 19px; margin-bottom: 0px; }
    
    .desktop-notice-wrapper { display: none !important; }
    .mobile-bell-container { display: flex !important; }
    
    .stat-box { padding: 8px 10px; }
    .stat-box h6 { font-size: 12px !important; margin-bottom: 0 !important; }
    .stat-box h4 { font-size: 16px !important; }
    .stat-icon { width: 30px; height: 30px; font-size: 12px; }
}
</style>
</head>

<body>

<div class="container py-4">

    <!-- হেডার -->
    <div class="header-card mb-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <img src="../assets/images/dgpi_logo.png" alt="Logo" class="portal-logo" onerror="this.onerror=null; this.src='https://cdn-icons-png.flaticon.com/512/2991/2991148.png';">
                <div>
                    <h4 class="fw-bold mb-0 text-white" style="letter-spacing: -0.02em;">CRIMS Student Portal</h4>
                    <p class="mb-0 text-white-50 small">Dhaka Government Polytechnic Institute</p>
                </div>
            </div>
            <div class="d-none d-md-block text-end">
                <span class="badge bg-primary bg-opacity-25 text-info border border-primary border-opacity-50 px-3 py-2 rounded-pill small">
                    <i class="fa-regular fa-clock me-1"></i> <?= date("l, d F Y") ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Quick Stats Widgets -->
    <div class="row g-2 mb-4">
        <div class="col-6">
            <a href="today_notices.php" class="stat-box gap-2">
                <div class="stat-icon bg-primary bg-opacity-25 text-primary border border-primary border-opacity-25">
                    <i class="fa-solid fa-bell"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-0 fs-6">Today Notice</h6>
                    <h4 class="fw-bold mb-0 text-white"><?= $todayNoticesCount ?></h4>
                </div>
            </a>
        </div>
        <div class="col-6">
            <div class="stat-box gap-2">
                <div class="stat-icon bg-success bg-opacity-25 text-success border border-success border-opacity-25">
                    <i class="fa-solid fa-book-open-reader"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-0 fs-6">Today's Classes</h6>
                    <h4 class="fw-bold mb-0 text-white"><?= $todayClassesCount ?></h4>
                </div>
            </div>
        </div>
    </div>

    <!-- ক্যালেন্ডার মাস পরিবর্তন বার -->
    <div class="row align-items-center mb-4 g-3">
        <div class="col-6 col-md-3 text-start">
            <a href="?month=<?= $prevMonth ?>&year=<?= $prevYear ?>" class="btn btn-outline-primary btn-sm px-3 rounded-pill">
                <i class="fa-solid fa-chevron-left me-1"></i> Prev
            </a>
        </div>
        <div class="col-12 col-md-6 order-3 order-md-2 text-center">
            <h4 class="fw-bold mb-0 text-primary">
                <i class="fa-regular fa-calendar-days me-2"></i>
                <?= date("F Y", strtotime("$year-$month-01")) ?>
            </h4>
        </div>
        <div class="col-6 col-md-3 order-2 order-md-3 text-end">
            <a href="?month=<?= $nextMonth ?>&year=<?= $nextYear ?>" class="btn btn-outline-primary btn-sm px-3 rounded-pill">
                Next <i class="fa-solid fa-chevron-right ms-1"></i>
            </a>
        </div>
    </div>

    <!-- ক্যালেন্ডার গ্রিড -->
    <div class="calendar-grid">
        <div class="day-header">Mon</div>
        <div class="day-header">Tue</div>
        <div class="day-header">Wed</div>
        <div class="day-header">Thu</div>
        <div class="day-header weekend">Fri</div>
        <div class="day-header weekend">Sat</div>
        <div class="day-header">Sun</div>

        <?php
        for ($i = 1; $i < $firstDay; $i++) { echo '<div class="calendar-day empty-day"></div>'; }

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $isToday = ($day == $todayDay && $month == $todayMonth && $year == $todayYear);
            $hasClassOff = false;

            $dayOfWeek = date("N", strtotime("$year-$month-$day"));
            $isWeekend = ($dayOfWeek == 5 || $dayOfWeek == 6);

            $formattedDate = sprintf('%04d-%02d-%02d', $year, $month, $day);
            $holidayName = $googleHolidays[$formattedDate] ?? false;
            if ($holidayName) {
                $hasClassOff = true;
            }

            if (isset($noticeData[$day])) {
                foreach ($noticeData[$day] as $nItem) {
                    $cName = strtolower(trim($nItem['category']));
                    if (in_array($cName, ['class off', 'class-off', 'holiday'])) { 
                        $hasClassOff = true;
                        break;
                    }
                }
            }

            $holidayJson = $holidayName ? htmlspecialchars(json_encode(['name' => $holidayName, 'date' => $formattedDate]), ENT_QUOTES, 'UTF-8') : 'null';
$hasNoticeBool = (!empty($noticeData[$day])) ? 'true' : 'false';
$clickHandler = "onclick=\"handleDateClick('{$formattedDate}', {$holidayJson}, {$hasNoticeBool})\"";
        ?>

        <div class="calendar-day <?= $isToday ? 'today' : '' ?> <?= $isWeekend ? 'weekend-day' : '' ?> <?= $hasClassOff ? 'has-class-off' : '' ?> <?= $holidayName ? 'clickable-holiday' : '' ?>"
             <?= $clickHandler ?>>
            
            <div class="d-flex justify-content-between align-items-center w-100">
                <div class="date-number"><?= $day ?></div>
                <!-- সরকারি ছুটি থাকলে শুধু ছোট সুন্দর লাল ডট শো করবে -->
                <?php if ($holidayName): ?>
                    <span class="holiday-indicator-dot" title="<?= htmlspecialchars($holidayName) ?>"></span>
                <?php endif; ?>
            </div>

            <!-- ১. কম্পিউটার ভিউ (নোটিশে ক্লিকে সরাসরি view.php তে এবং more এ ক্লিকে day_notices.php এ যাবে) -->
            <div class="desktop-notice-wrapper w-100">
                <?php if (isset($noticeData[$day])): 
                    $totalNotices = count($noticeData[$day]); 
                    $maxDisplay = 2; 
                    $count = 0;
                ?>
                    <?php foreach ($noticeData[$day] as $notice): 
                        if ($count >= $maxDisplay) { break; } $count++;
                        $colorClass = getCategoryClass($notice['category']);
                    ?>
                        <a href="view.php?id=<?= $notice['id']; ?>" class="notice-item <?= $colorClass ?>" onclick="event.stopPropagation();">
                            <span><i class="fa-solid fa-bell me-1"></i> <?= htmlspecialchars(!empty($notice['category']) ? $notice['category'] : 'Notice'); ?></span>
                        </a>
                    <?php endforeach; ?>

                    <?php if ($totalNotices > $maxDisplay): $remaining = $totalNotices - $maxDisplay; 
                        $formattedDateLink = $year . '-' . sprintf('%02d', $month) . '-' . sprintf('%02d', $day);
                    ?>
                        <a href="day_notices.php?date=<?= $formattedDateLink ?>" class="more-notice-badge text-decoration-none" onclick="event.stopPropagation();">
                            +<?= $remaining ?> more
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <!-- ২. মোবাইল ভিউ (সর্বোচ্চ ২টি বেল আইকন এবং বেশি থাকলে +X ব্যাজ) -->
            <div class="mobile-bell-container">
    <?php 
    if (isset($noticeData[$day])): 
        $mTotal = count($noticeData[$day]);
        $mShow = 2;
        $mCount = 0;
        $formattedDateLink = $year . '-' . sprintf('%02d', $month) . '-' . sprintf('%02d', $day);
        
        foreach ($noticeData[$day] as $notice): 
            if ($mCount >= $mShow) break;
            $mCount++;
            $colorClass = getCategoryClass($notice['category']);
    ?>
        <span class="mobile-bell-icon <?= $colorClass ?>">
            <i class="fa-solid fa-bell"></i>
        </span>
    <?php 
        endforeach;
        if ($mTotal > $mShow): 
            $mRemaining = $mTotal - $mShow;
    ?>
        <span class="mobile-more-badge">+<?= $mRemaining ?> more</span>
    <?php 
        endif;
    endif; 
    ?>
</div>

        </div> <!-- calendar-day শেষ -->

        <?php } ?> <!-- লুপের ক্লোজিং ব্র্যাকেট (যা মিসিং ছিল) -->
    </div> <!-- calendar-grid শেষ -->

    <!-- ক্যাটাগরি কালার লেজেন্ড (শুধুমাত্র ফোনে দেখাবে: d-md-none) -->
    <div class="compact-legend-box d-md-none">
    <div class="d-flex flex-wrap gap-1 align-items-center justify-content-center">
        <span class="legend-item bg-danger bg-opacity-25 text-danger border border-danger border-opacity-50"><span class="holiday-indicator-dot me-1"></span> Holiday</span>
        <span class="legend-item cat-job-sheet"><i class="fa-solid fa-bell"></i> Job Sheet</span>
        <span class="legend-item cat-class-test"><i class="fa-solid fa-bell"></i> Class Test</span>
        <span class="legend-item cat-quiz-test"><i class="fa-solid fa-bell"></i> Quiz Test</span>
        <span class="legend-item cat-presentation"><i class="fa-solid fa-bell"></i> Presentation</span>
        <span class="legend-item cat-assignment"><i class="fa-solid fa-bell"></i> Assignment</span>
        <span class="legend-item cat-practical"><i class="fa-solid fa-bell"></i> Practical</span>
        <span class="legend-item cat-sessional"><i class="fa-solid fa-bell"></i> Sessional</span>
        <span class="legend-item cat-practical-exam"><i class="fa-solid fa-bell"></i> Practical Exam</span>
        <span class="legend-item cat-mid-exam"><i class="fa-solid fa-bell"></i> Mid Exam</span>
        <span class="legend-item cat-semester-final"><i class="fa-solid fa-bell"></i> Semester Final</span>
        <span class="legend-item cat-class-starts"><i class="fa-solid fa-bell"></i> Class Starts</span>
        <span class="legend-item cat-class-off"><i class="fa-solid fa-bell"></i> Class Off</span>
        
        <!-- নতুন ৭টি ক্যাটাগরি লেজেন্ড -->
        <span class="legend-item cat-event"><i class="fa-solid fa-bell"></i> Event</span>
        <span class="legend-item cat-urgent-notice"><i class="fa-solid fa-bell"></i> Urgent Notice</span>
        <span class="legend-item cat-class-related"><i class="fa-solid fa-bell"></i> Class Related</span>
        <span class="legend-item cat-academic-notice"><i class="fa-solid fa-bell"></i> Academic Notice</span>
        <span class="legend-item cat-general-notice"><i class="fa-solid fa-bell"></i> General Notice</span>
        <span class="legend-item cat-result"><i class="fa-solid fa-bell"></i> Result</span>
        <span class="legend-item cat-others"><i class="fa-solid fa-bell"></i> Others</span>
    </div>
</div>
    
    <!-- Student Profile অ্যাক্সেস কার্ড (সব ডিভাইসে ১০০% রেসপন্সিভ) -->
<div class="glass-card p-3 p-md-4 mt-4 border border-info border-opacity-25 shadow-sm" style="background: linear-gradient(135deg, rgba(16, 23, 31, 0.92) 0%, rgba(13, 92, 117, 0.25) 100%);">
    <div class="d-flex flex-column flex-sm-row align-items-center justify-content-between gap-3 text-center text-sm-start">
        <div class="d-flex flex-column flex-sm-row align-items-center gap-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: rgba(56, 189, 248, 0.14); border: 1px solid rgba(56, 189, 248, 0.35); color: #38bdf8;">
                <i class="fa-solid fa-user-graduate fs-5"></i>
            </div>
            <div>
                <h6 class="fw-bold text-white mb-1">Student Academic Profile</h6>
                <small class="text-secondary d-block">Check your live attendance &amp; semester results</small>
            </div>
        </div>
        <a href="../student_profile/login.php" class="btn fw-bold px-4 py-2 rounded-pill shadow-sm d-inline-flex align-items-center justify-content-center gap-2 col-12 col-sm-auto text-dark" style="background: linear-gradient(135deg, #38bdf8 0%, #2dd4bf 100%); border: none;">
            <span>Student Profile</span>
            <i class="fa-solid fa-arrow-right"></i>
        </a>
    </div>
</div>

<!-- ক্লাস রুটিন সেকশন -->
    <div class="glass-card p-4 mt-5">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <h5 class="mb-0 fw-bold text-white">
                <i class="fa-solid fa-clock text-primary me-2"></i> Class Routine
            </h5>
            <a href="<?= $driveUrl ?>" target="_blank" class="btn btn-success btn-sm px-3 rounded-pill shadow-sm">
                <i class="fa-brands fa-google-drive me-1"></i> View Drive
            </a>
        </div>

        <div class="card-body p-0">
            <div class="d-flex flex-wrap gap-2 justify-content-center mb-4" id="routineDaysTab" role="tablist">
                <?php foreach ($weekDays as $d): ?>
                    <?php 
                    $isCurrentDayTab = ($d == $currentDayName); $activeClass = ($d == $defaultActiveDay) ? 'active' : '';
                    ?>
                    <button class="nav-link day-nav-btn btn btn-outline-primary <?= $activeClass ?> fw-bold py-2 px-3 shadow-sm" 
                            id="pills-<?= strtolower($d) ?>-tab" 
                            data-bs-toggle="pill" 
                            data-bs-target="#pills-<?= strtolower($d) ?>" 
                            type="button" role="tab"><?= $d ?> <?= $isCurrentDayTab ? "(Today)" : "" ?></button>
                <?php endforeach; ?>
            </div>

            <div class="tab-content" id="routineDaysTabContent">
                <?php foreach ($weekDays as $d): ?>
                <?php $paneActive = ($d == $defaultActiveDay) ? 'show active' : ''; ?>
                <div class="tab-pane fade <?= $paneActive ?>" id="pills-<?= strtolower($d) ?>" role="tabpanel">
                    
                    <div class="row g-3 justify-content-center">
                        <?php 
                        $hasPeriod = false;
                        for ($p = 1; $p <= 7; $p++):
                            if (isset($routineData[$d][$p])): $hasPeriod = true;
                                $item = $routineData[$d][$p]; $span = 1;
                                if (isset($item['period_span']) && $item['period_span'] > 1) {
                                    $span = (int)$item['period_span'];
                                }
                        ?>
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="glass-card p-3 h-100 border-start border-4 border-primary text-start shadow-sm" style="background: #111827;">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <?php 
                                        $periodText = "Period " . $p;
                                        if ($span > 1) { $periodText .= " - " . ($p + $span - 1); }
                                        ?>
                                        <span class="badge bg-primary bg-opacity-25 text-info border border-primary border-opacity-50"><?= $periodText ?></span>
                                        <small class="text-muted fw-bold"><i class="fa-regular fa-clock me-1"></i> <?= htmlspecialchars($item['time_slot']) ?></small>
                                    </div>
                                    
                                    <div class="mb-2">
                                        <h5 class="fw-bold text-primary mb-1">
                                            <span class="text-muted fs-6 fw-normal">Subject Code:</span> <?= htmlspecialchars($item['subject_code']) ?>
                                        </h5>
                                        <?php if (!empty($item['subject_name'])): ?>
                                            <div class="text-secondary small">
                                                <strong class="text-muted fw-bold">Subject Name:</strong> <?= htmlspecialchars($item['subject_name']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="small text-secondary">
                                        <div class="mb-1 fw-bold"><i class="fa-solid fa-user-tie text-primary me-2"></i><strong class="text-muted">Teacher:</strong> <?= htmlspecialchars($item['teacher_code']) ?></div>
                                        <div><i class="fa-solid fa-door-open text-primary me-2"></i><strong class="text-muted fw-bold">Room:</strong> <?= htmlspecialchars($item['room_no']) ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php 
                                if ($span > 1) { $p += ($span - 1); }
                            endif; 
                        endfor; 
                        
                        if (!$hasPeriod):
                        ?>
                            <div class="col-12 text-center py-4">
                                <p class="text-muted mb-0"><i class="fa-solid fa-mug-hot me-1"></i> No classes scheduled for <?= $d ?>.</p>
                            </div>
                        <?php endif; ?>
                    </div>

                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- প্র্যাকটিক্যাল জব এবং সেশনাল টাস্ক -->
    <div class="mt-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold text-white mb-0">
                <i class="fa-solid fa-book-bookmark text-primary me-2"></i> Practical Job & Sessional Tasks
            </h5>
        </div>
        
        <div class="row g-3">
            <?php if (count($subjects) > 0): ?>
                <?php foreach ($subjects as $sub): ?>
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="subject-card" onclick="openTaskModal(<?= $sub['id'] ?>, '<?= htmlspecialchars($sub['subject_code'], ENT_QUOTES) ?> - <?= htmlspecialchars($sub['subject_name'], ENT_QUOTES) ?>')">
                        <i class="fa-solid fa-book-open text-primary fs-3 mb-2"></i>
                        <h6 class="fw-bold text-white mb-1"><?= htmlspecialchars($sub['subject_code']) ?></h6>
                        <small class="text-muted d-block text-truncate"><?= htmlspecialchars($sub['subject_name']) ?></small>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12"><p class="text-muted text-center py-3">কোনো বিষয় পাওয়া যায়নি।</p></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- রেজাল্ট জোন পোর্টাল দুটি -->
    <div class="row mt-5" id="resultPortalZone">
        <div class="col-12">
            <div class="glass-card p-4 text-center shadow-sm">
                <h5 class="fw-bold text-white mb-3"><i class="fa-solid fa-graduation-cap text-primary me-2"></i> Result Zone Portals</h5>
                <div class="d-flex justify-content-center align-items-center flex-wrap gap-3">
                    <a href="https://result.bteb.gov.bd/result-search" target="_blank" class="btn btn-primary px-4 py-2 rounded-pill fw-bold shadow-sm">
                        <i class="fa-solid fa-arrow-up-right-from-square me-2"></i> Result Portal 1
                    </a>
                    <a href="https://btebresultszone.com/results" target="_blank" class="btn btn-success px-4 py-2 rounded-pill fw-bold shadow-sm">
                        <i class="fa-solid fa-arrow-up-right-from-square me-2"></i> Result Portal 2
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- সেমিস্টার ফি পেমেন্ট কার্ড -->
    <div class="row mt-4" id="semesterFeeZone">
        <div class="col-12">
            <div class="glass-card p-4 text-center shadow-sm">
                <h5 class="fw-bold text-white mb-3"><i class="fa-solid fa-credit-card text-warning me-2"></i> Semester Fee Payment</h5>
                <div class="d-flex justify-content-center align-items-center flex-wrap gap-3">
                    <a href="https://billpay.sonalibank.com.bd/dhkPolytechnic/Payment/" target="_blank" class="btn btn-warning px-4 py-2 rounded-pill fw-bold shadow-sm text-dark">
                        <i class="fa-solid fa-arrow-up-right-from-square me-2"></i> Pay Semester Fee
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- ইমারজেন্সি কন্টাক্টস (Emergency Contacts) -->
    <div class="row mt-5">
        <div class="col-12 mb-3">
            <h5 class="fw-bold text-white">
                <i class="fa-solid fa-address-book text-danger me-2"></i> Emergency Contacts (CRs)
            </h5>
        </div>
        
        <div class="col-12 col-md-6 mb-3">
            <div class="contact-card">
                <div class="contact-avatar">MR</div>
                <h5 class="fw-bold text-white mb-1">Md. Mahathe Hasan Tuhin</h5>
                <p class="text-muted small mb-3">Class Representative (CR)</p>
                <div class="social-btns">
                    <a href="https://wa.me/+8801570201997" class="btn-whatsapp" title="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
                    <a href="tel:+8801617612493" class="btn-phone" title="Phone Call"><i class="fa-solid fa-phone"></i></a>
                    <a href="https://www.facebook.com/md.mehedi.hassan.tuhin.32152" class="btn-facebook" title="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 mb-3">
            <div class="contact-card">
                <div class="contact-avatar" style="background: linear-gradient(135deg, #fd7e14 0%, #ffc107 100%);">ZZ</div>
                <h5 class="fw-bold text-white mb-1">Zazam Ahamad Zawad</h5>
                <p class="text-muted small mb-3">Class Representative (CR)</p>
                <div class="social-btns">
                    <a href="https://wa.me/+8801961255886" class="btn-whatsapp" title="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
                    <a href="tel:+8801961255886" class="btn-phone" title="Phone Call"><i class="fa-solid fa-phone"></i></a>
                    <a href="https://www.facebook.com/Zazam.Ahamad.zawad" class="btn-facebook" title="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center mt-5 mb-3 text-muted">
        <small>© <?= date("Y"); ?> CRIMS - Dhaka Government Polytechnic Institute</small>
    </div>

</div>

<!-- মোবাইল বটম ন্যাভবার -->
<div class="mobile-bottom-nav">
    <a href="#" class="active"><i class="fa-solid fa-house"></i> Home</a>
    <a href="#routineDaysTab"><i class="fa-solid fa-calendar-days"></i> Routine</a>
    <a href="<?= $driveUrl ?>"><i class="fa-brands fa-google-drive"></i> Drive</a>
    <a href="#resultPortalZone"><i class="fa-solid fa-graduation-cap"></i> Result</a>
</div>

<!-- সরকারি ছুটির পপ-আপ মডাল -->
<div class="modal fade" id="noticeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title fw-bold" id="noticeModalHeader">Notice Details</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4" id="noticeModalContainer">
      </div>
    </div>
  </div>
</div>

<!-- টাস্ক বা জব ডিটেইলস মডাল -->
<div class="modal fade" id="taskModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title fw-bold" id="taskModalTitle">Subject Details</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4" id="taskModalBody">
        <div class="text-center py-4">
            <div class="spinner-border text-primary"></div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>


// রিলোড দিলে স্ক্রল হিস্ট্রি ভুলে গিয়ে পেজ একদম প্রথমে (উপরে) চলে আসবে
if (history.scrollRestoration) {
    history.scrollRestoration = 'manual';
}
window.scrollTo(0, 0);


if (performance.getEntriesByType("navigation").length > 0 && performance.getEntriesByType("navigation")[0].type === "reload") {
    const currentUrl = new URL(window.location.href);
    if (currentUrl.searchParams.has('month') || currentUrl.searchParams.has('year')) {
        currentUrl.searchParams.delete('month');
        currentUrl.searchParams.delete('year');
        window.location.replace(currentUrl.pathname + (currentUrl.search ? currentUrl.search : ''));
    }
}

document.addEventListener("DOMContentLoaded", function() {
    <?php if (count($todayNotices) > 0): ?>
        let todayNoticeList = <?= json_encode($todayNotices) ?>;
        openNoticeListModal(todayNoticeList);
    <?php endif; ?>
});

// শুধুমাত্র সরকারি বন্ধের তারিখে ক্লিক করলেই এই পপ-আপ মডাল আসবে
function showHolidayPopup(holidayData) {
    if (!holidayData || !holidayData.name) return;

    let container = document.getElementById('noticeModalContainer');
    document.getElementById('noticeModalHeader').innerText = 'Holiday Details';
    container.innerHTML = `
        <div class="card border-0 bg-danger bg-opacity-10 mb-2 p-3 shadow-sm rounded-3 border border-danger border-opacity-25 text-center">
            <span class="badge bg-danger mb-2 align-self-center px-3 py-1">Holiday</span>
            <h5 class="fw-bold text-white mb-1"><i class="fa-solid fa-umbrella-beach text-danger me-2"></i> ${holidayData.name}</h5>
            <small class="text-danger fw-semibold">${holidayData.date}</small>
        </div>
    `;

    var noticeModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('noticeModal'));
    noticeModal.show();
}

function openNoticeListModal(notices) {
    let container = document.getElementById('noticeModalContainer');
    document.getElementById('noticeModalHeader').innerText = 'Today\'s Notices';
    container.innerHTML = '';

    if (!notices || notices.length === 0) {
        container.innerHTML = '<p class="text-muted text-center">কোনো নোটিশ পাওয়া যায়নি।</p>';
    } else {
        notices.forEach(function(item) {
            let catName = item.category ? item.category : 'Notice';
            let noticeDate = item.notice_date ? item.notice_date : '';
            let cardHtml = `
                <div class="card border-0 bg-dark mb-3 p-3 shadow-sm rounded-3" style="border: 1px solid rgba(255,255,255,0.08) !important;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-primary bg-opacity-25 text-info border border-primary border-opacity-50">${catName}</span>
                        <small class="text-muted">${noticeDate}</small>
                    </div>
                    <h6 class="fw-bold text-white mb-3">${item.title}</h6>
                    <div class="text-end">
                        <a href="view.php?id=${item.id}" class="btn btn-sm btn-primary px-3 rounded-pill">
                            <i class="fa-solid fa-eye me-1"></i> View Details
                        </a>
                    </div>
                </div>
            `;
            container.innerHTML += cardHtml;
        });
    }
    
    var noticeModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('noticeModal'));
    noticeModal.show();
}

function handleDateClick(dateStr, holidayData, hasNotice) {
    if (window.innerWidth <= 768) {
        // মোবাইলে সরকারি বন্ধ থাকলে পপ-আপ আসবে
        if (holidayData && holidayData.name) {
            showHolidayPopup(holidayData);
        } else if (hasNotice) {
            // নোটিশ থাকলে ওই নির্দিষ্ট দিনের নোটিশ পেজে নিয়ে যাবে
            window.location.href = 'day_notices.php?date=' + encodeURIComponent(dateStr);
        }
    } else {
        // কম্পিউটারে শুধুমাত্র সরকারি ছুটির দিনে পপ-আপ আসবে
        if (holidayData && holidayData.name) {
            showHolidayPopup(holidayData);
        }
    }
}

function openTaskModal(subjectId, subjectTitle) {
    document.getElementById('taskModalTitle').innerText = subjectTitle;
    document.getElementById('taskModalBody').innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div><p class="mt-2 text-muted">ডাটা লোড হচ্ছে...</p></div>';
    
    var taskModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('taskModal'));
    taskModal.show();

    fetch('get_tasks.php?subject_id=' + subjectId)
        .then(response => response.text())
        .then(text => {
            const cleanText = text.trim();
            const data = JSON.parse(cleanText);

            if (data && data.tabs !== undefined) {
                document.getElementById('taskModalBody').innerHTML = data.tabs + data.contents;
            } else {
                document.getElementById('taskModalBody').innerHTML = '<p class="text-muted text-center py-3">কোনো তথ্য পাওয়া যায়নি।</p>';
            }
        })
        .catch(err => {
            console.error('Fetch error:', err);
            document.getElementById('taskModalBody').innerHTML = '<p class="text-danger text-center py-3">ডাটা প্রসেস করতে সমস্যা হচ্ছে!</p>';
        });
}
</script>

</body>
</html>