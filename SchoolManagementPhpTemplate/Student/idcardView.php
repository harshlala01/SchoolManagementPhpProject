<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../Includes/dbcon.php';
include '../Includes/session.php';

require_once 'barcode/src/BarcodeGenerator.php';
require_once 'barcode/src/BarcodeGeneratorPNG.php';

use Picqer\Barcode\BarcodeGeneratorPNG;

// Fetch student data
$query = "SELECT tblstudents.Id, tblclass.className, tblclassarms.classArmName,
    tblstudents.studentName, tblstudents.regId, tblstudents.studentPhoto, 
    tblstudents.fatherName, tblstudents.motherName, tblstudents.priPhoneNo, 
    tblstudents.secPhoneNo, tblstudents.address, tblstudents.zone, 
    tblstudents.secLang, tblstudents.dob, tblstudents.commute,
    tblstudents.admissionType, tblstudents.admissionConcessionType,
    tblstudents.monthlyConcessionType, tblstudents.session  
    FROM tblstudents 
    INNER JOIN tblclass ON tblclass.Id = tblstudents.classId 
    INNER JOIN tblclassarms ON tblclassarms.Id = tblstudents.classSecId 
    WHERE tblstudents.Id = '{$_SESSION['userId']}'";

$rs = $conn->query($query);
if (!$rs || $rs->num_rows === 0) {
    die("Student not found.");
}
$data = $rs->fetch_assoc();

$regId = $data['regId'];
$barcodeDir = __DIR__ . '/barcodes';
$barcodePath = "$barcodeDir/$regId.png";

// Generate barcode if not exists
if (!is_dir($barcodeDir)) {
    mkdir($barcodeDir, 0777, true);
}
if (!file_exists($barcodePath)) {
    $generator = new BarcodeGeneratorPNG();
    $barcode = $generator->getBarcode($regId, $generator::TYPE_CODE_128);
    file_put_contents($barcodePath, $barcode);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Student ID Card</title>
  <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
  <link href="css/ruang-admin.min.css" rel="stylesheet">
  <link rel="icon" href="img/logo/techShell.jpg">
  <style>
    .id-card {
      width: 700px;
      margin: 0 auto 30px auto;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.2);
      overflow: hidden;
      background: #fff;
    }
    .id-header {
      background-color: #0066cc;
      color: white;
      padding: 10px;
      display: flex;
      align-items: center;
    }
    .id-header img {
      height: 90px;
      margin-left: 20px;
    }
    .school-info {
      flex: 1;
      text-align: center;
      margin-left: -40px;
    }
    .school-info h2 {
      margin: 0;
      font-size: 22px;
    }
    .school-info p {
      margin: 2px 0;
      font-size: 13px;
    }
    .school-info strong {
      display: block;
      margin-top: 5px;
      font-size: 15px;
    }
    .id-body {
      display: flex;
      padding: 20px;
    }
    .photo-section {
      flex: 1;
      text-align: center;
    }
    .photo-section img {
      width: 150px;
      height: 180px;
      border: 1px solid #ccc;
      border-radius: 5px;
      object-fit: cover;
    }
    .details-section {
      flex: 2;
      padding-left: 15px;
    }
    .details-row {
      display: flex;
      justify-content: space-between;
      margin-bottom: 5px;
    }
    .details-row p {
      font-size: 14px;
      margin: 2px 0;
      width: 48%;
    }
    .barcode-row {
      margin-top: 10px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .barcode img {
      height: 50px;
    }
    .signature img {
      height: 45px;
    }
    .signature strong {
      font-size: 13px;
      text-align: right;
      display: block;
      margin-top: 5px;
    }
    .blue-footer {
      background-color: #0066cc;
      height: 40px;
    }
  </style>
</head>

<body id="page-top">
<div id="wrapper">
  <?php include "Includes/sidebar.php"; ?>
  <div id="content-wrapper" class="d-flex flex-column">
    <div id="content">
      <?php include "Includes/topbar.php"; ?>

      <div class="container-fluid" id="container-wrapper">
        <!-- Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
          <h1 class="h3 mb-0 text-gray-800" style="white-space: nowrap;">Student ID Card</h1>
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="./">Home</a></li>
            <li class="breadcrumb-item active">ID Card</li>
          </ol>
        </div>

        <!-- ID Card -->
        <div class="id-card">
          <div class="id-header">
            <img src="./img/logo/schoolLogo.png" alt="School Logo">
            <div class="school-info">
              <h2>HERITAGE DAY SCHOOL</h2>
              <p>Nagaurkra, Barasat Para, Haringhata, Nadia, WB</p>
              <p>Office: 7364916702 / 9064109172</p>
              <p>(Affiliated to WBBSE. Code: 123456)</p>
              <strong>IDENTITY CARD</strong>
            </div>
          </div>

          <div class="id-body">
            <div class="photo-section">
              <?php 
              $photoPath = '../Student/' . $data['studentPhoto'];
              if (!empty($data['studentPhoto']) && file_exists($photoPath)) {
                  echo "<img src='$photoPath' alt='Student Photo'>";
              } else {
                  echo '<div style="width:150px;height:180px;border:1px solid #ccc;display:flex;align-items:center;justify-content:center;">No Image</div>';
              }
              ?>
            </div>

            <div class="details-section">
              <div class="details-row">
                <p><b>Student ID:</b> <?= $data['regId']; ?></p>
                <p><b>Session:</b> <?= $data['session']; ?></p>
              </div>
              <div class="details-row">
                <p><b>Name:</b> <?= $data['studentName']; ?></p>
                <p><b>DOB:</b> <?= date('d-m-Y', strtotime($data['dob'])); ?></p>
              </div>
              <div class="details-row">
                <p><b>Address:</b> <?= $data['address']; ?></p>
                <p><b>Sec:</b> <?= $data['classArmName']; ?></p>
              </div>
              <div class="details-row">
                <p><b>Class:</b> <?= $data['className']; ?></p>
                <p><b>Guardian:</b> <?= $data['fatherName']; ?></p>
              </div>
              <div class="details-row">
                <p><b>Mobile No:</b> <?= $data['priPhoneNo']; ?></p>
                <p></p>
              </div>

              <!-- Barcode & Signature -->
              <div class="barcode-row">
                <div class="barcode">
                  <img src="barcodes/<?= $regId; ?>.png" alt="Barcode">
                </div>
                <div class="signature">
                  <img src="./img/logo/signature.jpg"  alt="Signature">
                  <strong>PRINCIPAL</strong>
                </div>
              </div>
            </div>
          </div>

          <div class="blue-footer"></div>
        </div>

        <!-- Download Button -->
        <div class="text-center mt-4">
          <a href="generate_id_card.php?regId=<?= $data['regId']; ?>" class="btn btn-primary">
            <i class="fas fa-download"></i> Download ID Card PDF
          </a>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <div class="container-fluid px-4">
      <?php include "Includes/footer.php"; ?>
    </div>
  </div>
</div>

<a class="scroll-to-top rounded" href="#page-top"><i class="fas fa-angle-up"></i></a>
<script src="../vendor/jquery/jquery.min.js"></script>
<script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="js/ruang-admin.min.js"></script>
</body>
</html>
