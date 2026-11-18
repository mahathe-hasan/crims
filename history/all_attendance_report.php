<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("../config/session.php");
require_once("../config/database.php");

include("../includes/header.php");
include("../includes/sidebar.php");
include("../includes/navbar.php");

$start_date = trim($_GET['start_date'] ?? '');
$end_date   = trim($_GET['end_date'] ?? '');
$students_report = [];
$error_msg = '';

try {
    $join_condition = "s.id = ad.student_id";
    $params = [];

    // যদি ডেট রেঞ্জ দেওয়া থাকে, তাহলে শুধুমাত্র ওই তারিখের ক্লাসগুলো হিসাব হবে
    if (!empty($start_date) && !empty($end_date)) {
        $join_condition .= " AND ad.session_id IN (SELECT id FROM attendance_sessions WHERE class_date BETWEEN ? AND ?)";
        $params[] = $start_date;
        $params[] = $end_date;
    }

    $sql = "
        SELECT 
            s.*,
            COUNT(ad.id) AS total_classes,
            SUM(CASE WHEN ad.status = 'Present' THEN 1 ELSE 0 END) AS total_present,
            SUM(CASE WHEN ad.status != 'Present' AND ad.status IS NOT NULL THEN 1 ELSE 0 END) AS total_absent
        FROM students s
        LEFT JOIN attendance_details ad ON $join_condition
        GROUP BY s.id 
        ORDER BY CAST(s.roll AS UNSIGNED) ASC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $students_report = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error_msg = "Database Error: " . $e->getMessage();
}
?>

<div class="card shadow-sm border-0 rounded-4 bg-dark text-white">
    <div class="card-header bg-primary text-white py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h4 class="mb-0 fw-bold"><i class="fa-solid fa-percent me-2"></i> All Students Attendance Percentage</h4>
        <div class="d-flex gap-2">
            <a href="student_history.php" class="btn btn-sm btn-outline-light fw-semibold">
                <i class="fa-solid fa-arrow-left me-1"></i> Back
            </a>
            <!-- ডেট প্যারামিটারসহ PDF ডাউনলোড বাটন -->
            <a href="all_attendance_pdf.php?start_date=<?= urlencode($start_date) ?>&end_date=<?= urlencode($end_date) ?>" target="_blank" class="btn btn-sm btn-light text-danger fw-bold rounded-pill px-3">
                <i class="fa-solid fa-file-pdf me-1"></i> Download PDF
            </a>
        </div>
    </div>

    <div class="card-body p-3 p-md-4">

        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error_msg) ?></div>
        <?php endif; ?>

        <!-- ডেট রেঞ্জ ফিল্টার ফর্ম -->
        <form method="GET" class="row g-3 mb-4 align-items-end">
            <div class="col-md-4 col-12">
                <label class="form-label text-warning small fw-semibold">Start Date</label>
                <input type="date" name="start_date" class="form-control bg-secondary text-white border-secondary" value="<?= htmlspecialchars($start_date) ?>">
            </div>
            <div class="col-md-4 col-12">
                <label class="form-label text-warning small fw-semibold">End Date</label>
                <input type="date" name="end_date" class="form-control bg-secondary text-white border-secondary" value="<?= htmlspecialchars($end_date) ?>">
            </div>
            <div class="col-md-2 col-6">
                <button type="submit" class="btn btn-info w-100 fw-semibold text-dark"><i class="fa-solid fa-filter me-1"></i> Filter</button>
            </div>
            <div class="col-md-2 col-6">
                <a href="all_attendance_report.php" class="btn btn-outline-light w-100 fw-semibold"><i class="fa-solid fa-rotate-left me-1"></i> Reset</a>
            </div>
        </form>

        <!-- সক্রিয় ডেট রেঞ্জ ব্যাজ -->
        <?php if (!empty($start_date) && !empty($end_date)): ?>
            <div class="alert alert-info py-2 px-3 mb-3 small fw-semibold">
                <i class="fa-solid fa-calendar-check me-1"></i> Showing Attendance From: <strong><?= date("d-m-Y", strtotime($start_date)) ?></strong> To <strong><?= date("d-m-Y", strtotime($end_date)) ?></strong>
            </div>
        <?php endif; ?>

        <?php if (count($students_report) > 0): ?>

            <!-- ১. মোবাইল ভিউ (কার্ড ডিজাইন - ডানে বামে স্ক্রল করতে হবে না) -->
            <div class="d-md-none">
                <div class="row g-3">
                    <?php $sl = 1; foreach ($students_report as $row): 
                        $st_name = $row['full_name'] ?? $row['name'] ?? 'N/A';
                        $total   = (int)$row['total_classes'];
                        $present = (int)$row['total_present'];
                        $absent  = (int)$row['total_absent'];
                        $percent = ($total > 0) ? round(($present / $total) * 100, 1) : 0;
                        $color   = $percent >= 75 ? 'success' : ($percent >= 50 ? 'warning' : 'danger');
                    ?>
                        <div class="col-12">
                            <div class="card bg-secondary bg-opacity-25 border border-secondary rounded-3 p-3 shadow-sm">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <span class="badge bg-warning text-dark fw-bold me-1">#<?= $sl++ ?> | Roll: <?= htmlspecialchars($row['roll'] ?? '') ?></span>
                                        <h6 class="fw-bold text-white mt-2 mb-1"><?= htmlspecialchars($st_name) ?></h6>
                                        <div class="small text-light opacity-75">
                                            Sem: <strong><?= htmlspecialchars($row['semester'] ?? 'N/A') ?></strong> | 
                                            Sec: <strong><?= htmlspecialchars($row['section'] ?? 'N/A') ?></strong>
                                        </div>
                                    </div>
                                    <span class="badge bg-<?= $color ?> <?= $color == 'warning' ? 'text-dark' : '' ?> fs-6 px-3 py-2 rounded-pill">
                                        <?= $percent ?>%
                                    </span>
                                </div>

                                <!-- প্রোগ্রেস বার -->
                                <div class="progress my-2 bg-dark" style="height: 6px;">
                                    <div class="progress-bar bg-<?= $color ?>" style="width: <?= $percent ?>%;"></div>
                                </div>

                                <!-- ক্লাস সংখ্যা -->
                                <div class="row text-center mt-2 pt-2 border-top border-secondary g-0 small">
                                    <div class="col-4 border-end border-secondary">
                                        <span class="d-block text-light opacity-75">Total</span>
                                        <strong class="text-white"><?= $total ?></strong>
                                    </div>
                                    <div class="col-4 border-end border-secondary">
                                        <span class="d-block text-light opacity-75">Present</span>
                                        <strong class="text-success"><?= $present ?></strong>
                                    </div>
                                    <div class="col-4">
                                        <span class="d-block text-light opacity-75">Absent</span>
                                        <strong class="text-danger"><?= $absent ?></strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- ২. কম্পিউটার ভিউ (টেবিল - Semester ও Section আলাদা কলামে) -->
            <div class="table-responsive d-none d-md-block">
                <table class="table table-dark table-striped table-hover align-middle border border-secondary mb-0">
                    <thead class="table-secondary text-dark text-uppercase small">
                        <tr>
                            <th>SL</th>
                            <th>Roll</th>
                            <th>Student Name</th>
                            <th>Semester</th>
                            <th>Section</th>
                            <th class="text-center">Total Class</th>
                            <th class="text-center text-success">Present</th>
                            <th class="text-center text-danger">Absent</th>
                            <th class="text-center">Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $sl = 1; foreach ($students_report as $row): 
                            $st_name = $row['full_name'] ?? $row['name'] ?? 'N/A';
                            $total   = (int)$row['total_classes'];
                            $present = (int)$row['total_present'];
                            $absent  = (int)$row['total_absent'];
                            $percent = ($total > 0) ? round(($present / $total) * 100, 1) : 0;
                            $badge_color = $percent >= 75 ? 'bg-success' : ($percent >= 50 ? 'bg-warning text-dark' : 'bg-danger');
                        ?>
                            <tr>
                                <td><?= $sl++ ?></td>
                                <td class="fw-bold text-warning"><?= htmlspecialchars($row['roll'] ?? '') ?></td>
                                <td class="fw-semibold"><?= htmlspecialchars($st_name) ?></td>
                                <td><?= htmlspecialchars($row['semester'] ?? 'N/A') ?></td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($row['section'] ?? 'N/A') ?></span></td>
                                <td class="text-center fw-bold"><?= $total ?></td>
                                <td class="text-center fw-bold text-success"><?= $present ?></td>
                                <td class="text-center fw-bold text-danger"><?= $absent ?></td>
                                <td class="text-center">
                                    <span class="badge <?= $badge_color ?> fs-6 px-3 py-1 rounded-pill"><?= $percent ?>%</span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php else: ?>
            <div class="text-center py-4 text-muted">No student records found!</div>
        <?php endif; ?>
    </div>
</div>

<?php include("../includes/footer.php"); ?>