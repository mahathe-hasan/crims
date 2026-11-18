<?php
require_once("../config/session.php");
require_once("../config/database.php");

if(isset($_POST['submit'])) {
    $roll = $_POST['roll'];
    $full_name = $_POST['full_name'];
    $registration = $_POST['registration'];
    $department = $_POST['department'];
    $semester = $_POST['semester'];
    $shift = $_POST['shift'];
    $section = $_POST['section'];
    $session = $_POST['session'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $emergency_contact = $_POST['emergency_contact'] ?? '';
    $relation = $_POST['relation'] ?? '';
    $personal_emergency_contact = $_POST['personal_emergency_contact'] ?? '';
    $blood_group = $_POST['blood_group'] ?? '';

    $photo_name = ""; 

    if(isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        $fileTmpPath = $_FILES['photo']['tmp_name'];
        $fileName = $_FILES['photo']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png'];

        if(in_array($fileExtension, $allowedExtensions)) {
            $photo_name = md5(time() . $fileName) . '.' . $fileExtension;
            $dest_path = '../uploads/' . $photo_name;

            list($origWidth, $origHeight) = getimagesize($fileTmpPath);

            if($fileExtension == 'png') {
                $sourceImage = imagecreatefrompng($fileTmpPath);
            } else {
                $sourceImage = imagecreatefromjpeg($fileTmpPath);
            }

            $targetWidth = 300;
            $targetHeight = 300;
            $targetImage = imagecreatetruecolor($targetWidth, $targetHeight);

            if($fileExtension == 'png') {
                imagealphablending($targetImage, false);
                imagesavealpha($targetImage, true);
            }

            if ($origWidth > $origHeight) {
                $srcX = ($origWidth - $origHeight) / 2;
                $srcY = 0;
                $srcWidth = $origHeight;
                $srcHeight = $origHeight;
            } else {
                $srcX = 0;
                $srcY = ($origHeight - $origWidth) * 0.15; 
                if ($srcY < 0) $srcY = 0;
                $srcWidth = $origWidth;
                $srcHeight = $origWidth;
            }

            imagecopyresampled($targetImage, $sourceImage, 0, 0, $srcX, $srcY, $targetWidth, $targetHeight, $srcWidth, $srcHeight);

            if($fileExtension == 'png') {
                imagepng($targetImage, $dest_path, 6);
            } else {
                imagejpeg($targetImage, $dest_path, 90);
            }

            imagedestroy($sourceImage);
            imagedestroy($targetImage);
        }
    }

    $stmt = $conn->prepare("INSERT INTO students (roll, full_name, registration, department, semester, shift, section, session, phone, email, emergency_contact, relation, personal_emergency_contact, blood_group, photo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->execute([
        $roll, $full_name, $registration, $department, $semester, $shift, $section, $session, $phone, $email, $emergency_contact, $relation, $personal_emergency_contact, $blood_group, $photo_name
    ]);

    header("Location: index.php?success=1");
    exit();
}

include("../includes/header.php");
include("../includes/sidebar.php");
include("../includes/navbar.php");
?>

<div class="container-fluid px-2 px-md-4 py-3">
    <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
        <div class="card-header bg-primary text-white py-3 px-3 px-md-4">
            <h4 class="mb-0 fw-bold"><i class="fa-solid fa-user-plus me-2"></i> Add New Student</h4>
        </div>
        <div class="card-body p-3 p-md-4">
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Roll</label>
                        <input type="text" name="roll" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Full Name</label>
                        <input type="text" name="full_name" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Registration</label>
                        <input type="text" name="registration" class="form-control" required>
                    </div>
                    <!-- ডিপার্টমেন্ট ড্রপডাউন -->
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Department</label>
                        <select name="department" class="form-control" required>
                            <option value="">Select Department</option>
                            <option value="Computer Science and Technology">Computer Science and Technology</option>
                        </select>
                    </div>
                    <!-- সেমিস্টার ড্রপডাউন -->
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Semester</label>
                        <select name="semester" class="form-control" required>
                            <option value="">Select Semester</option>
                            <option value="1st">1st Semester</option>
                            <option value="2nd">2nd Semester</option>
                            <option value="3rd">3rd Semester</option>
                            <option value="4th">4th Semester</option>
                            <option value="5th">5th Semester</option>
                            <option value="6th">6th Semester</option>
                            <option value="7th">7th Semester</option>
                            <option value="8th">8th Semester</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Shift</label>
                        <select name="shift" class="form-control" required>
                            <option value="">Select Shift</option>
                            <option value="Morning">Morning</option>
                            <option value="Day">Day</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Section</label>
                        <select name="section" class="form-control" required>
                            <option value="">Select Section</option>
                            <option value="A">A</option>
                            <option value="B">B</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Session</label>
                        <input type="text" name="session" class="form-control" placeholder="e.g. 2024-25" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Phone</label>
                        <input type="text" name="phone" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Emergency Contact</label>
                        <input type="text" name="emergency_contact" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Relation</label>
                        <input type="text" name="relation" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Personal Emergency Contact</label>
                        <input type="text" name="personal_emergency_contact" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Blood Group</label>
                        <select name="blood_group" class="form-control">
                            <option value="">Select Blood Group</option>
                            <option value="A+">A+</option>
                            <option value="A-">A-</option>
                            <option value="B+">B+</option>
                            <option value="B-">B-</option>
                            <option value="AB+">AB+</option>
                            <option value="AB-">AB-</option>
                            <option value="O+">O+</option>
                            <option value="O-">O-</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Student Photo</label>
                        <input type="file" name="photo" class="form-control" accept="image/jpeg, image/png">
                    </div>
                    
                    <div class="col-12 mt-4">
                        <button type="submit" name="submit" class="btn btn-primary fw-semibold px-4"><i class="fa-solid fa-save me-1"></i> Save Student</button>
                        <a href="index.php" class="btn btn-secondary fw-semibold px-4"><i class="fa-solid fa-arrow-left me-1"></i> Back</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include("../includes/footer.php"); ?>