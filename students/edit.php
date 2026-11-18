<?php
require_once("../config/session.php");
require_once("../config/database.php");

if(!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$id]);
$student = $stmt->fetch();

if(!$student) {
    header("Location: index.php");
    exit();
}

if(isset($_POST['update'])) {
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

    $photo_name = $student['photo'];

    if(isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        $fileTmpPath = $_FILES['photo']['tmp_name'];
        $fileName = $_FILES['photo']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png'];

        if(in_array($fileExtension, $allowedExtensions)) {
            $new_photo_name = md5(time() . $fileName) . '.' . $fileExtension;
            $dest_path = '../uploads/' . $new_photo_name;

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

            if(!empty($student['photo']) && file_exists("../uploads/" . $student['photo'])) {
                unlink("../uploads/" . $student['photo']);
            }

            $photo_name = $new_photo_name;
        }
    }

    $updateStmt = $conn->prepare("UPDATE students SET roll = ?, full_name = ?, registration = ?, department = ?, semester = ?, shift = ?, section = ?, session = ?, phone = ?, email = ?, emergency_contact = ?, relation = ?, personal_emergency_contact = ?, blood_group = ?, photo = ? WHERE id = ?");
    
    $updateStmt->execute([
        $roll, $full_name, $registration, $department, $semester, $shift, $section, $session, $phone, $email, $emergency_contact, $relation, $personal_emergency_contact, $blood_group, $photo_name, $id
    ]);

    header("Location: index.php?updated=1");
    exit();
}

include("../includes/header.php");
include("../includes/sidebar.php");
include("../includes/navbar.php");
?>

<div class="container-fluid px-2 px-md-4 py-3">
    <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
        <div class="card-header bg-warning text-dark py-3 px-3 px-md-4">
            <h4 class="mb-0 fw-bold"><i class="fa-solid fa-pen-to-square me-2"></i> Edit Student Information</h4>
        </div>
        <div class="card-body p-3 p-md-4">
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Roll</label>
                        <input type="text" name="roll" class="form-control" value="<?= htmlspecialchars($student['roll']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Full Name</label>
                        <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($student['full_name']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Registration</label>
                        <input type="text" name="registration" class="form-control" value="<?= htmlspecialchars($student['registration']); ?>" required>
                    </div>
                    <!-- ডিপার্টমেন্ট ড্রপডাউন ও সিলেক্টেড ভ্যালু চেক -->
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Department</label>
                        <select name="department" class="form-control" required>
                            <option value="">Select Department</option>
                            <option value="Computer Science and Technology" <?= ($student['department'] == 'Computer Science and Technology') ? 'selected' : ''; ?>>Computer Science and Technology</option>
                        </select>
                    </div>
                    <!-- সেমিস্টার ড্রপডাউন ও সিলেক্টেড ভ্যালু চেক -->
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Semester</label>
                        <select name="semester" class="form-control" required>
                            <option value="">Select Semester</option>
                            <option value="1st" <?= ($student['semester'] == '1st') ? 'selected' : ''; ?>>1st Semester</option>
                            <option value="2nd" <?= ($student['semester'] == '2nd') ? 'selected' : ''; ?>>2nd Semester</option>
                            <option value="3rd" <?= ($student['semester'] == '3rd') ? 'selected' : ''; ?>>3rd Semester</option>
                            <option value="4th" <?= ($student['semester'] == '4th') ? 'selected' : ''; ?>>4th Semester</option>
                            <option value="5th" <?= ($student['semester'] == '5th') ? 'selected' : ''; ?>>5th Semester</option>
                            <option value="6th" <?= ($student['semester'] == '6th') ? 'selected' : ''; ?>>6th Semester</option>
                            <option value="7th" <?= ($student['semester'] == '7th') ? 'selected' : ''; ?>>7th Semester</option>
                            <option value="8th" <?= ($student['semester'] == '8th') ? 'selected' : ''; ?>>8th Semester</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Shift</label>
                        <select name="shift" class="form-control" required>
                            <option value="Morning" <?= ($student['shift'] == 'Morning') ? 'selected' : ''; ?>>Morning</option>
                            <option value="Day" <?= ($student['shift'] == 'Day') ? 'selected' : ''; ?>>Day</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Section</label>
                        <select name="section" class="form-control" required>
                            <option value="A" <?= ($student['section'] == 'A') ? 'selected' : ''; ?>>A</option>
                            <option value="B" <?= ($student['section'] == 'B') ? 'selected' : ''; ?>>B</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Session</label>
                        <input type="text" name="session" class="form-control" value="<?= htmlspecialchars($student['session'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Phone</label>
                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($student['phone']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Email</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($student['email']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Emergency Contact</label>
                        <input type="text" name="emergency_contact" class="form-control" value="<?= htmlspecialchars($student['emergency_contact'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Relation</label>
                        <input type="text" name="relation" class="form-control" value="<?= htmlspecialchars($student['relation'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Personal Emergency Contact</label>
                        <input type="text" name="personal_emergency_contact" class="form-control" value="<?= htmlspecialchars($student['personal_emergency_contact'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Blood Group</label>
                        <select name="blood_group" class="form-control">
                            <option value="">Select Blood Group</option>
                            <option value="A+" <?= (($student['blood_group'] ?? '') == 'A+') ? 'selected' : ''; ?>>A+</option>
                            <option value="A-" <?= (($student['blood_group'] ?? '') == 'A-') ? 'selected' : ''; ?>>A-</option>
                            <option value="B+" <?= (($student['blood_group'] ?? '') == 'B+') ? 'selected' : ''; ?>>B+</option>
                            <option value="B-" <?= (($student['blood_group'] ?? '') == 'B-') ? 'selected' : ''; ?>>B-</option>
                            <option value="AB+" <?= (($student['blood_group'] ?? '') == 'AB+') ? 'selected' : ''; ?>>AB+</option>
                            <option value="AB-" <?= (($student['blood_group'] ?? '') == 'AB-') ? 'selected' : ''; ?>>AB-</option>
                            <option value="O+" <?= (($student['blood_group'] ?? '') == 'O+') ? 'selected' : ''; ?>>O+</option>
                            <option value="O-" <?= (($student['blood_group'] ?? '') == 'O-') ? 'selected' : ''; ?>>O-</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Student Photo</label>
                        <input type="file" name="photo" class="form-control mb-2" accept="image/jpeg, image/png">
                        <?php if(!empty($student['photo']) && file_exists("../uploads/" . $student['photo'])){ ?>
                            <div class="mt-1 d-flex align-items-center gap-2">
                                <img src="../uploads/<?= htmlspecialchars($student['photo']); ?>" alt="Current Photo" width="60" height="60" style="object-fit: cover; border-radius: 50%;" class="border border-2 border-primary shadow-sm">
                                <span class="text-muted small">Current Photo</span>
                            </div>
                        <?php } ?>
                    </div>
                    
                    <div class="col-12 mt-4">
                        <button type="submit" name="update" class="btn btn-warning text-dark fw-bold px-4"><i class="fa-solid fa-sync me-1"></i> Update Student</button>
                        <a href="index.php" class="btn btn-secondary fw-semibold px-4"><i class="fa-solid fa-arrow-left me-1"></i> Back</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include("../includes/footer.php"); ?>