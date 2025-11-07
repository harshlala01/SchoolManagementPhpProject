<?php
session_start();

ob_clean();
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require('tfpdf.php');
include '../Includes/dbcon.php';

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

// Check login
if (!isset($_SESSION['regId'])) {
    die("Please login to view your ID card.");
}
$regId = $_SESSION['regId'];

// Fetch student details
$query = mysqli_query($conn, "SELECT * FROM tblstudents WHERE regId = '$regId'");
$row = mysqli_fetch_assoc($query);

$pdf = new tFPDF('P', 'mm', 'A4');
$pdf->AddPage();

$x = 10; $y = 10;
$card_width = 90;
$card_height = 55;
$gap_x = 10;
$gap_y = 10;
$count = 0;


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
//     $pdf->SetFont('Arial', 'B', 10);
// $pdf->Cell($card_width, 4, "HERITAGE DAY SCHOOL", 0, 1, 'C');

// $pdf->SetFont('Arial', '', 6);
// $pdf->Cell($card_width, 3, "Nagarukhra, Barasat Para, Haringhata, Nadia, WB", 0, 1, 'C');
// $pdf->Cell($card_width, 3, "Office: 7364916702 / 9064109172", 0, 1, 'C');
// $pdf->Cell($card_width, 3, "(Affiliated to WBBSE. Code: 123456)", 0, 1, 'C');

// $pdf->SetFont('Arial', 'B', 7);
// $pdf->Cell($card_width, 3, "IDENTITY CARD", 0, 1, 'C');


    // Logo
$pdf->Image('./img/logo/schoolLogo.png', $x + 2, $y + 2, 15, 15);








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

// Student Info
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
    $pdf->Cell(25, 4, 'Mobile No: ' . $row['priPhoneNo'], 0, 0);
    $pdf->Cell(30, 4,  'Guardian: ' . $row['fatherName'], 0, 1);

// // // Smaller width and adjusted position
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
//$pdf->Cell($barcodeWidth, 3, $row['regId'], 0, 0, 'C');
//$pdf->Cell($barcodeWidth, 3, $row['regId'], 0, 0, 'C');

// Now delete the barcode image
unlink($barcodeFile);


// // PRINCIPAL label niche
// $pdf->SetFont('Arial', 'B', 6.5);
// $pdf->SetXY($x + $card_width - 26, $y + $card_height - 11); // adjust to match new height
// $pdf->Cell(20, 3, 'PRINCIPAL', 0, 0, 'C');
$signatureWidth = 20;
$signatureHeight = 5;
$signatureX = $x + $card_width - 26;
$signatureY = $y + $card_height - 15; // moved near bottom

$pdf->Image('./img/logo/signature.jpg', $signatureX, $signatureY, $signatureWidth, $signatureHeight);

// 3. "PRINCIPAL" label — just below signature image
$pdf->SetFont('Arial', 'B', 6.5);
$pdf->SetXY($signatureX, $signatureY + $signatureHeight);
$pdf->Cell($signatureWidth, 3, 'PRINCIPAL', 0, 0, 'C');



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


// ob_end_clean();
// header('Content-Type: application/pdf');
// header('Content-Disposition: inline; filename="heritage_id_cards_class_' . $regId . '.pdf"');
// $pdf->Output('D');
ob_end_clean();
header('Content-Type: application/pdf');

if (isset($_GET['preview']) && $_GET['preview'] == 1) {
    $pdf->Output(); // inline output (for iframe)
} else {
    $pdf->Output('D'); // only when download link clicked
}


exit;
?>
