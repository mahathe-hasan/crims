<?php

require_once("../config/session.php");
require_once("../config/database.php");

if(!isset($_GET['id'])){
    header("Location: index.php");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM notices WHERE id=?");
$stmt->execute([$_GET['id']]);

$notice = $stmt->fetch();

if(!$notice){
    header("Location: index.php");
    exit();
}

include("../includes/header.php");
include("../includes/sidebar.php");
include("../includes/navbar.php");

?>

<div class="card border-0 shadow-lg" style="border-radius:18px;">

    <div class="card-header bg-warning">

        <h4 class="mb-0">
            <i class="fa-solid fa-pen-to-square"></i>
            Edit Notice
        </h4>

    </div>

    <div class="card-body">

        <form action="update.php" method="POST">

            <input type="hidden"
                   name="id"
                   value="<?= $notice['id']; ?>">

            <div class="mb-3">

                <label class="form-label">Notice Title</label>

                <input
                    type="text"
                    name="title"
                    class="form-control"
                    value="<?= htmlspecialchars($notice['title']); ?>"
                    required>

            </div>

            <div class="mb-3">

                <label class="form-label">Category</label>

                <select name="category"
                        class="form-select"
                        required>

                    <?php

                    $categories = [
                        "Job Sheet",
                        "Class Test",
                        "Quiz Test",
                        "Presentation",
                        "Assignment",
                        "Practical",
                        "Sessional",
                        "Practical Exam",
                        "Mid Exam",
                        "Semester Final",
                        "Class Off",
                        "Class Starts",
                        // নতুন ৭টি ক্যাটাগরি এখানে যোগ করা হলো
                        "Event",
                        "Urgent Notice",
                        "Class Related",
                        "Academic Notice",
                        "General Notice",
                        "Result",
                        "Others"
                    ];

                    foreach($categories as $cat){

                    ?>

                    <option
                    value="<?= $cat ?>"
                    <?= $notice['category']==$cat ? 'selected' : '' ?>>

                    <?= $cat ?>

                    </option>

                    <?php } ?>

                </select>

            </div>

            <div class="mb-3">

                <label class="form-label">Notice Date</label>

                <input
                    type="date"
                    name="notice_date"
                    class="form-control"
                    value="<?= $notice['notice_date']; ?>"
                    required>

            </div>

            <div class="mb-3">

                <label class="form-label">Description</label>

                <textarea
                    name="description"
                    class="form-control"
                    rows="6"
                    required><?= htmlspecialchars($notice['description']); ?></textarea>

            </div>

            <button class="btn btn-success">
                Update Notice
            </button>

            <a href="index.php"
               class="btn btn-secondary">
               Cancel
            </a>

        </form>

    </div>

</div>

<?php include("../includes/footer.php"); ?>