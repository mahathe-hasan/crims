<?php
require_once("../config/session.php");
require_once("../config/database.php");

include("../includes/header.php");
include("../includes/sidebar.php");
include("../includes/navbar.php");

$students = [];
if (isset($_GET['semester'])) {
    $stmt = $conn->prepare("SELECT id, full_name, roll FROM students WHERE semester=? ORDER BY CAST(roll AS UNSIGNED) ASC");
    $stmt->execute([$_GET['semester']]);
    $students = $stmt->fetchAll();
}
?>

<div class="card shadow-sm">
    <div class="card-header bg-primary text-white">
        <h4 class="mb-0">Take Attendance</h4>
    </div>
    <div class="card-body">
        <form method="GET">
            <div class="row">
                <!-- Department Dropdown -->
                <div class="col-md-4 mb-3">
                    <label>Department</label>
                    <select name="department" class="form-select" required>
                        <option value="Computer Science and Technology" <?= (($_GET['department'] ?? 'Computer Science and Technology') == 'Computer Science and Technology') ? 'selected' : '' ?>>Computer Science and Technology</option>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label>Semester</label>
                    <select name="semester" class="form-select" required>
                        <option value="">Select Semester</option>
                        <?php for($i=1; $i<=8; $i++): $sem = $i.(($i==1)?'st':(($i==2)?'nd':(($i==3)?'rd':'th'))).' Semester'; ?>
                            <option value="<?= $sem ?>" <?= (($_GET['semester'] ?? '') == $sem)?'selected':''; ?>><?= $sem ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <!-- Section Dropdown -->
                <div class="col-md-4 mb-3">
                    <label>Section</label>
                    <select name="section" class="form-select" required>
                        <option value="">Select Section</option>
                        <option value="A" <?= (($_GET['section'] ?? '') == 'A') ? 'selected' : '' ?>>A</option>
                        <option value="B" <?= (($_GET['section'] ?? '') == 'B') ? 'selected' : '' ?>>B</option>
                    </select>
                </div>
                
                <!-- Shift Dropdown -->
                <div class="col-md-4 mb-3">
                    <label>Shift</label>
                    <select name="shift" class="form-select" required>
                        <option value="">Select Shift</option>
                        <option value="Morning" <?= (($_GET['shift'] ?? '') == 'Morning') ? 'selected' : '' ?>>Morning</option>
                        <option value="Day" <?= (($_GET['shift'] ?? '') == 'Day') ? 'selected' : '' ?>>Day</option>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label>Date</label>
                    <input type="date" name="class_date" class="form-control" value="<?= $_GET['class_date'] ?? date('Y-m-d') ?>" readonly>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Time</label>
                    <input type="time" name="class_time" class="form-control" value="<?= $_GET['class_time'] ?? date('H:i') ?>">
                </div>

                <!-- Course Name with Auto-fill Datalist -->
                <div class="col-md-4 mb-3">
                    <label>Course Name</label>
                    <input type="text" name="course_name" id="course_name" class="form-control" list="courseList" value="<?= htmlspecialchars($_GET['course_name'] ?? '') ?>" oninput="autofillCourse(this.value)" placeholder="Type or select course" required>
                    
                    <!-- এখানে আপনার প্রয়োজনমতো কোর্স, কোড এবং শিক্ষকের নাম যুক্ত বা পরিবর্তন করতে পারবেন -->
                    <datalist id="courseList">
                        <option value="Programming in C" data-code="66611" data-teacher="Teacher Name 1">
                        <option value="Web Design and Development" data-code="66621" data-teacher="Teacher Name 2">
                        <option value="Data Structure & Algorithm" data-code="66631" data-teacher="Teacher Name 3">
                        <option value="Computer Peripherals" data-code="66612" data-teacher="Teacher Name 4">
                    </datalist>
                </div>

                <div class="col-md-4 mb-3">
                    <label>Course Code</label>
                    <input type="text" name="course_code" id="course_code" class="form-control" value="<?= htmlspecialchars($_GET['course_code'] ?? '') ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Teacher Name</label>
                    <input type="text" name="teacher_name" id="teacher_name" class="form-control" value="<?= htmlspecialchars($_GET['teacher_name'] ?? '') ?>" required>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-users"></i> Load Students</button>
                </div>
            </div>
        </form>

        <!-- JavaScript for Auto-fill -->
        <script>
        function autofillCourse(selectedCourse) {
            let options = document.querySelectorAll('#courseList option');
            let codeInput = document.getElementById('course_code');
            let teacherInput = document.getElementById('teacher_name');

            options.forEach(option => {
                if (option.value === selectedCourse) {
                    codeInput.value = option.getAttribute('data-code');
                    teacherInput.value = option.getAttribute('data-teacher');
                }
            });
        }
        </script>

        <?php if(count($students) > 0): ?>
        <hr>
        <form action="save.php" method="POST">
            <?php foreach(['department','semester','section','shift','course_name','course_code','teacher_name','class_date','class_time'] as $key): ?>
                <input type="hidden" name="<?= $key ?>" value="<?= htmlspecialchars($_GET[$key] ?? (($key=='class_date')?date('Y-m-d'):(($key=='class_time')?date('H:i'):(($key=='department')?'Computer Science and Technology':'')))); ?>">
            <?php endforeach; ?>

            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th width="100">Roll</th>
                            <th>Name</th>
                            <th width="120" class="text-center">Attendance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($students as $s): ?>
                        <tr>
                            <td class="fw-bold"><?= $s['roll']; ?></td>
                            <td><?= $s['full_name']; ?></td>
                            <td class="text-center">
                                <input type="hidden" name="present[]" id="input_<?= $s['id']; ?>" value="">
                                <button type="button" id="btn_<?= $s['id']; ?>" class="btn btn-sm btn-danger px-3 w-100" onclick="toggleAtt(<?= $s['id']; ?>)">Absent</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <button type="submit" class="btn btn-success w-100 mt-2"><i class="fa-solid fa-floppy-disk"></i> Save Attendance</button>
        </form>

        <script>
        function toggleAtt(id) {
            let inp = document.getElementById('input_' + id);
            let btn = document.getElementById('btn_' + id);
            if(inp.value === "") {
                inp.value = id;
                btn.className = "btn btn-sm btn-success px-3 w-100";
                btn.innerText = "Present";
            } else {
                inp.value = "";
                btn.className = "btn btn-sm btn-danger px-3 w-100";
                btn.innerText = "Absent";
            }
        }
        </script>
        <?php endif; ?>
    </div>
</div>

<?php include("../includes/footer.php"); ?>