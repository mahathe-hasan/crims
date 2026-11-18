<?php
ob_start();

require_once("../config/session.php");
require_once("../config/database.php");
require_once("../library/fpdf/fpdf.php");

date_default_timezone_set("Asia/Dhaka");

// ডেট রেঞ্জ প্যারামিটার গ্রহণ
$start_date = $_GET['start_date'] ?? '';
$end_date   = $_GET['end_date'] ?? '';

// নিখুঁত কুয়েরি: নির্দিষ্ট তারিখের মধ্যে যারা একবারও Present হয়নি
$sql = "
    SELECT *
    FROM students
    WHERE id NOT IN (
        SELECT DISTINCT attendance_details.student_id 
        FROM attendance_details 
        INNER JOIN attendance_sessions ON attendance_sessions.id = attendance_details.session_id
        WHERE attendance_details.status = 'Present'
";

$params = [];

if (!empty($start_date) && !empty($end_date)) {
    $sql .= " AND attendance_sessions.class_date BETWEEN ? AND ?";
    $params[] = $start_date;
    $params[] = $end_date;
}

$sql .= " ) ORDER BY CAST(roll AS UNSIGNED) ASC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$zero_students = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total_zero = count($zero_students);

/* ===========================
   Create PDF
=========================== */
$pdf = new FPDF('P','mm','A4');
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 20);
$pdf->SetTitle("Zero Attendance Report");
$pdf->SetAuthor("CRIMS");

/* ===========================
   Professional Header (Full Width: 210mm)
=========================== */
$logo = "../assets/images/dgpi_logo.png";

$pdf->SetFillColor(13, 71, 161);
$pdf->Rect(0, 0, 210, 40, 'F');

$pdf->SetTextColor(255, 255, 255);

if(file_exists($logo)){
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
$pdf->Ln(10);

/* ===========================
   Report Title
=========================== */
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(190, 10, 'Students With 0 Days Attendance Report', 0, 1, 'C');
$pdf->Ln(2);

/* ===========================
   Report Summary Info Block
=========================== */
$pdf->SetFillColor(240, 248, 255);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(45, 9, 'Report Type', 1, 0, 'L', true);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(145, 9, 'Never Attended Any Class (0 Attendance)', 1, 1, 'L');

$criteria_text = 'All Time / Overall';
if (!empty($start_date) && !empty($end_date)) {
    $criteria_text = "From " . date("d-m-Y", strtotime($start_date)) . " To " . date("d-m-Y", strtotime($end_date));
}

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(45, 9, 'Date Range', 1, 0, 'L', true);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(145, 9, $criteria_text, 1, 1, 'L');

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(45, 9, 'Total Students Found', 1, 0, 'L', true);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(145, 9, $total_zero, 1, 1, 'L');

$pdf->Ln(8);

/* ===========================
   Student List Table
=========================== */
$pdf->SetFont('Arial', 'B', 13);
$pdf->Cell(190, 8, 'Zero Attendance Student List', 0, 1, 'C');

$pdf->SetFillColor(220, 53, 69); 
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 11);

$pdf->Cell(15, 10, 'SL', 1, 0, 'C', true);
$pdf->Cell(35, 10, 'Roll', 1, 0, 'C', true);
$pdf->Cell(80, 10, 'Student Name', 1, 0, 'C', true);
$pdf->Cell(35, 10, 'Semester', 1, 0, 'C', true);
$pdf->Cell(25, 10, 'Section', 1, 1, 'C', true);

$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Arial', '', 10);

$sl = 1;
$fill = false;

if(count($zero_students) > 0) {
    foreach($zero_students as $row){
        if($fill){
            $pdf->SetFillColor(248, 249, 250);
        }else{
            $pdf->SetFillColor(255, 255, 255);
        }

        $fullName = $row['full_name'] ?? $row['name'] ?? 'N/A';
        $semester = $row['semester'] ?? 'N/A';
        $section  = $row['section'] ?? 'N/A';

        $pdf->Cell(15, 9, $sl++, 1, 0, 'C', true);
        $pdf->Cell(35, 9, $row['roll'], 1, 0, 'C', true);
        $pdf->Cell(80, 9, $fullName, 1, 0, 'L', true);
        $pdf->Cell(35, 9, $semester, 1, 0, 'C', true);
        $pdf->Cell(25, 9, $section, 1, 1, 'C', true);

        $fill = !$fill;
    }
} else {
    $pdf->Cell(190, 10, 'No zero attendance students found for the selected date range.', 1, 1, 'C', true);
}

/* ===========================
   Authority Signature
=========================== */
$pdf->Ln(20);
$pdf->SetDrawColor(120, 120, 120);
$pdf->Line(140, $pdf->GetY(), 190, $pdf->GetY());
$pdf->Ln(3);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(140);
$pdf->Cell(50, 8, 'Authorized Signature', 0, 1, 'C');

/* ===========================
   Footer
=========================== */
$pdf->Ln(10);
$pdf->SetFont('Arial', 'I', 9);
$pdf->SetTextColor(120, 120, 120);
$pdf->Cell(190, 6, 'Generated by CRIMS | Dhaka Government Polytechnic Institute', 0, 1, 'C');
$pdf->SetTextColor(0, 0, 0);

/* ===========================
   Output & Auto Download
=========================== */
$fileName = "Zero_Attendance_Report_" . date('d-m-Y') . ".pdf";

ob_end_clean();
$pdf->Output('D', $fileName);
exit;
?>