<?php
session_start();
include '../Includes/dbcon.php';

// Check session variables
if (!isset($_SESSION['classId']) || !isset($_SESSION['classSecId'])) {
    echo "<div class='alert alert-danger'>Session variables missing. Please login again.</div>";
    exit;
}

$classId = $_SESSION['classId'];
$classSecId = $_SESSION['classSecId'];

// Query to get students of the selected class and section
$queryStudents = "SELECT Id, studentName, regId, className, classArmName
                  FROM tblstudents
                  WHERE classId = '$classId' AND classSecId = '$classSecId'";

$rsStudents = $conn->query($queryStudents);
if (!$rsStudents) {
    echo "<div class='alert alert-danger'>SQL Error: " . $conn->error . "</div>";
    exit;
}

// Query to get class and class arm names for heading
$queryClassInfo = "SELECT c.className, ca.classArmName
                   FROM tblclass c
                   INNER JOIN tblclassarms ca ON c.Id = ca.classId
                   WHERE c.Id = '$classId' AND ca.Id = '$classSecId'";

$rsClassInfo = $conn->query($queryClassInfo);
$className = "";
$classArmName = "";

if ($rsClassInfo && $rsClassInfo->num_rows > 0) {
    $rowClassInfo = $rsClassInfo->fetch_assoc();
    $className = $rowClassInfo['className'];
    $classArmName = $rowClassInfo['classArmName'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <title>Dashboard</title>
  <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css" />
  <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
  <link href="css/ruang-admin.min.css" rel="stylesheet" />
</head>

<body id="page-top">
  <div id="wrapper">
    <!-- Sidebar -->
    <?php include "Includes/sidebar.php";?>
    <!-- Sidebar -->

    <div id="content-wrapper" class="d-flex flex-column">
      <div id="content">
        <!-- TopBar -->
        <?php include "Includes/topbar.php";?>
        <!-- Topbar -->

        <!-- Container Fluid-->
        <div class="container-fluid" id="container-wrapper">
          <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">
              All Students in Class <?php echo htmlspecialchars($className) . " - Section " . htmlspecialchars($classArmName); ?>
            </h1>

            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="./">Home</a></li>
              <li class="breadcrumb-item active" aria-current="page">All Students in Class</li>
            </ol>
          </div>

          <div class="row">
            <div class="col-lg-12">
              <div class="card mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                  <h6 class="m-0 font-weight-bold text-primary">All Students In Class</h6>
                </div>
                <div class="table-responsive p-3">
                  <table class="table align-items-center table-flush table-hover" id="dataTableHover">
                    <thead class="thead-light">
                      <tr>
                        <th>#</th>
                        <th>Student Name</th>
                        <th>Admission No</th>
                        <th>Class</th>
                        <th>Class Arm</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      if ($rsStudents->num_rows > 0) {
                          $sn = 0;
                          while ($row = $rsStudents->fetch_assoc()) {
                              $sn++;
                              echo "<tr>
                                      <td>{$sn}</td>
                                      <td>" . htmlspecialchars($row['studentName']) . "</td>
                                      <td>" . htmlspecialchars($row['regId']) . "</td>
                                      <td>" . htmlspecialchars($row['className']) . "</td>
                                      <td>" . htmlspecialchars($row['classArmName']) . "</td>
                                    </tr>";
                          }
                      } else {
                          echo "<tr><td colspan='5'><div class='alert alert-danger text-center'>No Record Found!</div></td></tr>";
                      }
                      ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
          <!--Row-->
        </div>
        <!---Container Fluid-->
      </div>
      <!-- Footer -->
      <?php include "Includes/footer.php";?>
      <!-- Footer -->
    </div>
  </div>

  <!-- Scroll to top -->
  <a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
  </a>

  <script src="../vendor/jquery/jquery.min.js"></script>
  <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
  <script src="js/ruang-admin.min.js"></script>
  <!-- Page level plugins -->
  <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
  <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>

  <!-- Page level custom scripts -->
  <script>
    $(document).ready(function () {
      $('#dataTableHover').DataTable();
    });
  </script>
</body>
</html>
