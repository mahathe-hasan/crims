<?php
ob_start();

require_once("../config/session.php");
require_once("../config/database.php");
require_once("../library/fpdf/fpdf.php");

date_default_timezone_set("Asia/Dhaka");

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

class PDF extends FPDF {
    function Header() {
        $this->SetFillColor(13, 71, 161);
        $this->Rect(0, 0, 210, 6, 'F');

        $this->SetY(12);
        $this->SetFont('Arial', 'B', 14);
        $this->SetTextColor(13, 71, 161);
        $this->Cell(0, 6, 'DHAKA GOVERNMENT POLYTECHNIC INSTITUTE', 0, 1, 'C');

        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 4, 'Tejgaon, Dhaka-1208', 0, 1, 'C');

        $this->SetFont('Arial', 'B', 10);
        $this->SetTextColor(50, 50, 50);
        $this->Cell(0, 5, 'Department of Computer Science and Technology (CST)', 0, 1, 'C');

        $this->SetDrawColor(200, 200, 200);
        $this->SetLineWidth(0.3);
        $this->Line(15, 30, 195, 30);
        $this->Ln(4);
    }

    function Footer() {
        $this->SetY(-12);
        $this->SetDrawColor(220, 220, 220);
        $this->Line(15, $this->GetY(), 195, $this->GetY());
        
        $this->SetY(-10);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(130, 130, 130);
        $this->Cell(0, 4, 'CRIMS - Student Information Management System', 0, 0, 'L');
        $this->Cell(0, 4, 'Page ' . $this->PageNo(), 0, 0, 'R');
    }
}

$pdf = new PDF('P', 'mm', 'A4');
$pdf->SetAutoPageBreak(true, 15);
$pdf->AddPage();

$pdf->SetTitle("Student Profile - " . $student['roll']);
$pdf->SetAuthor("CRIMS");

// Document Title Bar
$pdf->SetFillColor(240, 244, 248);
$pdf->SetTextColor(13, 71, 161);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 8, 'STUDENT INFORMATION PROFILE', 0, 1, 'C', true);
$pdf->Ln(5);

$start_y = $pdf->GetY();

// Student Brief Info (Full Width - No Photo Section)
$pdf->SetXY(15, $start_y);
$pdf->SetFont('Arial', 'B', 13);
$pdf->SetTextColor(30, 30, 30);
$pdf->Cell(180, 7, $student['full_name'], 0, 1, 'L');

$pdf->SetX(15);
$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(80, 80, 80);
$pdf->Cell(180, 5, 'Roll: ' . $student['roll'], 0, 1, 'L');
$pdf->SetX(15);
$pdf->Cell(180, 5, 'Registration: ' . ($student['registration'] ?: 'N/A'), 0, 1, 'L');
$pdf->SetX(15);
$pdf->Cell(180, 5, 'Department: ' . $student['department'], 0, 1, 'L');
$pdf->SetX(15);
$pdf->Cell(180, 5, 'Semester: ' . $student['semester'], 0, 1, 'L');

// Move down below the header info block for the main table
$pdf->SetY($start_y + 32);

// Detailed Table Header
$pdf->SetFillColor(13, 71, 161);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 10);

$pdf->Cell(65, 7, '   Data Field', 1, 0, 'L', true);
$pdf->Cell(115, 7, '   Information Details', 1, 1, 'L', true);

// Table Rows
$fields = [
    'Full Name'                    => $student['full_name'],
    'Roll'                         => $student['roll'],
    'Registration'                 => $student['registration'],
    'Department'                   => $student['department'],
    'Semester'                     => $student['semester'],
    'Phone Number'                 => $student['phone'],
    'Email Address'                => $student['email'],
    'Emergency Contact Number'   => $student['emergency_contact'] ?? 'N/A',
    'Relation'                     => $student['relation'] ?? 'N/A',
    'Personal Emergency Contact' => $student['personal_emergency_contact'] ?? 'N/A'
];

$pdf->SetFont('Arial', '', 10);
$fill = false;

foreach($fields as $label => $value){
    if($fill){
        $pdf->SetFillColor(248, 250, 252);
    } else {
        $pdf->SetFillColor(255, 255, 255);
    }

    $pdf->SetTextColor(60, 60, 60);
    $pdf->Cell(65, 7, '   ' . $label, 1, 0, 'L', true);
    
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetTextColor(20, 20, 20);
    $pdf->Cell(115, 7, '   ' . ($value !== "" ? $value : 'N/A'), 1, 1, 'L', true);
    
    $pdf->SetFont('Arial', '', 10);
    $fill = !$fill;
}

// Signature Area
$pdf->Ln(12);
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(50, 50, 50);

$pdf->Cell(135);
$pdf->Cell(45, 4, '', 'B', 1, 'C'); 
$pdf->Cell(135);
$pdf->Cell(45, 5, 'Authorized Signature', 0, 1, 'C');

ob_end_clean();
$pdf->Output('I', 'Student_Profile_' . $student['roll'] . '.pdf');
exit;