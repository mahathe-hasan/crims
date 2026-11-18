<?php
$current_page = basename($_SERVER['PHP_SELF']);
$current_uri  = $_SERVER['REQUEST_URI'];
?>
<div class="sidebar" id="sidebar">

    <!-- Logo Area (লোগো উপরে, লেখা নিচে এক লাইনে) -->
    <div class="logo text-center py-3">
        <div class="bg-white d-inline-block p-2 rounded-circle shadow-sm mb-2">
            <img src="../assets/images/dgpi_logo.png"
                 alt="DGPI Logo"
                 class="img-fluid"
                 style="width:45px; height:45px; object-fit:contain;">
        </div>
        <h5 class="text-white mb-0 fw-bold" style="font-size: 16px;">CRIMS</h5>
        <small class="text-light opacity-75" style="font-size: 11px;">
            DGP - CST Department
        </small>
    </div>

    <hr class="text-light opacity-25 mx-3 my-1">

    <div class="menu-items py-2">
        <a href="../admin/dashboard.php" class="<?= ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-house"></i>
            <span>Dashboard</span>
        </a>

        <a href="../students/index.php" class="<?= (strpos($current_uri, '/students/') !== false) ? 'active' : ''; ?>">
            <i class="fa-solid fa-user-graduate"></i>
            <span>Students</span>
        </a>

        <a href="../attendance/index.php" class="<?= (strpos($current_uri, '/attendance/') !== false) ? 'active' : ''; ?>">
            <i class="fa-solid fa-calendar-check"></i>
            <span>Attendance</span>
        </a>

        <a href="../reports/index.php" class="<?= (strpos($current_uri, '/reports/') !== false) ? 'active' : ''; ?>">
            <i class="fa-solid fa-chart-column"></i>
            <span>Reports</span>
        </a>
        
        <a href="../notice/index.php" class="<?= (strpos($current_uri, '/notice/') !== false) ? 'active' : ''; ?>">
            <i class="fa-solid fa-calendar-days"></i>
            <span>Notice</span>
        </a>
        
        <a href="../routine/index.php" class="<?= (strpos($current_uri, '/routine/') !== false) ? 'active' : ''; ?>">
            <i class="fa-solid fa-table-list"></i>
            <span>Routine</span>
        </a>

        <a href="../practical/index.php" class="<?= (strpos($current_uri, '/practical/') !== false) ? 'active' : ''; ?>">
            <i class="fa-solid fa-book-bookmark"></i>
            <span>Job / Sessional</span>
        </a>

        <a href="../history/student_history.php" class="<?= ($current_page == 'student_history.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-user-clock me-2"></i>
            <span>Student History</span>
        </a>

        <a href="../history/all_attendance_report.php" class="<?= ($current_page == 'all_attendance_report.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-percent"></i>
            <span>Attendance %</span>
        </a>
        <a href="../student/manage_gpa.php" class="<?= ($current_page == 'manage_gpa.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-graduation-cap"></i>
            <span>Manage GPA</span>
        </a>
    </div>

    <!-- Logout Button (উপরে তোলা হয়েছে) -->
    <div class="sidebar-footer px-3 py-3 mt-2 border-top border-secondary border-opacity-25">
        <a href="../auth/logout.php" class="logout-btn">
            <i class="fa-solid fa-right-from-bracket"></i>
            <span>Logout</span>
        </a>
        <div class="version text-center mt-2">
            <small class="text-white-50" style="font-size: 10px;">CRIMS v2.0 | Developed for DGP</small>
        </div>
    </div>

</div>

<!-- Mobile Drawer Overlay -->
<div class="sidebar-overlay" onclick="toggleSidebar()"></div>