<?php
ob_start();

require_once("../config/session.php");
require_once("../config/database.php");
require_once("../library/fpdf/fpdf.php");

date_default_timezone_set("Asia/Dhaka");

if (!isset($_GET['id'])) {
    die("Invalid Request");
}

$session_id = (int)$_GET['id'];

/* ===========================
   Attendance Session
=========================== */

$stmt = $conn->prepare("
SELECT *
FROM attendance_sessions
WHERE id=?
");

$stmt->execute([$session_id]);

$session = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$session) {
    die("Attendance Record Not Found.");
}

/* ===========================
   Present Students
=========================== */

$stmt = $conn->prepare("
SELECT
students.roll,
students.full_name

FROM attendance_details

INNER JOIN students
ON students.id = attendance_details.student_id

WHERE attendance_details.session_id=?
AND attendance_details.status='Present'

ORDER BY CAST(students.roll AS UNSIGNED) ASC
");

$stmt->execute([$session_id]);

$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ===========================
   Statistics
=========================== */

$total = $conn->prepare("
SELECT COUNT(*)
FROM attendance_details
WHERE session_id=?
");

$total->execute([$session_id]);

$total_students = $total->fetchColumn();

$present = $conn->prepare("
SELECT COUNT(*)
FROM attendance_details
WHERE session_id=?
AND status='Present'
");

$present->execute([$session_id]);

$total_present = $present->fetchColumn();

$total_absent = $total_students - $total_present;

/* ===========================
   Create PDF
=========================== */

$pdf = new FPDF('P','mm','A4');

$pdf->AddPage();

$pdf->SetAutoPageBreak(true,20);

$pdf->SetTitle("Attendance Report");

$pdf->SetAuthor("CRIMS");

/* ===========================
   Professional Header (Full Width: 210mm)
=========================== */

$logo = "../assets/images/dgpi_logo.png";

/* Blue Header */

$pdf->SetFillColor(13,71,161);
$pdf->Rect(0,0,210,40,'F');

$pdf->SetTextColor(255,255,255);

/* Logo */

if(file_exists($logo)){
    $pdf->Image($logo,12,8,20);
}

/* Institute */

$pdf->SetXY(0,8);

$pdf->SetFont('Arial','B',17);
$pdf->Cell(210,8,'Dhaka Government Polytechnic Institute',0,1,'C');

$pdf->SetX(0);
$pdf->SetFont('Arial','',11);
$pdf->Cell(210,6,'Tejgaon, Dhaka-1208',0,1,'C');

$pdf->SetX(0);
$pdf->SetFont('Arial','B',12);
$pdf->Cell(210,6,'Department of Computer Science and Technology (CST)',0,1,'C');

$pdf->SetX(0);
$pdf->SetFont('Arial','',10);
$pdf->Cell(210,6,'Class Record & Information Management System (CRIMS)',0,1,'C');

$pdf->SetTextColor(0,0,0);

$pdf->Ln(10);

/* ===========================
   Report Title
=========================== */

$pdf->SetFont('Arial','B',16);

$pdf->Cell(190,10,'Attendance Report',0,1,'C');

$pdf->Ln(2);

/* ===========================
   Session Information (Aligned Grid Layout - Total Width: 190mm)
   Standard Column 1 Label: 30, Value 1: 65, Column 2 Label: 30, Value 2: 65
=========================== */

$pdf->SetFillColor(240,248,255);

/* Row 1: Course Name & Course Code */
$pdf->SetFont('Arial','B',10);
$pdf->Cell(30,9,'Course Name',1,0,'L',true);
$pdf->SetFont('Arial','',10);
$pdf->Cell(65,9,$session['course_name'] ?? '',1,0,'L');
$pdf->SetFont('Arial','B',10);
$pdf->Cell(30,9,'Course Code',1,0,'L',true);
$pdf->SetFont('Arial','',10);
$pdf->Cell(65,9,$session['course_code'] ?? '',1,1,'L');

/* Row 2: Teacher Name (Full span or split nicely) */
$pdf->SetFont('Arial','B',10);
$pdf->Cell(30,9,'Teacher',1,0,'L',true);
$pdf->SetFont('Arial','',10);
$pdf->Cell(160,9,$session['teacher_name'] ?? '',1,1,'L');

/* Row 3: Department & Semester (Matched widths: 30 + 65 + 30 + 65 = 190) */
$pdf->SetFont('Arial','B',10);
$pdf->Cell(30,9,'Department',1,0,'L',true);
$pdf->SetFont('Arial','',10);
$pdf->Cell(65,9,$session['department'] ?? 'Computer Science and Technology',1,0,'L');
$pdf->SetFont('Arial','B',10);
$pdf->Cell(30,9,'Semester',1,0,'L',true);
$pdf->SetFont('Arial','',10);
$pdf->Cell(65,9,$session['semester'] ?? '',1,1,'L');

/* Row 4: Section & Shift (Matched widths: 30 + 65 + 30 + 65 = 190) */
$pdf->SetFont('Arial','B',10);
$pdf->Cell(30,9,'Section',1,0,'L',true);
$pdf->SetFont('Arial','',10);
$pdf->Cell(65,9,$session['section'] ?? '',1,0,'L');
$pdf->SetFont('Arial','B',10);
$pdf->Cell(30,9,'Shift',1,0,'L',true);
$pdf->SetFont('Arial','',10);
$pdf->Cell(65,9,$session['shift'] ?? '',1,1,'L');

/* Row 5: Date & Time (Matched widths: 30 + 65 + 30 + 65 = 190) */
$pdf->SetFont('Arial','B',10);
$pdf->Cell(30,9,'Date',1,0,'L',true);
$pdf->SetFont('Arial','',10);
$pdf->Cell(65,9,isset($session['class_date']) ? date("d-m-Y",strtotime($session['class_date'])) : '',1,0,'L');
$pdf->SetFont('Arial','B',10);
$pdf->Cell(30,9,'Time',1,0,'L',true);
$pdf->SetFont('Arial','',10);
$pdf->Cell(65,9,isset($session['class_time']) ? date("h:i A",strtotime($session['class_time'])) : '',1,1,'L');

$pdf->Ln(8);

/* ===========================
   Present Student List
=========================== */

$pdf->SetFont('Arial','B',13);
$pdf->Cell(190,8,'Present Student List',0,1,'C');

$pdf->SetFillColor(25,135,84);
$pdf->SetTextColor(255,255,255);
$pdf->SetFont('Arial','B',11);

$pdf->Cell(15,10,'SL',1,0,'C',true);
$pdf->Cell(35,10,'Roll',1,0,'C',true);
$pdf->Cell(110,10,'Student Name',1,0,'C',true);
$pdf->Cell(30,10,'Status',1,1,'C',true);

$pdf->SetTextColor(0,0,0);
$pdf->SetFont('Arial','',10);

$sl = 1;
$fill = false;

foreach($students as $row){

    if($fill){
        $pdf->SetFillColor(248,249,250);
    }else{
        $pdf->SetFillColor(255,255,255);
    }

    $pdf->Cell(15,9,$sl++,1,0,'C',true);
    $pdf->Cell(35,9,$row['roll'],1,0,'C',true);
    $pdf->Cell(110,9,$row['full_name'],1,0,'L',true);
    $pdf->Cell(30,9,'Present',1,1,'C',true);

    $fill = !$fill;
}

/* ===========================
   Summary
=========================== */

$pdf->Ln(8);

$pdf->SetFillColor(230,240,255);

$pdf->SetFont('Arial','B',11);

$pdf->Cell(60,10,'Total Students',1,0,'L',true);
$pdf->SetFont('Arial','',11);
$pdf->Cell(30,10,$total_students,1,1);

$pdf->SetFont('Arial','B',11);
$pdf->Cell(60,10,'Present',1,0,'L',true);
$pdf->SetFont('Arial','',11);
$pdf->Cell(30,10,$total_present,1,1);

$pdf->SetFont('Arial','B',11);
$pdf->Cell(60,10,'Absent',1,0,'L',true);
$pdf->SetFont('Arial','',11);
$pdf->Cell(30,10,$total_absent,1,1);

/* ===========================
   Teacher Signature
=========================== */

$pdf->Ln(20);

$pdf->SetDrawColor(120,120,120);

$pdf->Line(140,$pdf->GetY(),190,$pdf->GetY());

$pdf->Ln(3);

$pdf->SetFont('Arial','B',11);

$pdf->Cell(140);

$pdf->Cell(50,8,'Teacher Signature',0,1,'C');

/* ===========================
   Footer
=========================== */

$pdf->Ln(10);

$pdf->SetFont('Arial','I',9);

$pdf->SetTextColor(120,120,120);

$pdf->Cell(
    190,
    6,
    'Generated by CRIMS | Dhaka Government Polytechnic Institute',
    0,
    1,
    'C'
);

$pdf->SetTextColor(0,0,0);

/* ===========================
   Output (Renamed & Auto Download)
=========================== */

$courseName = str_replace(' ', '_', $session['course_name'] ?? 'Course');
$classDate = isset($session['class_date']) ? date("d-m-Y", strtotime($session['class_date'])) : date('d-m-Y');
$fileName = "4-2-CST-A_" . $courseName . "_Attendance_Report_" . $classDate . ".pdf";

ob_end_clean();
$pdf->Output('D', $fileName);

exit;
?>