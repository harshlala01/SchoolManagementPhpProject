    <?php
    ob_start();
    session_start();
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    require('tfpdf.php');
    include '../Includes/dbcon.php';

    // Fetch regId
    // $regId = $_SESSION['regId'] ?? '';
    // if (!$regId) {
    //     $q = mysqli_query($conn, "SELECT regId FROM student_marks ORDER BY id DESC LIMIT 1");
    //     $regId = mysqli_fetch_assoc($q)['regId'] ?? '';
    // }
$regId = $_GET['regId'] ?? ($_SESSION['regId'] ?? '');

if (!$regId) {
    die("No regId provided.");
}

    // Fetch student data
    $qry = mysqli_query($conn, "SELECT * FROM student_marks WHERE regId = '$regId' ORDER BY id DESC LIMIT 1");
    if (!$qry || mysqli_num_rows($qry) == 0) {
        die("No data found for regId = $regId");
    }
    $data = mysqli_fetch_assoc($qry);

    // Grade Function
    function getGrade($mark) {
        if ($mark >= 91) return 'A+';
        if ($mark >= 81) return 'A';
        if ($mark >= 71) return 'B+';
        if ($mark >= 61) return 'B';
        if ($mark >= 51) return 'C+';
        if ($mark >= 41) return 'C';
        if ($mark >= 33) return 'D';
        return 'E';
    }

    class PDF extends tFPDF {
        // function Header() {
        //     $this->Image('./img/logo/schoolLogo.png', 88, 12, 35);
        //     $this->Ln(35);
        // }
        function Header() {
    $this->Image('logo/schoolLogo.png', 10, 10, 30); // Left-aligned logo
    $this->SetY(12); // Reset Y so text doesn't overlap
    $this->Ln(20);   // Adjust spacing after the logo
}

        // function Watermark($img) {
        //     $this->SetAlpha(0.07);
        //     $this->Image($img, 50, 80, 110);
        //     $this->SetAlpha(1);
        // }

        // function SetAlpha($alpha) {
        //     $this->_out(sprintf("q %.3F 0 0 %.3F 0 0 cm", $alpha, $alpha));
        // }
    //         function Watermark($img) {
    //     $this->SetAlpha(0.07); // lower opacity for watermark
    //     $pageWidth = 210;  // A4 width in mm
    //     $pageHeight = 297; // A4 height in mm
    //     $imgWidth = 100;
    //     $imgHeight = 100;
    //     $x = ($pageWidth - $imgWidth) / 2;
    //     $y = ($pageHeight - $imgHeight) / 2;
    //     $this->Image($img, $x, $y, $imgWidth);
    //     $this->SetAlpha(1);
    // }

    function SetAlpha($alpha) {
        $this->_out(sprintf("q %.3F 0 0 %.3F 0 0 cm", $alpha, $alpha));
    }
    }


    $pdf = new PDF('P', 'mm', 'A4');
    $pdf->AddFont('DejaVu', '', 'DejaVuSans.ttf', true);
    $pdf->AddFont('DejaVu', 'B', 'DejaVuSans-Bold.ttf', true);
    $pdf->AddPage();
    // $pdf->Watermark('img/logo/schoolLogo.png');
    // Header
    // $pdf->SetFont('DejaVu', 'B', 14);
    // $pdf->Cell(0, 8, 'HERITAGE DAY SCHOOL', 0, 1, 'C');
    // $pdf->SetFont('DejaVu', '', 10);
    // $pdf->Cell(0, 6, 'Nagendra, Barrack Pore, Kolkata - 700124', 0, 1, 'C');
    // $pdf->Cell(0, 6, 'Academic Report', 0, 1, 'C');
    // $pdf->Cell(0, 6, 'Session: ' . $data['session'] . '    Class: ' . $data['className'], 0, 1, 'C');
    // $pdf->Ln(4);
    // Logo on top-left
$pdf->Image('logo/schoolLogo.png', 10, 7, 30); // x=10, y=10, width=30


// $photo_path = '../Student/' . ($data['photo'] ?? '');
$relative_photo = '../Student/' . ($data['photo'] ?? '');
$photo_path = realpath($relative_photo);

$photoX = 170;
$photoY = 10;
$photoW = 30;
$photoH = 36;

if (!empty($data['photo']) && file_exists($photo_path)) {
    // Simulate border radius with a light gray rectangle behind image
    // $pdf->SetDrawColor(180, 180, 180); // Light gray border
    // $pdf->SetLineWidth(0.4);
    // $pdf->Rect($photoX - 1, $photoY - 1, $photoW + 2, $photoH + 2, 'D'); // Slight border padding

    // Student photo
    $pdf->Image($photo_path, $photoX, $photoY, $photoW, $photoH);
} else {
    // Placeholder with border
    $pdf->SetDrawColor(180, 180, 180);
    $pdf->Rect($photoX - 1, $photoY - 1, $photoW + 2, $photoH + 2, 'D');

    $pdf->SetFont('Arial', '', 8);
    $pdf->SetXY($photoX, $photoY + ($photoH / 2) - 4);
    $pdf->Cell($photoW, 8, 'No Image', 0, 0, 'C');
}

// // Load watermark image (transparent PNG with low-opacity logo only)
// $watermark = 'logo/schoolLogo.png';

// // Watermark size (adjust as needed)
// $wWidth = 140;  // mm
// $wHeight = 140;

// // Position watermark to center of page
// $pageWidth = $pdf->GetPageWidth();
// $pageHeight = $pdf->GetPageHeight();
// $centerX = ($pageWidth - $wWidth) / 2;
// $centerY = ($pageHeight - $wHeight) / 2;

// // Place watermark image behind everything
// if (file_exists($watermark)) {
//     $pdf->Image($watermark, $centerX, $centerY, $wWidth, $wHeight, 'PNG');
// }

// Adjust Y so the text starts below the logo height
$pdf->SetY(6); // Or use $pdf->SetY(10 + logo height + padding) if needed
$pdf->Ln(4);    // Line break for spacing

// Add school name and details at the top center
$pdf->SetFont('DejaVu', 'B', 14);
$pdf->Cell(0, 8, 'HERITAGE DAY SCHOOL', 0, 1, 'C');

$pdf->SetFont('DejaVu', '', 10);
$pdf->Cell(0, 6, 'Nagaurkra, Barasat Para, Haringhata, Nadia, WB', 0, 1, 'C');
$pdf->Cell(0, 6, '(Affiliated to WBBSE. Code: 123456)', 0, 1, 'C');
$pdf->Cell(0, 6, 'Office: 7364916702 / 9064109172', 0, 1, 'C');
$pdf->SetFont('DejaVu', 'B', 12); // Bold font, size 12
$pdf->Cell(0, 6, 'Academic Report', 0, 1, 'C');
$pdf->Cell(0, 6, 'Session: ' . $data['session'] . '    Class: ' . $data['className'], 0, 1, 'C');


$pdf->Ln(2); // Space before student info section


    // Student Info
    $pdf->SetFont('DejaVu', '', 10);
    $pdf->Cell(95, 7, 'Name of Student: ' . $data['studentName'], 1);
    $pdf->Cell(96, 7, 'Roll No: ' . $data['rollNo'], 1);
    $pdf->Ln();
    $pdf->Cell(95, 7, 'Admission No: ' . $data['regId'], 1);
    $pdf->Cell(96, 7, 'DOB: ' . $data['dob'], 1);
    $pdf->Ln();
    $pdf->Cell(95, 7, "Mother's Name: " . $data['motherName'], 1);
    $pdf->Cell(96, 7, "Father's Name: " . $data['fatherName'], 1);
    $pdf->Ln();
    $pdf->Cell(191, 7, "Address: " . $data['address'], 1);
    $pdf->Ln(5);

    // Updated Scholastic Table Layout
    $pdf->Ln(4);
    $pdf->SetFont('DejaVu', 'B', 10);
    $pdf->SetFillColor(220, 230, 241); // #dce6f1
    $pdf->Cell(64, 12, 'Scholastic Areas', 1, 0, 'C', true);
    $pdf->Cell(64, 6, 'Term I', 1, 0, 'C', true);
    $pdf->Cell(63, 6, 'Overall', 1, 1, 'C', true);

    $pdf->Cell(64, 6, '', 0, 0);
    $pdf->Cell(32, 6, 'Half Yearly', 1, 0, 'C', true);
    $pdf->Cell(32, 6, 'Total', 1, 0, 'C', true);
    $pdf->Cell(32, 6, 'Grand Total', 1, 0, 'C', true);
    $pdf->Cell(31, 6, 'Grade', 1, 1, 'C', true);

    $pdf->SetFont('DejaVu', '', 10);
    $subject_names = [
        'hindi_bengali' => 'HINDI/BENGALI',
        'english' => 'ENGLISH',
        'math' => 'MATHS',
        'computer' => 'COMPUTER',
        // 'sst' => 'SST',
        // 'sst' => 'SST',
        'evs' => 'EVS',
        'practical' => 'PRACTICAL'
    ];

    $total = 0;
    foreach ($subject_names as $key => $label) {
        $mark = $data[$key] ?? 0;
        $total += $mark;
        $pdf->Cell(64, 7, $label, 1);
        $pdf->Cell(32, 7, $mark, 1, 0, 'C');
        $pdf->Cell(32, 7, $mark, 1, 0, 'C');
        $pdf->Cell(32, 7, $mark, 1, 0, 'C');
        $pdf->Cell(31, 7, getGrade($mark), 1, 1, 'C');
    }

    $percentage = round(($total / (count($subject_names) * 60)) * 100, 2);
    $attendance = $data['attendance'] ?? '0/160';
    $overall_grade = getGrade($percentage);

    $pdf->SetFont('DejaVu', 'B', 10);
    $pdf->SetFillColor(255, 204, 153);
    $pdf->Cell(28, 7, 'Attendance', 1, 0, 'C', true);
    $pdf->SetFont('DejaVu', '', 10);
    $pdf->Cell(22, 7, $attendance, 1, 0, 'C');

    $pdf->SetFont('DejaVu', 'B', 10);
    $pdf->Cell(29, 7, 'Total Marks', 1, 0, 'C', true);
    $pdf->SetFont('DejaVu', '', 10);
    $pdf->Cell(25, 7, "$total / " . (count($subject_names) * 60), 1, 0, 'C');

    $pdf->SetFont('DejaVu', 'B', 10);
    $pdf->Cell(28, 7, 'Percentage', 1, 0, 'C', true);
    $pdf->SetFont('DejaVu', '', 10);
    $pdf->Cell(25, 7, $percentage . '%', 1, 0, 'C');

    $pdf->SetFont('DejaVu', 'B', 10);
    $pdf->Cell(20, 7, 'Grade', 1, 0, 'C', true);
    $pdf->SetFont('DejaVu', '', 10);
    $pdf->Cell(14, 7, $overall_grade, 1, 1, 'C');

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

// Result → Pass
$pdf->SetFont('DejaVu', '', 10);
$pdf->SetX(126); // reset X
$pdf->Cell(37, 7, 'Result', 1, 0);
$pdf->Cell(38, 7, 'Pass', 1, 1);

// Percentage → 86.5%
$pdf->SetX(126);
$pdf->Cell(37, 7, 'Percentage', 1, 0);
$pdf->Cell(38, 7, $percentage, 1, 1);

// Rank → 5
$pdf->SetX(126);
$pdf->Cell(37, 7, 'Rank', 1, 0);
$pdf->Cell(38, 7, '5', 1, 1);
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
    $pdf->Ln(2);
    $pdf->SetTextColor(21, 87, 36); // #155724
    $pdf->SetFont('DejaVu', '', 10);
    $pdf->Write(6, 'Remarks: ');
    $pdf->SetFont('DejaVu', 'B', 10);
    $pdf->Write(6, 'He has been consistently progressing.');
    $pdf->SetTextColor(0);

    // Signatures
    $pdf->Ln(5);
$managerSign = './img/logo/maganer.jpeg';
$principalSign = './img/logo/signature.jpg';
$classTeacherSign = './img/logo/teacher.jpeg';

// Insert Signatures (width = 35, height = auto)
$pdf->Image($managerSign, 20, $pdf->GetY(), 35);
$pdf->Image($principalSign, 90, $pdf->GetY(), 35);
$pdf->Image($classTeacherSign, 160, $pdf->GetY(), 35);

$pdf->Ln(10); // Space after images

// Labels below signatures
$pdf->SetFont('DejaVu', '', 10);
$pdf->Cell(63, 7, '_________________', 0, 0, 'C');
$pdf->Cell(63, 7, '_________________', 0, 0, 'C');
$pdf->Cell(63, 7, '_________________', 0, 1, 'C');
$pdf->Cell(63, 7, 'Sign. of Manager', 0, 0, 'C');
$pdf->Cell(63, 7, 'Sign. of Principal', 0, 0, 'C');
$pdf->Cell(63, 7, 'Sign. of Class Teacher', 0, 1, 'C');


    // Grading Scale
    $pdf->Ln(1);
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

    ob_end_clean();
    $pdf->Output('', 'Final_Report_Card.pdf');
