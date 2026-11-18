<?php
require_once("../config/session.php");
require_once("../config/database.php");

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $day_name     = $_POST['day_name'];
    $period_no    = (int)$_POST['period_no'];
    $time_slot    = trim($_POST['time_slot']);
    $subject_code = trim($_POST['subject_code']);
    $subject_name = trim($_POST['subject_name']);
    $teacher_code = trim($_POST['teacher_code']);
    $room_no      = trim($_POST['room_no']);

    $stmt = $conn->prepare("INSERT INTO routines (day_name, period_no, time_slot, subject_code, subject_name, teacher_code, room_no) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$day_name, $period_no, $time_slot, $subject_code, $subject_name, $teacher_code, $room_no]);
    header("Location: index.php?success=1");
    exit();
}

include("../includes/header.php");
include("../includes/sidebar.php");
include("../includes/navbar.php");
?>

<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="card border-0 shadow-lg mt-3 mb-4" style="border-radius:15px; background-color: #1e293b !important; color: #f8fafc !important;">
            <div class="card-header bg-primary text-white py-3">
                <h5 class="mb-0 fw-bold"><i class="fa-solid fa-calendar-plus me-2"></i> Add Routine Slot</h5>
            </div>
            <div class="card-body p-4">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold" style="color: #f8fafc !important;">Day Name</label>
                        <select name="day_name" class="form-select" required style="background-color: #0f172a !important; color: #f8fafc !important; border-color: rgba(255, 255, 255, 0.15) !important;">
                            <option value="Sunday">Sunday</option>
                            <option value="Monday">Monday</option>
                            <option value="Tuesday">Tuesday</option>
                            <option value="Wednesday">Wednesday</option>
                            <option value="Thursday">Thursday</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold" style="color: #f8fafc !important;">Period No (1-7)</label>
                            <input type="number" min="1" max="7" name="period_no" class="form-control" placeholder="1" required style="background-color: #0f172a !important; color: #f8fafc !important; border-color: rgba(255, 255, 255, 0.15) !important;">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold" style="color: #f8fafc !important;">Time Slot</label>
                            <input type="text" name="time_slot" class="form-control" placeholder="e.g. 1:15-2:00" required style="background-color: #0f172a !important; color: #f8fafc !important; border-color: rgba(255, 255, 255, 0.15) !important;">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold" style="color: #f8fafc !important;">Subject Code</label>
                            <input type="text" name="subject_code" class="form-control" placeholder="e.g. 28532" required style="background-color: #0f172a !important; color: #f8fafc !important; border-color: rgba(255, 255, 255, 0.15) !important;">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold" style="color: #f8fafc !important;">Subject Name</label>
                            <input type="text" name="subject_name" class="form-control" placeholder="e.g. Database Management System" style="background-color: #0f172a !important; color: #f8fafc !important; border-color: rgba(255, 255, 255, 0.15) !important;">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold" style="color: #f8fafc !important;">Teacher Code</label>
                            <input type="text" name="teacher_code" class="form-control" placeholder="e.g. ZR" style="background-color: #0f172a !important; color: #f8fafc !important; border-color: rgba(255, 255, 255, 0.15) !important;">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold" style="color: #f8fafc !important;">Room No</label>
                            <input type="text" name="room_no" class="form-control" placeholder="e.g. 306" style="background-color: #0f172a !important; color: #f8fafc !important; border-color: rgba(255, 255, 255, 0.15) !important;">
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mt-3">
                        <a href="index.php" class="btn btn-secondary">Back</a>
                        <button type="submit" class="btn btn-success"><i class="fa-solid fa-check me-1"></i> Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include("../includes/footer.php"); ?>