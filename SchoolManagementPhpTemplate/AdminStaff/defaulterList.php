<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// include '../Includes/dbcon.php';
// include '../Includes/session.php';

// // Helper for safe output
// function esc($s) {
//     return htmlspecialchars($s, ENT_QUOTES);
// }

// // Get posted values
// $classId    = $_POST['classId']    ?? '';
// $classSecId = $_POST['classSecId'] ?? '';
// $criteria   = $_POST['criteria']   ?? '';

// $results    = [];
// $notFound   = false;

// if ($classId && $classSecId && $criteria) {
//     // Step 1: Check students exist or not
//     $check = $conn->prepare("SELECT COUNT(*) AS cnt FROM tblstudents WHERE classId = ? AND classSecId = ?");
//     $check->bind_param("ii", $classId, $classSecId);
//     $check->execute();
//     $checkRes = $check->get_result()->fetch_assoc();
//     $totalStudents = $checkRes['cnt'];

//     if ($totalStudents == 0) {
//         $notFound = true;
//     } else {
//         // Step 2: Fetch attendance summary
//         $sql = "
//             SELECT s.regId, s.studentName,
//                 IFNULL(SUM(CASE WHEN a.status = '1' THEN 1 ELSE 0 END), 0) AS presentDays,
//                 IFNULL(SUM(CASE WHEN a.status = '0' THEN 1 ELSE 0 END), 0) AS absentDays,
//                 COUNT(a.Id) AS totalDays
//             FROM tblstudents s
//             LEFT JOIN tblattendance a 
//               ON s.regId = a.admissionNo
//              AND s.classId = a.classId 
//              AND s.classSecId = a.classSecId
//             WHERE s.classId = ? AND s.classSecId = ?
//             GROUP BY s.regId, s.studentName
//         ";

//         $stmt = $conn->prepare($sql);
//         $stmt->bind_param("ii", $classId, $classSecId);
//         $stmt->execute();
//         $res = $stmt->get_result();

//         while ($row = $res->fetch_assoc()) {
//             $present = $row['presentDays'];
//             $total   = $row['totalDays'];
//             $percent = $total > 0 ? round(($present/$total)*100,2) : 0;

//             // Status text
//             // $statusText  = ($percent >= $criteria) ? "Eligible" : "Default";
//             $statusText  = ($percent >= $criteria) ? "Promoted" : "Default";

//             // Status color
//             if ($percent < 60) {
//                 $status = "<span style='color:red;'>$statusText</span>";
//             } else {
//                 $status = "<span style='color:green;'>$statusText</span>";
//             }

//             $results[] = [
//                 'admissionNo' => $row['regId'],
//                 'studentName' => $row['studentName'],
//                 'percent'     => $percent,
//                 'status'      => $status
//             ];
//         }
//     }
// }
?>

  <?php
  error_reporting(E_ALL);
  ini_set('display_errors', 1);

  include '../Includes/dbcon.php';
  include '../Includes/session.php';

  // Helper for safe output
  function esc($s) {
      return htmlspecialchars($s, ENT_QUOTES);
  }

  // Get posted values
  $classId    = $_POST['classId']    ?? '';
  $classSecId = $_POST['classSecId'] ?? '';
  $criteria   = $_POST['criteria']   ?? '';
  $startDate  = $_POST['startDate']  ?? '';
  $endDate    = $_POST['endDate']    ?? '';

  $results    = [];
  $notFound   = false;

  if ($classId && $classSecId && $criteria && $startDate && $endDate) {
      // Step 1: Check students exist or not
      $check = $conn->prepare("SELECT COUNT(*) AS cnt FROM tblstudents WHERE classId = ? AND classSecId = ?");
      $check->bind_param("ii", $classId, $classSecId);
      $check->execute();
      $checkRes = $check->get_result()->fetch_assoc();
      $totalStudents = $checkRes['cnt'];

      if ($totalStudents == 0) {
          $notFound = true;
      } else {
          // Step 2: Fetch attendance summary (with date filter)
          $sql = "
              SELECT s.regId, s.studentName,
                  IFNULL(SUM(CASE WHEN a.status = '1' THEN 1 ELSE 0 END), 0) AS presentDays,
                  IFNULL(SUM(CASE WHEN a.status = '0' THEN 1 ELSE 0 END), 0) AS absentDays,
                  COUNT(a.Id) AS totalDays
              FROM tblstudents s
              LEFT JOIN tblattendance a 
                ON s.regId = a.admissionNo
              AND s.classId = a.classId 
              AND s.classSecId = a.classSecId
              AND a.dateTimeTaken BETWEEN ? AND ?
              WHERE s.classId = ? AND s.classSecId = ?
              GROUP BY s.regId, s.studentName
          ";

          $stmt = $conn->prepare($sql);
          $stmt->bind_param("ssii", $startDate, $endDate, $classId, $classSecId);
          $stmt->execute();
          $res = $stmt->get_result();

        while ($row = $res->fetch_assoc()) {
      $present = $row['presentDays'];
      $total   = $row['totalDays'];
      $percent = $total > 0 ? round(($present/$total)*100,2) : 0;

      // Status check (0%–65% = Default, above 65% = Promoted)
      if ($percent <= 65) {
          $statusText = "Default";
          $status = "<span style='color:red;'>$statusText</span>";
      } else {
          $statusText = "Promoted";
          $status = "<span style='color:green;'>$statusText</span>";
      }

      $results[] = [
          'admissionNo' => $row['regId'],
          'studentName' => $row['studentName'],
          'percent'     => $percent,
          'status'      => $status
      ];
  }

      }
  }
  ?>

  <!DOCTYPE html>
  <html lang="en">
  <head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <title>Attendance Defaulter List</title>
  <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" />
  <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
  <link href="css/ruang-admin.min.css" rel="stylesheet" />
  <link href="img/logo/attnlg.jpg" rel="icon" />
  </head>

  <body id="page-top">
  <div id="wrapper">
    <?php include "Includes/sidebar.php"; ?>
    <div id="content-wrapper" class="d-flex flex-column">
      <div id="content">
        <?php include "Includes/topbar.php"; ?>

        <div class="container-fluid" id="container-wrapper">
          <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Attendance Defaulter List</h1>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="./">Home</a></li>
              <li class="breadcrumb-item active" aria-current="page">Attendance</li>
            </ol>
          </div>

          <!-- Card -->
          <div class="card shadow mb-4">
            <div class="card-header bg-primary text-white">
              <h4 class="mb-0">Filter Criteria</h4>
            </div>
            <div class="card-body">
              <form method="post" id="filterForm" class="row g-3 align-items-end">

                <!-- Class -->
                <div class="col-md-2">
                  <label for="classId" class="form-label">Class <span class="text-danger">*</span></label>
                  <select name="classId" id="classId" class="form-select custom-select" required>
                    <option value="">-- Select Class --</option>
                    <?php
                    $qry = "SELECT * FROM tblclass ORDER BY className ASC";
                    $result = $conn->query($qry);
                    while ($cls = $result->fetch_assoc()) {
                        $selected = ($classId == $cls['Id']) ? "selected" : "";
                        echo "<option value='" . esc($cls['Id']) . "' $selected>" . esc($cls['className']) . "</option>";
                    }
                    ?>
                  </select>
                </div>

                <!-- Section -->
                <div class="col-md-2">
                  <label for="classSecId" class="form-label">Section <span class="text-danger">*</span></label>
                  <select name="classSecId" id="classSecId" class="form-select custom-select" required>
                    <option value="">-- Select Section --</option>
                    <?php
                    $qrySec = "SELECT Id, classArmName FROM tblclassarms GROUP BY classArmName ORDER BY classArmName ASC";
                    $resSec = $conn->query($qrySec);
                    while ($sec = $resSec->fetch_assoc()) {
                        $selected = ($classSecId == $sec['Id']) ? 'selected' : '';
                        echo "<option value='" . esc($sec['Id']) . "' $selected>" . esc($sec['classArmName']) . "</option>";
                    }
                    ?>
                  </select>
                </div>


                <!-- Start Date -->
                <div class="col-md-2">
                  <label for="startDate" class="form-label">Start Date<span class="text-danger">*</span></label>
                  <input type="date" name="startDate" id="startDate" class="form-control" value="<?=esc($startDate)?>" required>
                </div>

                <!-- End Date -->
                <div class="col-md-2">
                  <label for="endDate" class="form-label">End Date<span class="text-danger">*</span></label>
                  <input type="date" name="endDate" id="endDate" class="form-control" value="<?=esc($endDate)?>" required>
                </div>


                <!-- Criteria -->
                <div class="col-md-2">
                  <label for="criteria" class="form-label">Criteria <span class="text-danger">*</span></label>
                  <select name="criteria" id="criteria" class="form-select custom-select" required>
                    <option value="">-- Select Criteria --</option>
                    <?php 
                    $criteriaOptions = [50,60,65,70,75,80,90,100];
                    foreach ($criteriaOptions as $opt) {
                        $sel = ($criteria == $opt) ? 'selected' : '';
                        echo "<option value='$opt' $sel>$opt% Minimum</option>";
                    }
                    ?>
                  </select>
                </div>

                <div class="col-md-2">
                  <button type="submit" class="btn btn-success w-100">Check Attendance</button>
                </div>
              </form>
            </div>
          </div>

          <!-- Results -->
          <?php if ($notFound): ?>
              <div class="alert alert-danger">⚠️ No students found for this Class & Section!</div>
          <?php elseif ($results): ?>
          <div class="card shadow">
            <div class="card-header bg-dark text-white">
              <h5 class="mb-0">Result (Criteria ≥ <?= esc($criteria) ?>%)</h5>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-bordered table-striped">
                  <thead class="table-dark">
                    <tr>
                      <th>Admission No</th>
                      <th>Student Name</th>
                      <th>Attendance %</th>
                      <th>Status</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($results as $r): ?>
                    <tr>
                      <td><?= esc($r['admissionNo']) ?></td>
                      <td><?= esc($r['studentName']) ?></td>
                      <td><?= $r['percent'] ?>%</td>
                      <td><?= $r['status'] ?></td>
                      <td>
                       <a href="defaulterDownload_excel.php?regId=<?= $r['admissionNo'] ?>&start=<?= $startDate ?>&end=<?= $endDate ?>" class="btn btn-sm btn-success mb-1">Excel</a>
                       <a href="defaulterDownload_pdf.php?regId=<?= $r['admissionNo'] ?>&start=<?= $startDate ?>&end=<?= $endDate ?>" class="btn btn-sm btn-danger">PDF</a>
                      </td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          <?php endif; ?>

        </div>
      </div>

      <?php include "Includes/footer.php"; ?>
    </div>
  </div>

  <script src="../vendor/jquery/jquery.min.js"></script>
  <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
  <script src="js/ruang-admin.min.js"></script>
  </body>
  </html>
