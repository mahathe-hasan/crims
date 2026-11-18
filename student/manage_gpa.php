<?php
require_once("../config/session.php");
require_once("../config/database.php");

include("../includes/header.php");
include("../includes/sidebar.php");
include("../includes/navbar.php");

$roll = trim($_GET['roll'] ?? $_POST['roll'] ?? '');
$student = null;
$results = [];
$msg = '';
$msg_type = 'success';

// ১. পাসওয়ার্ড রিসেট (আবার Registration নম্বরকে পাসওয়ার্ড বানানো)
if (isset($_POST['reset_password']) && !empty($_POST['student_id'])) {
    $st_id = (int)$_POST['student_id'];
    $stmt = $conn->prepare("UPDATE students SET password = NULL WHERE id = ?");
    $stmt->execute([$st_id]);
    $msg = "Password reset to default (Registration Number) successfully!";
}

// ২. ম্যানুয়ালি কাস্টম পাসওয়ার্ড সেট করা
if (isset($_POST['update_password']) && !empty($_POST['student_id'])) {
    $st_id = (int)$_POST['student_id'];
    $new_pass = trim($_POST['new_password']);
    if (!empty($new_pass)) {
        $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE students SET password = ? WHERE id = ?");
        $stmt->execute([$hashed, $st_id]);
        $msg = "Custom password updated successfully!";
    }
}

// ৩. GPA / Referred সেভ বা আপডেট লজিক (Ref 10 বা তার বেশি সাপোর্টসহ)
if (isset($_POST['save_gpa']) && !empty($_POST['student_id'])) {
    $st_id     = (int)$_POST['student_id'];
    $semester  = trim($_POST['semester']);
    $status    = $_POST['status'];
    $gpa       = ($status === 'Passed') ? (float)$_POST['gpa'] : 0.00;
    $ref_count = ($status === 'Referred') ? max(1, (int)$_POST['referred_count']) : 0;

    $sql = "INSERT INTO student_results (student_id, semester, gpa, status, referred_count) 
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE gpa = VALUES(gpa), status = VALUES(status), referred_count = VALUES(referred_count)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$st_id, $semester, $gpa, $status, $ref_count]);
    $msg = "$semester result saved successfully!";
}

// ৪. রেজাল্ট ডিলিট লজিক
if (isset($_GET['delete_id']) && !empty($roll)) {
    $del_id = (int)$_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM student_results WHERE id = ?");
    $stmt->execute([$del_id]);
    $msg = "Result entry deleted!";
    $msg_type = "warning";
}

// শিক্ষার্থী ও তার পূর্বের রেজাল্ট লোড করা
if (!empty($roll)) {
    $stmt = $conn->prepare("SELECT * FROM students WHERE roll = ? LIMIT 1");
    $stmt->execute([$roll]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($student) {
        $stmt_res = $conn->prepare("SELECT * FROM student_results WHERE student_id = ? ORDER BY semester ASC");
        $stmt_res->execute([$student['id']]);
        $results = $stmt_res->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

<div class="card shadow-sm border-0 rounded-4 bg-dark text-white">
    <div class="card-header bg-primary text-white py-3 px-4">
        <h4 class="mb-0 fw-bold"><i class="fa-solid fa-graduation-cap me-2"></i> Manage Student GPA & Login</h4>
    </div>
    <div class="card-body p-3 p-md-4">

        <?php if (!empty($msg)): ?>
            <div class="alert alert-<?= $msg_type ?> alert-dismissible fade show">
                <?= htmlspecialchars($msg) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- রোল সার্চ ফর্ম -->
        <form method="GET" class="row g-3 mb-4 align-items-end">
            <div class="col-md-6 col-12">
                <label class="form-label text-warning fw-semibold">Search Student by Roll</label>
                <input type="text" name="roll" class="form-control bg-secondary text-white border-secondary" value="<?= htmlspecialchars($roll) ?>" placeholder="e.g. 242384" required>
            </div>
            <div class="col-md-3 col-6">
                <button type="submit" class="btn btn-success w-100 fw-semibold"><i class="fa-solid fa-search me-1"></i> Find Student</button>
            </div>
            <div class="col-md-3 col-6">
                <a href="manage_gpa.php" class="btn btn-outline-light w-100 fw-semibold">Reset</a>
            </div>
        </form>

        <?php if (!empty($roll)): ?>
            <?php if ($student): ?>
                <!-- শিক্ষার্থীর তথ্য কার্ড -->
                <div class="card bg-secondary bg-opacity-25 border border-secondary rounded-3 p-3 mb-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h5 class="text-info fw-bold mb-1"><?= htmlspecialchars($student['full_name']) ?></h5>
                            <div class="small text-light">
                                Roll (User ID): <strong class="text-warning"><?= htmlspecialchars($student['roll']) ?></strong> | 
                                Reg No: <strong class="text-info"><?= htmlspecialchars($student['registration']) ?></strong> | 
                                Sem: <strong><?= htmlspecialchars($student['semester']) ?></strong> (Sec: <?= htmlspecialchars($student['section']) ?>)
                            </div>
                        </div>
                        <span class="badge <?= empty($student['password']) ? 'bg-info text-dark' : 'bg-warning text-dark' ?> px-3 py-2">
                            <?= empty($student['password']) ? 'Password: Default (Reg No)' : 'Password: Custom Set by Student/Admin' ?>
                        </span>
                    </div>
                </div>

                <div class="row g-4">
                    <!-- বাম পাশে: GPA / Referred এন্ট্রি ফর্ম -->
                    <div class="col-lg-6">
                        <div class="card bg-secondary bg-opacity-10 border border-secondary rounded-3 p-3 h-100">
                            <h6 class="text-warning fw-bold mb-3"><i class="fa-solid fa-plus-circle me-1"></i> Add / Update Semester GPA</h6>
                            <form method="POST">
                                <input type="hidden" name="student_id" value="<?= $student['id'] ?>">
                                <input type="hidden" name="roll" value="<?= htmlspecialchars($student['roll']) ?>">

                                <div class="mb-3">
                                    <label class="form-label small text-light">Select Semester</label>
                                    <select name="semester" class="form-select bg-secondary text-white border-secondary" required>
                                        <option value="1st Semester">1st Semester</option>
                                        <option value="2nd Semester">2nd Semester</option>
                                        <option value="3rd Semester">3rd Semester</option>
                                        <option value="4th Semester">4th Semester</option>
                                        <option value="5th Semester">5th Semester</option>
                                        <option value="6th Semester">6th Semester</option>
                                        <option value="7th Semester">7th Semester</option>
                                        <option value="8th Semester">8th Semester</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small text-light">Result Status</label>
                                    <select name="status" id="resultStatus" class="form-select bg-secondary text-white border-secondary" onchange="toggleResultFields()">
                                        <option value="Passed">Passed (GPA)</option>
                                        <option value="Referred">Referred (Ref)</option>
                                    </select>
                                </div>

                                <div class="mb-3" id="gpaBox">
                                    <label class="form-label small text-light">GPA (Out of 4.00)</label>
                                    <input type="number" step="0.01" min="0" max="4.00" name="gpa" class="form-control bg-secondary text-white border-secondary" placeholder="e.g. 3.75">
                                </div>

                                <div class="mb-3 d-none" id="refBox">
                                    <label class="form-label small text-danger fw-bold">Number of Referred Subjects (1, 10, 12...)</label>
                                    <input type="number" min="1" name="referred_count" class="form-control bg-secondary text-white border-secondary" value="1" placeholder="e.g. 1 or 10">
                                </div>

                                <button type="submit" name="save_gpa" class="btn btn-primary w-100 fw-bold">Save Result</button>
                            </form>
                        </div>
                    </div>

                    <!-- ডান পাশে: পাসওয়ার্ড ম্যানেজমেন্ট ও সংরক্ষিত রেজাল্ট -->
                    <div class="col-lg-6">
                        <div class="card bg-secondary bg-opacity-10 border border-secondary rounded-3 p-3 mb-4">
                            <h6 class="text-info fw-bold mb-2"><i class="fa-solid fa-key me-1"></i> Password Management</h6>
                            <p class="small text-light opacity-75 mb-3">Default password is Registration No (<strong><?= htmlspecialchars($student['registration']) ?></strong>).</p>
                            
                            <form method="POST" class="d-flex gap-2 mb-2">
                                <input type="hidden" name="student_id" value="<?= $student['id'] ?>">
                                <input type="hidden" name="roll" value="<?= htmlspecialchars($student['roll']) ?>">
                                <input type="text" name="new_password" class="form-control bg-secondary text-white border-secondary" placeholder="Set custom password" required>
                                <button type="submit" name="update_password" class="btn btn-warning text-dark fw-bold text-nowrap">Set</button>
                            </form>

                            <form method="POST">
                                <input type="hidden" name="student_id" value="<?= $student['id'] ?>">
                                <input type="hidden" name="roll" value="<?= htmlspecialchars($student['roll']) ?>">
                                <button type="submit" name="reset_password" class="btn btn-sm btn-outline-info w-100" onclick="return confirm('Reset password back to Registration Number?')">
                                    <i class="fa-solid fa-rotate-left me-1"></i> Reset to Registration Number
                                </button>
                            </form>
                        </div>

                        <div class="card bg-secondary bg-opacity-10 border border-secondary rounded-3 p-3">
                            <h6 class="text-success fw-bold mb-3"><i class="fa-solid fa-list-check me-1"></i> Saved Semester Results</h6>
                            <?php if (count($results) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-dark table-sm align-middle mb-0">
                                        <thead>
                                            <tr class="text-warning">
                                                <th>Semester</th>
                                                <th>Result</th>
                                                <th class="text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($results as $r): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($r['semester']) ?></td>
                                                    <td>
                                                        <?php if ($r['status'] === 'Passed'): ?>
                                                            <span class="badge bg-success">GPA: <?= number_format($r['gpa'], 2) ?></span>
                                                        <?php else: ?>
                                                            <span class="badge bg-danger">Ref <?= (int)$r['referred_count'] ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-end">
                                                        <a href="?roll=<?= urlencode($roll) ?>&delete_id=<?= $r['id'] ?>" class="btn btn-sm btn-outline-danger py-0 px-2" onclick="return confirm('Delete this result?')">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted small mb-0">No results added yet.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <div class="alert alert-warning text-center">No student found with Roll: <strong><?= htmlspecialchars($roll) ?></strong></div>
            <?php endif; ?>
        <?php endif; ?>

    </div>
</div>

<script>
function toggleResultFields() {
    const status = document.getElementById('resultStatus').value;
    const gpaBox = document.getElementById('gpaBox');
    const refBox = document.getElementById('refBox');
    if (status === 'Referred') {
        gpaBox.classList.add('d-none');
        refBox.classList.remove('d-none');
    } else {
        gpaBox.classList.remove('d-none');
        refBox.classList.add('d-none');
    }
}
</script>

<?php include("../includes/footer.php"); ?>