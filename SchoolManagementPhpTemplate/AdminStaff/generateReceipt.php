
<?php
ob_start(); // ✅ only this at the top
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../Includes/dbcon.php';
require('tfpdf.php');

// if (!isset($_GET['type'])) {
//     die("Invalid receipt type.");
// }
$allowedTypes = ['admission', 'monthly', 'uniform'];
$type = $_GET['type'] ?? '';

if (!in_array($type, $allowedTypes)) {
    die("Invalid receipt type.");
}


$type = $_GET['type'];
// var_dump($_GET);
// die();

$pdf = new tFPDF();
$pdf->AddPage();
$pdf->AddFont('DejaVu', '', 'DejaVuSansCondensed.ttf', true);
$pdf->AddFont('DejaVu', 'B', 'DejaVuSans-Bold.ttf', true);
$pdf->SetFont('DejaVu', '', 11);

function printHeader($pdf, $title) {
    $pdf->SetFont('DejaVu', 'B', 14);
    $pdf->Cell(0, 8, "Heritage Day School", 0, 1, 'C');
    $pdf->SetFont('DejaVu', '', 11);
    $pdf->Cell(0, 6, "Nagarukhra,Barasat Para,Haringhata,Nadia,West Bengal", 0, 1, 'C');
    $pdf->Cell(0, 6, "Office: 7364916702/9064109172", 0, 1, 'C');
    // $pdf->Image('img/logo/schoolLogo.png', 88, $pdf->GetY(), 35, 35);/
      $pdf->Image('logo/schoolLogo.png',88, $pdf->GetY(), 35,35);
    $pdf->Ln(30);
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

    printHeader($pdf, ucfirst($row['payment_type']) . " Fee Receipt");
       $startX = 3;
        $startY = $pdf->GetY();

      

       $columns = [
    ['name' => 'Transaction ID', 'width' => 26, 'value' => $row['payment_id']],
    ['name' => 'Registration No', 'width' => 21, 'value' => $row['regId']],
    ['name' => 'Student Name', 'width' => 24, 'value' => $row['studentName']],
    ['name' => 'Payment Date', 'width' => 21, 'value' => date('d-m-Y', strtotime($row['created_at']))],
    ['name' => 'Payment Mode', 'width' => 26, 'value' => $row['payment_mode']],
    ['name' => 'Class', 'width' => 19, 'value' => $row['className']],
    ['name' => 'Admission', 'width' => 23, 'value' => $row['admissionType']],
    ['name' => 'Session', 'width' => 23, 'value' => $row['session']],
    ['name' => 'Month', 'width' => 23, 'value' => $row['month']],
];

// Set font and headers
$pdf->SetFont('Arial', '', 9);
$pdf->SetX(3);

// Define headers and widths
$headerWidths = [26, 23, 24, 21, 26, 17, 23, 23, 23];
$headers = ['Transaction ID', 'Registration No', 'Student Name', 'Payment Date', 'Payment Mode', 'Class', 'Admission', 'Session', 'Month'];

// Print header row
foreach ($headers as $i => $title) {
    $pdf->Cell($headerWidths[$i], 6, $title, 1, 0, 'C');
}
$pdf->Ln();
$monthVal = (strcasecmp($row['payment_type'], 'monthly') === 0 && !empty($row['month']))
    ? $row['month']
    : 'N/A';
// Sample data row (replace with foreach loop for multiple rows)
$values = [
    $row['payment_id'],
    $row['regId'],
    $row['studentName'],  // long name test
    date('d-m-Y', strtotime($row['created_at'])),
    $row['payment_mode'],
    $row['className'],
    $row['admissionType'],
    $row['session'],
   $monthVal 
   
];


// Calculate number of lines for each cell
$lineHeights = [];
foreach ($values as $i => $val) {
    $stringWidth = $pdf->GetStringWidth($val);
    $colWidth = $headerWidths[$i];
    $lines = ceil($stringWidth / $colWidth);
    $lineHeights[] = $lines * 5; // 5 is line height
}
$rowHeight = max($lineHeights); // Ensure all cells match tallest

// Print the row
$x = 3;
$y = $pdf->GetY();
for ($i = 0; $i < count($values); $i++) {
    $pdf->SetXY($x, $y);
    $colW = $headerWidths[$i];

    // Check if text needs wrapping (multiline)
    $stringWidth = $pdf->GetStringWidth($values[$i]);
    if ($stringWidth > $colW) {
        $pdf->MultiCell($colW, 5, $values[$i], 0, 'C');
        $pdf->Rect($x, $y, $colW, $rowHeight); // Border
    } else {
        $pdf->Cell($colW, $rowHeight, $values[$i], 1, 0, 'C');
    }
    $x += $colW;
}
$pdf->Ln($rowHeight);
$pdf->Ln($rowHeight);
   
  $base_amount = (float)($row['grand_total'] ?? 0);
    $amount_paid = (float)($row['amount_paying'] ?? 0);
    $due_amount = (float)($row['due'] ?? 0);

    // Determine payment type
    $paymentType = strtolower(trim($row['payment_type']));

    // Get correct concession string
    if ($paymentType === 'admission') {
        $concession = $row['admissionConcessionType'] ?? 'N/A';
    } elseif ($paymentType === 'monthly') {
        $concession = $row['monthlyConcessionType'] ?? 'N/A';
    } else {
        $concession = 'N/A';
    }

    // Extract concession percentage
    $concession_percentage = 0;
    if (preg_match('/\((\d+)%\)/', $concession, $matches)) {
        $concession_percentage = (int)$matches[1];
    }

    // Calculate final and due amounts
    $concession_value = ($base_amount * $concession_percentage) / 100;
    $final_amount = $base_amount - $concession_value;
    $due_amount = $final_amount - $amount_paid;
    $pdf->AddFont('DejaVu', '', 'DejaVuSansCondensed.ttf', true);

    // Table style
    $centerX = (210 - 100) / 2;
    $pdf->SetFont('DejaVu', '', 11);

    $pdf->SetX($centerX);
    $pdf->Cell(50, 8, 'Fee Type', 'TB', 0, 'C');
    $pdf->Cell(50, 8, 'Amount (₹)', 'TB', 0, 'C');
    $pdf->Ln();

    $pdf->SetX($centerX);
    $pdf->Cell(50, 8, "Grand Total:", 'TB', 0, 'C');
    $pdf->Cell(50, 8, "₹ " . number_format($base_amount, 2), 'TB', 1, 'C');

    $pdf->SetX($centerX);
    $pdf->Cell(50, 8, "Concession Type:", 'TB', 0, 'C');
    $pdf->Cell(50, 8, $concession, 'TB', 1, 'C');

    $pdf->SetX($centerX);
    $pdf->Cell(50, 8, "Final Amount:", 'TB', 0, 'C');
    $pdf->Cell(50, 8, "₹ " . number_format($final_amount, 2), 'TB', 1, 'C');

    $pdf->SetX($centerX);
    $pdf->Cell(50, 8, "Amount Paid:", 'TB', 0, 'C');
    $pdf->Cell(50, 8, "₹ " . number_format($amount_paid, 2), 'TB', 1, 'C');

    $pdf->SetX($centerX);
    $pdf->Cell(50, 8, "Due Amount:", 'TB', 0, 'C');
    $pdf->Cell(50, 8, "₹ " . number_format($due_amount, 2), 'TB', 1, 'C');

    $pdf->Ln(10);



        // $pdf->SetFont('Arial', 'I', 10);
        $pdf->SetFont('DejaVu', '', 11);
        $pdf->SetX(70);
        $totalText = ' Amount Paid: ₹ ' . number_format($amount_paid, 2);
        $textWidth = $pdf->GetStringWidth($totalText) + 10;
        $pdf->Cell($textWidth, 10, $totalText, 1, 0, 'C');
      
}
elseif ($type === 'uniform') {
    if (!isset($_GET['regId'])) die("Missing regId.");
    $regId = $_GET['regId'];
    // $stmt = $conn->prepare("SELECT * FROM payment_uniforms WHERE regId = ?");
    // $stmt = $conn->prepare("SELECT * FROM payment_uniforms WHERE regId = ? ORDER BY id DESC LIMIT 1");
$stmt = $conn->prepare("
    SELECT pu.*, p.studentName, p.className, p.payment_mode, p.admissionType, p.session, p.month, p.created_at
    FROM payment_uniforms pu
    LEFT JOIN payments p 
        ON BINARY pu.payment_id = BINARY p.payment_id
    WHERE pu.regId = ?
    ORDER BY pu.id DESC
    LIMIT 1
");


    $stmt->bind_param("s", $regId);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    $row = $data;
    if ($data) {
        printHeader($pdf, "Uniform  / Books / Notebook Receipt");
        // $pdf->SetFont('DejaVu', '', 11);
        // $pdf->Cell(0, 8, "Reg. No: {$regId} | Date: " . date('d-m-Y'), 0, 1);
                $startX = 3;
        $startY = $pdf->GetY();

      

       $columns = [
    ['name' => 'Transaction ID', 'width' => 26, 'value' => $row['payment_id']],
    ['name' => 'Registration No', 'width' => 21, 'value' => $row['regId']],
    ['name' => 'Student Name', 'width' => 24, 'value' => $row['studentName']],
    ['name' => 'Payment Date', 'width' => 21, 'value' => date('d-m-Y', strtotime($row['created_at']))],
    ['name' => 'Payment Mode', 'width' => 26, 'value' => $row['payment_mode']],
    ['name' => 'Class', 'width' => 19, 'value' => $row['className']],
    ['name' => 'Session', 'width' => 23, 'value' => $row['session']],
   
];

// Set font and headers
$pdf->SetFont('Arial', '', 9);
$pdf->SetX(3);

// Define headers and widths
$headerWidths = [36, 23, 24, 28, 26, 27, 23];
$headers = ['Transaction ID', 'Registration No', 'Student Name', 'Payment Date', 'Payment Mode', 'Class', 'Session'];

// Print header row
foreach ($headers as $i => $title) {
    $pdf->Cell($headerWidths[$i], 6, $title, 1, 0, 'C');
}
$pdf->Ln();
/*$monthVal = (strcasecmp($row['payment_type'], 'monthly') === 0 && !empty($row['month']))
    ? $row['month']
    : 'N/A';*/
// Sample data row (replace with foreach loop for multiple rows)
$values = [
    $row['payment_id'],
    $row['regId'],
    $row['studentName'],  // long name test
    date('d-m-Y', strtotime($row['created_at'])),
    $row['payment_mode'],
    $row['className'],
    // $row['admissionType'],
    // $row['session'],
  //   !empty($row['admissionType']) ? $row['admissionType'] : 'N/A',
    !empty($row['session']) ? $row['session'] : 'N/A',
  // $monthVal 
   
];


// Calculate number of lines for each cell
$lineHeights = [];
foreach ($values as $i => $val) {
    $stringWidth = $pdf->GetStringWidth($val);
    $colWidth = $headerWidths[$i];
    $lines = ceil($stringWidth / $colWidth);
    $lineHeights[] = $lines * 5; // 5 is line height
}
$rowHeight = max($lineHeights); // Ensure all cells match tallest

// Print the row
$x = 3;
$y = $pdf->GetY();
for ($i = 0; $i < count($values); $i++) {
    $pdf->SetXY($x, $y);
    $colW = $headerWidths[$i];

    // Check if text needs wrapping (multiline)
    $stringWidth = $pdf->GetStringWidth($values[$i]);
    if ($stringWidth > $colW) {
        $pdf->MultiCell($colW, 5, $values[$i], 0, 'C');
        $pdf->Rect($x, $y, $colW, $rowHeight); // Border
    } else {
        $pdf->Cell($colW, $rowHeight, $values[$i], 1, 0, 'C');
    }
    $x += $colW;
}
$pdf->Ln($rowHeight);



// After the row is printed, move down
// $pdf->Ln($rowHeight);
        $pdf->Ln(1);

         $pdf->SetFont('DejaVu', 'B', 12);
        $pdf->Cell(0, 10, 'Uniform', 0, 1,'C');

        $pdf->SetFont('DejaVu', '', 11);
        $pdf->Cell(80, 8, "Item", 1,0,'C');
        $pdf->Cell(50, 8, "Size", 1,0,'C');
        $pdf->Cell(50, 8, "Price", 1,0,'C');
        $pdf->Ln();

        function printRow($pdf, $label, $size, $price) {
            if (!empty($price) && floatval($price) > 0) {
                $pdf->Cell(80, 8, $label, 1,0,'C');
                $pdf->Cell(50, 8, $size, 1,0,'C');
                $pdf->Cell(50, 8, "₹" . number_format($price, 2), 1,0,'C');
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

        $pdf->SetFont('DejaVu', '', 11);
        $pdf->Cell(130, 8, "Uniform Total", 1,0,'C');
        $pdf->Cell(50, 8, "₹" . number_format($uniform_total, 2), 1,0,'C');
        $pdf->Ln(9);

        $pdf->SetFont('DejaVu', 'B', 12);
        $pdf->Cell(0, 10, 'Book List', 0, 1,'C');
        $pdf->SetFont('DejaVu', '', 11);
        $books = $conn->prepare("SELECT book_name, price FROM book_list WHERE class_code = ?");
        $books->bind_param("s", $data['book_list_selector']);
        $books->execute();
        $books->bind_result($b_name, $b_price);
        $book_total = 0;
        while ($books->fetch()) {
            $pdf->Cell(130, 8, $b_name, 1,0,'C');
            $pdf->Cell(50, 8, "₹" . number_format($b_price, 2), 1,0,'C');
            $pdf->Ln();
            $book_total += floatval($b_price);
        }
        $books->close();
        $pdf->SetFont('DejaVu', '', 11);
        $pdf->Cell(130, 8, "Book Total", 1,0,'C');
        $pdf->Cell(50, 8, "₹" . number_format($book_total, 2), 1,0,'C');
        $pdf->Ln(10);

        $pdf->SetFont('DejaVu', 'B', 12);
        $pdf->Cell(0, 10, 'Notebook List', 0, 1,'C');
        $pdf->SetFont('DejaVu', '', 11);
        $nb = $conn->prepare("SELECT subject, quantity, notebook_type, price FROM notebook_list WHERE class_code = ?");
        $nb->bind_param("s", $data['book_list_selector']);
        $nb->execute();
        $nb->bind_result($subj, $qty, $nb_type, $nb_price);
        $notebook_total = 0;
        while ($nb->fetch()) {
            $pdf->Cell(60, 8, $subj, 1,0,'C');
            $pdf->Cell(30, 8, $qty . ' pieces', 1,0,'C');
            $pdf->Cell(60, 8, $nb_type, 1,0,'C');
            $pdf->Cell(30, 8, "₹" . number_format($nb_price, 2), 1,0,'C');
            $pdf->Ln();
            $notebook_total += floatval($nb_price);
        }
        $nb->close();
        $pdf->SetFont('DejaVu', 'B', 11);
        $pdf->Cell(150, 8, "Notebook Total", 1,0,'C');
        $pdf->Cell(30, 8, "₹" . number_format($notebook_total, 2), 1,0,'C');
        $pdf->Ln(10);

         $base_amount = $uniform_total + $book_total + $notebook_total;
      
$fetchLatestPayment = $conn->prepare("SELECT amount_paying, due FROM payments WHERE regId = ? AND payment_type = 'Books/Uniform' ORDER BY payment_id DESC LIMIT 1");
$fetchLatestPayment->bind_param("s", $regId);
$fetchLatestPayment->execute();
$fetchLatestPayment->bind_result($amtPaid, $amtDue);
$fetchLatestPayment->fetch();
$fetchLatestPayment->close();

$paid_amount = floatval($amtPaid);
$due_amount = floatval($amtDue);

        $pdf->SetFont('DejaVu', 'B', 13);
        $pdf->Cell(150, 10, "Uniform+Books+Notebook Grand Total", 1,0,'C');
        $pdf->Cell(30, 10, "₹" . number_format($base_amount, 2), 1,0,'C');
        $pdf->Ln();

        $pdf->SetFont('DejaVu', 'B', 11);
        $pdf->Cell(150, 8, "Amount Paid", 1,0,'C');
        $pdf->Cell(30, 8, "₹" . number_format($paid_amount, 2), 1,0,'C');
        $pdf->Ln();

        $pdf->Cell(150, 8, "Due Amount", 1,0,'C');
        $pdf->Cell(30, 8, "₹" . number_format($due_amount, 2), 1,0,'C');
        $pdf->Ln(12);

        $pdf->SetFont('DejaVu', 'B', 12);
        $pdf->Cell(180, 10, "Amount Paid: ₹ " . number_format($paid_amount, 2), 1, 1, 'C');
        
        } else {
            die("Uniform data not found.");
        }
} else {
    die("Invalid receipt type.");
}

if (ob_get_length()) ob_end_clean();
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="receipt_' . $type . '.pdf"');
$pdf->Output('I');
exit;
?>
