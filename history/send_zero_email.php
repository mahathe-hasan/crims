<?php
require_once("../config/session.php");
require_once("../config/database.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once("../library/phpmailer/src/Exception.php");
require_once("../library/phpmailer/src/PHPMailer.php");
require_once("../library/phpmailer/src/SMTP.php");

date_default_timezone_set("Asia/Dhaka");

$start_date = $_GET['start_date'] ?? '';
$end_date   = $_GET['end_date'] ?? '';

// ইংরেজি সংখ্যাকে বাংলায় রূপান্তর করার ফাংশন
function convertToBanglaNumber($number) {
    $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    $bangla  = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
    return str_replace($english, $bangla, $number);
}

$sql = "
    SELECT *
    FROM students
    WHERE email IS NOT NULL AND email != ''
    AND id NOT IN (
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

$sql .= " )";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$zero_students = $stmt->fetchAll(PDO::FETCH_ASSOC);

$success_count = 0;
$fail_count = 0;

foreach ($zero_students as $student) {
    $to = $student['email'];
    $name = $student['full_name'] ?? $student['name'] ?? 'Student';
    $roll = $student['roll'] ?? 'N/A';

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'crims.dgpi@gmail.com'; 
        $mail->Password   = 'zqvvbbmidurkcree'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom('crims.dgpi@gmail.com', 'CRIMS Authority - DGPI');
        $mail->addAddress($to, $name);

        $mail->Subject = "Important Notice: Notice Regarding Class Absence and Attendance Confirmation (CRIMS - DGPI)";
        
        $raw_date_text = (!empty($start_date) && !empty($end_date)) 
            ? date("d-m-Y", strtotime($start_date)) . " থেকে " . date("d-m-Y", strtotime($end_date)) 
            : "বর্তমান সেমিস্টার শুরু থেকে এখন";

        $date_range_text = convertToBanglaNumber($raw_date_text);

        $message = "প্রিয় শিক্ষার্থী $name, ( $roll )\n\n";
        $message .= "আপনাকে অবহিত করা যাচ্ছে যে, " . $date_range_text . " পর্যন্ত অনুষ্ঠিত ক্লাসসমূহে আপনার কোনো উপস্থিতি পরিলক্ষিত হয়নি।\n\n";
        $message .= "নিয়মিত ক্লাসে অনুপস্থিতি আপনার থিওরি কন্টিনিউয়াস (TC) ও প্র্যাকটিক্যাল কন্টিনিউয়াস (PC) নম্বরের ওপর নেতিবাচক প্রভাব পরবে, যা পরবর্তীতে আপনার সেমিস্টার পরীক্ষার ফলাফলেও প্রভাব পরবে। তাই আপনার একাডেমিক কার্যক্রম ও ফলাফল বিবেচনায় পরবর্তী সকল ক্লাসে নিয়মিত ও যথাসময়ে উপস্থিত থাকার জন্য বিশেষভাবে অনুরোধ করা হলো।\n\n";
        $message .= "কোনো অনিবার্য বা যৌক্তিক কারণে ক্লাসে উপস্থিত হতে অসমর্থ হলে, যথাসময়ে সংশ্লিষ্ট ক্লাস শিক্ষক/বিভাগীয় কর্তৃপক্ষকে অবহিত করার জন্য নির্দেশনা প্রদান করা হলো।\n\n";
        $message .= "আপনার একাডেমিক অগ্রগতি ও উপস্থিতি নিশ্চিত করার স্বার্থে পরবর্তী ক্লাসসমূহে নিয়মিত উপস্থিত থাকার জন্য বিশেষভাবে অনুরোধ করা হলো।\n\n";
        $message .= "ধন্যবাদান্তে,\n";
        $message .= "CRIMS কর্তৃপক্ষ\n";
        $message .= "Dhaka Government Polytechnic Institute";

        $mail->Body = $message;

        $mail->send();
        $success_count++;

        // প্রতিটি মেইল পাঠানোর পর ১ সেকেন্ডের বিরতি (স্প্যাম প্রটেকশন)
        sleep(1);

    } catch (Exception $e) {
        $fail_count++;
    }
}

header("Location: student_history.php?zero_attendance=1&start_date=$start_date&end_date=$end_date&email_sent=1&success=$success_count");
exit;
?>