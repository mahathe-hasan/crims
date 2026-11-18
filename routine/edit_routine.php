<?php
require_once("../config/session.php");
require_once("../config/database.php");

$id = $_GET['id'] ?? null;
if(!$id){
    header("Location: index.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $day_name     = trim($_POST['day_name']);
    $period_no    = trim($_POST['period_no']);
    $time_slot    = trim($_POST['time_slot']);
    $subject_code = trim($_POST['subject_code']);
    $subject_name = trim($_POST['subject_name']);
    $teacher_code = trim($_POST['teacher_code']);
    $room_no      = trim($_POST['room_no']);
    $period_span  = trim($_POST['period_span']);

    if(!empty($day_name) && !empty($period_no)){
        $stmt = $conn->prepare("UPDATE routines SET day_name = ?, period_no = ?, time_slot = ?, subject_code = ?, subject_name = ?, teacher_code = ?, room_no = ?, period_span = ? WHERE id = ?");
        $stmt->execute([$day_name, $period_no, $time_slot, $subject_code, $subject_name, $teacher_code, $room_no, $period_span, $id]);
        header("Location: index.php?success=update");
        exit();
    }
}

$stmt = $conn->prepare("SELECT * FROM routines WHERE id = ?");
$stmt->execute([$id]);
$routine = $stmt->fetch();

if(!$routine){
    header("Location: index.php");
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
                <h5 class="mb-0 fw-bold"><i class="fa-solid fa-pen-to-square me-2"></i> Edit Routine Slot</h5>
            </div>
            <div class="card-body p-4">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold" style="color: #f8fafc !important;">Day Name</label>
                        <select name="day_name" class="form-select" required style="background-color: #0f172a !important; color: #f8fafc !important; border-color: rgba(255, 255, 255, 0.15) !important;">
                            <?php foreach(['Sunday','Monday','Tuesday','Wednesday','Thursday'] as $day): ?>
                                <option value="<?= $day ?>" <?= $routine['day_name'] == $day ? 'selected' : '' ?> style="background-color: #0f172a; color: #f8fafc;"><?= $day ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold" style="color: #f8fafc !important;">Period No</label>
                            <input type="text" name="period_no" class="form-control" value="<?= htmlspecialchars($routine['period_no']) ?>" required style="background-color: #0f172a !important; color: #f8fafc !important; border-color: rgba(255, 255, 255, 0.15) !important;">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold" style="color: #f8fafc !important;">Time Slot</label>
                            <input type="text" name="time_slot" class="form-control" value="<?= htmlspecialchars($routine['time_slot']) ?>" placeholder="e.g. 1.15-2.00" required style="background-color: #0f172a !important; color: #f8fafc !important; border-color: rgba(255, 255, 255, 0.15) !important;">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold" style="color: #f8fafc !important;">Subject Code</label>
                            <input type="text" name="subject_code" class="form-control" value="<?= htmlspecialchars($routine['subject_code']) ?>" required style="background-color: #0f172a !important; color: #f8fafc !important; border-color: rgba(255, 255, 255, 0.15) !important;">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold" style="color: #f8fafc !important;">Subject Name</label>
                            <input type="text" name="subject_name" class="form-control" value="<?= htmlspecialchars($routine['subject_name'] ?? '') ?>" placeholder="e.g. Database Management System" style="background-color: #0f172a !important; color: #f8fafc !important; border-color: rgba(255, 255, 255, 0.15) !important;">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold" style="color: #f8fafc !important;">Teacher Code</label>
                            <input type="text" name="teacher_code" class="form-control" value="<?= htmlspecialchars($routine['teacher_code']) ?>" required style="background-color: #0f172a !important; color: #f8fafc !important; border-color: rgba(255, 255, 255, 0.15) !important;">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold" style="color: #f8fafc !important;">Room No</label>
                            <input type="text" name="room_no" class="form-control" value="<?= htmlspecialchars($routine['room_no']) ?>" required style="background-color: #0f172a !important; color: #f8fafc !important; border-color: rgba(255, 255, 255, 0.15) !important;">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold" style="color: #f8fafc !important;">Period Span</label>
                        <input type="number" name="period_span" class="form-control" value="<?= htmlspecialchars($routine['period_span']) ?>" min="1" required style="background-color: #0f172a !important; color: #f8fafc !important; border-color: rgba(255, 255, 255, 0.15) !important;">
                    </div>
                    <div class="d-flex justify-content-between mt-3">
                        <a href="index.php" class="btn btn-secondary">Back</a>
                        <button type="submit" class="btn btn-success"><i class="fa-solid fa-check me-1"></i> Update Routine</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include("../includes/footer.php"); ?>