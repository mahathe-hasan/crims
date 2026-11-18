<?php

require_once("../config/session.php");
require_once("../config/database.php");

if(!isset($_GET['id'])){
    header("Location: index.php");
    exit;
}

$id = (int)$_GET['id'];

$stmt = $conn->prepare("SELECT * FROM students WHERE id=?");
$stmt->execute([$id]);

$student = $stmt->fetch();

if(!$student){
    die("Student Not Found.");
}

include("../includes/header.php");
include("../includes/sidebar.php");
include("../includes/navbar.php");

?>

<div class="container-fluid px-2 px-md-4 py-3">

    <div class="card border-0 shadow-lg rounded-4 overflow-hidden">

        <div class="card-header bg-primary text-white d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 py-3 px-3 px-md-4">
            <h4 class="mb-0 fs-5 fs-md-4">
                <i class="fa-solid fa-user-graduate me-2"></i>
                Student Profile
            </h4>
            <a href="pdf_single.php?id=<?= $student['id']; ?>" target="_blank" class="btn btn-light btn-sm fw-bold shadow-sm">
                <i class="fa-solid fa-download me-1"></i> Download Student Info
            </a>
        </div>

        <div class="card-body p-3 p-md-4">

            <div class="row justify-content-center">

                <!-- Profile Photo & Name Section (Rectangular Photo & White Name on Dark Background) -->
                <div class="col-12 col-md-10 col-lg-8 text-center mb-4 p-4 rounded-4 bg-dark text-white shadow">
                    <?php if(!empty($student['photo']) && file_exists("../uploads/" . $student['photo'])){ ?>
                        <img src="../uploads/<?= htmlspecialchars($student['photo']); ?>" alt="Student Photo" style="width: 130px; height: 150px; object-fit: cover; border-radius: 8px;" class="border border-2 border-light shadow-sm mb-3">
                    <?php } else { ?>
                        <img src="../assets/images/dgpi_logo.png" alt="Default Photo" style="width: 130px; height: 150px; object-fit: cover; border-radius: 8px;" class="border border-2 border-light shadow-sm mb-3">
                    <?php } ?>
                    <h3 class="fw-bold text-white mb-0"><?= htmlspecialchars($student['full_name']); ?></h3>
                </div>

                <div class="col-12 col-md-10 col-lg-8">

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped align-middle" style="word-break: break-word;">
                            <tr>
                                <th class="w-40 bg-light">Roll</th>
                                <td class="fw-semibold"><?= htmlspecialchars($student['roll']); ?></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Registration</th>
                                <td><?= htmlspecialchars($student['registration']); ?></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Department</th>
                                <td><?= htmlspecialchars($student['department']); ?></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Semester</th>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($student['semester']); ?></span></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Shift</th>
                                <td><span class="badge bg-info text-dark"><?= htmlspecialchars($student['shift'] ?? ''); ?></span></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Section</th>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($student['section'] ?? ''); ?></span></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Session</th>
                                <td><span class="badge bg-info text-dark"><?= htmlspecialchars($student['session'] ?? ''); ?></span></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Phone</th>
                                <td><?= htmlspecialchars($student['phone']); ?></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Email</th>
                                <td><?= htmlspecialchars($student['email']); ?></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Emergency Contact Number</th>
                                <td><?= htmlspecialchars($student['emergency_contact'] ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Relation</th>
                                <td><?= htmlspecialchars($student['relation'] ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Personal Emergency Contact Number</th>
                                <td><?= htmlspecialchars($student['personal_emergency_contact'] ?? 'N/A'); ?></td>
                            </tr>
                            <!-- ব্লাড গ্রুপ ফিল্ড যুক্ত করা হলো -->
                            <tr>
                                <th class="bg-light">Blood Group</th>
                                <td><span class="badge bg-danger"><?= htmlspecialchars($student['blood_group'] ?? 'N/A'); ?></span></td>
                            </tr>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center gap-2 mt-4">
                        <a href="edit.php?id=<?= $student['id']; ?>" class="btn btn-warning fw-semibold px-4">
                            <i class="fa-solid fa-pen me-1"></i> Edit
                        </a>

                        <a href="index.php" class="btn btn-secondary fw-semibold px-4">
                            <i class="fa-solid fa-arrow-left me-1"></i> Back
                        </a>
                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

<?php include("../includes/footer.php"); ?>