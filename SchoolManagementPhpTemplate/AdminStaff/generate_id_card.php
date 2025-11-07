<!-- 

<?php
// ob_clean();
// ob_start();
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
// require('tfpdf.php');
// include '../Includes/dbcon.php';

// if (!isset($_GET['class_id'])) {
//     die("Class ID missing.");
// }

// $class_id = $_GET['class_id'];

// // Get student data
// $stmt = $conn->prepare("SELECT s.*, c.className 
//                         FROM tblstudents s 
//                         INNER JOIN tblclass c ON s.classId = c.Id 
//                         WHERE s.classId = ?");
// $stmt->bind_param("i", $class_id);
// $stmt->execute();
// $result = $stmt->get_result();
// if ($result->num_rows == 0) {
//     die("No students found.");
// }

// $pdf = new tFPDF('P', 'mm', 'A4');
// $pdf->AddPage();

// $x = 10; $y = 10;
// $card_width = 90;
// $card_height = 55;
// $gap_x = 10;
// $gap_y = 10;
// $count = 0;

// while ($row = $result->fetch_assoc()) {
//     if ($count > 0 && $count % 6 == 0) {
//         $pdf->AddPage();
//         $x = 10; $y = 10;
//     }

//     // Card background
//     $pdf->Rect($x, $y, $card_width, $card_height);

//     // ===== TOP BLUE HEADER =====
//     // $pdf->SetFillColor(0, 102, 204); // Blue
//     // $pdf->Rect($x, $y, $card_width, 10, 'F');
//     // $pdf->SetTextColor(255, 255, 255);
//     // $pdf->SetFont('Arial', 'B', 8);
//     // $pdf->SetXY($x + 15, $y + 1);
//     // $pdf->Cell($card_width - 30, 4, "HERITAGE DAY SCHOOL", 0, 2, 'C');
//     // $pdf->SetFont('Arial', '', 6);
//     //   $pdf->SetX($x + 2);
//     // $pdf->Cell($card_width - 4, 3, 'Office: 7364916702 / 9064109172', 0, 1, 'C');
//     // $pdf->Cell($card_width - 30, 3, "(Affiliated to WBBSE. Code: 123456)", 0, 2, 'C');
//     // $pdf->SetFont('Arial', 'B', 7);
//     // $pdf->Cell($card_width - 30, 3, "IDENTITY CARD", 0, 2, 'C');
//     // $pdf->Cell($card_width - 4, 3, 'Nagarukhra, Barasat Para, Haringhata, Nadia, WB', 0, 1, 'C');
  
//     // ===== TOP BLUE HEADER =====
// $pdf->SetFillColor(0, 102, 204); // Blue
// $pdf->Rect($x, $y, $card_width, 18, 'F'); // Increased height to 18

// $pdf->SetTextColor(255, 255, 255);
// $pdf->SetFont('Arial', 'B', 8);
// $pdf->SetXY($x + 2, $y + 1);
// $pdf->Cell($card_width - 4, 4, "HERITAGE DAY SCHOOL", 0, 1, 'C');

// $pdf->SetFont('Arial', '', 6);
// $pdf->SetX($x + 2);
// $pdf->SetFont('Arial', '', 6);
// $pdf->SetX($x + 2);
// $pdf->Cell($card_width - 4, 3, "Nagarukhra, Barasat Para, Haringhata, Nadia, WB", 0, 1, 'C');
// $pdf->SetX($x + 2);
// $pdf->Cell($card_width - 4, 3, "Office: 7364916702 / 9064109172", 0, 1, 'C');
// $pdf->SetX($x + 2);
// $pdf->Cell($card_width - 4, 3, "(Affiliated to WBBSE. Code: 123456)", 0, 1, 'C');
// $pdf->SetFont('Arial', 'B', 7);
// $pdf->Cell($card_width - 4, 3, "IDENTITY CARD", 0, 1, 'C');


//     // Logo (adjust path)
//     $pdf->Image('logo/schoolLogo.png', $x + 2, $y + 2, 15, 15);

//     // ===== PHOTO =====
//     // $photo_path = '../Student/' . $row['passport'];
//     // if (!empty($row['passport']) && file_exists($photo_path)) {
//     //     $pdf->Image($photo_path, $x + 3, $y + 20, 22, 26);
//     // } else {
//     //     $pdf->Rect($x + 3, $y + 20, 22, 26);
//     //     $pdf->SetFont('Arial', '', 5);
//     //     $pdf->SetXY($x + 3, $y + 25);
//     //     $pdf->Cell(22, 4, 'No Image', 0, 0, 'C');
//     // }
//     $photo_path = '../Student/' . $row['passport']; // Correct folder

// if (!empty($row['passport']) && file_exists($photo_path)) {
//     $pdf->Image($photo_path, $x + 3, $y + 20, 22, 26);
// } else {
//     $pdf->Rect($x + 3, $y + 20, 22, 26);
//     $pdf->SetFont('Arial', '', 5);
//     $pdf->SetXY($x + 3, $y + 32);
//     $pdf->Cell(22, 4, 'No Image', 0, 0, 'C');
// }


//     // ===== STUDENT DETAILS =====
//     $pdf->SetTextColor(0, 0, 0);
//     // $pdf->SetFont('Arial', '', 6.5);
//     $pdf->SetFont('Arial', '', 6.5);
//     $leftX = $x + 28;
//     $infoY = $y + 20;

   

// // Line 1: Student ID | Session
// $pdf->SetXY($leftX, $infoY);
// $pdf->Cell(25, 4, 'Student ID: ' . $row['regId'], 0, 0);
// $pdf->Cell(30, 4, 'Session: 2025-2026', 0, 1);

// // Line 2: Name | DOB
// $pdf->SetX($leftX);
// $pdf->Cell(25, 4, 'Name: ' . $row['studentName'] . ' ' . $row['lastName'], 0, 0);
// $pdf->Cell(30, 4, 'DOB: ' . date('d-m-Y', strtotime($row['dob'])), 0, 1);

// // Line 3: Address | (empty)
// $pdf->SetX($leftX);
// $pdf->Cell(60, 4, 'Address: ' . $row['address'], 0, 1);

// // Line 4: Class | Section
// $pdf->SetX($leftX);
// $pdf->Cell(25, 4, 'Class: ' . $row['className'], 0, 0);
// $pdf->Cell(30, 4,   $row['classArmName'], 0, 1);

// // Line 5: Mobile No.
// $pdf->SetX($leftX);
// // $pdf->Cell(25, 4, 'Roll No: ' . $row['section'], 0, 0);
// $pdf->Cell(30, 4, 'Mobile No: ' . $row['priPhoneNo'], 0, 1);
// // $pdf->Image($barcodeFile, $leftX, $pdf->GetY() + 1, 50, 8);


// //     $pdf->SetXY($leftX, $infoY);
// //     $pdf->Cell(50, 4, 'Student ID: ' . $row['regId'], 0, 2);
// //     $pdf->Cell(50, 4, 'Name: ' . $row['studentName'] . ' ' . $row['lastName'], 0, 2);
// //     $pdf->Cell(50, 4, 'Father: ' . $row['fatherName'], 0, 2);
// //     $pdf->Cell(50, 4, 'DOB: ' . $row['fatherName'], 0, 2);
// //     $pdf->Cell(50, 4, 'Address: ' . $row['fatherName'], 0, 2);
// //     $pdf->Cell(50, 4, 'Roll No.: ' . $row['fatherName'], 0, 2);
// //     $pdf->Cell(25, 4, 'Class: I', 0, 0);             // First cell: Label
// //      $pdf->Cell(25, 4, 'Session:2025-2026', 0, 1);    // Second cell: Value

// // //    $pdf->Cell(50, 4, 'Class: ' . $row['className'], 0, 2);
// //     $pdf->Cell(25, 4, 'Emergency: ' . $row['priPhoneNo'], 0, 1);

//     // ===== SIGNATURE =====
//     $pdf->SetFont('Arial', 'I', 6.5);
//     $pdf->SetXY($x + $card_width - 28, $y + $card_height - 10);
//     // $pdf->Cell(25, 4, 'Signature Here', 0, 2, 'C');
//     $pdf->SetFont('Arial', 'B', 6.5);
//     $pdf->Cell(25, 3, 'PRINCIPAL', 0, 0, 'C');

//     // ===== BOTTOM BAR =====
//     $pdf->SetFillColor(0, 102, 204);
//     $pdf->SetTextColor(255, 255, 255);
//     $pdf->Rect($x, $y + $card_height - 5, $card_width, 5, 'F');
//     $pdf->SetFont('Arial', '', 6);
//     $pdf->SetXY($x, $y + $card_height - 4.5);
//     // $pdf->Cell($card_width, 4, 'YOUR SLOGAN HERE     YOUR SLOGAN HERE', 0, 0, 'C');

//     // Move position
//     $x += $card_width + $gap_x;
//     if ($x + $card_width > 200) {
//         $x = 10;
//         $y += $card_height + $gap_y;
//     }

//     $count++;
// }

// ob_end_clean();
// header('Content-Type: application/pdf');
// header('Content-Disposition: inline; filename="heritage_id_cards_class_' . $class_id . '.pdf"');
// $pdf->Output();
// exit;
?> 
-->
<?php
ob_clean();
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require('tfpdf.php');
include '../Includes/dbcon.php';
// require_once 'php-barcode-generator-main/src/BarcodeBar.php';
// require_once 'php-barcode-generator-main/src/Barcode.php';
// require_once 'php-barcode-generator-main/src/Types/TypeInterface.php';
// require_once 'php-barcode-generator-main/src/Types/TypeCode128.php';
// require_once 'php-barcode-generator-main/src/BarcodeGenerator.php';
// require_once 'php-barcode-generator-main/src/BarcodeGeneratorPNG.php';

// use Picqer\Barcode\BarcodeGeneratorPNG;
require_once 'barcode/src/Exceptions/BarcodeException.php';

require_once 'barcode/src/BarcodeBar.php';
require_once 'barcode/src/Barcode.php';
require_once 'barcode/src/Types/TypeInterface.php';
require_once 'barcode/src/Types/TypeCode128.php';

require_once 'barcode/src/Renderers/RendererInterface.php';
require_once 'barcode/src/Renderers/PngRenderer.php';

require_once 'barcode/src/BarcodeGenerator.php';
require_once 'barcode/src/BarcodeGeneratorPNG.php';

use Picqer\Barcode\BarcodeGeneratorPNG;





if (!isset($_GET['class_id'])) {
    die("Class ID missing.");
}

$class_id = $_GET['class_id'];

// Get student data
$stmt = $conn->prepare("SELECT s.*, c.className 
                        FROM tblstudents s 
                        INNER JOIN tblclass c ON s.classId = c.Id 
                        WHERE s.classId = ?");
$stmt->bind_param("i", $class_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    die("No students found.");
}

$pdf = new tFPDF('P', 'mm', 'A4');
$pdf->AddPage();

$x = 10; $y = 10;
$card_width = 90;
$card_height = 55;
$gap_x = 10;
$gap_y = 10;
$count = 0;

while ($row = $result->fetch_assoc()) {
    if ($count > 0 && $count % 6 == 0) {
        $pdf->AddPage();
        $x = 10; $y = 10;
    }

    // Card background
    $pdf->Rect($x, $y, $card_width, $card_height);

    // ===== TOP BLUE HEADER =====
    $pdf->SetFillColor(0, 102, 204); // Blue
    $pdf->Rect($x, $y, $card_width, 18, 'F');

    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetXY($x + 2, $y + 1);
    $pdf->Cell($card_width - 4, 4, "HERITAGE DAY SCHOOL", 0, 1, 'C');

    $pdf->SetFont('Arial', '', 6);
    $pdf->SetX($x + 2);
    $pdf->Cell($card_width - 4, 3, "Nagarukhra, Barasat Para, Haringhata, Nadia, WB", 0, 1, 'C');
    $pdf->SetX($x + 2);
    $pdf->Cell($card_width - 4, 3, "Office: 7364916702 / 9064109172", 0, 1, 'C');
    $pdf->SetX($x + 2);
    $pdf->Cell($card_width - 4, 3, "(Affiliated to WBBSE. Code: 123456)", 0, 1, 'C');
    $pdf->SetFont('Arial', 'B', 7);
    $pdf->Cell($card_width - 4, 3, "IDENTITY CARD", 0, 1, 'C');

    // Logo
    $pdf->Image('logo/schoolLogo.png', $x + 2, $y + 2, 15, 15);

    // ===== PHOTO =====
    // $photo_path = '../Student/' . $row['passport'];
    // if (!empty($row['passport']) && file_exists($photo_path)) {
    //     $pdf->Image($photo_path, $x + 3, $y + 20, 22, 26);
    // } else {
    //     $pdf->Rect($x + 3, $y + 20, 22, 26);
    //     $pdf->SetFont('Arial', '', 5);
    //     $pdf->SetXY($x + 3, $y + 32);
    //     $pdf->Cell(22, 4, 'No Image', 0, 0, 'C');
    // }
$photo_path = '../Student/' . $row['studentPhoto'];
if (!empty($row['studentPhoto']) && file_exists($photo_path)) {
    $pdf->Image($photo_path, $x + 3, $y + 20, 22, 26);
} else {
    $pdf->Rect($x + 3, $y + 20, 22, 26);
    $pdf->SetFont('Arial', '', 5);
    $pdf->SetXY($x + 3, $y + 32);
    $pdf->Cell(22, 4, 'No Image', 0, 0, 'C');
}



    // ===== STUDENT DETAILS =====
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial', '', 6.5);
    $leftX = $x + 28;
    $infoY = $y + 20;

    // Line 1: Student ID | Session
    $pdf->SetXY($leftX, $infoY);
    $pdf->Cell(25, 4, 'Student ID: ' . $row['regId'], 0, 0);
    $pdf->Cell(30, 4, 'Session: 2025-2026', 0, 1);

    // Line 2: Name | DOB
    $pdf->SetX($leftX);
    $pdf->Cell(25, 4, 'Name: ' . $row['studentName'] , 0, 0);
    $pdf->Cell(30, 4, 'DOB: ' . date('d-m-Y', strtotime($row['dob'])), 0, 1);

    // Line 3: Address
    $pdf->SetX($leftX);
    $pdf->Cell(60, 4, 'Address: ' . $row['address'], 0, 1);

    // Line 4: Class | Section
    $pdf->SetX($leftX);
    $pdf->Cell(25, 4, 'Class: ' . $row['className'], 0, 0);
    $pdf->Cell(30, 4, $row['classArmName'], 0, 1);

    // Line 5: Mobile No.
    $pdf->SetX($leftX);
    $pdf->Cell(30, 4, 'Mobile No: ' . $row['priPhoneNo'], 0, 1);

//     // ===== BARCODE using bwip-js API =====
//    $barcodeUrl = "https://bwipjs-api.metafloor.com/?bcid=code128&text=" . urlencode($row['regId']) . "&scale=2&height=10&includetext";
// $barcodeImage = file_get_contents($barcodeUrl);
// $barcodeFile = tempnam(sys_get_temp_dir(), 'barcode_') . '.png';
// file_put_contents($barcodeFile, $barcodeImage);
// use Picqer\Barcode\BarcodeGeneratorPNG;

// $generator = new BarcodeGeneratorPNG();
// $barcode = $generator->getBarcode($row['regId'], $generator::TYPE_CODE_128);

// $barcodeFile = tempnam(sys_get_temp_dir(), 'barcode_') . '.png';
// file_put_contents($barcodeFile, $barcode);

// $pdf->Image($barcodeFile, $barcodeX, $barcodeY, $barcodeWidth, $barcodeHeight);
// unlink($barcodeFile);


// // Smaller width and adjusted position
// $barcodeWidth =30;
// $barcodeHeight = 8;
// $barcodeX = $leftX + 3; // center it
// $barcodeY = $y + $card_height - 14;

// $pdf->Image($barcodeFile, $barcodeX, $barcodeY, $barcodeWidth, $barcodeHeight);
$generator = new BarcodeGeneratorPNG();
$barcode = $generator->getBarcode($row['regId'], $generator::TYPE_CODE_128);

$barcodeFile = tempnam(sys_get_temp_dir(), 'barcode_') . '.png';
file_put_contents($barcodeFile, $barcode);

// Barcode position and size
$barcodeWidth = 30;
$barcodeHeight = 8;
$barcodeX = $leftX + 1;
$barcodeY = $y + $card_height - 14;

$pdf->Image($barcodeFile, $barcodeX, $barcodeY, $barcodeWidth, $barcodeHeight);

// Optional: Student ID below barcode
$pdf->SetFont('Arial', '', 6);
$pdf->SetXY($barcodeX, $barcodeY + $barcodeHeight + 1);
$pdf->Cell($barcodeWidth, 3, $row['regId'], 0, 0, 'C');

// Now delete the barcode image
unlink($barcodeFile);


// Optional: Student ID below barcode
$pdf->SetFont('Arial', '', 6);
$pdf->SetXY($barcodeX, $barcodeY + $barcodeHeight + 1);
$pdf->Cell($barcodeWidth, 3, $row['regId'], 0, 0, 'C');

unlink($barcodeFile);

    // ===== SIGNATURE =====
    $pdf->SetFont('Arial', 'B', 6.5);
    $pdf->SetXY($x + $card_width - 28, $y + $card_height - 10);
    $pdf->Cell(25, 3, 'PRINCIPAL', 0, 0, 'C');

    // ===== BOTTOM BAR =====
    $pdf->SetFillColor(0, 102, 204);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Rect($x, $y + $card_height - 5, $card_width, 5, 'F');
    $pdf->SetFont('Arial', '', 6);
    $pdf->SetXY($x, $y + $card_height - 4.5);

    // Move position to next card
    $x += $card_width + $gap_x;
    if ($x + $card_width > 200) {
        $x = 10;
        $y += $card_height + $gap_y;
    }

    $count++;
}

ob_end_clean();
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="heritage_id_cards_class_' . $class_id . '.pdf"');
$pdf->Output();
exit;
 ?>

<?php
// ob_clean();
// ob_start();
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// // Include tFPDF
// require('tfpdf.php');

// // Include DB connection
// include '../Includes/dbcon.php';

// // Include Composer Autoload for Barcode
// require_once __DIR__ . '/../../../vendor/autoload.php';

// use Picqer\Barcode\BarcodeGeneratorPNG;

// if (!isset($_GET['class_id'])) {
//     die("Class ID missing.");
// }

// $class_id = $_GET['class_id'];

// // Get student data
// $stmt = $conn->prepare("SELECT s.*, c.className 
//                         FROM tblstudents s 
//                         INNER JOIN tblclass c ON s.classId = c.Id 
//                         WHERE s.classId = ?");
// $stmt->bind_param("i", $class_id);
// $stmt->execute();
// $result = $stmt->get_result();
// if ($result->num_rows == 0) {
//     die("No students found.");
// }

// $pdf = new tFPDF('P', 'mm', 'A4');
// $pdf->AddPage();

// $x = 10; $y = 10;
// $card_width = 90;
// $card_height = 55;
// $gap_x = 10;
// $gap_y = 10;
// $count = 0;

// while ($row = $result->fetch_assoc()) {
//     if ($count > 0 && $count % 6 == 0) {
//         $pdf->AddPage();
//         $x = 10; $y = 10;
//     }

//     // Card background
//     $pdf->Rect($x, $y, $card_width, $card_height);

//     // ===== TOP BLUE HEADER =====
//     $pdf->SetFillColor(0, 102, 204); // Blue
//     $pdf->Rect($x, $y, $card_width, 18, 'F');

//     $pdf->SetTextColor(255, 255, 255);
//     $pdf->SetFont('Arial', 'B', 8);
//     $pdf->SetXY($x + 2, $y + 1);
//     $pdf->Cell($card_width - 4, 4, "HERITAGE DAY SCHOOL", 0, 1, 'C');

//     $pdf->SetFont('Arial', '', 6);
//     $pdf->SetX($x + 2);
//     $pdf->Cell($card_width - 4, 3, "Nagarukhra, Barasat Para, Haringhata, Nadia, WB", 0, 1, 'C');
//     $pdf->SetX($x + 2);
//     $pdf->Cell($card_width - 4, 3, "Office: 7364916702 / 9064109172", 0, 1, 'C');
//     $pdf->SetX($x + 2);
//     $pdf->Cell($card_width - 4, 3, "(Affiliated to WBBSE. Code: 123456)", 0, 1, 'C');
//     $pdf->SetFont('Arial', 'B', 7);
//     $pdf->Cell($card_width - 4, 3, "IDENTITY CARD", 0, 1, 'C');

//     // Logo
//     $pdf->Image('logo/schoolLogo.png', $x + 2, $y + 2, 15, 15);

//     // ===== PHOTO =====
//     $photo_path = '../Student/' . $row['studentPhoto'];
//     if (!empty($row['studentPhoto']) && file_exists($photo_path)) {
//         $pdf->Image($photo_path, $x + 3, $y + 20, 22, 26);
//     } else {
//         $pdf->Rect($x + 3, $y + 20, 22, 26);
//         $pdf->SetFont('Arial', '', 5);
//         $pdf->SetXY($x + 3, $y + 32);
//         $pdf->Cell(22, 4, 'No Image', 0, 0, 'C');
//     }

//     // ===== STUDENT DETAILS =====
//     $pdf->SetTextColor(0, 0, 0);
//     $pdf->SetFont('Arial', '', 6.5);
//     $leftX = $x + 28;
//     $infoY = $y + 20;

//     // Line 1: Student ID | Session
//     $pdf->SetXY($leftX, $infoY);
//     $pdf->Cell(25, 4, 'Student ID: ' . $row['regId'], 0, 0);
//     $pdf->Cell(30, 4, 'Session: 2025-2026', 0, 1);

//     // Line 2: Name | DOB
//     $pdf->SetX($leftX);
//     $fullName = $row['studentName'];
//     if (!empty($row['lastName'])) {
//         $fullName .= ' ' . $row['lastName'];
//     }
//     $pdf->Cell(25, 4, 'Name: ' . $fullName, 0, 0);
//     $pdf->Cell(30, 4, 'DOB: ' . date('d-m-Y', strtotime($row['dob'])), 0, 1);

//     // Line 3: Address
//     $pdf->SetX($leftX);
//     $pdf->Cell(60, 4, 'Address: ' . $row['address'], 0, 1);

//     // Line 4: Class | Section
//     $pdf->SetX($leftX);
//     $pdf->Cell(25, 4, 'Class: ' . $row['className'], 0, 0);
//     $pdf->Cell(30, 4, $row['classArmName'], 0, 1);

//     // Line 5: Mobile No.
//     $pdf->SetX($leftX);
//     $pdf->Cell(30, 4, 'Mobile No: ' . $row['priPhoneNo'], 0, 1);

//     // ===== BARCODE =====
//     $generator = new BarcodeGeneratorPNG();
//     $barcode = $generator->getBarcode($row['regId'], $generator::TYPE_CODE_128);
//     $barcodeFile = tempnam(sys_get_temp_dir(), 'barcode_') . '.png';
//     file_put_contents($barcodeFile, $barcode);

//     $barcodeWidth = 30;
//     $barcodeHeight = 8;
//     $barcodeX = $leftX + 3;
//     $barcodeY = $y + $card_height - 14;

//     $pdf->Image($barcodeFile, $barcodeX, $barcodeY, $barcodeWidth, $barcodeHeight);

//     // Barcode text
//     $pdf->SetFont('Arial', '', 6);
//     $pdf->SetXY($barcodeX, $barcodeY + $barcodeHeight + 1);
//     $pdf->Cell($barcodeWidth, 3, $row['regId'], 0, 0, 'C');

//     unlink($barcodeFile);

//     // ===== SIGNATURE =====
//     $pdf->SetFont('Arial', 'B', 6.5);
//     $pdf->SetXY($x + $card_width - 28, $y + $card_height - 10);
//     $pdf->Cell(25, 3, 'PRINCIPAL', 0, 0, 'C');

//     // ===== BOTTOM BAR =====
//     $pdf->SetFillColor(0, 102, 204);
//     $pdf->SetTextColor(255, 255, 255);
//     $pdf->Rect($x, $y + $card_height - 5, $card_width, 5, 'F');
//     $pdf->SetFont('Arial', '', 6);
//     $pdf->SetXY($x, $y + $card_height - 4.5);

//     // Move to next card
//     $x += $card_width + $gap_x;
//     if ($x + $card_width > 200) {
//         $x = 10;
//         $y += $card_height + $gap_y;
//     }

//     $count++;
// }

// ob_end_clean();
// header('Content-Type: application/pdf');
// header('Content-Disposition: inline; filename="heritage_id_cards_class_' . $class_id . '.pdf"');
// $pdf->Output();
// exit;
