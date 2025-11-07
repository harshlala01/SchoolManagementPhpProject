<?php
// session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../Includes/dbcon.php';
include '../Includes/session.php';

// Check if user is logged in
$userId = $_SESSION['userId'] ?? null;
if (!$userId) {
    die("Session expired or user not logged in.");
}

// Step 1: Load or update class and section in session on form submit
if (isset($_POST['classId'])) {
    $_SESSION['classId'] = $_POST['classId'];
}
if (isset($_POST['classSecId'])) {
    $_SESSION['classSecId'] = $_POST['classSecId'];
}


// If session vars not set, try to get from student record
if (!isset($_SESSION['classId']) || !isset($_SESSION['classSecId'])) {
    $result = mysqli_query($conn, "SELECT classId, classSecId FROM tblstudents WHERE Id = '$userId' LIMIT 1");
    if ($row = mysqli_fetch_assoc($result)) {
        $_SESSION['classId'] = $row['classId'];
        $_SESSION['classSecId'] = $row['classSecId'];
    } else {
        die("Unable to find student class and section.");
    }
}
$classId = $_SESSION['classId'] ?? '';
$classSecId = $_SESSION['classSecId'] ?? '';
// echo "ClassId: $classId, ClassSecId: $classSecId<br>";

// Step 2: Get current active sessionTermId (assuming isActive='0' means active)
$sessionTermQuery = mysqli_query($conn, "SELECT Id FROM tblsessionterm WHERE isActive = '0' LIMIT 1");
$sessionTermRow = mysqli_fetch_assoc($sessionTermQuery);
$sessionTermId = $sessionTermRow['Id'] ?? 0;

// Step 3: Insert attendance records for today if none exist yet
$dateTaken = date("Y-m-d");
if ($classId && $classSecId) {
    $attendanceCheckQuery = mysqli_query($conn, "SELECT * FROM tblattendance WHERE classId = '$classId' AND classSecId = '$classSecId' AND dateTimeTaken = '$dateTaken'");
    if (mysqli_num_rows($attendanceCheckQuery) == 0) {
        $studentsQuery = mysqli_query($conn, "SELECT regId FROM tblstudents WHERE classId = '$classId' AND classSecId = '$classSecId'");
        while ($student = mysqli_fetch_assoc($studentsQuery)) {
            $regId = $student['regId'];
            mysqli_query($conn, "INSERT INTO tblattendance (admissionNo, classId, classSecId, sessionTermId, status, dateTimeTaken) VALUES ('$regId', '$classId', '$classSecId', '$sessionTermId', '0', '$dateTaken')");
        }
    }
}

// Step 4: Handle attendance submission
$statusMsg = "";
if (isset($_POST['save'])) {
    $admissionNo = $_POST['admissionNo'] ?? [];
    $check = $_POST['check'] ?? [];
    $N = count($admissionNo);

    // Check if attendance is already taken (any status=1 record exists)
    $alreadyTakenQuery = mysqli_query($conn, "SELECT * FROM tblattendance WHERE classId = '$classId' AND classSecId = '$classSecId' AND dateTimeTaken = '$dateTaken' AND status = '1'");
    if (mysqli_num_rows($alreadyTakenQuery) > 0) {
        $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>Attendance has already been taken for today!</div>";
    } else {
        // Update status=1 for checked students, 0 for unchecked
        for ($i = 0; $i < $N; $i++) {
            $regId = $admissionNo[$i];
            $stat = in_array($regId, $check) ? 1 : 0;
            $update = mysqli_query($conn, "UPDATE tblattendance SET status = '$stat' WHERE admissionNo = '$regId' AND classId = '$classId' AND classSecId = '$classSecId' AND dateTimeTaken = '$dateTaken'");
            if (!$update) {
                $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>Error updating attendance for student $regId!</div>";
                break;
            }
        }
        if (empty($statusMsg)) {
            $statusMsg = "<div class='alert alert-success' style='margin-right:700px;'>Attendance taken successfully!</div>";
        }
    }
}

// Step 5: Fetch students with class and section names
$studentsResult = [];
if ($classId && $classSecId) {
    $query = "
        SELECT 
            s.*, 
            c.className, 
            ca.classArmName 
        FROM tblstudents s
        LEFT JOIN tblclass c ON s.classId = c.Id
        LEFT JOIN tblclassarms ca ON s.classSecId = ca.Id
        WHERE s.classId = '$classId' AND s.classSecId = '$classSecId'
        ORDER BY s.studentName ASC
    ";
    $studentsResult = mysqli_query($conn, $query);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <title>Take Attendance</title>
  <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" />
  <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
  <link href="css/ruang-admin.min.css" rel="stylesheet" />
  <link href="img/logo/attnlg.jpg" rel="icon" />

  <script>
    function loadSections(classId) {
      if (classId == "") {
        document.getElementById("classSecId").innerHTML = "<option value=''>--Select Section--</option>";
        return;
      }
      var xhr = new XMLHttpRequest();
      xhr.open("GET", "ajaxClassArms2.php?cid=" + classId, true);
      xhr.onload = function () {
        if (this.status == 200) {
          document.getElementById("classSecId").innerHTML = this.responseText;
        }
      };
      xhr.send();
    }
  </script>
</head>

<body id="page-top">
  <div id="wrapper">
    <?php include "Includes/sidebar.php"; ?>
    <div id="content-wrapper" class="d-flex flex-column">
      <div id="content">
        <?php include "Includes/topbar.php"; ?>

        <div class="container-fluid" id="container-wrapper">
          <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Take Attendance (Today's Date: <?php echo date("d-m-Y"); ?>)</h1>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="./">Home</a></li>
              <li class="breadcrumb-item active" aria-current="page">Attendance</li>
            </ol>
          </div>

          <form method="post" id="attendanceForm">
            <div class="row mb-3">
              <div class="col-md-6">
                <label for="classId" class="form-control-label">Class <span class="text-danger">*</span></label>
                <select name="classId" id="classId" required class="form-control" onchange="loadSections(this.value); this.form.submit();">
                  <option value="">--Select Class--</option>
                  <?php
                  $qry = "SELECT * FROM tblclass ORDER BY className ASC";
                  $result = $conn->query($qry);
                  while ($cls = $result->fetch_assoc()) {
                      $selected = ($classId == $cls['Id']) ? "selected" : "";
                      echo "<option value='{$cls['Id']}' $selected>{$cls['className']}</option>";
                  }
                  ?>
                </select>
              </div>
              <div class="col-md-6">
                <label for="classSecId" class="form-control-label">Class Section <span class="text-danger">*</span></label>
                <select name="classSecId" id="classSecId" required class="form-control" onchange="this.form.submit()">
                  <option value="">--Select Section--</option>
                  <?php
                  if ($classId) {
                      // $qrySec = "SELECT * FROM tblclassarms WHERE classId = '$classId' ORDER BY classArmName ASC";
                            // $qrySec = "SELECT MIN(Id) as Id, classArmName FROM tblclassarms GROUP BY classArmName ORDER BY classArmName ASC";
                            $qrySec = "SELECT  Id, classArmName FROM tblclassarms GROUP BY classArmName ORDER BY classArmName ASC";
                      $resSec = $conn->query($qrySec);
                      while ($sec = $resSec->fetch_assoc()) {
                          $selected = ($classSecId == $sec['Id']) ? "selected" : "";
                          echo "<option value='{$sec['Id']}' $selected>{$sec['classArmName']}</option>";
                      }
                  }
                  
                  ?>
                </select>
              </div>
            </div>

            <div class="table-responsive p-3">
              <?php echo $statusMsg; ?>
              <table class="table table-bordered table-hover">
                <thead class="thead-light">
                  <tr>
                    <th>#</th>
                    <th>Student Name</th>
                    <th>Admission No</th>
                    <th>Class</th>
                    <th>Section</th>
                    <th>Present</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  if ($studentsResult && mysqli_num_rows($studentsResult) > 0) {
                      $sn = 0;
                      while ($row = mysqli_fetch_assoc($studentsResult)) {
                          $sn++;
                          echo "<tr>";
                          echo "<td>" . $sn . "</td>";
                          echo "<td>" . htmlspecialchars($row['studentName']) . "</td>";
                          echo "<td>" . htmlspecialchars($row['regId']) . "</td>";
                          echo "<td>" . htmlspecialchars($row['className']) . "</td>";
                          echo "<td>" . htmlspecialchars($row['classArmName']) . "</td>";
                          echo "<td><input type='checkbox' name='check[]' value='" . $row['regId'] . "' class='form-control'></td>";
                          echo "</tr>";
                          echo "<input type='hidden' name='admissionNo[]' value='" . $row['regId'] . "' />";
                      }
                  } else {
                      echo "<tr><td colspan='6' class='text-center text-danger'>No students found for selected Class and Section.</td></tr>";
                  }
                  ?>
                </tbody>
              </table>
            </div>

            <button type="submit" name="save" class="btn btn-primary">Take Attendance</button>
          </form>
        </div>

        <?php include "Includes/footer.php"; ?>
      </div>
    </div>
  </div>

  <script src="../vendor/jquery/jquery.min.js"></script>
  <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
  <script src="js/ruang-admin.min.js"></script>
</body>

</html>


