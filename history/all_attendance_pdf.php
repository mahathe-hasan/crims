<?php
ob_start();

require_once("../config/session.php");
require_once("../config/database.php");
require_once("../library/fpdf/fpdf.php");

date_default_timezone_set("Asia/Dhaka");

$start_date = trim($_GET['start_date'] ?? '');
$end_date   = trim($_GET['end_date'] ?? '');

$join_condition = "s.id = ad.student_id";
$params = [];

if (!empty($start_date) && !empty($end_date)) {
    $join_condition .= " AND ad.session_id IN (SELECT id FROM attendance_sessions WHERE class_date BETWEEN ? AND ?)";
    $params[] = $start_date;
    $params[] = $end_date;
}

$sql = "
    SELECT 
        s.*,
        COUNT(ad.id) AS total_classes,
        SUM(CASE WHEN ad.status = 'Present' THEN 1 ELSE 0 END) AS total_present
    FROM students s
    LEFT JOIN attendance_details ad ON $join_condition
    GROUP BY s.id 
    ORDER BY CAST(s.roll AS UNSIGNED) ASC
";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total_students = count($students);

$first_st     = $students[0] ?? [];
$box_semester = $first_st['semester'] ?? 'All';
$box_section  = $first_st['section'] ?? 'All';
$box_shift    = $first_st['shift'] ?? 'Day';

if (!empty($start_date) && !empty($end_date)) {
    $date_text = date("d-m-Y", strtotime($start_date)) . " To " . date("d-m-Y", strtotime($end_date));
} else {
    $date_text = "All Time (" . date("d-m-Y") . ")";
}

/* ===========================
   টেবিল হেডার ফাংশন (প্রতি পেজের জন্য)
=========================== */
function printTableHeader($pdf) {
    $pdf->SetFillColor(25, 135, 84);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('Arial', 'B', 10);

    $pdf->Cell(15, 9, 'SL', 1, 0, 'C', true);
    $pdf->Cell(35, 9, 'Roll', 1, 0, 'C', true);
    $pdf->Cell(105, 9, 'Student Name', 1, 0, 'C', true);
    $pdf->Cell(35, 9, 'Attendance (%)', 1, 1, 'C', true);

    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial', '', 10);
}

/* ===========================
   Create PDF
=========================== */
$pdf = new FPDF('P', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetAutoPageBreak(false);
$pdf->SetTitle("Attendance Percentage Report");
$pdf->SetAuthor("CRIMS");

/* ===========================
   Professional Header (Full Width: 210mm)
=========================== */
$logo = "../assets/images/dgpi_logo.png";

$pdf->SetFillColor(13, 71, 161);
$pdf->Rect(0, 0, 210, 40, 'F');

$pdf->SetTextColor(255, 255, 255);

if (file_exists($logo)) {
    $pdf->Image($logo, 12, 8, 20);
}

$pdf->SetXY(0, 8);
$pdf->SetFont('Arial', 'B', 17);
$pdf->Cell(210, 8, 'Dhaka Government Polytechnic Institute', 0, 1, 'C');

$pdf->SetX(0);
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(210, 6, 'Tejgaon, Dhaka-1208', 0, 1, 'C');

$pdf->SetX(0);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(210, 6, 'Department of Computer Science and Technology (CST)', 0, 1, 'C');

$pdf->SetX(0);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(210, 6, 'Class Record & Information Management System (CRIMS)', 0, 1, 'C');

$pdf->SetTextColor(0, 0, 0);
$pdf->Ln(8);

/* ===========================
   Report Title
=========================== */
$pdf->SetFont('Arial', 'B', 15);
$pdf->Cell(190, 9, 'Attendance Percentage Report', 0, 1, 'C');
$pdf->Ln(2);

/* ===========================
   Info Box (Total Width = 190mm)
=========================== */
$pdf->SetDrawColor(100, 100, 100);

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(35, 8, 'Department', 1, 0, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(155, 8, 'Computer Science and Technology (CST)', 1, 1, 'L');

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(35, 8, 'Semester', 1, 0, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(60, 8, $box_semester, 1, 0, 'L');
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(30, 8, 'Section', 1, 0, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(65, 8, $box_section, 1, 1, 'L');

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(35, 8, 'Shift', 1, 0, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(60, 8, $box_shift, 1, 0, 'L');
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(30, 8, 'Total Students', 1, 0, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(65, 8, $total_students, 1, 1, 'L');

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(35, 8, 'Date Range', 1, 0, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(60, 8, $date_text, 1, 0, 'L');
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(30, 8, 'Time', 1, 0, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(65, 8, date("h:i A"), 1, 1, 'L');

$pdf->Ln(8);

/* ===========================
   Student List Table
=========================== */
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(190, 8, 'Student Attendance Percentage List', 0, 1, 'C');

printTableHeader($pdf);

$sl = 1;
$fill = false;

if ($total_students > 0) {
    foreach ($students as $row) {
        if ($pdf->GetY() > 270) {
            $pdf->AddPage();
            printTableHeader($pdf);
        }

        if ($fill) {
            $pdf->SetFillColor(248, 249, 250);
        } else {
            $pdf->SetFillColor(255, 255, 255);
        }

        $st_name = $row['full_name'] ?? $row['name'] ?? 'N/A';
        $total   = (int)$row['total_classes'];
        $present = (int)$row['total_present'];
        $percent = ($total > 0) ? round(($present / $total) * 100, 1) : 0;

        $pdf->Cell(15, 8.5, $sl++, 1, 0, 'C', true);
        $pdf->Cell(35, 8.5, $row['roll'] ?? '', 1, 0, 'C', true);
        $pdf->Cell(105, 8.5, '  ' . $st_name, 1, 0, 'L', true);

        // ৩টি স্পষ্ট রঙ (৭৫%+ গাঢ় সবুজ, ৬০%-৭৪.৯% গাঢ় নীল, ৬০% এর নিচে গাঢ় লাল)
        if ($percent >= 75) {
            $pdf->SetTextColor(25, 135, 84);   // গাঢ় সবুজ (Green)
        } elseif ($percent >= 60) {
            $pdf->SetTextColor(255, 191, 0);  // গাঢ় হলুদ (Dark Yellow / Gold)
        } else {
            $pdf->SetTextColor(220, 53, 69);   // গাঢ় লাল (Red)
        }
        $pdf->SetFont('Arial', 'B', 10);

        $pdf->Cell(35, 8.5, $percent . '%', 1, 1, 'C', true);

        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', '', 10);

        $fill = !$fill;
    }
} else {
    $pdf->Cell(190, 10, 'No student attendance records found.', 1, 1, 'C', true);
}

/* ===========================
   Authority Signature & Footer
=========================== */
if ($pdf->GetY() > 245) {
    $pdf->AddPage();
}

$pdf->Ln(20);
$pdf->SetDrawColor(120, 120, 120);
$pdf->Line(140, $pdf->GetY(), 190, $pdf->GetY());
$pdf->Ln(3);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(140);
$pdf->Cell(50, 8, 'Authorized Signature', 0, 1, 'C');

$pdf->Ln(10);
$pdf->SetFont('Arial', 'I', 9);
$pdf->SetTextColor(120, 120, 120);
$pdf->Cell(190, 6, 'Generated by CRIMS | Dhaka Government Polytechnic Institute', 0, 1, 'C');
$pdf->SetTextColor(0, 0, 0);

/* ===========================
   Output & Auto Download
=========================== */
$fileName = "Attendance_Percentage_Report_" . date('d-m-Y') . ".pdf";

ob_end_clean();
$pdf->Output('D', $fileName);
exit;
?>