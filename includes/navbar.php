<div class="main">

    <div class="topbar d-flex justify-content-between align-items-center px-4 py-3 bg-white shadow-sm">
        
        <div class="d-flex align-items-center gap-3">
            <!-- Hamburger Menu (Mobile Only) -->
            <button class="btn btn-light d-md-none border p-2" onclick="toggleSidebar()">
                <i class="fa-solid fa-bars fs-5"></i>
            </button>

            <div>
                <h3 class="mb-0 fw-bold fs-4 text-dark">
                    CRIMS Dashboard
                </h3>
                <small class="text-muted" style="font-size: 13px;">
                    Dhaka Government Polytechnic Institute
                </small>
            </div>
        </div>

        <div class="d-flex align-items-center gap-3">
            <div class="text-end d-none d-sm-block">
                <div class="fw-semibold text-dark">
                    <?php echo isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'Administrator'; ?>
                </div>
                <small class="text-muted">Administrator</small>
            </div>

            <img src="../assets/images/dgpi_logo.png"
                width="42"
                height="42"
                alt="Logo"
                class="rounded-circle border border-2 border-primary bg-white p-1 shadow-sm">
        </div>
    
    </div>
    
    <!-- Date & Time Bar -->
    <div class="px-4 py-2 bg-white shadow-sm rounded mb-4 d-flex justify-content-between flex-wrap mx-3 mt-3 align-items-center border-0">
        <div class="py-1">
            <i class="fa-solid fa-calendar-days text-primary me-2"></i>
            <strong>Date :</strong>
            <span class="text-secondary"><?php echo date("d F Y"); ?></span>
        </div>
        <div class="py-1">
            <i class="fa-solid fa-clock text-success me-2"></i>
            <strong>Time :</strong>
            <span id="liveClock" class="text-secondary"></span>
        </div>
    </div>

    <div class="content px-3 px-md-4 pb-4">