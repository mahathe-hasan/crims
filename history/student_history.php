<?php
require_once("../config/session.php");
require_once("../config/database.php");

include("../includes/header.php");
include("../includes/sidebar.php");
include("../includes/navbar.php");

$student_info = null;
$subject_summary = [];
$total_all_classes = 0;
$total_all_present = 0;

$roll = trim($_GET['roll'] ?? '');
// যদি রোল সার্চ দেওয়া হয়, তাহলে 0 Attendance মোড সম্পূর্ণ বন্ধ থাকবে
$show_zero_attendance = isset($_GET['zero_attendance']) && empty($roll);

// শুধুমাত্র জিরো অ্যাটেন্ডেন্সের জন্য ডেট ফিল্টার
$start_date = $_GET['start_date'] ?? '';
$end_date   = $_GET['end_date'] ?? '';
$zero_attendance_students = [];

if (!empty($roll)) {
    // ১. শিক্ষার্থীর তথ্য আনা
    $stmt_st = $conn->prepare("SELECT * FROM students WHERE roll = ? LIMIT 1");
    $stmt_st->execute([$roll]);
    $student_info = $stmt_st->fetch(PDO::FETCH_ASSOC);

    if ($student_info) {
        $student_id = $student_info['id'];

        // ২. বিষয় কোড (course_code) অনুযায়ী গ্রুপ করা
        $sql_summary = "
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

        $stmt_sum = $conn->prepare($sql_summary);
        $stmt_sum->execute([$student_id]);
        $subject_summary = $stmt_sum->fetchAll(PDO::FETCH_ASSOC);

        foreach ($subject_summary as $sub) {
            $total_all_classes += (int)$sub['total_classes'];
            $total_all_present += (int)$sub['total_present'];
        }
    }
} elseif ($show_zero_attendance) {
    // ডেট রেঞ্জের সাপেক্ষে জিরো অ্যাটেন্ডেন্স কুয়েরি
    $sql = "
        SELECT *
        FROM students
        WHERE id NOT IN (
            SELECT DISTINCT attendance_details.student_id 
            FROM attendance_details 
            INNER JOIN attendance_sessions ON attendance_sessions.id = attendance_details.session_id
            WHERE attendance_details.status = 'Present'
    ";
    
    $params = [];
    if (!empty($start_date) && !empty($end_date)) {
        $sql .= " AND attendance_sessions.class_date BETWEEN ? AND ?";
        $params[] = $start_date;
        $params[] = $end_date;
    }
    
    $sql .= " ) ORDER BY CAST(roll AS UNSIGNED) ASC";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $zero_attendance_students = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<div class="card shadow-sm border-0 rounded-4 bg-dark text-white">
    <div class="card-header bg-primary text-white py-3 px-4">
        <h4 class="mb-0 fw-bold"><i class="fa-solid fa-user-graduate me-2"></i> Student Attendance History</h4>
    </div>
    <div class="card-body p-3 p-md-4">
        
        <!-- সার্চ ফর্ম -->
        <form method="GET" class="row g-3 mb-4 align-items-end">
            <div class="col-md-5 col-sm-12">
                <label class="form-label text-warning fw-semibold">Enter Student Roll</label>
                <input type="text" name="roll" class="form-control bg-secondary text-white border-secondary" value="<?= htmlspecialchars($roll) ?>" placeholder="e.g. 242412">
            </div>
            <div class="col-md-2 col-6">
                <button type="submit" class="btn btn-success w-100 fw-semibold"><i class="fa-solid fa-search me-1"></i> Check</button>
            </div>
            <div class="col-md-3 col-6">
                <a href="?zero_attendance=1" class="btn btn-outline-danger w-100 fw-semibold text-nowrap">
                    <i class="fa-solid fa-user-xmark me-1"></i> 0 Attendance
                </a>
            </div>
            <div class="col-md-2 col-12">
                <a href="student_history.php" class="btn btn-outline-light w-100 fw-semibold">
                    <i class="fa-solid fa-rotate-left me-1"></i> Reset
                </a>
            </div>

            <!-- ডেট ফিল্টার (শুধুমাত্র ০ প্রেজেন্ট মোডে থাকবে, রোল সার্চ দিলে কখনই আসবে না) -->
            <?php if ($show_zero_attendance && empty($roll)): ?>
                <div class="col-12 mt-3 p-3 bg-secondary bg-opacity-10 border border-secondary rounded-3">
                    <h6 class="text-warning mb-3 fw-bold"><i class="fa-solid fa-calendar-days me-1"></i> Filter by Date Range (0 Attendance)</h6>
                    <div class="row g-3">
                        <input type="hidden" name="zero_attendance" value="1">
                        <div class="col-md-5">
                            <label class="form-label text-light small">Start Date</label>
                            <input type="date" name="start_date" class="form-control bg-secondary text-white border-secondary" value="<?= htmlspecialchars($start_date) ?>">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label text-light small">End Date</label>
                            <input type="date" name="end_date" class="form-control bg-secondary text-white border-secondary" value="<?= htmlspecialchars($end_date) ?>">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-info w-100 fw-semibold text-dark"><i class="fa-solid fa-filter me-1"></i> Apply</button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </form>

        <!-- শিক্ষার্থী সার্চ রেজাল্ট -->
        <?php if (!empty($roll)): ?>
            <?php if ($student_info): ?>
                
                <!-- প্রোফাইল কার্ড -->
                <div class="card bg-secondary bg-opacity-25 border border-secondary rounded-4 p-3 mb-4">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <?php 
                                $photoName = $student_info['photo'] ?? $student_info['image'] ?? '';
                                $photoPath = (!empty($photoName) && file_exists("../uploads/" . $photoName)) 
                                    ? "../uploads/" . htmlspecialchars($photoName) 
                                    : "https://cdn-icons-png.flaticon.com/512/3135/3135715.png";
                            ?>
                            <img src="<?= $photoPath ?>" alt="Student Photo" class="rounded-circle border border-2 border-primary" style="width: 65px; height: 65px; object-fit: cover;">
                            <div>
                                <h4 class="text-white fw-bold mb-1 fs-5"><?= htmlspecialchars($student_info['full_name'] ?? $student_info['name'] ?? 'N/A') ?></h4>
                                <div class="text-light small">
                                    <span class="text-warning fw-bold me-2">Roll: <?= htmlspecialchars($student_info['roll']) ?></span> | 
                                    <span class="mx-1">Semester: <strong><?= htmlspecialchars($student_info['semester'] ?? 'N/A') ?></strong></span> | 
                                    <span class="badge bg-secondary ms-1">Sec: <?= htmlspecialchars($student_info['section'] ?? 'N/A') ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- সর্বমোট শতকরা হার -->
                        <div class="text-md-end text-start">
                            <?php 
                                $overall_percentage = ($total_all_classes > 0) ? round(($total_all_present / $total_all_classes) * 100) : 0;
                            ?>
                            <span class="small text-light d-block mb-1">Overall Attendance</span>
                            <span class="badge <?= $overall_percentage >= 75 ? 'bg-success' : ($overall_percentage >= 50 ? 'bg-warning text-dark' : 'bg-danger') ?> fs-6 px-3 py-2 rounded-pill">
                                <?= $overall_percentage ?>% (<?= $total_all_present ?> / <?= $total_all_classes ?> Classes)
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Class Attendance Record -->
                <div class="card bg-secondary bg-opacity-10 text-white rounded-4 shadow-sm border border-secondary overflow-hidden mb-4">
                    <div class="py-3 px-4 bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold"><i class="fa-solid fa-clipboard-check me-2"></i> Class Attendance Record</h5>
                    </div>

                    <div class="p-3">
                        <?php if (count($subject_summary) > 0): ?>

                            <!-- ১. মোবাইল ভিউ (কার্ড ভিত্তিক ডিজাইন - ঝকঝকে এবং পড়তে সহজ) -->
                            <div class="d-md-none">
                                <div class="row g-3">
                                    <?php foreach ($subject_summary as $sub): 
                                        $total = (int)$sub['total_classes'];
                                        $present = (int)$sub['total_present'];
                                        $absent = (int)$sub['total_absent'];
                                        $percentage = ($total > 0) ? round(($present / $total) * 100) : 0;
                                        $color = $percentage >= 75 ? 'success' : ($percentage >= 50 ? 'warning' : 'danger');
                                    ?>
                                        <div class="col-12">
                                            <div class="card border border-secondary rounded-3 p-3 shadow-sm bg-dark">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <div>
                                                        <span class="badge bg-secondary mb-1 fs-7 text-light border border-secondary"><?= htmlspecialchars($sub['course_code']) ?></span>
                                                        <!-- কোর্সের নাম স্পষ্ট দেখতে উজ্জ্বল সায়ান কালার দেওয়া হয়েছে -->
                                                        <h6 class="fw-bold text-info mb-0" style="font-size: 1rem; letter-spacing: 0.3px;">
                                                            <?= htmlspecialchars($sub['course_name']) ?>
                                                        </h6>
                                                    </div>
                                                    <span class="badge bg-<?= $color ?> rounded-pill fs-6 px-2 py-1">
                                                        <?= $percentage ?>%
                                                    </span>
                                                </div>

                                                <!-- প্রোগ্রেস বার -->
                                                <div class="progress my-2 bg-secondary bg-opacity-25" style="height: 6px;">
                                                    <div class="progress-bar bg-<?= $color ?>" role="progressbar" style="width: <?= $percentage ?>%;"></div>
                                                </div>

                                                <!-- সংখ্যা তথ্য গ্রিড -->
                                                <div class="row text-center mt-2 pt-2 border-top border-secondary g-0">
                                                    <div class="col-4 border-end border-secondary">
                                                        <span class="d-block small text-light opacity-75">Total</span>
                                                        <strong class="text-white fs-6"><?= $total ?></strong>
                                                    </div>
                                                    <div class="col-4 border-end border-secondary">
                                                        <span class="d-block small text-light opacity-75">Present</span>
                                                        <strong class="text-success fs-6"><?= $present ?></strong>
                                                    </div>
                                                    <div class="col-4">
                                                        <span class="d-block small text-light opacity-75">Absent</span>
                                                        <strong class="text-danger fs-6"><?= $absent ?></strong>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- ২. ডেস্কটপ ভিউ (টেবিল) -->
                            <div class="table-responsive d-none d-md-block">
                                <table class="table table-dark table-hover align-middle mb-0 border border-secondary">
                                    <thead class="table-secondary text-dark text-uppercase small">
                                        <tr>
                                            <th>Code</th>
                                            <th>Course Name</th>
                                            <th style="min-width: 170px;">Attendance Ratio</th>
                                            <th class="text-center">Total</th>
                                            <th class="text-center text-success">Present</th>
                                            <th class="text-center text-danger">Absent</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($subject_summary as $sub): 
                                            $total = (int)$sub['total_classes'];
                                            $present = (int)$sub['total_present'];
                                            $absent = (int)$sub['total_absent'];
                                            $percentage = ($total > 0) ? round(($present / $total) * 100) : 0;
                                            $color = $percentage >= 75 ? 'success' : ($percentage >= 50 ? 'warning' : 'danger');
                                        ?>
                                            <tr>
                                                <td class="fw-bold text-warning"><?= htmlspecialchars($sub['course_code']) ?></td>
                                                <td>
                                                    <!-- স্পষ্ট ও পরিষ্কার দৃশ্যমান টেক্সট কালার -->
                                                    <span class="fw-bold text-info d-block"><?= htmlspecialchars($sub['course_name']) ?></span>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="progress flex-grow-1 bg-secondary bg-opacity-25" style="height: 6px;">
                                                            <div class="progress-bar bg-<?= $color ?>" style="width: <?= $percentage ?>%;"></div>
                                                        </div>
                                                        <span class="badge bg-<?= $color ?> rounded-pill"><?= $percentage ?>%</span>
                                                    </div>
                                                </td>
                                                <td class="text-center fw-bold text-light"><?= $total ?></td>
                                                <td class="text-center fw-bold text-success"><?= $present ?></td>
                                                <td class="text-center fw-bold text-danger"><?= $absent ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                        <?php else: ?>
                            <div class="text-center py-4 text-muted">
                                <i class="fa-solid fa-folder-open fa-2x mb-2 d-block"></i>
                                No attendance records found for this roll.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            <?php else: ?>
                <div class="alert alert-warning py-3 text-center">
                    <i class="fa-solid fa-triangle-exclamation me-1"></i> No student found with Roll: <strong><?= htmlspecialchars($roll) ?></strong>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- ০ প্রেজেন্ট শিক্ষার্থী তালিকা ভিউ (শুধুমাত্র ০ প্রেজেন্ট মোডে আসবে, রোল সার্চে কখনই আসবে না) -->
        <?php if ($show_zero_attendance && empty($roll)): ?>
            <div class="alert alert-danger py-3 mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0 fs-6">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i> Zero Attendance: <strong><?= count($zero_attendance_students) ?></strong> Found
                    <?php if(!empty($start_date) && !empty($end_date)): ?>
                        <span class="small text-light ms-2">(From <?= date("d-m-Y", strtotime($start_date)) ?> To <?= date("d-m-Y", strtotime($end_date)) ?>)</span>
                    <?php endif; ?>
                </h5>
                <div class="d-flex gap-2">
                    <a href="zero_attendance_pdf.php?zero_attendance=1&start_date=<?= urlencode($start_date) ?>&end_date=<?= urlencode($end_date) ?>" target="_blank" class="btn btn-sm btn-light text-danger fw-bold rounded-pill px-3">
                        <i class="fa-solid fa-file-pdf me-1"></i> PDF
                    </a>
                    <a href="send_zero_email.php?start_date=<?= urlencode($start_date) ?>&end_date=<?= urlencode($end_date) ?>" class="btn btn-sm btn-warning text-dark fw-bold rounded-pill px-3" onclick="return confirm('Send warning email to all?');">
                        <i class="fa-solid fa-envelope me-1"></i> Email All
                    </a>
                </div>
            </div>

            <?php if (count($zero_attendance_students) > 0): ?>
                <!-- ১. ডেস্কটপ ভিউ -->
                <div class="table-responsive d-none d-md-block">
                    <table class="table table-dark table-striped table-hover align-middle border border-secondary">
                        <thead class="table-danger text-dark">
                            <tr>
                                <th>SL</th>
                                <th>Photo</th>
                                <th>Roll</th>
                                <th>Student Name</th>
                                <th>Semester</th>
                                <th>Section</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; foreach ($zero_attendance_students as $st): 
                                $photoName = $st['photo'] ?? $st['image'] ?? '';
                                $photoPath = (!empty($photoName) && file_exists("../uploads/" . $photoName)) 
                                    ? "../uploads/" . htmlspecialchars($photoName) 
                                    : "https://cdn-icons-png.flaticon.com/512/3135/3135715.png";
                            ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td>
                                        <img src="<?= $photoPath ?>" alt="Student" class="rounded-circle border border-2 border-secondary" style="width: 40px; height: 40px; object-fit: cover;">
                                    </td>
                                    <td class="fw-bold text-warning"><?= htmlspecialchars($st['roll'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($st['full_name'] ?? $st['name'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($st['semester'] ?? 'N/A') ?></td>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($st['section'] ?? 'N/A') ?></span></td>
                                    <td><span class="badge bg-danger">0 Attendance</span></td>
                                    <td class="text-center">
                                        <a href="../students/view.php?id=<?= $st['id'] ?>" class="btn btn-sm btn-outline-info rounded-pill px-3">
                                            <i class="fa-solid fa-id-card me-1"></i> Profile
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- ২. মোবাইল ভিউ -->
                <div class="d-md-none">
                    <div class="row g-3">
                        <?php foreach ($zero_attendance_students as $st): 
                            $photoName = $st['photo'] ?? $st['image'] ?? '';
                            $photoPath = (!empty($photoName) && file_exists("../uploads/" . $photoName)) 
                                ? "../uploads/" . htmlspecialchars($photoName) 
                                : "https://cdn-icons-png.flaticon.com/512/3135/3135715.png";
                        ?>
                            <div class="col-12">
                                <div class="card bg-secondary bg-opacity-25 border border-secondary rounded-3 p-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="<?= $photoPath ?>" alt="Photo" class="rounded-circle border border-2 border-danger" style="width: 50px; height: 50px; object-fit: cover;">
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between">
                                                <h6 class="fw-bold text-warning mb-1">Roll: <?= htmlspecialchars($st['roll'] ?? '') ?></h6>
                                                <span class="badge bg-danger">0 Days</span>
                                            </div>
                                            <div class="text-white small fw-semibold"><?= htmlspecialchars($st['full_name'] ?? $st['name'] ?? 'N/A') ?></div>
                                            <div class="small text-light">
                                                <span>Semester: <?= htmlspecialchars($st['semester'] ?? 'N/A') ?></span> | 
                                                <span>Sec: <?= htmlspecialchars($st['section'] ?? 'N/A') ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-2 pt-2 border-top border-secondary text-end">
                                        <a href="../students/view.php?id=<?= $st['id'] ?>" class="btn btn-sm btn-info rounded-pill px-3 w-100 fw-semibold text-dark">
                                            <i class="fa-solid fa-user me-1"></i> Student Profile
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            <?php else: ?>
                <div class="text-center py-4 text-muted">No zero attendance students found!</div>
            <?php endif; ?>
        <?php endif; ?>

    </div>
</div>

<?php include("../includes/footer.php"); ?>