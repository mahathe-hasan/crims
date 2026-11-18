<?php
require_once("../config/session.php");
require_once("../config/database.php");

include("../includes/header.php");
include("../includes/sidebar.php");
include("../includes/navbar.php");

$id = $_GET['id'] ?? null;
if (!$id) {
    echo "<script>window.location.href='../reports/index.php';</script>";
    exit();
}

// Fetch session info
$stmt = $conn->prepare("SELECT * FROM attendance_sessions WHERE id = ?");
$stmt->execute([$id]);
$session = $stmt->fetch();

if (!$session) {
    echo "<div class='alert alert-danger m-4'>Attendance session not found.</div>";
    include("../includes/footer.php");
    exit();
}

// Fetch students and existing attendance status for this session
$stmt_att = $conn->prepare("
    SELECT s.id as student_id, s.full_name, s.roll, a.status 
    FROM students s 
    LEFT JOIN attendance a ON s.id = a.student_id AND a.session_id = ?
    WHERE s.semester = ?
    ORDER BY CAST(s.roll AS UNSIGNED) ASC
");
$stmt_att->execute([$id, $session['semester']]);
$students = $stmt_att->fetchAll();

// Handle Form Submission for Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_name = $_POST['course_name'];
    $course_code = $_POST['course_code'] ?? '';
    $teacher_name = $_POST['teacher_name'];
    $department = $_POST['department'] ?? '';
    $semester = $_POST['semester'] ?? '';
    $section = $_POST['section'] ?? '';
    $shift = $_POST['shift'] ?? '';
    $class_date = $_POST['class_date'];
    $class_time = $_POST['class_time'];
    $present_students = $_POST['present'] ?? [];

    // Update Session Info
    $update_session = $conn->prepare("
        UPDATE attendance_sessions 
        SET course_name = ?, course_code = ?, teacher_name = ?, department = ?, semester = ?, section = ?, shift = ?, class_date = ?, class_time = ? 
        WHERE id = ?
    ");
    $update_session->execute([$course_name, $course_code, $teacher_name, $department, $semester, $section, $shift, $class_date, $class_time, $id]);

    // Update Attendance Records
    foreach ($students as $student) {
        $student_id = $student['student_id'];
        $status = in_array($student_id, $present_students) ? 1 : 0;

        // Check if record exists
        $check = $conn->prepare("SELECT id FROM attendance WHERE session_id = ? AND student_id = ?");
        $check->execute([$id, $student_id]);
        
        if ($check->rowCount() > 0) {
            $update_att = $conn->prepare("UPDATE attendance SET status = ? WHERE session_id = ? AND student_id = ?");
            $update_att->execute([$status, $id, $student_id]);
        } else {
            $insert_att = $conn->prepare("INSERT INTO attendance (session_id, student_id, status) VALUES (?, ?, ?)");
            $insert_att->execute([$id, $student_id, $status]);
        }
    }

    echo "<script>window.location.href='../reports/index.php';</script>";
    exit();
}
?>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 bg-dark text-white">
    <div class="card-header bg-primary text-white py-3 px-4 d-flex justify-content-between align-items-center">
        <h4 class="mb-0 fw-bold"><i class="fa-solid fa-pen-to-square me-2"></i> Edit Attendance</h4>
        <a href="../reports/index.php" class="btn btn-light btn-sm fw-semibold text-dark">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Reports
        </a>
    </div>
    <div class="card-body p-4">
        <form method="POST" action="edit.php?id=<?= $id ?>">
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label text-white">Subject Name</label>
                    <input type="text" name="course_name" class="form-control bg-secondary text-white border-0" value="<?= htmlspecialchars($session['course_name'] ?? '') ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label text-white">Course Code</label>
                    <input type="text" name="course_code" class="form-control bg-secondary text-white border-0" value="<?= htmlspecialchars($session['course_code'] ?? '') ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label text-white">Teacher Name</label>
                    <input type="text" name="teacher_name" class="form-control bg-secondary text-white border-0" value="<?= htmlspecialchars($session['teacher_name'] ?? '') ?>" required>
                </div>
                
                <!-- Department Dropdown -->
                <div class="col-md-4">
                    <label class="form-label text-white">Department</label>
                    <select name="department" class="form-select bg-secondary text-white border-0" required>
                        <option value="Computer Science and Technology" <?= (($session['department'] ?? '') == 'Computer Science and Technology') ? 'selected' : '' ?>>Computer Science and Technology</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label text-white">Semester</label>
                    <input type="text" name="semester" class="form-control bg-secondary text-white border-0" value="<?= htmlspecialchars($session['semester'] ?? '') ?>" required>
                </div>

                <!-- Section Dropdown -->
                <div class="col-md-2">
                    <label class="form-label text-white">Section</label>
                    <select name="section" class="form-select bg-secondary text-white border-0" required>
                        <option value="">Select</option>
                        <option value="A" <?= (($session['section'] ?? '') == 'A') ? 'selected' : '' ?>>A</option>
                        <option value="B" <?= (($session['section'] ?? '') == 'B') ? 'selected' : '' ?>>B</option>
                    </select>
                </div>

                <!-- Shift Dropdown -->
                <div class="col-md-2">
                    <label class="form-label text-white">Shift</label>
                    <select name="shift" class="form-select bg-secondary text-white border-0" required>
                        <option value="">Select Shift</option>
                        <option value="Morning" <?= (($session['shift'] ?? '') == 'Morning') ? 'selected' : '' ?>>Morning</option>
                        <option value="Day" <?= (($session['shift'] ?? '') == 'Day') ? 'selected' : '' ?>>Day</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label text-white">Date</label>
                    <input type="date" name="class_date" class="form-control bg-secondary text-white border-0" value="<?= $session['class_date'] ?? '' ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-white">Time</label>
                    <input type="time" name="class_time" class="form-control bg-secondary text-white border-0" value="<?= $session['class_time'] ?? '' ?>" required>
                </div>
            </div>

            <hr class="border-secondary">

            <div class="table-responsive">
                <table class="table table-dark table-bordered align-middle">
                    <thead>
                        <tr>
                            <th width="100">Roll</th>
                            <th>Name</th>
                            <th width="140" class="text-center">Attendance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($students as $s): 
                            $status = $s['status'] ?? 0;
                        ?>
                        <tr>
                            <td class="fw-bold text-white"><?= htmlspecialchars($s['roll']); ?></td>
                            <td class="text-white"><?= htmlspecialchars($s['full_name']); ?></td>
                            <td class="text-center">
                                <input type="hidden" name="present[]" id="input_<?= $s['student_id']; ?>" value="<?= ($status == 1) ? $s['student_id'] : ''; ?>">
                                <button type="button" id="btn_<?= $s['student_id']; ?>" class="btn btn-sm <?= ($status == 1) ? 'btn-success' : 'btn-danger'; ?> px-3 w-100 fw-bold" onclick="toggleAtt(<?= $s['student_id']; ?>)">
                                    <?= ($status == 1) ? 'Present' : 'Absent'; ?>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="d-flex gap-2 mt-3">
                <a href="../reports/index.php" class="btn btn-secondary w-50 py-2 fw-bold text-center">
                    <i class="fa-solid fa-arrow-left me-1"></i> Cancel
                </a>
                <button type="submit" class="btn btn-success w-50 py-2 fw-bold">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Update Attendance
                </button>
            </div>
        </form>

        <script>
        function toggleAtt(id) {
            let inp = document.getElementById('input_' + id);
            let btn = document.getElementById('btn_' + id);
            if(inp.value === "") {
                inp.value = id;
                btn.className = "btn btn-sm btn-success px-3 w-100 fw-bold";
                btn.innerText = "Present";
            } else {
                inp.value = "";
                btn.className = "btn btn-sm btn-danger px-3 w-100 fw-bold";
                btn.innerText = "Absent";
            }
        }
        </script>
    </div>
</div>

<?php include("../includes/footer.php"); ?>