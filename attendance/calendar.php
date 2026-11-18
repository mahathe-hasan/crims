<?php

require_once("../config/session.php");
require_once("../config/database.php");

include("../includes/header.php");
include("../includes/sidebar.php");
include("../includes/navbar.php");

/* ===========================
    Attendance Events
=========================== */

$stmt = $conn->query("
SELECT
    id,
    class_date,
    course_name
FROM attendance_sessions
ORDER BY class_date ASC
");

$events = [];

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){

    $events[] = [
        "title" => $row['course_name'],
        "start" => $row['class_date'],
        "url"   => "../reports/view.php?id=".$row['id']
    ];

}

$jsonEvents = json_encode($events);

?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold mb-0">
        <i class="fa-solid fa-calendar-days text-success me-2"></i>
        Attendance Calendar
    </h3>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-3">
        <div id="calendar"></div>
    </div>
</div>

<style>
#calendar {
    max-width: 100%;
    margin: auto;
}

.fc-toolbar-title {
    font-size: 24px;
    font-weight: bold;
}

.fc-button {
    background-color: #198754 !important;
    border: none !important;
}

.fc-button:hover {
    background-color: #157347 !important;
}

.fc-day-today {
    background-color: #fff8dc !important;
}

.fc-daygrid-event {
    border-radius: 6px;
    padding: 2px 4px;
    font-size: 12px;
}

.fc-event {
    cursor: pointer;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var calendarEl = document.getElementById('calendar');

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        height: 700,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,listMonth'
        },
        events: <?= $jsonEvents; ?>,
        eventColor: '#198754',
        eventTextColor: '#ffffff',
        eventClick: function(info){
            info.jsEvent.preventDefault();
            window.location.href = info.event.url;
        }
    });

    calendar.render();
});
</script>

<?php include("../includes/footer.php"); ?>