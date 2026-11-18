<?php

require_once("../config/session.php");
require_once("../config/database.php");

include("../includes/header.php");
include("../includes/sidebar.php");
include("../includes/navbar.php");

$stmt = $conn->query("SELECT * FROM students ORDER BY CAST(roll AS UNSIGNED) ASC");
$students = $stmt->fetchAll();

?>

<div class="container-fluid px-2 px-md-4 py-3">

<?php if(isset($_GET['deleted'])){ ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fa-solid fa-trash me-1"></i> Student Deleted Successfully.
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php } ?>

<?php if(isset($_GET['updated'])){ ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fa-solid fa-circle-check me-1"></i> Student Updated Successfully.
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php } ?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <h3 class="fw-bold mb-0">
        <i class="fa-solid fa-user-graduate text-primary me-2"></i>
        Student List
    </h3>
    <div class="d-flex gap-2">
        <a href="add.php" class="btn btn-primary fw-semibold shadow-sm">
            <i class="fa-solid fa-plus me-1"></i> Add Student
        </a>
        <a href="pdf.php?v=<?= time(); ?>" target="_blank" class="btn btn-danger fw-semibold shadow-sm">
            <i class="fa-solid fa-file-pdf me-1"></i> Download PDF
        </a>
    </div>
</div>

<div class="card border-0 shadow-lg rounded-4 overflow-hidden">
    <div class="card-body p-3 p-md-4">
        
        <div class="mb-3">
            <input
                type="text"
                id="searchStudent"
                class="form-control form-control-lg bg-light border-0"
                placeholder="🔍 Search Student by name, roll, registration..."
            >
        </div>

        <?php if(count($students) > 0){ ?>
            <!-- Desktop View Table -->
            <div class="table-responsive d-none d-lg-block">
                <table class="table table-hover align-middle mb-0 text-center searchable-table">
                    <thead class="table-primary">
                        <tr>
                            <th>Photo</th>
                            <th>Roll</th>
                            <th class="text-start">Student Name</th>
                            <th>Registration</th>
                            <th class="text-start">Department</th>
                            <th>Semester</th>
                            <th>Shift</th>
                            <th>Section</th>
                            <th>Session</th>
                            <th width="220">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($students as $student){ ?>
                        <tr>
                            <td>
                                <?php if(!empty($student['photo']) && file_exists("../uploads/" . $student['photo'])){ ?>
                                    <div style="width: 60px; height: 60px; min-width: 60px; border-radius: 50%; overflow: hidden; margin: 0 auto;" class="border border-2 border-primary shadow-sm">
                                        <img src="../uploads/<?= htmlspecialchars($student['photo']); ?>" alt="Photo" width="300" height="300" style="width: 100%; height: 100%; object-fit: cover; object-position: center; image-rendering: -webkit-optimize-contrast;">
                                    </div>
                                <?php } else { ?>
                                    <div style="width: 60px; height: 60px; min-width: 60px; border-radius: 50%; overflow: hidden; margin: 0 auto;" class="border border-2 border-primary shadow-sm">
                                        <img src="../assets/images/dgpi_logo.png" alt="Default" width="300" height="300" style="width: 100%; height: 100%; object-fit: cover; object-position: center; image-rendering: -webkit-optimize-contrast;">
                                    </div>
                                <?php } ?>
                            </td>
                            <td class="fw-bold"><?= htmlspecialchars($student['roll']); ?></td>
                            <td class="text-start fw-semibold"><?= htmlspecialchars($student['full_name']); ?></td>
                            <td><?= htmlspecialchars($student['registration']); ?></td>
                            <td class="text-start"><?= htmlspecialchars($student['department']); ?></td>
                            <td><span class="badge bg-info text-dark"><?= htmlspecialchars($student['semester']); ?></span></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($student['shift'] ?? ''); ?></span></td>
                            <td><span class="badge bg-info text-dark"><?= htmlspecialchars($student['section'] ?? ''); ?></span></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($student['session'] ?? ''); ?></span></td>
                            <td>
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="view.php?id=<?= $student['id']; ?>" class="btn btn-sm btn-info text-white" title="View">
                                        <i class="fa-solid fa-eye"></i> View
                                    </a>
                                    <a href="edit.php?id=<?= $student['id']; ?>" class="btn btn-sm btn-warning text-dark fw-bold" title="Edit">
                                        <i class="fa-solid fa-pen"></i> Edit
                                    </a>
                                    <a href="delete.php?id=<?= $student['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this student?');" title="Delete">
                                        <i class="fa-solid fa-trash"></i> Delete
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile & Tablet View Cards -->
            <div class="row g-3 d-lg-none searchable-cards">
                <?php foreach($students as $student){ ?>
                <div class="col-12 col-md-6 search-card-item">
                    <div class="card border border-secondary shadow-sm rounded-3 h-100 bg-secondary text-white">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <?php if(!empty($student['photo']) && file_exists("../uploads/" . $student['photo'])){ ?>
                                        <div style="width: 55px; height: 55px; min-width: 55px; border-radius: 50%; overflow: hidden;" class="border border-white border-2 shadow-sm">
                                            <img src="../uploads/<?= htmlspecialchars($student['photo']); ?>" alt="Photo" width="300" height="300" style="width: 100%; height: 100%; object-fit: cover; object-position: center; image-rendering: -webkit-optimize-contrast;">
                                        </div>
                                    <?php } else { ?>
                                        <div style="width: 55px; height: 55px; min-width: 55px; border-radius: 50%; overflow: hidden;" class="border border-white border-2 shadow-sm">
                                            <img src="../assets/images/dgpi_logo.png" alt="Default" width="300" height="300" style="width: 100%; height: 100%; object-fit: cover; object-position: center; image-rendering: -webkit-optimize-contrast;">
                                        </div>
                                    <?php } ?>
                                    <span class="badge bg-primary fs-6">Roll: <?= htmlspecialchars($student['roll']); ?></span>
                                </div>
                                <span class="badge bg-dark"><?= htmlspecialchars($student['semester']); ?></span>
                            </div>
                            
                            <h5 class="card-title fw-bold text-white mb-2"><?= htmlspecialchars($student['full_name']); ?></h5>
                            
                            <div class="mb-3 small text-white-50">
                                <div>Reg No: <strong class="text-white"><?= htmlspecialchars($student['registration']); ?></strong></div>
                                <div>Department: <strong class="text-white"><?= htmlspecialchars($student['department']); ?></strong></div>
                                <div class="mt-1">
                                    Shift: <span class="badge bg-info text-dark"><?= htmlspecialchars($student['shift'] ?? ''); ?></span> | 
                                    Section: <span class="badge bg-dark"><?= htmlspecialchars($student['section'] ?? ''); ?></span> | 
                                    Session: <strong class="text-white"><?= htmlspecialchars($student['session'] ?? ''); ?></strong>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-1 border-top border-secondary pt-2 flex-wrap">
                                <a href="view.php?id=<?= $student['id']; ?>" class="btn btn-info btn-sm text-white">
                                    <i class="fa-solid fa-eye me-1"></i>View
                                </a>
                                <a href="edit.php?id=<?= $student['id']; ?>" class="btn btn-warning btn-sm text-dark fw-bold">
                                    <i class="fa-solid fa-pen me-1"></i>Edit
                                </a>
                                <a href="delete.php?id=<?= $student['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this student?');">
                                    <i class="fa-solid fa-trash me-1"></i>Delete
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>

        <?php } else { ?>
            <div class="text-center text-muted py-4">
                No students found.
            </div>
        <?php } ?>

    </div>
</div>

</div>

<script>
document.getElementById("searchStudent").addEventListener("keyup", function() {
    let value = this.value.toLowerCase();
    
    let rows = document.querySelectorAll(".searchable-table tbody tr");
    rows.forEach(function(row) {
        row.style.display = row.innerText.toLowerCase().includes(value) ? "" : "none";
    });

    let cards = document.querySelectorAll(".search-card-item");
    cards.forEach(function(card) {
        card.style.display = card.innerText.toLowerCase().includes(value) ? "" : "none";
    });
});
</script>

<?php include("../includes/footer.php"); ?>