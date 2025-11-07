
<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../Includes/dbcon.php';
require('tfpdf.php');

if (!isset($_GET['type'])) {
    die("Invalid receipt type.");
}

$type = $_GET['type'];
$pdf = new tFPDF();
$pdf->AddPage();
$pdf->AddFont('DejaVu', '', 'DejaVuSansCondensed.ttf', true);
$pdf->AddFont('DejaVu', 'B', 'DejaVuSans-Bold.ttf', true);
$pdf->SetFont('DejaVu', '', 11);

function printHeader($pdf, $title) {
    $pdf->SetFont('DejaVu', 'B', 14);
    $pdf->Cell(0, 10, "Heritage Day School", 0, 1, 'C');
    $pdf->SetFont('DejaVu', '', 11);
    $pdf->Cell(0, 6, "Nagarukhra, Barasat Para, Haringhata, Nadia, West Bengal", 0, 1, 'C');
    $pdf->Cell(0, 6, "Office: 7364916702 / 9064109172", 0, 1, 'C');
    $pdf->Ln(10);
    $pdf->SetFont('DejaVu', 'B', 13);
    $pdf->Cell(0, 10, $title, 0, 1, 'C');
    $pdf->Ln(5);
}

if ($type === 'admission' || $type === 'monthly') {
    if (!isset($_GET['payment_id'])) die("Missing payment_id.");
    $payment_id = $_GET['payment_id'];

    $stmt = $conn->prepare("SELECT * FROM payments WHERE payment_id = ?");
    $stmt->bind_param("s", $payment_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if ($row) {
        printHeader($pdf, ucfirst($row['payment_type']) . " Fee Receipt");
        $pdf->SetFont('DejaVu', '', 10);
        $pdf->Cell(40, 8, "Transaction ID:", 1);
        $pdf->Cell(50, 8, $row['payment_id'], 1);
        $pdf->Cell(40, 8, "Registration No:", 1);
        $pdf->Cell(50, 8, $row['regId'], 1);
        $pdf->Ln();
        $pdf->Cell(40, 8, "Student Name:", 1);
        $pdf->Cell(50, 8, $row['studentName'], 1);
        $pdf->Cell(40, 8, "Class:", 1);
        $pdf->Cell(50, 8, $row['className'], 1);
        $pdf->Ln();
        $pdf->Cell(40, 8, "Session:", 1);
        $pdf->Cell(50, 8, $row['session'], 1);
        $pdf->Cell(40, 8, "Month:", 1);
        $pdf->Cell(50, 8, $row['month'] ?: 'N/A', 1);
        $pdf->Ln();
        $pdf->Cell(40, 8, "Payment Mode:", 1);
        $pdf->Cell(50, 8, $row['payment_mode'], 1);
        $pdf->Cell(40, 8, "Date:", 1);
        $pdf->Cell(50, 8, date('d-m-Y', strtotime($row['created_at'])), 1);
        $pdf->Ln(10);
        $pdf->Cell(60, 8, "Amount Paying", 1);
        $pdf->Cell(60, 8, "\xE2\x82\xB9" . number_format($row['amount_paying'], 2), 1);
        $pdf->Ln();
        $pdf->Cell(60, 8, "Grand Total", 1);
        $pdf->Cell(60, 8, "\xE2\x82\xB9" . number_format($row['grand_total'], 2), 1);
        $pdf->Ln();
        $pdf->Cell(60, 8, "Due", 1);
        $pdf->Cell(60, 8, "\xE2\x82\xB9" . number_format($row['due'], 2), 1);
    } else {
        die("Payment not found.");
    }
}
elseif ($type === 'uniform') {
    if (!isset($_GET['regId'])) die("Missing regId.");
    $regId = $_GET['regId'];
    $stmt = $conn->prepare("SELECT * FROM payment_uniforms WHERE regId = ?");
    $stmt->bind_param("s", $regId);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();

    if ($data) {
        printHeader($pdf, "Books / Uniform / Notebook Receipt");
        $pdf->SetFont('DejaVu', '', 11);
        $pdf->Cell(0, 8, "Reg. No: {$regId} | Date: " . date('d-m-Y'), 0, 1);
        $pdf->Ln(3);

        $pdf->SetFont('DejaVu', 'B', 11);
        $pdf->Cell(60, 8, "Item", 1);
        $pdf->Cell(40, 8, "Size", 1);
        $pdf->Cell(40, 8, "Price", 1);
        $pdf->Ln();
        function printRow($pdf, $label, $size, $price) {
            if (!empty($price) && floatval($price) > 0) {
                $pdf->Cell(60, 8, $label, 1);
                $pdf->Cell(40, 8, $size, 1);
                $pdf->Cell(40, 8, "\xE2\x82\xB9" . number_format($price, 2), 1);
                $pdf->Ln();
            }
        }
        printRow($pdf, $data['torso_type'], $data['torso_size'], $data['torso_price']);
        printRow($pdf, $data['bottom_type'], $data['bottom_size'], $data['bottom_price']);
        printRow($pdf, "Tie", $data['tie_size'], $data['tie_price']);
        printRow($pdf, "Belt", $data['belt_size'], $data['belt_price']);
        printRow($pdf, "Socks", $data['socks_size'], $data['socks_price']);
        printRow($pdf, "Other", '-', $data['other_amount']);

        $uniform_total = floatval($data['torso_price']) + floatval($data['bottom_price']) +
                         floatval($data['tie_price']) + floatval($data['belt_price']) +
                         floatval($data['socks_price']) + floatval($data['other_amount']);

        $pdf->SetFont('DejaVu', 'B', 11);
        $pdf->Cell(100, 8, "Uniform Total", 1);
        $pdf->Cell(40, 8, "\xE2\x82\xB9" . number_format($uniform_total, 2), 1);
        $pdf->Ln(10);

        // Book List
        $pdf->SetFont('DejaVu', 'B', 12);
        $pdf->Cell(0, 10, 'Book List', 0, 1);
        $pdf->SetFont('DejaVu', '', 11);
        $books = $conn->prepare("SELECT book_name, price FROM book_list WHERE class_code = ?");
        $books->bind_param("s", $data['book_list_selector']);
        $books->execute();
        $books->bind_result($b_name, $b_price);
        $book_total = 0;
        while ($books->fetch()) {
            $pdf->Cell(120, 8, $b_name, 1);
            $pdf->Cell(40, 8, "\xE2\x82\xB9" . number_format($b_price, 2), 1);
            $pdf->Ln();
            $book_total += floatval($b_price);
        }
        $books->close();
        $pdf->SetFont('DejaVu', 'B', 11);
        $pdf->Cell(120, 8, "Book Total", 1);
        $pdf->Cell(40, 8, "\xE2\x82\xB9" . number_format($book_total, 2), 1);
        $pdf->Ln(10);

        // Notebook List
        $pdf->SetFont('DejaVu', 'B', 12);
        $pdf->Cell(0, 10, 'Notebook List', 0, 1);
        $pdf->SetFont('DejaVu', '', 11);
        $nb = $conn->prepare("SELECT subject, quantity, notebook_type, price FROM notebook_list WHERE class_code = ?");
        $nb->bind_param("s", $data['book_list_selector']);
        $nb->execute();
        $nb->bind_result($subj, $qty, $nb_type, $nb_price);
        $notebook_total = 0;
        while ($nb->fetch()) {
            $pdf->Cell(60, 8, $subj, 1);
            $pdf->Cell(30, 8, $qty, 1);
            $pdf->Cell(60, 8, $nb_type, 1);
            $pdf->Cell(30, 8, "\xE2\x82\xB9" . number_format($nb_price, 2), 1);
            $pdf->Ln();
            $notebook_total += floatval($nb_price);
        }
        $nb->close();
        $pdf->SetFont('DejaVu', 'B', 11);
        $pdf->Cell(150, 8, "Notebook Total", 1);
        $pdf->Cell(40, 8, "\xE2\x82\xB9" . number_format($notebook_total, 2), 1);
        $pdf->Ln(10);

        $grand_total = $uniform_total + $book_total + $notebook_total;
        $pdf->SetFont('DejaVu', 'B', 13);
        $pdf->Cell(150, 10, "Uniform+Books+Notebook Grand Total", 1);
        $pdf->Cell(40, 10, "\xE2\x82\xB9" . number_format($grand_total, 2), 1);
    } else {
        die("Uniform data not found.");
    }
}
else {
    die("Invalid receipt type.");
}

if (ob_get_length()) ob_end_clean();
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="receipt_' . $type . '.pdf"');
$pdf->Output('I');
exit;
