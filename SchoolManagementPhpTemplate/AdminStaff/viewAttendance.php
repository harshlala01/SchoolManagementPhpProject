<?php 
error_reporting(0);
include '../Includes/dbcon.php';
include '../Includes/session.php';

if (isset($_POST['submit'])) {
    $date = $_POST['attendanceDate'];
    $classId = $_POST['classId'];
    $classSecId = $_POST['classSecId'];

    foreach ($_POST['status'] as $regId => $status) {
        // Get admission number from tblstudents using regId
        $studentQuery = mysqli_query($conn, "SELECT Id FROM tblstudents WHERE regId = '$regId'");
        $studentData = mysqli_fetch_assoc($studentQuery);

        if ($studentData) {
            $admissionNo = $studentData['Id'];

            // Check if attendance already marked for this student on that date
            $check = mysqli_query($conn, "SELECT * FROM tblattendance WHERE admissionNo = '$admissionNo' AND dateTimeTaken = '$date'");

            if (mysqli_num_rows($check) == 0) {
                // Insert attendance
                mysqli_query($conn, "INSERT INTO tblattendance (admissionNo, classId, classSecId, sessionTermId, status, dateTimeTaken) 
                    VALUES ('$admissionNo', '$classId', '$classSecId', '1', '$status', '$date')");
            }
        }
    }

    echo "<p><strong>Attendance saved successfully!</strong></p>";
}
?>




  <!DOCTYPE html>
  <html lang="en">

  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link href="img/logo/techShell.jpg" rel="icon">
    <title>Dashboard</title>
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="css/ruang-admin.min.css" rel="stylesheet">
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
              <h1 class="h3 mb-0 text-gray-800">View Class Attendance</h1>
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="./">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">View Class Attendance</li>
              </ol>
            </div>

            <div class="row">
              <div class="col-lg-12">
                <!-- Form Basic -->
                <div class="card mb-4">
                  <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">View Class Attendance</h6>
                      <?php echo $statusMsg; ?>
                  </div>
                  <div class="card-body">
                    <form method="post">
                      <div class="form-group row mb-3">
                          <div class="col-xl-6">
                          <label class="form-control-label">Select Date<span class="text-danger ml-2">*</span></label>
                              <input type="date" class="form-control" name="dateTaken" id="exampleInputFirstName" placeholder="Class Arm Name">
                          </div>
                          <div class="col-xl-6">
            <label class="form-control-label">Class <span class="text-danger">*</span></label>
            <select name="classId" id="classId" required class="form-control">
              <option value="">--Select Class--</option>
              <?php
              $qry = "SELECT * FROM tblclass ORDER BY className ASC";
              $result = $conn->query($qry);
              while ($cls = $result->fetch_assoc()) {
                $selected = ($row['classId'] ?? '') == $cls['Id'] ? 'selected' : '';
                echo "<option value='{$cls['Id']}' $selected>{$cls['className']}</option>";
              }
              ?>
            </select>
          </div>
                  <div class="col-xl-6">
      <label class="form-control-label">Class Section <span class="text-danger">*</span></label>
      <select name="classSecId" id="classSecId" required class="form-control">
        <option value="">--Select Section--</option>
        <?php
        $qrySec = "SELECT MIN(Id) as Id, classArmName FROM tblclassarms GROUP BY classArmName ORDER BY classArmName ASC";
        $resSec = $conn->query($qrySec);
        while ($sec = $resSec->fetch_assoc()) {
          $selected = ($row['classSecId'] ?? '') == $sec['Id'] ? 'selected' : '';
          echo "<option value='{$sec['Id']}' $selected>{$sec['classArmName']}</option>";
        }
        ?>
      </select>
    </div>
                          <!-- <div class="col-xl-6">
                          <label class="form-control-label">Class Arm Name<span class="text-danger ml-2">*</span></label>
                        <input type="text" class="form-control" name="classArmName" value="<?php echo $row['classArmName'];?>" id="exampleInputFirstName" placeholder="Class Arm Name">
                          </div> -->
                      </div>
                      <button type="submit" name="view" class="btn btn-primary">View Attendance</button>
                    </form>
                  </div>
                </div>

                <!-- Input Group -->
            <div class="row">
  <div class="col-lg-12">
    <div class="card mb-4">
      <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Class Attendance</h6>
      </div>
      <div class="table-responsive p-3">
        <table class="table align-items-center table-flush table-hover" id="dataTableHover">
          <thead class="thead-light">
            <tr>
              <th>#</th>
              <th>First Name</th>
              <th>Admission No</th>
              <th>Class</th>
              <th>Class Arm</th>
              <th>Session</th>
              <th>Term</th>
              <th>Status</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <?php
            if (isset($_POST['view'])) {
                $dateTaken = $_POST['dateTaken'];
                $classId = $_POST['classId'];
                $classSecId = $_POST['classSecId'];

                $query = "
                    SELECT 
                        s.regId,
                        s.studentName,
                        c.className,
                        ca.classArmName,
                        a.status,
                        a.dateTimeTaken,
                        st.sessionName,
                        t.termName
                    FROM tblstudents s
                    LEFT JOIN tblattendance a
                        ON a.admissionNo = s.regId
                        AND DATE(a.dateTimeTaken) = '$dateTaken'
                        AND a.classId = '$classId'
                        AND a.classSecId = '$classSecId'
                    LEFT JOIN tblclass c ON c.Id = s.classId
                    LEFT JOIN tblclassarms ca ON ca.Id = s.classSecId
                    LEFT JOIN tblsessionterm st ON st.Id = a.sessionTermId
                    LEFT JOIN tblterm t ON t.Id = st.termId
                    WHERE s.classId = '$classId' AND s.classSecId = '$classSecId'
                    ORDER BY s.studentName ASC
                ";

                $result = $conn->query($query);

                if ($result->num_rows > 0) {
                    $sn = 0;
                    while ($row = $result->fetch_assoc()) {
                        $sn++;
                        if ($row['status'] === '1') {
                            $status = "Present"; $colour = "#00FF00";
                        } else if ($row['status'] === '0') {
                            $status = "Absent"; $colour = "#FF0000";
                        } else {
                            $status = "Not Taken"; $colour = "#CCCCCC";
                        }
                        echo "
                        <tr>
                            <td>$sn</td>
                            <td>".htmlspecialchars($row['studentName'])."</td>
                            <td>".htmlspecialchars($row['regId'])."</td>
                            <td>".htmlspecialchars($row['className'])."</td>
                            <td>".htmlspecialchars($row['classArmName'])."</td>
                            <td>".htmlspecialchars($row['sessionName'])."</td>
                            <td>".htmlspecialchars($row['termName'])."</td>
                            <td style='background-color: $colour;'>$status</td>
                            <td>".htmlspecialchars($row['dateTimeTaken'])."</td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='9' class='text-center text-danger'>No records found.</td></tr>";
                }
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

            </div>
            <!--Row-->

            <!-- Documentation Link -->
            <!-- <div class="row">
              <div class="col-lg-12 text-center">
                <p>For more documentations you can visit<a href="https://getbootstrap.com/docs/4.3/components/forms/"
                    target="_blank">
                    bootstrap forms documentations.</a> and <a
                    href="https://getbootstrap.com/docs/4.3/components/input-group/" target="_blank">bootstrap input
                    groups documentations</a></p>
              </div>
            </div> -->

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
        $('#dataTable').DataTable(); // ID From dataTable 
        $('#dataTableHover').DataTable(); // ID From dataTable with Hover
      });
    </script>
  </body>

  </html>