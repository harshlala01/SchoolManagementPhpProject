<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require('../Includes/dbcon.php');
require('tfpdf.php');

// Input
$regId = $_GET['regId'] ?? '';
$term = $_GET['term'] ?? '';
if (!$regId || !$term) exit("Missing parameters.");
// if (!$regId) exit("Missing regId");

// Step 1: Fetch student data (any one term is enough)
// $sql = "SELECT * FROM student_marks WHERE regId = ? LIMIT 1";
// $stmt = $conn->prepare($sql);
// $stmt->bind_param("s", $regId);
// $stmt->execute();
// $result = $stmt->get_result();
// $student = $result->fetch_assoc();

// if (!$student) exit("Student not found");

// Step 2: Fetch Term 1 and Term 2 marks
$termMarks = [];
foreach (['Term 1', 'Term 2'] as $term) {
    $sql = "SELECT * FROM student_marks WHERE regId = ? AND term = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $regId, $term);
    $stmt->execute();
    $result = $stmt->get_result();
    $termMarks[$term] = $result->fetch_assoc();
}
// $termData = ['Term 1' => null, 'Term 2' => null];
// while ($row = $result->fetch_assoc()) {
//     if ($row['term'] === 'Term 1') $termData['Term 1'] = $row;
//     if ($row['term'] === 'Term 2') $termData['Term 2'] = $row;
// }
// $stmt->close();.
$stmt->close();
$termData = $termMarks;
// echo "<pre>"; print_r($termData); echo "</pre>";


if (!$termData['Term 1'] && !$termData['Term 2']) {
    exit("No marks data found.");
}

$halfYearlyMarks = $termData['Term 1'] ?? [];
$annualMarks = $termData['Term 2'] ?? [];

$marksRow = $termData['Term 2'] ?? $termData['Term 1']; // Latest used for student info
$classId = (int)$marksRow['classId'];
$className = strtoupper(trim($marksRow['className']));
$student = $marksRow;
// --- Subject definitions based on class ---
$primaryClasses = ['I', 'II', 'III', 'IV', 'V'];
if (in_array($className, $primaryClasses)) {
    $subjectList = [
        'English'         => ['theory' => 'english_theory',         'practical' => 'english_practical'],
        'Hindi/Bengali'   => ['theory' => 'hindi_bengali_theory',   'practical' => 'hindi_bengali_practical'],
        'Math'            => ['theory' => 'math_theory',            'practical' => 'math_practical'],
        'EVS'             => ['theory' => 'evs_theory',             'practical' => 'evs_practical'],
        'Computer'        => ['theory' => 'computer_theory',        'practical' => 'computer_practical'],
        'Drawing'         => ['theory' => null,                     'practical' => 'drawing_practical'],
    ];
} else {
    $subjectList = [
        'English'         => ['theory' => 'english_theory',         'practical' => 'english_practical'],
        'Hindi/Bengali'   => ['theory' => 'hindi_bengali_theory',   'practical' => 'hindi_bengali_practical'],
        'Math'            => ['theory' => 'math_theory',            'practical' => 'math_practical'],
        'Science'         => ['theory' => 'science_theory',         'practical' => 'science_practical'],
        'SST'             => ['theory' => 'sst_theory',             'practical' => 'sst_practical'],
        'Computer'        => ['theory' => 'computer_theory',        'practical' => 'computer_practical'],
        'Drawing'         => ['theory' => null,                     'practical' => 'drawing_practical'],
    ];
}

                    // --- Generate final subject marks ---
                    $subjects = [];
                    foreach ($subjectList as $subjectName => $fields) {
                        $halfTheory = $fields['theory'] && isset($halfYearlyMarks[$fields['theory']]) ? (int)$halfYearlyMarks[$fields['theory']] : 0;
                        $halfPractical = $fields['practical'] && isset($halfYearlyMarks[$fields['practical']]) ? (int)$halfYearlyMarks[$fields['practical']] : 0;

                        $annualTheory = $fields['theory'] && isset($annualMarks[$fields['theory']]) ? (int)$annualMarks[$fields['theory']] : 0;
                        $annualPractical = $fields['practical'] && isset($annualMarks[$fields['practical']]) ? (int)$annualMarks[$fields['practical']] : 0;

                        // Use annual if available, else half yearly
                        $theoryMarks = ($annualTheory > 0 || $annualPractical > 0) ? $annualTheory : $halfTheory;
                        $practicalMarks = ($annualTheory > 0 || $annualPractical > 0) ? $annualPractical : $halfPractical;

                        $total = $theoryMarks + $practicalMarks;
                        $grade = getGrade($total);

                        $subjects[] = [
                            'name' => $subjectName,
                            'theory' => $theoryMarks,
                            'practical' => $practicalMarks,
                            'total' => $total,
                            'grade' => $grade
                        ];
                    }           
// echo "<pre>";
// print_r($subjects);
// echo "</pre>";
// --- Grade Helper ---
// function getGrade($marks) {
//     if ($marks >= 90) return 'A+';
//     elseif ($marks >= 80) return 'A';
//     elseif ($marks >= 70) return 'B+';
//     elseif ($marks >= 60) return 'B';
//     elseif ($marks >= 50) return 'C';
//     elseif ($marks >= 33) return 'D';
//     else return 'F';
// }
// Step 3: Subject list as per class
// $classId = (int)$student['classId'];

// if ($classId >= 1 && $classId <= 5) {
//     $subjects = [
//         "English" => "english_theory",
//         "Hindi/Bengali" => "hindi_bengali_theory",
//         "Math" => "math_theory",
//         "EVS" => "evs_theory",
//         "Computer" => "computer_theory",
//         "Drawing" => "drawing_practical"
//     ];
// } else {
//     $subjects = [
//         "English" => "english_theory",
//         "Hindi/Bengali" => "hindi_bengali_theory",
//         "Math" => "math_theory",
//         "Science" => "science_theory",
//         "SST" => "sst_theory",
//         "Computer" => "computer_theory",
//         "Drawing" => "drawing_practical"
//     ];
// }

// Step 4: Initialize PDF
$pdf = new tFPDF('P','mm','A4');
// $pdf->AddPage();
// $pdf->AddFont('DejaVu','','DejaVuSans.ttf',true);
// $pdf->AddFont('DejaVu', 'B', 'DejaVuSans-Bold.ttf', true);
// $pdf->SetFont('DejaVu','',12);
$pdf->AddPage();
$pdf->AddFont('DejaVu','','DejaVuSans.ttf',true);
$pdf->AddFont('DejaVu','B','DejaVuSans-Bold.ttf',true);
$pdf->SetFont('DejaVu','',12);
// Simulate light blue background instead of rgba(0, 0, 255, 0.1)
// $pdf->SetFillColor(230, 240, 255); // Light blue
// $pdf->Image('./logo/schoolLogo.png', 50, 100, 100);

// Header
$pdf->Image('./logo/schoolLogo.png', 10, 8, 40);
// Student Photo (Right)
if (empty($student['studentPhoto'])) {
    $stmt = $conn->prepare("SELECT * FROM tblstudents WHERE regId = ?");
    $stmt->bind_param("i", $regId);
    $stmt->execute();
    $result = $stmt->get_result();
    $profileRow = $result->fetch_assoc();

    if ($profileRow) {
        $student = array_merge($student, $profileRow); // merge to fill missing fields
    }
}
$photo_path = '../Student/' . $student['studentPhoto'];
if (!empty($student['studentPhoto']) && file_exists($photo_path)) {
     $pdf->Image($photo_path, 167, 8, 32, 38);
} else {
      $pdf->Rect(167, 8, 32, 38);
    $pdf->SetFont('Arial', '', 6);
    $pdf->SetXY(167, 25);
    $pdf->Cell(32, 4, 'No Image Available', 0, 0, 'C');
}

// echo "Photo Path: $photo_path<br>";
// echo "File Exists? " . (file_exists($photo_path) ? 'Yes' : 'No') . "<br>";



// echo "📸 Looking for: $photo_path\n";
// echo "✅ File Exists? " . (file_exists($photo_path) ? 'Yes' : 'No');
// exit;




// $photo_path = '../Student/' . $row['studentPhoto'];
// if (!empty($row['studentPhoto']) && file_exists($photo_path)) {
//     $pdf->Image($photo_path, $x + 3, $y + 20, 22, 26);
// } else {
//     $pdf->Rect($x + 3, $y + 20, 22, 26);
//     $pdf->SetFont('Arial', '', 5);
//     $pdf->SetXY($x + 3, $y + 32);
//     $pdf->Cell(22, 4, 'No Image', 0, 0, 'C');
// }

$pdf->SetY(10); // Align with logo/photo
$pdf->SetX(10); // Reset X before writing text
$pdf->SetFont('DejaVu','B',14);
$pdf->Cell(190, 8, 'HERITAGE DAY SCHOOL', 0, 1, 'C');
$pdf->SetFont('DejaVu','',10);
$pdf->Cell(0, 6, 'Nagaurkra, Barasat Para, Haringhata, Nadia, WB', 0, 1, 'C');
$pdf->Cell(0, 6, '(Affiliated to WBBSE. Code: 123456)', 0, 1, 'C');
$pdf->Cell(0, 6, 'Office: 7364916702 / 9064109172', 0, 1, 'C');
$pdf->SetFont('DejaVu','B',12);
$pdf->Cell(0, 6, 'Academic Report', 0, 1, 'C');
$pdf->Cell(0, 6, 'Session: ' . $student['session'] . '    Class: ' . $student['className'], 0, 1, 'C');
$pdf->Ln(2);

// Header
// $pdf->Cell(190,10,"Student Marksheet",0,1,'C');
// $pdf->SetFont('DejaVu','',11);
// $pdf->Cell(190,10,"Name: ".$student['studentName']."   |   Class: ".$student['className']." ".$student['classArmName'],0,1);


// $pdf->Cell(190,10,"Session: ".$student['session']."   |   Reg ID: ".$student['regId'],0,1);

// Student Info
$pdf->SetFont('DejaVu','',10);
$pdf->Cell(95, 7, 'Name of Student: ' . $student['studentName'], 1);
$pdf->Cell(96, 7, 'Roll No: ' . $student['rollNo'], 1);
$pdf->Ln();
$pdf->Cell(95, 7, 'Admission No: ' . $student['regId'], 1);
$pdf->Cell(96, 7, 'DOB: ' . $student['dob'], 1);
$pdf->Ln();
$pdf->Cell(95, 7, "Mother's Name: " . $student['motherName'], 1);
$pdf->Cell(96, 7, "Father's Name: " . $student['fatherName'], 1);
$pdf->Ln();
$pdf->Cell(191, 7, "Address: " . $student['address'], 1);
$pdf->Ln(10);

$pdf->Ln(-1);
$pdf->SetFont('DejaVu', 'B', 10);
$pdf->SetFillColor(220, 230, 241);



// // Row 2 (Subheaders)
// $pdf->Cell(60, 10, '', 1, 0, 'C');
// $pdf->Cell(30, 10, 'Half Yearly', 1, 0, 'C', true);
// $pdf->Cell(30, 10, 'Half Yearly', 1, 0, 'C', true);
// $pdf->Cell(30, 10, 'Total', 1, 0, 'C', true);
// $pdf->Cell(30, 10, 'Grand Total', 1, 0, 'C', true);
// $pdf->Cell(30, 10, 'Grade', 1, 1, 'C', true);


// Colors
$fillColor = [220, 230, 241];
$pdf->SetFillColor(...$fillColor);

// Column widths
// $w = [60, 33, 33, 33, 33]; // 5 columns
$w = [50, 30, 30, 26, 30, 41];
// Row height
$h = 7;

// First Row: Main Headings
$pdf->SetFont('DejaVu','B',10);
$pdf->Cell($w[0], $h*2, 'Scholastic Areas', 1, 0, 'C', true);

// 'Term 1' spans 2 columns (Half Yearly + Total)
$pdf->Cell($w[4], $h, 'Term 1', 1, 0, 'C', true);
$pdf->Cell($w[4], $h, 'Term 2', 1, 0, 'C', true);

// 'Overall' spans 2 columns (Grand Total + Grade)
$pdf->Cell($w[5] + $w[5], $h, 'Overall', 1, 1, 'C', true);

// Second Row: Sub Headings under Term 1 and Overall
$pdf->Cell($w[0], $h, '', 0, 0); // empty below Scholastic Areas

$pdf->Cell($w[1], $h, 'Half Yearly', 1, 0, 'C', true);
$pdf->Cell($w[1], $h, 'Annual Yearly', 1, 0, 'C', true);
$pdf->Cell($w[2], $h, 'Total', 1, 0, 'C', true);
$pdf->Cell($w[3], $h, 'Grand Total', 1, 0, 'C', true);
$pdf->Cell($w[3], $h, 'Grade', 1, 1, 'C', true);    
$pdf->Cell(50, 7, 'Subject', 1, 0, 'C');
$pdf->Cell(30, 7, '100', 1, 0, 'C');
$pdf->Cell(30, 7, '100', 1, 0, 'C');
$pdf->Cell(30, 7, '100', 1, 0, 'C');
$pdf->Cell(26, 7, '100', 1, 0, 'C');
$pdf->Cell(26, 7, 'Grade', 1, 1, 'C');
//  $pdf->Ln(4);

$pdf->SetFont('DejaVu','',10);
// $grandTotal = 0;
// $maxMarks = 0;

// foreach ($subjectList as $subjectName => $fields) {
//     $theoryField = $fields['theory'];
//     $practicalField = $fields['practical'];

//     $halfTheory = $theoryField && isset($termData['Term 1'][$theoryField]) ? (int)$termData['Term 1'][$theoryField] : 0;
//     $halfPractical = $practicalField && isset($termData['Term 1'][$practicalField]) ? (int)$termData['Term 1'][$practicalField] : 0;

//     $annualTheory = $theoryField && isset($termData['Term 2'][$theoryField]) ? (int)$termData['Term 2'][$theoryField] : 0;
//     $annualPractical = $practicalField && isset($termData['Term 2'][$practicalField]) ? (int)$termData['Term 2'][$practicalField] : 0;

//     // Final (latest term only for "Total")
//     $finalTheory = ($annualTheory > 0 || $annualPractical > 0) ? $annualTheory : $halfTheory;
//     $finalPractical = ($annualTheory > 0 || $annualPractical > 0) ? $annualPractical : $halfPractical;

//     $halfTotal = $halfTheory + $halfPractical;
//     $annualTotal = $annualTheory + $annualPractical;
//     $finalTotal = $finalTheory + $finalPractical;
//     $grade = getGrade($finalTotal);

//     // PDF Output
//     $pdf->Cell(50, 10, $subjectName, 1, 0, 'C');
//     $pdf->Cell(30, 10, $halfTotal, 1, 0, 'C');
//     $pdf->Cell(30, 10, $annualTotal, 1, 0, 'C');
//     $pdf->Cell(30, 10, $finalTotal, 1, 0, 'C');
//     $pdf->Cell(26, 10, $finalTotal, 1, 0, 'C');
//     $pdf->Cell(26, 10, $grade, 1, 1, 'C');

//     $grandTotal += $finalTotal;
//     $maxMarks += 100;
// }

$grandTotal = 0;
$maxMarks = 0;
$failedSubjects = [];

foreach ($subjectList as $subjectName => $fields) {
    $theoryField = $fields['theory'];
    $practicalField = $fields['practical'];

    $halfTheory = $theoryField && isset($termData['Term 1'][$theoryField]) ? (int)$termData['Term 1'][$theoryField] : 0;
    $halfPractical = $practicalField && isset($termData['Term 1'][$practicalField]) ? (int)$termData['Term 1'][$practicalField] : 0;

    $annualTheory = $theoryField && isset($termData['Term 2'][$theoryField]) ? (int)$termData['Term 2'][$theoryField] : 0;
    $annualPractical = $practicalField && isset($termData['Term 2'][$practicalField]) ? (int)$termData['Term 2'][$practicalField] : 0;

    // Final marks (based on latest term data)
    $finalTheory = ($annualTheory > 0 || $annualPractical > 0) ? $annualTheory : $halfTheory;
    $finalPractical = ($annualTheory > 0 || $annualPractical > 0) ? $annualPractical : $halfPractical;

    $halfTotal = $halfTheory + $halfPractical;
    $annualTotal = $annualTheory + $annualPractical;
    $finalTotal = $finalTheory + $finalPractical;

    // Grade and pass/fail logic
    $grade = getGrade($finalTotal);
    $status = ($finalTotal < 30) ? 'Fail' : 'Pass';
    if ($finalTotal < 30) $failedSubjects[] = $subjectName;

    // PDF Table Row (optional if rendering in table)
    $pdf->Cell(50, 7, $subjectName, 1, 0, 'C');
    $pdf->Cell(30, 7, $halfTotal, 1, 0, 'C');
    $pdf->Cell(30, 7, $annualTotal, 1, 0, 'C');
    $pdf->Cell(30, 7, $finalTotal, 1, 0, 'C');
    $pdf->Cell(26, 7, $finalTotal, 1, 0, 'C');
    $pdf->Cell(26, 7, $grade, 1, 0, 'C');
    $pdf->Cell(26, 7, $status, 1, 1, 'C');

    $grandTotal += $finalTotal;
    $maxMarks += 100;
}


// --- Summary ---
$percentage = $maxMarks > 0 ? ($grandTotal / $maxMarks) * 100 : 0;
$percentage = round($percentage, 2); 
$finalGrade = getGrade($percentage);
$rank = getRankOrDivision($percentage);
function getRankOrDivision($percentage) {
  if ($percentage < 33) return 'Fail';             // or 'Last Division'
    if ($percentage >= 75) return '1st Division';
    if ($percentage >= 60) return '2nd Division';
    if ($percentage >= 45) return '3rd Division';
    if ($percentage >= 33) return '4th Division';
    return 'Fail'; // fallback
}
// function getRankOrDivision($percentage) {
//     if ($percentage >= 90) return '1st';
//     if ($percentage >= 80) return '2nd';
//     if ($percentage >= 60) return '3rd';
//     if ($percentage >= 33) return 'Pass';
//     return 'Fail';
// }


// $pdf->Ln(5);
// $pdf->SetFont('Arial', 'B', 12);
// $pdf->Cell(60, 10, "Grand Total", 1);
// $pdf->Cell(75, 10, "$grandTotal / $maxMarks", 1, 0, 'C');
// $pdf->Cell(25, 10, number_format($percentage, 2) . '%', 1, 0, 'C');
// $pdf->Cell(25, 10, $finalGrade, 1, 1, 'C');
// Jo term/session aap report me use kar rahe ho (jaise Term 1 ya Term 2 ka sessionTermId)
$sessionTermId = $marksRow['sessionTermId'] ?? ''; // marks data se milega
if (!$sessionTermId) {
    // fallback ya default (optional)
    $sessionTermId = '2025-26-T1'; // ya aapke system ka current session term
}
$statusPresent = 'Present';
// Student admissionNo
$admissionNo = $marksRow['admissionNo'] ?? $regId;

// // Attendance fetch query (present days)
// $sqlPresent = "SELECT COUNT(*) as presentDays FROM tblattendance 
//                WHERE admissionNo = ? AND status = ? AND sessionTermId = ?";
// $stmtPresent = $conn->prepare($sqlPresent);
// $stmtPresent->bind_param("sss", $admissionNo, $statusPresent, $sessionTermId);
// var_dump($admissionNo, $sessionTermId, $statusPresent);

// $stmtPresent->execute();
// $resPresent = $stmtPresent->get_result();
// $presentRow = $resPresent->fetch_assoc();
// $presentDays = $presentRow['presentDays'] ?? 0;
// $stmtPresent->close();

$sqlTotal = "SELECT COUNT(*) as totalDays FROM tblattendance 
             WHERE admissionNo = ? AND sessionTermId = ? ";
$stmtTotal = $conn->prepare($sqlTotal);
$stmtTotal->bind_param("ss", $admissionNo, $sessionTermId);
$stmtTotal->execute();
$resTotal = $stmtTotal->get_result();
$totalRow = $resTotal->fetch_assoc();
$totalDays = $totalRow['totalDays'] ?? 0;
$stmtTotal->close();

// Attendance percentage
$attendancePercent = $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 2) : 0;

$attendance = $student['attendance'] ?? '0/160';
$pdf->Ln(1);
$pdf->SetFont('DejaVu','B',10);
$pdf->SetFillColor(255,204,153);
$pdf->Cell(28, 7, 'Attendance', 1, 0, 'C', true);
$pdf->SetFont('DejaVu','',10);
$pdf->Cell(22, 7, $attendance, 1, 0, 'C');
// $pdf->Cell(22, 7, "$presentDays / $totalDays", 1, 0, 'C');
$pdf->SetFont('DejaVu','B',10);
$pdf->Cell(29, 7, 'Total Marks', 1, 0, 'C', true);
$pdf->SetFont('DejaVu','',10);
$pdf->Cell(25, 7, "$grandTotal", 1, 0, 'C');
$pdf->SetFont('DejaVu','B',10);
$pdf->Cell(28, 7, 'Percentage', 1, 0, 'C', true);
$pdf->SetFont('DejaVu','',10);
$pdf->Cell(25, 7, "$percentage%", 1, 0, 'C');
$pdf->SetFont('DejaVu','B',10);
$pdf->Cell(20, 7, 'Grade', 1, 0, 'C', true);
$pdf->Cell(14, 7, $finalGrade, 1, 1, 'C');
// Output
// $pdf->Output();

// Grade Logic
// function getGrade($marks) {
//     if ($marks >= 90) return 'A+';
//     if ($marks >= 80) return 'A';
//     if ($marks >= 70) return 'B+';
//     if ($marks >= 60) return 'B';
//     if ($marks >= 50) return 'C';
//     if ($marks >= 33) return 'D';
//     return 'F';
// }
function getGrade($marks) {
    if ($marks < 30) return 'F';
    if ($marks >= 90) return 'A+';
    if ($marks >= 80) return 'A';
    if ($marks >= 70) return 'B+';
    if ($marks >= 60) return 'B';
    if ($marks >= 50) return 'C+';
    if ($marks >= 40) return 'C';
    if ($marks >= 30) return 'D';
    return 'F';
}



// $pdf->Ln(4);
// $pdf->SetFont('DejaVu','',10);
// $pdf->Cell(95,8,'Co-Scholastic Areas',1,0,'C',true);
// $pdf->Cell(95,8,'Grade',1,1,'C',true);
// $pdf->Cell(95,8,'Discipline',1,0);
// $pdf->Cell(95,8,'A',1,1,'C');
// $pdf->Cell(95,8,'Attitude',1,0);
// $pdf->Cell(95,8,'A',1,1,'C');
// $pdf->Cell(95,8,'Neatness',1,0);
// $pdf->Cell(95,8,'B',1,1,'C');
  // Co-Scholastic
    $pdf->Ln(2);
// Left table (Co-Scholastic)
$yStart = $pdf->GetY();
$pdf->SetFont('DejaVu', 'B', 10);
$pdf->SetFillColor(209, 236, 241); // #d1ecf1
$pdf->Cell(55, 9, 'Co-Scholastic Areas', 1, 0, 'C', true);
$pdf->Cell(55, 9, 'Performance', 1, 1, 'C', true);
$pdf->SetFont('DejaVu', '', 10);
$skills = [
    ['Discipline', 'Excellent'],
    ['Listening Skill', 'Good Listener'],
    ['Reading Skill', 'Fluent'],
    ['Writing Skill', 'Awesome'],
    ['Creative Thinking', 'Good Creator'],
    ['Interest', 'Reading'],
    ['Hobby', 'Swimming']
];
 foreach ($skills as $s) {
        $pdf->Cell(55, 9, $s[0], 1);
        $pdf->Cell(55, 9, $s[1], 1);
        $pdf->Ln();
    }

// Position it at the same Y as Co-Scholastic
$pdf->SetY($yStart);
$pdf->SetX(135); // Adjust X to align it properly to the right

// Heading row (single cell)
$pdf->SetFont('DejaVu', 'B', 10);
$pdf->SetFillColor(195, 230, 203); // #c3e6cb // light green
$pdf->SetX(126);
$pdf->Cell(75, 7, 'Result', 1, 1, 'C', true); // 60 width, center align, next line


// $pdf->Ln(5);
// $pdf->SetFont('DejaVu','',10);
// $pdf->Cell(60,8,'Result: '.($average >= 33 ? 'Pass' : 'Fail'),1,0);
// $pdf->Cell(65,8,'Percentage: '.number_format($average,2).'%',1,0);
// $pdf->Cell(65,8,'Rank: 1st',1,1);

// $pdf->Ln(3);
// $pdf->SetFont('DejaVu','',9);
// $pdf->MultiCell(190,7,'*Admission open for Class XI (Science, Commerce, Humanities) for session 2025-26. Contact School Reception.*',0,'C');
// $pdf->Ln(6);
// Rank → 5
// Result → Pass
$pdf->SetFont('DejaVu', '', 10);
$pdf->SetX(126); // reset X
$pdf->Cell(37, 7, 'Result', 1, 0);
$pdf->Cell(38, 7, $status, 1, 1);
// $pdf->SetX(126); $pdf->Cell(37, 7, 'Result', 1);
//  $pdf->Cell(38, 7, ($status == 'D') ? 'Fail' : 'Pass', 1, 1);
// Percentage → 86.5%
$pdf->SetX(126);
$pdf->Cell(37, 7, 'Percentage', 1, 0);
$pdf->Cell(38, 7, "$percentage%", 1, 1);
$pdf->SetX(126);
$pdf->Cell(37, 7, 'Rank', 1, 0);
$pdf->Cell(38, 7, "$rank", 1, 1);
// Move a bit below the Result table
$pdf->Ln(4); // vertical space
$pdf->SetX(126); // shift left a bit for wider box
$boxWidth = 75; // increased width
$pdf->SetFont('DejaVu', 'B', 10);
$pdf->SetFillColor(255, 243, 205); // #fff3cd

$pdf->MultiCell($boxWidth, 5,
    "ADMISSION OPEN\nPlay Group to Class 8th Session 2025-26\nAdmission Form Available from 15 Dec 2024\nOnline & Offline\nContact Us: 7364916702 / 9064109172",
    1, // border
    'C', // center text
    true // filled background
);

 // Remarks
    $pdf->Ln(1);
    $pdf->SetTextColor(21, 87, 36); // #155724
    $pdf->SetFont('DejaVu', '', 10);
    $pdf->Write(6, 'Remarks: ');
    $pdf->SetFont('DejaVu', 'B', 10);
    $pdf->Write(6, 'He has been consistently progressing.');
    $pdf->SetTextColor(0);

    // Signatures
    $pdf->Ln(4);
$managerSign = './img/logo/maganer.jpeg';
$principalSign = './img/logo/signature.jpg';
$classTeacherSign = './img/logo/teacher.jpeg';

// Insert Signatures (width = 35, height = auto)
$pdf->Image($managerSign, 20, $pdf->GetY(), 35);
$pdf->Image($principalSign, 90, $pdf->GetY(), 35);
$pdf->Image($classTeacherSign, 160, $pdf->GetY(), 35);

$pdf->Ln(4); // Space after images

// Labels below signatures
$pdf->SetFont('DejaVu', '', 10);
$pdf->Cell(63, 7, '_________________', 0, 0, 'C');
$pdf->Cell(63, 7, '_________________', 0, 0, 'C');
$pdf->Cell(63, 7, '_________________', 0, 1, 'C');
$pdf->Cell(63, 7, 'Sign. of Manager', 0, 0, 'C');
$pdf->Cell(63, 7, 'Sign. of Principal', 0, 0, 'C');
$pdf->Cell(63, 7, 'Sign. of Class Teacher', 0, 1, 'C');


    // Grading Scale
    $pdf->Ln(-2);
    $pdf->SetFont('DejaVu', 'B', 11);
    $pdf->Cell(190, 6, 'Instructions', 0, 1, 'C');
    $pdf->Ln(0);
    $pdf->SetFont('DejaVu', '', 10);
    $pdf->Write(6, "Grading scale for scholastic areas: ");
    $pdf->SetFont('DejaVu', 'B', 10);
    $pdf->Write(6, "Grades are awarded on a 8-point grading scale as follows–");
    $pdf->Ln(7);

    $pdf->SetFont('DejaVu', 'B', 9);
    $pdf->SetFillColor(224, 224, 224);
    $pdf->Cell(38, 8, 'Marks Range in (%)', 1, 0, 'C', true);
    $pdf->Cell(21, 8, '91-100', 1, 0, 'C');
    $pdf->Cell(21, 8, '81-90', 1, 0, 'C');
    $pdf->Cell(21, 8, '71-80', 1, 0, 'C');
    $pdf->Cell(21, 8, '61-70', 1, 0, 'C');
    $pdf->Cell(21, 8, '51-60', 1, 0, 'C');
    $pdf->Cell(21, 8, '41-50', 1, 0, 'C');
    $pdf->Cell(27, 8, '32-40', 1, 1, 'C');

    $pdf->SetFont('DejaVu', '', 9);
    $pdf->SetFillColor(240, 240, 240);
    $pdf->Cell(38, 8, 'Grade', 1, 0, 'C', true);
    $pdf->Cell(21, 8, 'A+', 1, 0, 'C');
    $pdf->Cell(21, 8, 'A', 1, 0, 'C');
    $pdf->Cell(21, 8, 'B+', 1, 0, 'C');
    $pdf->Cell(21, 8, 'B', 1, 0, 'C');
    $pdf->Cell(21, 8, 'C+', 1, 0, 'C');
    $pdf->Cell(21, 8, 'C', 1, 0, 'C');
    $pdf->Cell(27, 8, 'D', 1, 1, 'C');
$pdf->Output();
// $pdf->Output('marksheet_' . $student['studentName'] . '.pdf', 'D');

?>