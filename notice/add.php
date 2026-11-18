<?php

require_once("../config/session.php");
require_once("../config/database.php");

include("../includes/header.php");
include("../includes/sidebar.php");
include("../includes/navbar.php");

?>

<div class="card border-0 shadow-lg" style="border-radius:18px;">

    <div class="card-header bg-primary text-white">
        <h4 class="mb-0">
            <i class="fa-solid fa-plus"></i>
            Add Notice
        </h4>
    </div>

    <div class="card-body">

        <form action="save.php" method="POST">

            <div class="mb-3">

                <label class="form-label">Notice Title</label>

                <input type="text"
                       name="title"
                       class="form-control"
                       required>

            </div>

            <div class="mb-3">

                <label class="form-label">Category</label>

                <select name="category" class="form-select" required>

    <option value="">Select Category</option>

    <option>Job Sheet</option>
    <option>Class Test</option>
    <option>Quiz Test</option>
    <option>Presentation</option>
    <option>Assignment</option>
    <option>Practical</option>
    <option>Sessional</option>
    <option>Practical Exam</option>
    <option>Mid Exam</option>
    <option>Semester Final</option>
    <option>Class Off</option>
    <option>Class Starts</option>

    <!-- নতুন ৭টি ক্যাটাগরি এখানে যোগ করা হলো -->
    <option>Event</option>
    <option>Urgent Notice</option>
    <option>Class Related</option>
    <option>Academic Notice</option>
    <option>General Notice</option>
    <option>Result</option>
    <option>Others</option>

</select>

            </div>

            <div class="mb-3">

                <label class="form-label">Notice Date</label>

                <input type="date"
                       name="notice_date"
                       class="form-control"
                       required>

            </div>

            <div class="mb-3">

                <label class="form-label">Description</label>

                <textarea name="description"
                          rows="6"
                          class="form-control"
                          required></textarea>

            </div>

            <button class="btn btn-success">
                <i class="fa-solid fa-floppy-disk"></i>
                Save Notice
            </button>

            <a href="index.php"
               class="btn btn-secondary">
                Cancel
            </a>

        </form>

    </div>

</div>

<?php include("../includes/footer.php"); ?>