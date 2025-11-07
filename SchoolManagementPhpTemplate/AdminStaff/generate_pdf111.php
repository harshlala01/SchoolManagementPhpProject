<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../Includes/dbcon.php';
require('tfpdf.php');

$regId = $_GET['regId'] ?? '';
$term = $_GET['term'] ?? '';
if (!$regId || !$term) exit("Missing parameters.");

// // Get student and marks row
$query = "SELECT * FROM student_marks WHERE regId = ? AND term = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $regId, $term);
$stmt->execute();
$result = $stmt->get_result();
$marksRow = $result->fetch_assoc();
if (!$marksRow) exit("Student not found.");

// Extract class ID from data

// // Class-wise subject map
// // Class-wise subject map
// $subjectMap = [];

// if ($classId >= 1 && $classId <= 5) {
//     $subjectMap = [
//         'Hindi/Bengali' => ['theory' => 'hindi_bengali', 'practical' => null],
//         'English'       => ['theory' => 'english', 'practical' => null],
//         'Math'          => ['theory' => 'math', 'practical' => null],
//         'EVS'           => ['theory' => 'evs', 'practical' => null],
//         'Computer'      => ['theory' => 'computer', 'practical' => null],
//         'Drawing'       => ['theory' => 'drawing', 'practical' => null],
//         // Remove SST and Science for primary classes
//     ];
// } elseif ($classId >= 6 && $classId <= 10) {
//     $subjectMap = [
//         'Hindi/Bengali' => ['theory' => 'hindi_bengali_theory', 'practical' => 'hindi_bengali_practical'],
//         'English'       => ['theory' => 'english_theory', 'practical' => 'english_practical'],
//         'Math'          => ['theory' => 'math_theory', 'practical' => 'math_practical'],
//         'Science'       => ['theory' => 'science_theory', 'practical' => 'science_practical'],
//         'SST'           => ['theory' => 'sst_theory', 'practical' => 'sst_practical'],
//         'Computer'      => ['theory' => 'computer_theory', 'practical' => 'computer_practical'],
//         'Drawing'       => ['theory' => null, 'practical' => 'drawing_practical'],
//         // No EVS for upper classes
//     ];
// }
// $marksQuery = "SELECT * FROM student_marks WHERE regId = ? AND term = ?";
// $stmt = $conn->prepare($marksQuery);
// $stmt->bind_param("ss", $regId, $term);
// $stmt->execute();
// $marksResult = $stmt->get_result();
// $marksData = $marksResult->fetch_assoc();
// if (!$marksData) exit("Marks not found.");

// $className = strtoupper(trim($marksRow['className']));
// $primaryClasses = ['I', 'II', 'III', 'IV', 'V'];

// if (in_array($className, $primaryClasses)) {
    //     $subjectList = [
//         'English' => ['theory' => 'english_theory', 'practical' => 'english_practical'],
//         'Hindi/Bengali' => ['theory' => 'hindi_bengali_theory', 'practical' => 'hindi_bengali_practical'],
//         'Math' => ['theory' => 'math_theory', 'practical' => 'math_practical'],
//         'EVS' => ['theory' => 'evs_theory', 'practical' => 'evs_practical'],
//         'Computer' => ['theory' => 'computer_theory', 'practical' => 'computer_practical'],
//         'Drawing' => ['theory' => null, 'practical' => 'drawing_practical'],
//     ];
// } else {
//     $subjectList = [
//         'English' => ['theory' => 'english_theory', 'practical' => 'english_practical'],
//         'Hindi/Bengali' => ['theory' => 'hindi_bengali_theory', 'practical' => 'hindi_bengali_practical'],
//         'Math' => ['theory' => 'math_theory', 'practical' => 'math_practical'],
//         'Science' => ['theory' => 'science_theory', 'practical' => 'science_practical'],
//         'SST' => ['theory' => 'sst_theory', 'practical' => 'sst_practical'],
//         'Computer' => ['theory' => 'computer_theory', 'practical' => 'computer_practical'],
//         'Drawing' => ['theory' => null, 'practical' => 'drawing_practical'],
//     ];
// }
// --- Fetch both term 1 and term 2 marks ---
// $marksDataHalf = [];
// $marksDataAnnual = [];



// Now use this for PDF generation:
// $subjects = [];
// foreach ($subjectList as $subjectName => $fields) {
//     $theoryMarks = $fields['theory'] ? (int)$marksData[$fields['theory']] : 0;
//     $practicalMarks = $fields['practical'] ? (int)$marksData[$fields['practical']] : 0;
//     $total = $theoryMarks + $practicalMarks;
//     $grade = getGrade($total);
//     $subjects[] = [
//         'subjectName' => $subjectName,
//         'theory' => $fields['theory'] ? $theoryMarks : 'N/A',
//         'practical' => $fields['practical'] ? $practicalMarks : 'N/A',
//         'total' => $total,
//         'grade' => $grade
//     ];
// }
// $subjects = [];
// foreach ($subjectList as $subjectName => $fields) {
//     $theoryKey = $fields['theory'];
//     $practicalKey = $fields['practical'];

//     $theoryMarks = $theoryKey ? (int)($marksRow[$theoryKey] ?? 0) : 0;
//     $practicalMarks = $practicalKey ? (int)($marksRow[$practicalKey] ?? 0) : 0;

//     $total = $theoryMarks + $practicalMarks;
//     $grade = getGrade($total);

//     $subjects[] = [
//         'name' => $subjectName,
//         'theory' => $theoryMarks,
//         'practical' => $practicalMarks,
//         'total' => $total,
//         'grade' => $grade
//     ];
// }
$classId = $marksRow['classId'];
$query = "SELECT * FROM student_marks WHERE regId = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $regId);
$stmt->execute();
$result = $stmt->get_result();

$termData = [
    'Term 1' => null,
    'Term 2' => null,
];

while ($row = $result->fetch_assoc()) {
    if ($row['term'] == 'Term 1') $termData['Term 1'] = $row;
    if ($row['term'] == 'Term 2') $termData['Term 2'] = $row;
}

if (!$termData['Term 1'] && !$termData['Term 2']) {
    exit("No data found.");
}

// --- Use any one term row to extract class name ---
$marksRow = $termData['Term 1'] ?? $termData['Term 2'];
$className = strtoupper(trim($marksRow['className']));

// --- Subject list based on class ---
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

$subjects = [];
foreach ($subjectList as $subjectName => $fields) {
    // Half Yearly
    $halfTheory = $fields['theory'] && isset($halfYearlyMarks[$fields['theory']]) ? (int)$halfYearlyMarks[$fields['theory']] : 0;
    $halfPractical = $fields['practical'] && isset($halfYearlyMarks[$fields['practical']]) ? (int)$halfYearlyMarks[$fields['practical']] : 0;

    // Annual
    $annualTheory = $fields['theory'] && isset($annualMarks[$fields['theory']]) ? (int)$annualMarks[$fields['theory']] : 0;
    $annualPractical = $fields['practical'] && isset($annualMarks[$fields['practical']]) ? (int)$annualMarks[$fields['practical']] : 0;

    // Final marks: if annual available, use it; else use half yearly
    if ($annualTheory > 0 || $annualPractical > 0) {
        $theoryMarks = $annualTheory;
        $practicalMarks = $annualPractical;
    } else {
        $theoryMarks = $halfTheory;
        $practicalMarks = $halfPractical;
    }

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



// Helper: Grade calculation
function getGrade($marks) {
    if ($marks >= 90) return 'A+';
    elseif ($marks >= 80) return 'A';
    elseif ($marks >= 70) return 'B+';
    elseif ($marks >= 60) return 'B';
    elseif ($marks >= 50) return 'C';
    elseif ($marks >= 33) return 'D';
    else return 'F';
}

$pdf = new tFPDF('P','mm','A4');
$pdf->AddPage();
$pdf->AddFont('DejaVu','','DejaVuSans.ttf',true);
$pdf->AddFont('DejaVu', 'B', 'DejaVuSans-Bold.ttf', true);
$pdf->SetFont('DejaVu','',12);
$pdf->SetFillColor(255, 255, 255);

// Header
$pdf->Image('./logo/schoolLogo.png',10,6,25);
$pdf->SetFont('DejaVu','',16);
$pdf->Cell(190,10,'ABC PUBLIC SCHOOL',0,1,'C');
$pdf->SetFont('DejaVu','',12);
$pdf->Cell(190,8,'Session: 2024-2025',0,1,'C');
$pdf->Cell(190,8,'Class: '.$marksRow['session'].' '.$marksRow['className'],0,1,'C');
$examTitle = ($term == 'Term 1') ? 'Half Yearly Examination' : 'Annual Examination';
$pdf->SetFont('DejaVu','',12);
$pdf->Cell(190,8,$examTitle,0,1,'C');

$pdf->Ln(2);

// Student info
$pdf->SetFont('DejaVu','',11);
$pdf->Cell(60,8,'Name: '.$marksRow['studentName'],0,0);
$pdf->Cell(70,8,'Roll No: '.$marksRow['rollNo'],0,0);
$pdf->Cell(60,8,'DOB: '.$marksRow['dob'],0,1);

$pdf->Cell(60,8,"Father's Name: ".$marksRow['fatherName'],0,0);
$pdf->Cell(70,8,"Mother's Name: ".$marksRow['motherName'],0,1);
$pdf->Ln(5);

// Table Header
$pdf->SetFont('DejaVu','',10);
$pdf->SetFillColor(200,220,255);
// $pdf->Cell(60,10,'Subject',1,0,'C',true);
// $pdf->Cell(25,10,'Theory',1,0,'C',true);
// $pdf->Cell(25,10,'Total',1,0,'C',true);
// $pdf->Cell(25,10,'Grand Total',1,0,'C',true);
// $pdf->Cell(25,10,'Grade',1,1,'C',true);
$pdf->Cell(60,10,'Subject',1,0,'C',true);
$pdf->Cell(25,10,'Half Yearly',1,0,'C',true);
$pdf->Cell(25,10,'Annual',1,0,'C',true);
$pdf->Cell(25,10,'Total',1,0,'C',true);
$pdf->Cell(25,10,'Grade',1,1,'C',true);


// Marks Rows
$grandTotal = 0;
$maxMarks = 0;

// foreach ($subjectMap as $subjectName => $keys) {
//     $theoryMarks = isset($marksRow[$keys['theory']]) ? (int)$marksRow[$keys['theory']] : 0;
//     $practicalMarks = isset($keys['practical']) && isset($marksRow[$keys['practical']]) ? (int)$marksRow[$keys['practical']] : 0;
//     $total = $theoryMarks + $practicalMarks;
//     $grade = getGrade($total);

//     $pdf->Cell(60,10,$subjectName,1);
//     $pdf->Cell(25,10,$theoryMarks,1,0,'C');
//     $pdf->Cell(25,10,$practicalMarks,1,0,'C');
//     $pdf->Cell(25,10,$total,1,0,'C');
//     $pdf->Cell(25,10,$grade,1,1,'C');

//     $grandTotal += $total;
//     $maxMarks += 100; // You may customize per subject
// }
// foreach ($subjects as $subject) {
//     $pdf->Cell(60,10,$subject['subjectName'],1);
//     $pdf->Cell(25,10,$subject['theory'],1,0,'C');
//     $pdf->Cell(25,10,$subject['practical'],1,0,'C');
//     $pdf->Cell(25,10,$subject['total'],1,0,'C');
//     $pdf->Cell(25,10,$subject['grade'],1,1,'C');

//     $grandTotal += $subject['total'];
//     $maxMarks += 100; // Ya 80, 50 jitna full marks ho
// }

// foreach($subjects as $sub){
//     $totalMarks = $sub['theory'] + ($sub['practical'] ?? 0);
//     $grade = getGrade($totalMarks);
//     $pdf->Cell(60,10,$sub['subjectName'],1);
//     $pdf->Cell(25,10,$totalMarks,1,0,'C');
//     $pdf->Cell(25,10,$grade,1,1,'C');
//     $grandTotal += $totalMarks;
//     $maxMarks += 100;
// }
// foreach($subjects as $sub){
//     $theory = is_numeric($sub['theory']) ? (int)$sub['theory'] : 0;
//     $practical = is_numeric($sub['practical']) ? (int)$sub['practical'] : 0;
//     $totalMarks = $theory + $practical;
//     $grade = getGrade($totalMarks);

//     $pdf->Cell(60,10,$sub['subjectName'],1);
//     $pdf->Cell(25,10,$totalMarks,1,0,'C');
//     $pdf->Cell(25,10,$grade,1,1,'C');

//     $grandTotal += $totalMarks;
//     $maxMarks += 100;
// }
// foreach($subjects as $sub){
//     $theory = is_numeric($sub['theory']) ? (int)$sub['theory'] : 0;
//     $practical = is_numeric($sub['practical']) ? (int)$sub['practical'] : 0;
//     $hasPractical = $sub['practical'] !== 'N/A';

//     // Logic: If practical hai, then theory column me theory + practical dikhao
//     $displayTheory = $hasPractical ? $theory + $practical : $theory;
//     $displayPractical = $hasPractical ? $practical : '-';

//     $totalMarks = $displayTheory; // kyunki practical already include ho gaya
//     $grade = getGrade($totalMarks);

//     $pdf->Cell(60,10,$sub['subjectName'],1);
//     $pdf->Cell(25,10,$displayTheory,1,0,'C');
//     $pdf->Cell(25,10,$displayPractical,1,0,'C');
//     $pdf->Cell(25,10,$totalMarks,1,0,'C');
//     $pdf->Cell(25,10,$grade,1,1,'C');

//     $grandTotal += $totalMarks;
//     $maxMarks += 100; // aap customize bhi kar sakte ho
// }
// foreach($subjects as $sub){
//     $theory = is_numeric($sub['theory']) ? (int)$sub['theory'] : 0;
//     $practical = is_numeric($sub['practical']) ? (int)$sub['practical'] : 0;
//     $hasPractical = $sub['practical'] !== 'N/A';

//     $displayTheory = $hasPractical ? $theory + $practical : $theory;
//     $totalMarks = $displayTheory;
//     $grade = getGrade($totalMarks);

//       $pdf->Cell(60,10,$sub['subjectName'],1);
//     $pdf->Cell(25,10,$displayTheory,1,0,'C');
//     // $pdf->Cell(25,10,$displayPractical,1,0,'C');
//     $pdf->Cell(25,10,$totalMarks,1,0,'C');
//     $pdf->Cell(25,10,$totalMarks,1,0,'C');
//     $pdf->Cell(25,10,$grade,1,1,'C');

//     $grandTotal += $totalMarks;
//     $maxMarks += 100;
// }

$grandTotal = 0;
$maxMarks = 0;

foreach ($subjectList as $subjectName => $fields) {
    $theoryField = $fields['theory'];
    if (!$theoryField) continue;

    $halfYearly = isset($termData['Term 1'][$theoryField]) ? (int)$termData['Term 1'][$theoryField] : 0;
    $annual     = isset($termData['Term 2'][$theoryField]) ? (int)$termData['Term 2'][$theoryField] : 0;

    $totalMarks = $halfYearly + $annual;
    $grade = getGrade($totalMarks);

    $pdf->Cell(60,10, $subjectName,1);
    $pdf->Cell(25,10, $halfYearly,1,0,'C');
    $pdf->Cell(25,10, $annual,1,0,'C');
    $pdf->Cell(25,10, $totalMarks,1,0,'C');
    $pdf->Cell(25,10, $grade,1,1,'C');

    $grandTotal += $totalMarks;
    $maxMarks += 200; // because both terms are counted (100 each)
}

// Summary
$average = $maxMarks > 0 ? ($grandTotal / $maxMarks) * 100 : 0;

$pdf->Ln(5);
$pdf->SetFont('DejaVu','',10);
$pdf->Cell(190,8,'Remarks: Very Good Performance.',1,1);

$pdf->Ln(4);
$pdf->SetFont('DejaVu','',10);
$pdf->Cell(95,8,'Co-Scholastic Areas',1,0,'C',true);
$pdf->Cell(95,8,'Grade',1,1,'C',true);
$pdf->Cell(95,8,'Discipline',1,0);
$pdf->Cell(95,8,'A',1,1,'C');
$pdf->Cell(95,8,'Attitude',1,0);
$pdf->Cell(95,8,'A',1,1,'C');
$pdf->Cell(95,8,'Neatness',1,0);
$pdf->Cell(95,8,'B',1,1,'C');

$pdf->Ln(5);
$pdf->SetFont('DejaVu','',10);
$pdf->Cell(60,8,'Result: '.($average >= 33 ? 'Pass' : 'Fail'),1,0);
$pdf->Cell(65,8,'Percentage: '.number_format($average,2).'%',1,0);
$pdf->Cell(65,8,'Rank: 1st',1,1);

$pdf->Ln(3);
$pdf->SetFont('DejaVu','',9);
$pdf->MultiCell(190,7,'*Admission open for Class XI (Science, Commerce, Humanities) for session 2025-26. Contact School Reception.*',0,'C');
$pdf->Ln(6);

// Signatures
$pdf->Image('./img/logo/maganer.jpeg', 30, $pdf->GetY(), 30);
$pdf->Image('./img/logo/signature.jpg', 90, $pdf->GetY(), 30);
$pdf->Image('./img/logo/teacher.jpeg', 150, $pdf->GetY(), 30);
$pdf->Ln(25);
$pdf->SetX(30);
$pdf->Cell(50, 7, 'Class Teacher', 0, 0, 'C');
$pdf->Cell(60, 7, 'Exam I/C', 0, 0, 'C');
$pdf->Cell(50, 7, 'Principal', 0, 1, 'C');

// Grading Scale
$pdf->Ln(8);
$pdf->SetFont('DejaVu','B',11);
$pdf->Cell(190,8,'Grading Scale',1,1,'C');
$pdf->SetFont('DejaVu','',10);
$pdf->Cell(95,8,'Marks Range',1,0,'C',true);
$pdf->Cell(95,8,'Grade',1,1,'C',true);
$pdf->Cell(95,8,'90 - 100',1,0,'C');
$pdf->Cell(95,8,'A+',1,1,'C');
$pdf->Cell(95,8,'80 - 89',1,0,'C');
$pdf->Cell(95,8,'A',1,1,'C');
$pdf->Cell(95,8,'70 - 79',1,0,'C');
$pdf->Cell(95,8,'B+',1,1,'C');
$pdf->Cell(95,8,'60 - 69',1,0,'C');
$pdf->Cell(95,8,'B',1,1,'C');
$pdf->Cell(95,8,'50 - 59',1,0,'C');
$pdf->Cell(95,8,'C',1,1,'C');
$pdf->Cell(95,8,'33 - 49',1,0,'C');
$pdf->Cell(95,8,'D',1,1,'C');
$pdf->Cell(95,8,'Below 33',1,0,'C');
$pdf->Cell(95,8,'F',1,1,'C');

$pdf->Output();

?>





<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require('../Includes/dbcon.php');
require('tfpdf.php');

// Input
$regId = $_GET['regId'] ?? '';
if (!$regId) exit("Missing regId");

// Step 1: Fetch student data (any one term is enough)
$sql = "SELECT * FROM student_marks WHERE regId = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $regId);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

if (!$student) exit("Student not found");

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

// Step 3: Subject list as per class
$classId = (int)$student['classId'];

if ($classId >= 1 && $classId <= 5) {
    $subjects = [
        "English" => "english_theory",
        "Hindi/Bengali" => "hindi_bengali_theory",
        "Math" => "math_theory",
        "EVS" => "evs_theory",
        "Computer" => "computer_theory",
        "Drawing" => "drawing_practical"
    ];
} else {
    $subjects = [
        "English" => "english_theory",
        "Hindi/Bengali" => "hindi_bengali_theory",
        "Math" => "math_theory",
        "Science" => "science_theory",
        "SST" => "sst_theory",
        "Computer" => "computer_theory",
        "Drawing" => "drawing_practical"
    ];
}

// Step 4: Initialize PDF
$pdf = new tFPDF();
$pdf->AddPage();
$pdf->AddFont('DejaVu','','DejaVuSans.ttf',true);
$pdf->AddFont('DejaVu', 'B', 'DejaVuSans-Bold.ttf', true);
$pdf->SetFont('DejaVu','',12);

// Header
$pdf->Cell(190,10,"Student Marksheet",0,1,'C');
$pdf->SetFont('DejaVu','',11);
$pdf->Cell(190,10,"Name: ".$student['studentName']."   |   Class: ".$student['className']." ".$student['classArmName'],0,1);
$pdf->Cell(190,10,"Session: ".$student['session']."   |   Reg ID: ".$student['regId'],0,1);

// Table Header
$pdf->Ln(2);
$pdf->SetFont('DejaVu','',11);
$pdf->Cell(60,10,'Subject',1);
$pdf->Cell(25,10,'Half Yearly',1);
$pdf->Cell(25,10,'Annual',1);
$pdf->Cell(25,10,'Total',1);
$pdf->Cell(25,10,'Grade',1,1);

$grandTotal = 0;
$maxMarks = 0;

// Step 5: Subject rows
foreach ($subjects as $label => $field) {
    $half = isset($termMarks['Term 1'][$field]) ? (int)$termMarks['Term 1'][$field] : 0;
    $annual = isset($termMarks['Term 2'][$field]) ? (int)$termMarks['Term 2'][$field] : 0;
    $total = $annual > 0 ? $annual : $half;
    $grade = getGrade($total);

    $pdf->Cell(60,10,$label,1);
    $pdf->Cell(25,10,$half,1,0,'C');
    $pdf->Cell(25,10,$annual,1,0,'C');
    $pdf->Cell(25,10,$total,1,0,'C');
    $pdf->Cell(25,10,$grade,1,1,'C');

    $grandTotal += $total;
    $maxMarks += 100;
}

// Step 6: Grand total
$pdf->SetFont('DejaVu','B',11);
$pdf->Cell(110,10,"Grand Total",1);
$pdf->Cell(25,10,$grandTotal,1,0,'C');
$pdf->Cell(25,10,$maxMarks,1,0,'C');
$pdf->Cell(25,10,getGrade($grandTotal),1,1,'C');

// Output
$pdf->Output();

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

?>
