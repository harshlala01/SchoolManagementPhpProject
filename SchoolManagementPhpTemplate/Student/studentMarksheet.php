<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
// include '../Includes/dbcon.php';
// include '../Includes/session.php';

// $regId = $_GET['regId'] ?? $_SESSION['regId'] ?? '';
// $q = "SELECT * FROM student_marks WHERE regId='$regId'";
// $r = mysqli_query($conn, $q);
// $data = mysqli_fetch_assoc($r);
// if (!$data) exit("<h4>No student with RegID $regId</h4>");



// function getGrade($m) {
//   if ($m>=91) return 'A+'; if ($m>=81) return 'A';
//   if ($m>=71) return 'B+'; if ($m>=61) return 'B';
//   if ($m>=51) return 'C+'; if ($m>=41) return 'C';
//   return 'D';
// }
// $subjectMap = [
//   'HINDI' => 'hindi_bengali', 'ENGLISH'=>'english',
//   'MATHS'=>'math', 'COMPUTER'=>'computer',
//   'EVS'=>'evs', 'PRACTICAL'=>'practical'
// ];
// $marks=[]; $maxPer=60;
// foreach($subjectMap as $sub=>$col){
//   $m = intval($data[$col] ?? 0);
//   $marks[] = ['subject'=>$sub, 'half'=>$m,'term'=>$m,'total'=>$m,'grade'=>getGrade($m)];
// }
// $totalMax = count($marks)*$maxPer;
// $totalObtained = array_sum(array_column($marks,'total'));
// $percentage = round(($totalObtained/$totalMax)*100,2);
// $finalGrade = getGrade($percentage);
?>
<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
// include '../Includes/dbcon.php';
// include '../Includes/session.php';

// $regId = $_GET['regId'] ?? $_SESSION['regId'] ?? '';
// $q = "SELECT * FROM student_marks WHERE regId='$regId'";
// $r = mysqli_query($conn, $q);
// $data = mysqli_fetch_assoc($r);
// if (!$data) exit("<h4>No student with RegID $regId</h4>");

// function getGrade($m) {
//   if ($m>=91) return 'A+';
//   if ($m>=81) return 'A';
//   if ($m>=71) return 'B+';
//   if ($m>=61) return 'B';
//   if ($m>=51) return 'C+';
//   if ($m>=41) return 'C';
//   return 'D';
// }

// $subjectMap = [
//   'HINDI' => 'hindi_bengali', 'ENGLISH'=>'english',
//   'MATHS'=>'math', 'COMPUTER'=>'computer',
//   'EVS'=>'evs', 'PRACTICAL'=>'practical'
// ];

// $marks=[]; $maxPer = 60;

// foreach($subjectMap as $sub => $col){
//   $m = intval($data[$col] ?? 0);
//   $marks[] = [
//     'subject' => $sub,
//     'half' => $m,
//     'term' => $m,
//     'total' => $m,
//     'grade' => getGrade($m)
//   ];
// }

// $totalMax = count($marks)*$maxPer;
// $totalObtained = array_sum(array_column($marks,'total'));
// $percentage = round(($totalObtained/$totalMax)*100,2);
// $finalGrade = getGrade($percentage);
?>
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../Includes/dbcon.php';
include '../Includes/session.php';

$regId = $_GET['regId'] ?? $_SESSION['regId'] ?? '';
// if (empty($regId)) exit("<h4>RegID not found in URL or session</h4>");
if (empty($regId)) {
    $showNoRegIdModal = true;  // flag to show modal in HTML later
} else {
    $showNoRegIdModal = false;
    // Continue with rest of your code to fetch student data
    $q = "SELECT * FROM student_marks WHERE regId='$regId' ORDER BY id DESC LIMIT 1";
    $r = mysqli_query($conn, $q);
    $data = mysqli_fetch_assoc($r);
    if (!$data) {
        $showNoRecordModal = true;  // Another flag if no record found for regId
    } else {
        $showNoRecordModal = false;
        // rest of your code to display report card
    }
}

// Grade calculation function
function getGrade($m) {
    if ($m >= 91) return 'A+';
    if ($m >= 81) return 'A';
    if ($m >= 71) return 'B+';
    if ($m >= 61) return 'B';
    if ($m >= 51) return 'C+';
    if ($m >= 41) return 'C';
    return 'D';
}

// If form is submitted, update marks
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subjects = ['hindi_bengali', 'english', 'math', 'computer', 'evs', 'sst', 'practical', 'drawing'];
    $total = 0;
    foreach ($subjects as $subject) {
        $$subject = intval($_POST[$subject] ?? 0);
        $total += $$subject;
    }

    $totalSubjects = count($subjects);
    $percentage = $total / ($totalSubjects * 60) * 100;
    $grade = getGrade($percentage);

    // Update query
    // $update = "UPDATE student_marks SET 
    //     hindi_bengali=$hindi_bengali, english=$english, math=$math, computer=$computer,
    //     evs=$evs, sst=$sst, practical=$practical, drawing=$drawing,
    //     total_marks=$total, percentage=$percentage, grade='$grade'
    //     WHERE regId='$regId'";

    // if (mysqli_query($conn, $update)) {
    //     echo "<script>alert('Marks updated successfully');</script>";
    // } else {
    //     echo "<script>alert('Update failed');</script>";
    // }
    // First get latest row ID
$getLatest = "SELECT id FROM student_marks WHERE regId='$regId' ORDER BY id DESC LIMIT 1";
$getResult = mysqli_query($conn, $getLatest);
$latestRow = mysqli_fetch_assoc($getResult);
$latestId = $latestRow['id'] ?? 0;

if ($latestId > 0) {
    $update = "UPDATE student_marks SET 
        hindi_bengali=$hindi_bengali, english=$english, math=$math, computer=$computer,
        evs=$evs, sst=$sst, practical=$practical, drawing=$drawing,
        total_marks=$total, percentage=$percentage, grade='$grade'
        WHERE id=$latestId";

    if (mysqli_query($conn, $update)) {
        echo "<script>alert('Marks updated successfully');</script>";
    } else {
        echo "<script>alert('Update failed');</script>";
    }
}

}

// Fetch latest student data after update
// $q = "SELECT * FROM student_marks WHERE regId='$regId'";
$q = "SELECT * FROM student_marks WHERE regId='$regId' ORDER BY id DESC LIMIT 1";
$r = mysqli_query($conn, $q);
$data = mysqli_fetch_assoc($r);
// if (!$data) exit("<h4>No student with RegID $regId</h4>");
// if (!$data) {
//     $showNoRecordModal = true;
    
// } else {
//     $showNoRecordModal = false;
   
// }
if (!$data) {
    $showNoRecordModal = true;
   
    $data = [
        'className' => 'N/A',
        'photo' => './img/logo/default.png',
        'studentName' => 'Student Not Found',
        'rollNo' => '-',
        'motherName' => '-',
        'regId' => $regId,
        'fatherName' => '-',
        'dob' => '-',
        'address' => '-',
        'remarks' => 'No record available.',
        'rank' => '-',
        
    ];
}

// Subject mapping for display
$subjectMap = [
    'HINDI' => 'hindi_bengali',
    'ENGLISH' => 'english',
    'MATHS' => 'math',
    'COMPUTER' => 'computer',
    'EVS' => 'evs',
    'SST' => 'sst',
    'PRACTICAL' => 'practical',
    'DRAWING' => 'drawing'
];

$marks = [];
$maxPerSubject = 60;

foreach ($subjectMap as $subject => $column) {
    $obtained = intval($data[$column] ?? 0);
    $marks[] = [
        'subject' => $subject,
        'half' => $obtained,
        'term' => $obtained,
        'total' => $obtained, 
        'grade' => getGrade($obtained)
    ];
}

$totalMax = count($marks) * $maxPerSubject;
$totalObtained = array_sum(array_column($marks, 'total'));
$percentage = round(($totalObtained / $totalMax) * 100, 2);
$finalGrade = getGrade($percentage);
?>
<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
// include '../Includes/dbcon.php';
// include '../Includes/session.php';

// $regId = $_GET['regId'] ?? $_SESSION['regId'] ?? '';
// $showNoRegIdModal = false;
// $showNoRecordModal = false;
// $showNoPdfModal = false;

// // Grade calculation function
// function getGrade($m) {
//     if ($m >= 91) return 'A+';
//     if ($m >= 81) return 'A';
//     if ($m >= 71) return 'B+';
//     if ($m >= 61) return 'B';
//     if ($m >= 51) return 'C+';
//     if ($m >= 41) return 'C';
//     return 'D';
// }

// // Step 1: If no regId
// if (empty($regId)) {
//     $showNoRegIdModal = true;
// } else {
//     // Step 2: Fetch latest record
//     $q = "SELECT * FROM student_marks WHERE regId='$regId' ORDER BY id DESC LIMIT 1";
//     $r = mysqli_query($conn, $q);
//     $data = mysqli_fetch_assoc($r);

//     if (!$data) {
//         $showNoRecordModal = true;
//         $data = [
//             'className' => 'N/A',
//             'photo' => './img/logo/default.png',
//             'studentName' => 'Student Not Found',
//             'rollNo' => '-',
//             'motherName' => '-',
//             'regId' => $regId,
//             'fatherName' => '-',
//             'dob' => '-',
//             'address' => '-',
//             'remarks' => 'No record available.',
//             'rank' => '-',
//         ];
//     }
// }

// // Step 3: Handle PDF access
// if (isset($_GET['pdf']) && $_GET['pdf'] === '1') {
//     if (empty($regId) || !$data || $data['studentName'] === 'Student Not Found') {
//         $showNoPdfModal = true;
//     } else {
//         // Actual download page redirect karo
//         header("Location: studentReportCard.php?regId=$regId");
//         exit;
//     }
// }


// // Step 4: If POST submitted, update marks
// if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$showNoRegIdModal) {
//     $subjects = ['hindi_bengali', 'english', 'math', 'computer', 'evs', 'sst', 'practical', 'drawing'];
//     $total = 0;
//     foreach ($subjects as $subject) {
//         $$subject = intval($_POST[$subject] ?? 0);
//         $total += $$subject;
//     }

//     $percentage = $total / (count($subjects) * 60) * 100;
//     $grade = getGrade($percentage);

//     $getLatest = "SELECT id FROM student_marks WHERE regId='$regId' ORDER BY id DESC LIMIT 1";
//     $getResult = mysqli_query($conn, $getLatest);
//     $latestRow = mysqli_fetch_assoc($getResult);
//     $latestId = $latestRow['id'] ?? 0;

//     if ($latestId > 0) {
//         $update = "UPDATE student_marks SET 
//             hindi_bengali=$hindi_bengali, english=$english, math=$math, computer=$computer,
//             evs=$evs, sst=$sst, practical=$practical, drawing=$drawing,
//             total_marks=$total, percentage=$percentage, grade='$grade'
//             WHERE id=$latestId";

//         if (mysqli_query($conn, $update)) {
//             echo "<script>alert('Marks updated successfully');</script>";
//         } else {
//             echo "<script>alert('Update failed');</script>";
//         }
//     }

//     // Refresh data after update
//     $q = "SELECT * FROM student_marks WHERE regId='$regId' ORDER BY id DESC LIMIT 1";
//     $r = mysqli_query($conn, $q);
//     $data = mysqli_fetch_assoc($r);
// }

// // Step 5: Subject-wise marks breakdown
// $subjectMap = [
//     'HINDI' => 'hindi_bengali',
//     'ENGLISH' => 'english',
//     'MATHS' => 'math',
//     'COMPUTER' => 'computer',
//     'EVS' => 'evs',
//     'SST' => 'sst',
//     'PRACTICAL' => 'practical',
//     'DRAWING' => 'drawing'
// ];

// $marks = [];
// $maxPerSubject = 60;

// foreach ($subjectMap as $subject => $column) {
//     $obtained = intval($data[$column] ?? 0);
//     $marks[] = [
//         'subject' => $subject,
//         'half' => $obtained,
//         'term' => $obtained,
//         'total' => $obtained,
//         'grade' => getGrade($obtained)
//     ];
// }

// $totalMax = count($marks) * $maxPerSubject;
// $totalObtained = array_sum(array_column($marks, 'total'));
// $percentage = round(($totalObtained / $totalMax) * 100, 2);
// $finalGrade = getGrade($percentage);
?>
<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
// include '../Includes/dbcon.php';
// include '../Includes/session.php';

// $regId = $_GET['regId'] ?? $_SESSION['regId'] ?? '';
// $showNoRegIdModal = false;
// $showNoRecordModal = false;

// // If no regId found
// if (empty($regId)) {
//     $showNoRegIdModal = true;
// } else {
//     // Fetch latest student data
//     $q = "SELECT * FROM student_marks WHERE regId='$regId' ORDER BY id DESC LIMIT 1";
//     $r = mysqli_query($conn, $q);
//     $data = mysqli_fetch_assoc($r);

//     if (!$data) {
//         $showNoRecordModal = true;
//     }
// }

// // Grade calculation function
// function getGrade($m) {
//     if ($m >= 91) return 'A+';
//     if ($m >= 81) return 'A';
//     if ($m >= 71) return 'B+';
//     if ($m >= 61) return 'B';
//     if ($m >= 51) return 'C+';
//     if ($m >= 41) return 'C';
//     return 'D';
// }

// // Get class name
// $className = $_SESSION['className'] ?? ($data['className'] ?? '');

// // Get subjects dynamically from DB
// $subjectQuery = "SELECT subjectName FROM subjects WHERE className = '$className'";
// $subjectResult = mysqli_query($conn, $subjectQuery);

// $subjects = [];
// if ($subjectResult && mysqli_num_rows($subjectResult) > 0) {
//     while ($row = mysqli_fetch_assoc($subjectResult)) {
//         $subjects[] = $row['subjectName'];
//     }
// } else {
//     echo "<p class='text-danger'>❌ No subjects found for class $className</p>";
// }

// // On form submit — Update marks
// if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//     $total = 0;
//     foreach ($subjects as $subject) {
//         // Convert subject to lowercase + underscores (like 'Hindi/Bengali' => 'hindi_bengali')
//         $field = strtolower(str_replace([' ', '/', '-'], '_', $subject));
//         $$field = intval($_POST[$field] ?? 0);
//         $total += $$field;
//     }

//     $totalSubjects = count($subjects);
//     $percentage = $total / ($totalSubjects * 60) * 100;
//     $grade = getGrade($percentage);

//     // Get latest row ID for update
//     $getLatest = "SELECT id FROM student_marks WHERE regId='$regId' ORDER BY id DESC LIMIT 1";
//     $getResult = mysqli_query($conn, $getLatest);
//     $latestRow = mysqli_fetch_assoc($getResult);
//     $latestId = $latestRow['id'] ?? 0;

//     if ($latestId > 0) {
//         // Build dynamic update query
//         $updateParts = [];
//         foreach ($subjects as $subject) {
//             $field = strtolower(str_replace([' ', '/', '-'], '_', $subject));
//             $value = $$field;
//             $updateParts[] = "$field=$value";
//         }

//         $updateParts[] = "total_marks=$total";
//         $updateParts[] = "percentage=$percentage";
//         $updateParts[] = "grade='$grade'";
//         $updateQuery = "UPDATE student_marks SET " . implode(', ', $updateParts) . " WHERE id=$latestId";

//         if (mysqli_query($conn, $updateQuery)) {
//             echo "<script>alert('Marks updated successfully');</script>";
//         } else {
//             echo "<script>alert('Update failed');</script>";
//         }
//     }
// }

// // Fetch data again after update
// $q = "SELECT * FROM student_marks WHERE regId='$regId' ORDER BY id DESC LIMIT 1";
// $r = mysqli_query($conn, $q);
// $data = mysqli_fetch_assoc($r);

// // Handle no data fallback
// if (!$data) {
//     $showNoRecordModal = true;
//     $data = [
//         'className' => 'N/A',
//         'photo' => './img/logo/default.png',
//         'studentName' => 'Student Not Found',
//         'rollNo' => '-',
//         'motherName' => '-',
//         'regId' => $regId,
//         'fatherName' => '-',
//         'dob' => '-',
//         'address' => '-',
//         'remarks' => 'No record available.',
//         'rank' => '-',
//     ];
// }

// // Prepare marks display
// $marks = [];
// $maxPerSubject = 60;

// foreach ($subjects as $subject) {
//     $field = strtolower(str_replace([' ', '/', '-'], '_', $subject));
//     $obtained = intval($data[$field] ?? 0);
//     $marks[] = [
//         'subject' => strtoupper($subject),
//         'half' => $obtained,
//         'term' => $obtained,
//         'total' => $obtained,
//         'grade' => getGrade($obtained)
//     ];
// }

// $totalMax = count($marks) * $maxPerSubject;
// $totalObtained = array_sum(array_column($marks, 'total'));
// $percentage = round(($totalObtained / $totalMax) * 100, 2);
// $finalGrade = getGrade($percentage);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Student MarkSheet</title>
  <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
  <link href="css/ruang-admin.min.css" rel="stylesheet">
  <link rel="icon" href="img/logo/techShell.jpg">
  <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> -->

  <style>
    body { font-family: Arial; background: #f4f4f4; padding: 1px; color: #000; }
    .report-card {
      background: #fff; width: 900px; margin: auto; padding: 20px;
      border: 2px solid #000; box-shadow: 0 0 10px rgba(0,0,0,0.15);
      position: relative; z-index: 1;
    }
    .report-card::before {
      content: ""; position: absolute; top: 53%; left: 50%;
      width: 700px; height: 700px;
      background: url('./img/logo/schoolLogo.png') no-repeat center center;
      background-size: contain; opacity: 0.1; transform: translate(-50%, -50%);
      z-index: 0;
    }
    .report-card * { position: relative; z-index: 1; }

    .header {
      width: 100%; text-align: center;
       padding-bottom: 10px;
      position: relative; margin-bottom: 20px;
    }
    .header-logo {
      position: absolute; top: 0; left: 0;
    }
    .header-logo img {
      height: 190px; margin: 10px 20px;
    }
    .header-photo {
      position: absolute; top: 0; right: 0;
    }
    .header-photo img {
      height: 190px; width: 180px; object-fit: cover;
      border: 2px solid #000; margin: 10px 20px;
      border-radius:10px
    }
    .header h1 { font-size: 28px; margin: 10px 0 5px; font-weight: bold; }
    .header p { margin: 2px 0; font-size: 14px; }
    .report-title { margin-top: 10px; }
    .report-title h4 {
      margin: 10px 0 5px; font-weight: bold; text-decoration: underline;
    }
    .report-title p { margin: 2px 0; font-size: 15px; }

    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td {
      border: 1px solid #000; padding: 6px;
      font-size: 13px; text-align: center;
    }
    .info-table td { text-align: left; font-size: 14px; }
    .marks-table th { background: #dce6f1; font-weight: bold; }
    .summary-table th,
    .summary-table td:nth-child(odd) { background: #f4b183; font-weight: bold; }
    .summary-table td:nth-child(even) { background: #fff; }
    .co-scholastic-table th { background: #d1ecf1; }
    .result-table th { background: #c3e6cb; }

    .bottom-section { display: flex; gap: 20px; margin-top: 20px; }
    .left-section { width: 60%; }
    .right-section { width: 40%; }

    .admission-box {
      border: 1px solid #000; font-size: 13px; text-align: center;
      padding: 10px; margin-top: 10px; background: #fff3cd;
    }

    .remarks {
      margin-top: 15px; font-weight: bold;
      font-size: 14px; color: #155724;
    }

    .signatures {
      display: flex; justify-content: space-between;
      margin-top: 30px; font-size: 14px;
    }
    .signatures div {
      width: 30%; text-align: center;
    }
    .signatures img {
      height: 50px; display: block; margin: 0 auto 5px;
    }

    .grade-scale th { background: #dee2e6; }
    .grade-scale td { background: #f8f9fa; }
    .grade-scale th, .grade-scale td {
      border: 1px solid #000; padding: 6px;
      font-size: 13px; text-align: center;
    }
  </style>
</head>
<!-- <body> -->
  <body id="page-top">
<div id="wrapper">
  <?php include "Includes/sidebar.php"; ?>
  <div id="content-wrapper" class="d-flex flex-column">
    <div id="content">
      <?php include "Includes/topbar.php"; ?>

      <div class="container-fluid" id="container-wrapper">
        <!-- Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
          <h1 class="h3 mb-0 text-gray-800" style="white-space: nowrap;">Student MarkSheet</h1>
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="./">Home</a></li>
            <li class="breadcrumb-item active">Student MarkSheet</li>
          </ol>
        </div>
  <div class="report-card">
    <!-- Header Section -->
    <div class="header">
      <div class="header-logo">
        <img src="./img/logo/schoolLogo.png" alt="School Logo">
      </div>
      <h1>HERITAGE DAY SCHOOL</h1>
      <p>Nagaurkra, Barasat Para, Haringhata, Nadia, WB</p>
      <p>(Affiliated to WBBSE. Code: 123456)</p>
      <p>Office: 7364916702 / 9064109172</p>
      <div class="report-title">
        <h4>Academic Report</h4>
        <!-- <p>Academic Session: <?= $data['session'] ?></p> -->
        <p>Academic Session: <?= htmlspecialchars($data['session'] ?? 'N/A') ?></p>
        <!-- <p><strong>Class: <?= $data['className'] ?></strong></p> -->
        <p><strong>Class: <?= htmlspecialchars($data['className'] ?? 'N/A') ?></strong></p>
      </div>
      <div class="header-photo">
        <img src="<?= htmlspecialchars($data['photo']  ?? './img/logo/default.png') ?>" alt="Student Photo">
      </div>
    </div>

    <!-- Student Info -->
    <table class="info-table">
      <tr><td><strong>Name of Student :</strong> <?= $data['studentName'] ?></td>
      <td><strong>Roll No. :</strong> <?= $data['rollNo'] ?></td></tr>
      <tr><td><strong>Mother's Name :</strong> <?= $data['motherName'] ?></td>
      <td><strong>Admission No :</strong> <?= $data['regId'] ?></td></tr>
      <tr><td><strong>Father's Name :</strong> <?= $data['fatherName'] ?></td>
      <td><strong>Date of Birth :</strong> <?= $data['dob'] ?></td></tr>
      <tr><td colspan="2"><strong>Address :</strong> <?= $data['address'] ?></td></tr>
    </table>

    <!-- Marks Table -->
    <!-- <table class="marks-table">
      <tr><th rowspan="2">Scholastic Areas</th>
        <th colspan="2">Term 1</th><th colspan="2">Overall</th></tr>
      <tr><th>Half Yearly</th><th>Total</th><th>Grand Total</th><th>Grade</th></tr>
      <tr><td><strong>Subject</strong></td><td><strong><?= $maxPer ?></strong></td>
        <td><strong><?= $maxPer ?></strong></td><td><strong><?= $maxPer ?></strong></td><td><strong>Grade</strong></td></tr>
      <?php foreach($marks as $r): ?>
        <tr>
          <td><?= $r['subject'] ?></td>
          <td><?= $r['half'] ?></td>
          <td><?= $r['term'] ?></td>
          <td><?= $r['total'] ?></td>
          <td><?= $r['grade'] ?></td>
        </tr>
      <?php endforeach; ?>
    </table> -->
    <table class="marks-table" border="1" cellspacing="0" cellpadding="5">
  <tr>
    <th rowspan="2">Scholastic Areas</th>
    <th colspan="2">Term 1</th>
    <th colspan="2">Overall</th>
  </tr>
  <tr>
    <th>Half Yearly</th>
    <th>Total</th>
    <th>Grand Total</th>
    <th>Grade</th>
  </tr>
  <tr>
    <td><strong>Subject</strong></td>
    <td><strong><?= $maxPerSubject ?></strong></td>
    <td><strong><?= $maxPerSubject ?></strong></td>
    <td><strong><?= $maxPerSubject * 2 ?></strong></td>
    <td><strong>Grade</strong></td>
  </tr>

  <?php foreach ($marks as $row): ?>
    <tr>
      <td><?= $row['subject'] ?></td>
      <td><?= $row['half'] ?></td>
      <td><?= $row['term'] ?></td>
      <td><?= $row['total'] ?></td>
      <td><?= $row['grade'] ?></td>
    </tr>
    <!-- <pre><?php print_r($marks); ?></pre> -->

  <?php endforeach; ?>
</table>

    <!-- Summary Table -->
    <table class="summary-table">
      <tr>
        <th>Attendance</th><td>0/160</td>
        <th>Total Marks</th><td><?= "$totalObtained / $totalMax" ?></td>
        <th>Percentage</th><td><?= $percentage.'%' ?></td>
        <th>Grade</th><td><?= $finalGrade ?></td>
      </tr>
    </table>

    <!-- Bottom Section -->
    <div class="bottom-section">
      <div class="left-section">
        <table class="co-scholastic-table">
          <tr><th colspan="2">Co-Scholastic Areas</th></tr>
          <tr><td>Discipline</td><td>Excellent</td></tr>
          <tr><td>Listening Skill</td><td>Good Listener</td></tr>
          <tr><td>Reading Skill</td><td>Fluent</td></tr>
          <tr><td>Writing Skill</td><td>Awesome</td></tr>
          <tr><td>Creative Thinking</td><td>Good Creator</td></tr>
          <tr><td>Interest</td><td>Reading</td></tr>
          <tr><td>Hobby</td><td>Swimming</td></tr>
        </table>
      </div>
      <div class="right-section">
        <table class="result-table">
          <tr><th colspan="2">Result</th></tr>
          <tr><td>Result</td><td><?= $percentage>=40?'Pass':'Fail' ?></td></tr>
          <tr><td>Percentage</td><td><?= $percentage ?>%</td></tr>
          <tr><td>Rank</td><td><?= $data['rank'] ?? '-' ?></td></tr>
        </table>
        <div class="admission-box">
          <strong>ADMISSION OPEN</strong><br>
          Play Group to Class 8th Session <?= (date('Y')+1) ?>-<?= (date('Y')+2) ?><br>
          Admission Form from 15 Dec <?= date('Y') ?><br>
          <strong>Online & Offline</strong><br>
          Contact Us: 7364916702 / 9064109172
        </div>
      </div>
    </div>

    <!-- Remarks -->
    <p class="remarks"><strong>Remarks:</strong> <?= $data['remarks'] ?? 'He has been consistently progressing.' ?></p>

    <!-- Signatures -->
    <div class="signatures">
      <div>
        <img src="./img/logo/maganer.jpeg" alt="Manager Signature">
        <em>Sign. of Manager</em>
        <hr style="border-top: 2px solid black;">
      </div>
      <div>
        <img src="./img/logo/signature.jpg" alt="Principal Signature">
        <em>Sign. Of Principal</em>
        <hr style="border-top: 2px solid black;">
      </div>
      <div>
        <img src="./img/logo/teacher.jpeg" alt="Class Teacher Signature">
        <em>Sign. of Class Teacher</em>
        <hr style="border-top: 2px solid black;">
      </div>
    </div>

    <!-- Grade Scale -->
    <p style="text-align: center; font-weight: bold; margin-top: 5px;">Instructions</p>
    <div style="margin-top:30px;">
      <p><strong>Grading scale for scholastic areas:</strong> Grades are awarded on a 8-point grading scale as follows–</p>
      <table class="grade-scale">
        <tr>
          <th>Marks Range in (%)</th>
          <td>91-100</td><td>81-90</td><td>71-80</td><td>61-70</td>
          <td>51-60</td><td>41-50</td><td>32-40</td>
        </tr>
        <tr>
          <th>Grade</th>
          <td>A+</td><td>A</td><td>B+</td><td>B</td><td>C+</td><td>C</td><td>D</td>
        </tr>
      </table>
    </div>
  </div>
  </div>
  <!-- Modal for No RegID -->
<!-- <div class="modal fade" id="noRegIdModal" tabindex="-1" aria-labelledby="noRegIdModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="noRegIdModalLabel">Error</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        RegID not found in URL or session. Please login properly.
      </div>
      <div class="modal-footer">
        <a href="login.php" class="btn btn-primary">Go to Login</a>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div> -->

<!-- Modal for No Record Found -->
<!-- <div class="modal fade" id="noRecordModal" tabindex="-1" aria-labelledby="noRecordModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="noRecordModalLabel">No Record Found</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         <button type="button" class="close" data-dismiss="modal" aria-label="Close"> Bootstrap 4
           <button type="button" class="close " data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        No student record found for RegID: <?= htmlspecialchars($regId) ?>
      </div>
      <div class="modal-footer">
        <a href="studentMarksheet.php" class="btn btn-primary">Back to Dashboard</a>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
         <button type="button" class="btn btn-secondary px-4" data-dismiss="modal">Okay</button>
      </div>
    </div>
  </div>
</div> -->

         <div class="text-center mt-4">
          <a href="studentReportCard.php?regId=<?= $data['regId']; ?>" class="btn btn-primary">
            <i class="fas fa-download"></i> Download ID Card PDF
          </a>
          <!-- <a href="?regId=<?= $regId ?>&pdf=1" class="btn btn-primary mb-3">
  <i class="fas fa-download"></i> Download Report Card PDF
</a> -->
<!-- <?php if ($showNoPdfModal): ?>
<script>
    window.onload = () => {
        const modal = new bootstrap.Modal(document.getElementById('noPdfModal'));
        modal.show();
    };
</script>
<div class="modal fade" id="noPdfModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content bg-warning">
      <div class="modal-header"><h5 class="modal-title">PDF Unavailable</h5></div>
      <div class="modal-body">No student record available to download the PDF.</div>
      <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">OK</button></div>
    </div>
  </div>
</div>
<?php endif; ?> -->

<!-- <script>
  function checkAndDownload(regId) {
    fetch('checkRecord.php?regId=' + regId)
      .then(res => res.json())
      .then(data => {
        if (data.status === 'found') {
          // Redirect if record exists
          window.location.href = 'studentReportCard.php?regId=' + regId;
        } else {
          // Show modal if record not found
          var modal = new bootstrap.Modal(document.getElementById('noRecordModal'));
          modal.show();
        }
      });
  }
</script> -->

        </div>
    <!-- Footer -->
    <div class="container-fluid px-4">
      <?php include "Includes/footer.php"; ?>
    </div>
  </div>
</div>
</div>

<a class="scroll-to-top rounded" href="#page-top"><i class="fas fa-angle-up"></i></a>
<script src="../vendor/jquery/jquery.min.js"></script>
<script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- Bootstrap 5 JS Bundle (Includes Popper) -->
<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script src="js/ruang-admin.min.js"></script>
</body>
<?php if (!empty($showNoRegIdModal) && $showNoRegIdModal): ?>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    var noRegIdModal = new bootstrap.Modal(document.getElementById('noRegIdModal'));
    noRegIdModal.show();
  });
</script>
<?php endif; ?>

<?php if (!empty($showNoRecordModal) && $showNoRecordModal): ?>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    var noRecordModal = new bootstrap.Modal(document.getElementById('noRecordModal'));
    noRecordModal.show();
  });
</script>
<?php endif; ?>

</html>

<!-- </body>
</html> -->
