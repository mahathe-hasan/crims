<?php
session_start();
require_once("../config/database.php");

if (!isset($_SESSION['student_portal_id'])) {
    header("Location: login.php");
    exit;
}

$student_id = (int)$_SESSION['student_portal_id'];
$pass_msg = '';
$pass_msg_type = 'success';

// ১. শিক্ষার্থীর তথ্য আনা
$stmt = $conn->prepare("SELECT * FROM students WHERE id = ? LIMIT 1");
$stmt->execute([$student_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    session_destroy();
    header("Location: login.php");
    exit;
}

// ২. পাসওয়ার্ড পরিবর্তন লজিক
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_my_password'])) {
    $current_pass = trim($_POST['current_password'] ?? '');
    $new_pass     = trim($_POST['new_password'] ?? '');
    $confirm_pass = trim($_POST['confirm_password'] ?? '');

    $current_valid = false;
    if (!empty($student['password'])) {
        $current_valid = password_verify($current_pass, $student['password']);
    } else {
        $current_valid = ($current_pass === trim($student['registration']));
    }

    if (!$current_valid) {
        $pass_msg = "Current password is incorrect!";
        $pass_msg_type = "danger";
    } elseif (strlen($new_pass) < 4) {
        $pass_msg = "New password must be at least 4 characters!";
        $pass_msg_type = "danger";
    } elseif ($new_pass !== $confirm_pass) {
        $pass_msg = "New password and Confirm password do not match!";
        $pass_msg_type = "danger";
    } else {
        $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
        $upd = $conn->prepare("UPDATE students SET password = ? WHERE id = ?");
        $upd->execute([$hashed, $student_id]);
        $pass_msg = "Password updated successfully!";
        $pass_msg_type = "success";

        $stmt->execute([$student_id]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

// ৩. সাবজেক্ট অনুযায়ী Class Attendance Record আনা
$sql_att = "
    SELECT 
        COALESCE(attendance_sessions.course_code, 'N/A') AS course_code,
        MAX(attendance_sessions.course_name) AS course_name,
        COUNT(attendance_details.id) AS total_classes,
        SUM(CASE WHEN attendance_details.status = 'Present' THEN 1 ELSE 0 END) AS total_present,
        SUM(CASE WHEN attendance_details.status != 'Present' THEN 1 ELSE 0 END) AS total_absent
    FROM attendance_details
    INNER JOIN attendance_sessions ON attendance_sessions.id = attendance_details.session_id
    WHERE attendance_details.student_id = ?
    GROUP BY attendance_sessions.course_code
    ORDER BY attendance_sessions.course_code ASC
";
$stmt_att = $conn->prepare($sql_att);
$stmt_att->execute([$student_id]);
$subject_summary = $stmt_att->fetchAll(PDO::FETCH_ASSOC);

$total_all_classes = 0;
$total_all_present = 0;
foreach ($subject_summary as $sub) {
    $total_all_classes += (int)$sub['total_classes'];
    $total_all_present += (int)$sub['total_present'];
}
$overall_att_percent = ($total_all_classes > 0) ? round(($total_all_present / $total_all_classes) * 100, 1) : 0;
$ring_color = $overall_att_percent >= 75 ? '#10b981' : ($overall_att_percent >= 60 ? '#f59e0b' : '#ef4444');

// ৪. সেমিস্টার রেজাল্ট ও BTEB ডিপ্লোমা ইঞ্জিনিয়ারিং নিয়মে Overall CGPA হিসাব
$stmt_res = $conn->prepare("SELECT * FROM student_results WHERE student_id = ? ORDER BY semester ASC");
$stmt_res->execute([$student_id]);
$results = $stmt_res->fetchAll(PDO::FETCH_ASSOC);

$bteb_weights = [
    '1st' => 5,
    '2nd' => 5,
    '3rd' => 5,
    '4th' => 10,
    '5th' => 15,
    '6th' => 20,
    '7th' => 25,
    '8th' => 15
];

$weighted_points = 0;
$total_weight_used = 0;
$total_refs = 0;

foreach ($results as $r) {
    if ($r['status'] === 'Passed' && $r['gpa'] > 0) {
        $sem_str = $r['semester'];
        $weight = 0;
        foreach ($bteb_weights as $key => $w) {
            if (stripos($sem_str, $key) !== false) {
                $weight = $w;
                break;
            }
        }
        if ($weight > 0) {
            $weighted_points += ((float)$r['gpa'] * $weight);
            $total_weight_used += $weight;
        }
    } elseif ($r['status'] === 'Referred') {
        $total_refs += (int)$r['referred_count'];
    }
}

$overall_cgpa = ($total_weight_used > 0) ? number_format($weighted_points / $total_weight_used, 2) : '0.00';

$photoName = $student['photo'] ?? '';
$photoPath = (!empty($photoName) && file_exists("../uploads/" . $photoName)) 
    ? "../uploads/" . htmlspecialchars($photoName) 
    : "https://cdn-icons-png.flaticon.com/512/3135/3135715.png";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($student['full_name']) ?> | Student Profile</title>
    <link rel="icon" type="image/png" href="../assets/images/dgpi_logo.png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-main: #090d16;
            --card-bg: #111827;
            --card-inner: #1a2234;
            --border-color: rgba(255, 255, 255, 0.08);
            --accent-blue: #3b82f6;
            --accent-cyan: #06b6d4;
        }
        body {
            background-color: var(--bg-main);
            background-image: radial-gradient(circle at top right, rgba(59, 130, 246, 0.12), transparent 40%),
                              radial-gradient(circle at bottom left, rgba(6, 182, 212, 0.08), transparent 40%);
            color: #f1f5f9;
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .pro-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35);
            overflow: hidden;
        }
        /* প্রিমিয়াম Deep Teal-Cyan হেডার ব্যানার */
        .card-banner {
            background: linear-gradient(135deg, #0f766e 0%, #0369a1 100%);
            border-bottom: 1px solid rgba(56, 189, 248, 0.25);
            padding: 15px 22px;
        }
        .info-pill {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 0.82rem;
            color: #e2e8f0;
        }
        .table-custom {
            --bs-table-bg: transparent;
            --bs-table-color: #f1f5f9;
            --bs-table-border-color: rgba(255, 255, 255, 0.08);
        }
        .table-custom thead th {
            background: rgba(255, 255, 255, 0.05);
            color: #94a3b8;
            font-weight: 700;
            font-size: 0.78rem;
            letter-spacing: 0.6px;
            padding: 13px 16px;
        }
        .table-custom tbody td {
            padding: 14px 16px;
            vertical-align: middle;
        }
        .mobile-sub-card {
            background: var(--card-inner);
            border: 1px solid var(--border-color);
            border-radius: 14px;
        }
        /* রিফ্রেশ দিলে স্মুথ প্রোগ্রেস বার ফিল অ্যানিমেশন */
        .animated-progress {
            width: 0%;
            transition: width 1.2s cubic-bezier(0.22, 1, 0.36, 1);
        }
        /* গোল সার্কুলার রিং (% দেখানোর জন্য) */
        .overall-circle-ring {
            width: 68px;
            height: 68px;
            border-radius: 50%;
            background: conic-gradient(var(--ring-clr) calc(var(--pct, 0) * 1%), rgba(255, 255, 255, 0.08) 0);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .overall-circle-inner {
            width: 54px;
            height: 54px;
            border-radius: 50%;
            background: #101726;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 0.82rem;
            color: #ffffff;
        }
        /* ছোট ও প্রিমিয়াম Semester Result কার্ড */
        .sem-box {
            background: linear-gradient(160deg, #161f33 0%, #0d1320 100%);
            border: 1px solid rgba(255, 255, 255, 0.09);
            border-radius: 14px;
            padding: 12px 10px;
            transition: all 0.25s ease;
        }
        .sem-box.passed-card {
            background: linear-gradient(160deg, rgba(16, 185, 129, 0.14) 0%, #0f1726 100%);
            border-color: rgba(16, 185, 129, 0.32);
        }
        .sem-box.ref-card {
            background: linear-gradient(160deg, rgba(239, 68, 68, 0.14) 0%, #0f1726 100%);
            border-color: rgba(239, 68, 68, 0.32);
        }
        .sem-box:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.35);
        }
        .gpa-pill {
            display: inline-block;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            padding: 2px 10px;
            border-radius: 50px;
            background: rgba(16, 185, 129, 0.16);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.35);
        }
        .ref-pill {
            display: inline-block;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            padding: 2px 10px;
            border-radius: 50px;
            background: rgba(239, 68, 68, 0.16);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.35);
        }
        .cgpa-highlight {
            background: linear-gradient(135deg, rgba(29, 78, 216, 0.22) 0%, rgba(6, 182, 212, 0.18) 100%);
            border: 1px solid rgba(56, 189, 248, 0.35);
            border-radius: 18px;
        }
        /* Account সেকশন */
        .account-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 20px;
            text-decoration: none;
            color: #f8fafc;
            background: transparent;
            border: none;
            width: 100%;
            transition: background 0.2s;
        }
        .account-row:hover {
            background: rgba(255, 255, 255, 0.04);
        }
        .icon-badge {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
        }
        /* সুন্দর Change Password Modal ইনপুট */
        .modal-glass {
            background: #111827;
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 22px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.65);
        }
        .pro-input-group .input-group-text {
            background: #0b101b;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-right: none;
            color: #38bdf8;
            border-radius: 12px 0 0 12px;
        }
        .pro-input-group .form-control {
            background: #0b101b;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-left: none;
            color: #ffffff;
            padding: 11px 14px;
            border-radius: 0 12px 12px 0;
        }
        .pro-input-group .form-control:focus {
            box-shadow: none;
            border-color: #38bdf8;
        }
        .pro-input-group:focus-within .input-group-text {
            border-color: #38bdf8;
        }
        /* ফুটার ডিজাইন */
        .pro-footer {
            margin-top: auto;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            background: rgba(11, 16, 27, 0.7);
            padding: 18px 15px;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="container py-4" style="max-width: 1050px;">

    <!-- শীর্ষে ছোট ব্র্যান্ড হেডার -->
    <div class="d-flex justify-content-between align-items-center mb-4 px-1">
        <div class="d-flex align-items-center gap-2">
           <a href="../public_notice/index.php" class="text-decoration-none">
            <img src="../assets/images/dgpi_logo.png" alt="DGPI" class="bg-white rounded-circle p-1" style="width: 38px; height: 38px; object-fit: contain;">
        </a><div>
                <h6 class="mb-0 fw-bold text-white">Dhaka Government Polytechnic Institute</h6>
                <small class="text-info" style="font-size: 0.75rem;">Student Academic Profile • CRIMS</small>
            </div>
        </div>
        <span class="badge bg-primary bg-opacity-25 text-info border border-info border-opacity-25 px-3 py-2 rounded-pill d-none d-sm-inline-block">
            Session: <?= htmlspecialchars($student['session']) ?>
        </span>
    </div>

    <?php if (!empty($pass_msg)): ?>
        <div class="alert alert-<?= $pass_msg_type ?> alert-dismissible fade show rounded-4">
            <?= htmlspecialchars($pass_msg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- ১. প্রোফাইল হেডার কার্ড -->
    <div class="pro-card p-4 mb-4">
        <div class="row align-items-center g-4 text-center text-md-start">
            <div class="col-md-auto">
                <img src="<?= $photoPath ?>" alt="Student Photo" class="rounded-circle border border-3 border-info shadow-lg" style="width: 96px; height: 96px; object-fit: cover;">
            </div>
            <div class="col-md">
                <h3 class="fw-extrabold text-white mb-1"><?= htmlspecialchars($student['full_name']) ?></h3>
                <p class="text-info fw-semibold mb-3 small">
                    <i class="fa-solid fa-microchip me-1"></i> <?= htmlspecialchars($student['department']) ?>
                </p>
                <div class="d-flex flex-wrap justify-content-center justify-content-md-start gap-2">
                    <span class="info-pill"><i class="fa-solid fa-id-badge text-warning me-1"></i> Roll: <strong class="text-white"><?= htmlspecialchars($student['roll']) ?></strong></span>
                    <span class="info-pill"><i class="fa-solid fa-address-card text-info me-1"></i> Reg No: <strong class="text-white"><?= htmlspecialchars($student['registration']) ?></strong></span>
                    <span class="info-pill"><i class="fa-solid fa-layer-group text-primary me-1"></i> Semester: <strong class="text-white"><?= htmlspecialchars($student['semester']) ?></strong></span>
                    <span class="info-pill"><i class="fa-solid fa-users text-success me-1"></i> Section: <strong class="text-white"><?= htmlspecialchars($student['section']) ?></strong></span>
                    <span class="info-pill"><i class="fa-solid fa-clock text-warning me-1"></i> Shift: <strong class="text-white"><?= htmlspecialchars($student['shift']) ?></strong></span>
                </div>
            </div>
        </div>
    </div>

    <!-- ২. Class Attendance Record (Overall ব্যাজ বাদ দেওয়া হয়েছে এবং নতুন Teal-Cyan কালার দেওয়া হয়েছে) -->
    <div class="pro-card mb-4">
        <div class="card-banner">
            <h5 class="mb-0 fw-bold text-white"><i class="fa-solid fa-clipboard-check me-2"></i> Class Attendance Record</h5>
        </div>

        <div class="p-3 p-md-4">
            <?php if (count($subject_summary) > 0): ?>

                <!-- মোবাইল ভিউ: কার্ড ভিত্তিক ডিজাইন + গোল রিং সহ Overall Attendance কার্ড -->
                <div class="d-md-none">
                    <div class="mobile-sub-card p-3 mb-3 border-info border-opacity-50" style="background: linear-gradient(135deg, rgba(15,118,110,0.22), rgba(3,105,161,0.18));">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="small text-info fw-bold text-uppercase">Overall Attendance</span>
                                <div class="small text-light opacity-75 mt-1">
                                    Present: <strong class="text-white count-num" data-target="<?= $total_all_present ?>">0</strong> / <span class="count-num" data-target="<?= $total_all_classes ?>">0</span> Classes
                                </div>
                            </div>
                            <!-- গোল সার্কুলার রিং -->
                            <div class="overall-circle-ring" id="overallRing" data-target-pct="<?= $overall_att_percent ?>" style="--pct: 0; --ring-clr: <?= $ring_color ?>;">
                                <div class="overall-circle-inner">
                                    <span class="count-num" data-target="<?= $overall_att_percent ?>" data-decimals="<?= fmod($overall_att_percent, 1) != 0 ? '1' : '0' ?>">0</span>%
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- প্রতিটি সাবজেক্টের কার্ড -->
                    <div class="row g-3">
                        <?php foreach ($subject_summary as $sub): 
                            $total   = (int)$sub['total_classes'];
                            $present = (int)$sub['total_present'];
                            $absent  = (int)$sub['total_absent'];
                            $pct     = ($total > 0) ? round(($present / $total) * 100) : 0;
                            $color   = $pct >= 75 ? 'success' : ($pct >= 60 ? 'warning' : 'danger');
                        ?>
                            <div class="col-12">
                                <div class="mobile-sub-card p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <span class="badge bg-secondary mb-1"><?= htmlspecialchars($sub['course_code']) ?></span>
                                            <h6 class="fw-bold text-info mb-0"><?= htmlspecialchars($sub['course_name']) ?></h6>
                                        </div>
                                        <span class="badge bg-<?= $color ?> <?= $color == 'warning' ? 'text-dark' : '' ?> rounded-pill px-2 py-1">
                                            <span class="count-num" data-target="<?= $pct ?>">0</span>%
                                        </span>
                                    </div>
                                    <div class="progress my-2 bg-dark" style="height: 6px;">
                                        <div class="progress-bar bg-<?= $color ?> animated-progress" data-width="<?= $pct ?>%"></div>
                                    </div>
                                    <div class="row text-center mt-2 pt-2 border-top border-secondary border-opacity-25 g-0 small">
                                        <div class="col-4 border-end border-secondary border-opacity-25">
                                            <span class="text-secondary d-block">Total</span>
                                            <strong class="text-white count-num" data-target="<?= $total ?>">0</strong>
                                        </div>
                                        <div class="col-4 border-end border-secondary border-opacity-25">
                                            <span class="text-secondary d-block">Present</span>
                                            <strong class="text-success count-num" data-target="<?= $present ?>">0</strong>
                                        </div>
                                        <div class="col-4">
                                            <span class="text-secondary d-block">Absent</span>
                                            <strong class="text-danger count-num" data-target="<?= $absent ?>">0</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- কম্পিউটার/ট্যাবলেট ভিউ: টেবিল -->
                <div class="table-responsive d-none d-md-block">
                    <table class="table table-custom table-hover mb-0 border border-secondary border-opacity-25 rounded-3">
                        <thead>
                            <tr>
                                <th>CODE</th>
                                <th>COURSE NAME</th>
                                <th style="min-width: 190px;">ATTENDANCE RATIO</th>
                                <th class="text-center">TOTAL</th>
                                <th class="text-center">PRESENT</th>
                                <th class="text-center">ABSENT</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($subject_summary as $sub): 
                                $total   = (int)$sub['total_classes'];
                                $present = (int)$sub['total_present'];
                                $absent  = (int)$sub['total_absent'];
                                $pct     = ($total > 0) ? round(($present / $total) * 100) : 0;
                                $color   = $pct >= 75 ? 'success' : ($pct >= 60 ? 'warning' : 'danger');
                            ?>
                                <tr>
                                    <td class="fw-bold text-warning"><?= htmlspecialchars($sub['course_code']) ?></td>
                                    <td class="fw-semibold text-info"><?= htmlspecialchars($sub['course_name']) ?></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1 bg-dark" style="height: 7px;">
                                                <div class="progress-bar bg-<?= $color ?> animated-progress" data-width="<?= $pct ?>%"></div>
                                            </div>
                                            <span class="badge bg-<?= $color ?> <?= $color == 'warning' ? 'text-dark' : '' ?> rounded-pill px-2">
                                                <span class="count-num" data-target="<?= $pct ?>">0</span>%
                                            </span>
                                        </div>
                                    </td>
                                    <td class="text-center fw-bold text-white count-num" data-target="<?= $total ?>">0</td>
                                    <td class="text-center fw-bold text-success count-num" data-target="<?= $present ?>">0</td>
                                    <td class="text-center fw-bold text-danger count-num" data-target="<?= $absent ?>">0</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php else: ?>
                <div class="text-center py-4 text-secondary">
                    <i class="fa-solid fa-folder-open fa-2x mb-2 d-block"></i>
                    No class attendance records found yet.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ৩. Semester Result এবং নিচে শুধু Overall CGPA -->
    <div class="pro-card p-3 p-md-4 mb-4">
        <div class="mb-3">
            <h5 class="fw-bold text-white mb-0"><i class="fa-solid fa-graduation-cap text-info me-2"></i> Semester Result</h5>
        </div>

        <?php if (count($results) > 0): ?>
            <div class="row g-2 g-md-3 mb-4">
                <?php foreach ($results as $res): 
                    $is_passed = ($res['status'] === 'Passed');
                ?>
                    <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                        <div class="sem-box <?= $is_passed ? 'passed-card' : 'ref-card' ?> text-center h-100">
                            <div class="text-light opacity-75 fw-semibold mb-1" style="font-size: 0.75rem;"><?= htmlspecialchars($res['semester']) ?></div>
                            <?php if ($is_passed): ?>
                                <h4 class="fw-extrabold text-success mb-1"><?= number_format($res['gpa'], 2) ?></h4>
                                <span class="gpa-pill">GPA</span>
                            <?php else: ?>
                                <h4 class="fw-extrabold text-danger mb-1">Ref <?= (int)$res['referred_count'] ?></h4>
                                <span class="ref-pill">Referred</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-4 text-secondary mb-3">
                <i class="fa-solid fa-file-circle-question fa-2x mb-2 d-block"></i>
                No semester results published yet.
            </div>
        <?php endif; ?>

        <!-- নিচে শুধু Overall CGPA বক্স -->
        <div class="cgpa-highlight p-3 p-md-4">
            <div class="row align-items-center g-3 text-center text-md-start">
                <div class="col-md">
                    <h4 class="fw-bold text-white mb-1">Overall CGPA</h4>
                    <?php if ($total_refs > 0): ?>
                        <span class="badge bg-danger px-3 py-1 rounded-pill">
                            <i class="fa-solid fa-triangle-exclamation me-1"></i> Active Referred Subjects: <?= $total_refs ?> (Pending Clear)
                        </span>
                    <?php else: ?>
                        <small class="text-success fw-semibold"><i class="fa-solid fa-circle-check me-1"></i> All Published Semesters Cleared</small>
                    <?php endif; ?>
                </div>
                <div class="col-md-auto">
                    <div class="bg-dark bg-opacity-75 border border-info border-opacity-25 rounded-4 px-4 py-3 text-center">
                        <span class="display-6 fw-extrabold text-info"><?= $overall_cgpa ?></span>
                        <span class="text-secondary fw-semibold"> / 4.00</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ৪. ACCOUNT কার্ড (Change password এবং Log out) -->
    <div class="pro-card mb-3">
        <div class="px-4 pt-3 pb-1">
            <span class="small text-secondary fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.8px;">Account</span>
        </div>
        
        <button type="button" class="account-row border-bottom border-secondary border-opacity-10" data-bs-toggle="modal" data-bs-target="#changePassModal">
            <div class="d-flex align-items-center gap-3">
                <div class="icon-badge bg-warning bg-opacity-10 text-warning">
                    <i class="fa-solid fa-key"></i>
                </div>
                <span class="fw-semibold">Change password</span>
            </div>
            <i class="fa-solid fa-chevron-right text-secondary small"></i>
        </button>

        <a href="logout.php" class="account-row text-danger">
            <div class="d-flex align-items-center gap-3">
                <div class="icon-badge bg-danger bg-opacity-10 text-danger">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </div>
                <span class="fw-semibold">Log out</span>
            </div>
        </a>
    </div>

</div>

<!-- ৫. প্রফেশনাল ফুটার -->
<footer class="pro-footer py-3">
    <div class="container" style="max-width: 1050px;">
        <small class="text-secondary" style="font-size: 0.75rem;">
            &copy; <?= date('Y') ?> CRIMS - Dhaka Government Polytechnic Institute
        </small>
    </div>
</footer>

<!-- সুন্দর Change Password পপআপ মডেল -->
<div class="modal fade" id="changePassModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
        <div class="modal-content modal-glass text-white">
            <div class="modal-header border-bottom border-secondary border-opacity-25 px-4 pt-4 pb-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="icon-badge bg-info bg-opacity-10 text-info">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <div>
                        <h6 class="modal-title fw-bold text-white mb-0">Change Password</h6>
                        <small class="text-secondary" style="font-size: 0.73rem;">Update your account security</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body px-4 py-3">
                    <div class="mb-3">
                        <label class="form-label small text-secondary fw-semibold">Current Password</label>
                        <div class="input-group pro-input-group">
                            <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                            <input type="password" name="current_password" class="form-control" placeholder="••••••••" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-secondary fw-semibold">New Password</label>
                        <div class="input-group pro-input-group">
                            <span class="input-group-text"><i class="fa-solid fa-key"></i></span>
                            <input type="password" name="new_password" class="form-control" placeholder="••••••••" required>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small text-secondary fw-semibold">Confirm New Password</label>
                        <div class="input-group pro-input-group">
                            <span class="input-group-text"><i class="fa-solid fa-circle-check"></i></span>
                            <input type="password" name="confirm_password" class="form-control" placeholder="••••••••" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary border-opacity-25 px-4 pb-4 pt-3">
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="change_my_password" class="btn btn-info btn-sm fw-bold rounded-pill px-4 text-dark">
                        <i class="fa-solid fa-check me-1"></i> Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- প্রোগ্রেস বার এবং ০ থেকে সংখ্যা গণনার অ্যানিমেশন স্ক্রিপ্ট -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    // ১. প্রোগ্রেস বার রিফ্রেশ ফিল অ্যানিমেশন
    setTimeout(function() {
        document.querySelectorAll('.animated-progress').forEach(function(bar) {
            bar.style.width = bar.getAttribute('data-width');
        });
    }, 100);

    // ২. Overall % এবং Total, Present, Absent সংখ্যা ০ থেকে আসল মান পর্যন্ত অ্যানিমেশন
    const duration = 1100;
    const counters = document.querySelectorAll('.count-num');
    const overallRing = document.getElementById('overallRing');
    const targetRingPct = overallRing ? parseFloat(overallRing.getAttribute('data-target-pct')) || 0 : 0;

    let startTime = null;
    function animateNumbers(timestamp) {
        if (!startTime) startTime = timestamp;
        const progress = Math.min((timestamp - startTime) / duration, 1);
        const ease = 1 - Math.pow(1 - progress, 3);

        counters.forEach(function(el) {
            const target = parseFloat(el.getAttribute('data-target')) || 0;
            const decimals = parseInt(el.getAttribute('data-decimals') || '0', 10);
            const currentVal = target * ease;
            el.textContent = decimals > 0 ? currentVal.toFixed(decimals) : Math.round(currentVal);
        });

        if (overallRing) {
            overallRing.style.setProperty('--pct', (targetRingPct * ease).toFixed(1));
        }

        if (progress < 1) {
            requestAnimationFrame(animateNumbers);
        } else {
            counters.forEach(function(el) {
                const target = parseFloat(el.getAttribute('data-target')) || 0;
                const decimals = parseInt(el.getAttribute('data-decimals') || '0', 10);
                el.textContent = decimals > 0 ? target.toFixed(decimals) : Math.round(target);
            });
            if (overallRing) {
                overallRing.style.setProperty('--pct', targetRingPct);
            }
        }
    }

    requestAnimationFrame(animateNumbers);
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>