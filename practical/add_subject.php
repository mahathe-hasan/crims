<?php
require_once("../config/session.php");
require_once("../config/database.php");

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $subject_code = trim($_POST['subject_code']);
    $subject_name = trim($_POST['subject_name']);

    if(!empty($subject_code) && !empty($subject_name)){
        $stmt = $conn->prepare("INSERT INTO subjects (subject_code, subject_name) VALUES (?, ?)");
        $stmt->execute([$subject_code, $subject_name]);
        header("Location: index.php?success=1");
        exit();
    }
}

include("../includes/header.php");
include("../includes/sidebar.php");
include("../includes/navbar.php");
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card border-0 shadow-lg mt-4" style="border-radius:15px; background-color: #1e293b !important; color: #f8fafc !important;">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0 fw-bold"><i class="fa-solid fa-book me-2"></i> Add New Subject</h5>
            </div>
            <div class="card-body p-4">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold" style="color: #f8fafc !important;">Subject Code</label>
                        <input type="text" name="subject_code" class="form-control" placeholder="e.g. 28532" required style="background-color: #0f172a !important; color: #f8fafc !important; border-color: rgba(255, 255, 255, 0.15) !important;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold" style="color: #f8fafc !important;">Subject Name</label>
                        <input type="text" name="subject_name" class="form-control" placeholder="e.g. Database Management System" required style="background-color: #0f172a !important; color: #f8fafc !important; border-color: rgba(255, 255, 255, 0.15) !important;">
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="index.php" class="btn btn-secondary">Back</a>
                        <button type="submit" class="btn btn-success"><i class="fa-solid fa-check me-1"></i> Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include("../includes/footer.php"); ?>