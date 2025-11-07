  <?php 
  error_reporting(E_ALL);
  ini_set('display_errors', 1);
  $statusMsg = ''; 
  include '../Includes/dbcon.php';
  include '../Includes/session.php';

  $_SESSION['classId'];
  $_SESSION['classSecId'];

  ?>

  <!DOCTYPE html>
  <html lang="en">

  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link href="img/logo/attnlg.jpg" rel="icon">
    <title>Dashboard</title>
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="css/ruang-admin.min.css" rel="stylesheet">

  <!-- <script>
      function typeDropDown(str) {
      if (str == "") {
          document.getElementById("txtHint").innerHTML = "";
          return;
      } else { 
          if (window.XMLHttpRequest) {
              // code for IE7+, Firefox, Chrome, Opera, Safari
              xmlhttp = new XMLHttpRequest();
          } else {
              // code for IE6, IE5
              xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
          }
          xmlhttp.onreadystatechange = function() {
              if (this.readyState == 4 && this.status == 200) {
                  document.getElementById("txtHint").innerHTML = this.responseText;
              }
          };
          xmlhttp.open("GET","ajaxCallTypes.php?tid="+str,true);
          xmlhttp.send();
      }
  }
  </script> -->
 <script>
    function typeDropDown(str) {
      var container = document.getElementById("dynamicInputs");
      container.innerHTML = ''; // Clear previous inputs

      if (str == "2") {
        // Single Date Input
        container.innerHTML = `
          <div class="form-group row mb-3">
            <label class="col-sm-3 col-form-label">Select Date <span class="text-danger">*</span></label>
            <div class="col-sm-6">
              <input type="date" name="singleDate" class="form-control" required>
            </div>
          </div>
        `;
      } else if (str == "3") {
        // Date Range Inputs
        container.innerHTML = `
          <div class="form-group row mb-3">
            <label class="col-sm-3 col-form-label">From Date <span class="text-danger">*</span></label>
            <div class="col-sm-3">
              <input type="date" name="fromDate" class="form-control" required>
            </div>
            <label class="col-sm-1 col-form-label">To Date <span class="text-danger">*</span></label>
            <div class="col-sm-3">
              <input type="date" name="toDate" class="form-control" required>
            </div>
          </div>
        `;
      }
      // For "1" or empty, no extra inputs needed
    }
  </script>
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
              <h1 class="h3 mb-0 text-gray-800">View Student Attendance</h1>
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="./">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">View Student Attendance</li>
              </ol>
            </div>

            <div class="row">
              <div class="col-lg-12">
                <!-- Form Basic -->
                <div class="card mb-4">
                  <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">View Student Attendance</h6>
                      <?php echo $statusMsg; ?>
                  </div>
                  <div class="card-body">
                    <form method="post">

                      <div class="form-group row mb-3">
                          <div class="col-xl-6">
                          <label class="form-control-label">Select Student<span class="text-danger ml-2">*</span></label>
                          <?php
                          // Fetch all students ordered by name
                          $qry= "SELECT * FROM tblstudents ORDER BY studentName ASC";
                          $result = $conn->query($qry);
                          $num = $result->num_rows;		
                          if ($num > 0){
                            echo ' <select required name="regId" class="form-control mb-3">';
                            echo'<option value="">--Select Student--</option>';
                            while ($rows = $result->fetch_assoc()){
                              echo '<option value="'.$rows['regId'].'">'.$rows['studentName'].'</option>';
                            }
                            echo '</select>';
                          }
                          ?>  
                          </div>
                          <div class="col-xl-6">
                          <label class="form-control-label">Type<span class="text-danger ml-2">*</span></label>
                            <select required name="type" onchange="typeDropDown(this.value)" class="form-control mb-3">
                            <option value="">--Select--</option>
                            <option value="1" >All</option>
                            <option value="2" >By Single Date</option>
                            <option value="3" >By Date Range</option>
                          </select>
                          </div>
                      </div>
                        <?php
                          echo"<div id='txtHint'></div>";
                        ?>
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
                          <th>Student Name</th>
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

                      if(isset($_POST['view'])){

                        $admissionNumber =  $_POST['regId'];
                        $type =  $_POST['type'];
  echo "<pre>";
  print_r($_POST);
  echo "</pre>";

                        $query = ''; // initialize query variable

                        // Check if session variables are set
                        if (!isset($_SESSION['classId']) || !isset($_SESSION['classSecId'])) {
                          echo "<div class='alert alert-danger'>Class or Class Section session not set.</div>";
                        } else {

                            if($type == "1"){ // All Attendance
                              $query = "SELECT tblattendance.Id, tblattendance.status, tblattendance.dateTimeTaken, tblclass.className,
                              tblclassarms.classArmName, tblsessionterm.sessionName, tblsessionterm.termId, tblterm.termName,
                              tblstudents.studentName, tblstudents.regId AS admissionNumber
                              FROM tblattendance
                              INNER JOIN tblclass ON tblclass.Id = tblattendance.classId
                              INNER JOIN tblclassarms ON tblclassarms.Id = tblattendance.classSecId
                              INNER JOIN tblsessionterm ON tblsessionterm.Id = tblattendance.sessionTermId
                              INNER JOIN tblterm ON tblterm.Id = tblsessionterm.termId
                              INNER JOIN tblstudents ON tblstudents.regId = tblattendance.admissionNo
                              WHERE tblattendance.admissionNo = '$admissionNumber' 
                              AND tblattendance.classId = '$_SESSION[classId]' 
                              AND tblattendance.classSecId = '$_SESSION[classSecId]'";
                            }
                            else if($type == "2"){ // Single Date Attendance
                              if (isset($_POST['singleDate']) && !empty($_POST['singleDate'])) {
                                $singleDate = $_POST['singleDate'];
                                $query = "SELECT tblattendance.Id, tblattendance.status, tblattendance.dateTimeTaken, tblclass.className,
                                tblclassarms.classArmName, tblsessionterm.sessionName, tblsessionterm.termId, tblterm.termName,
                                tblstudents.studentName, tblstudents.regId AS admissionNumber
                                FROM tblattendance
                                INNER JOIN tblclass ON tblclass.Id = tblattendance.classId
                                INNER JOIN tblclassarms ON tblclassarms.Id = tblattendance.classSecId
                                INNER JOIN tblsessionterm ON tblsessionterm.Id = tblattendance.sessionTermId
                                INNER JOIN tblterm ON tblterm.Id = tblsessionterm.termId
                                INNER JOIN tblstudents ON tblstudents.regId = tblattendance.admissionNo
                                -- WHERE tblattendance.dateTimeTaken = '$singleDate'
                                WHERE DATE(tblattendance.dateTimeTaken) = '$singleDate' 
                                AND tblattendance.admissionNo = '$admissionNumber' 
                                AND tblattendance.classId = '$_SESSION[classId]' 
                                AND tblattendance.classSecId = '$_SESSION[classSecId]'";
                              } else {
                                  echo "<div class='alert alert-danger'>Please select a date.</div>";
                              }
                            }
                            else if($type == "3"){ // Date Range Attendance
                              if(isset($_POST['fromDate'], $_POST['toDate']) && !empty($_POST['fromDate']) && !empty($_POST['toDate'])){
                                $fromDate = $_POST['fromDate'];
                                $toDate = $_POST['toDate'];
                                $query = "SELECT tblattendance.Id, tblattendance.status, tblattendance.dateTimeTaken, tblclass.className,
                                tblclassarms.classArmName, tblsessionterm.sessionName, tblsessionterm.termId, tblterm.termName,
                                tblstudents.studentName, tblstudents.regId AS admissionNumber
                                FROM tblattendance
                                INNER JOIN tblclass ON tblclass.Id = tblattendance.classId
                                INNER JOIN tblclassarms ON tblclassarms.Id = tblattendance.classSecId
                                INNER JOIN tblsessionterm ON tblsessionterm.Id = tblattendance.sessionTermId
                                INNER JOIN tblterm ON tblterm.Id = tblsessionterm.termId
                                INNER JOIN tblstudents ON tblstudents.regId = tblattendance.admissionNo
                                -- WHERE tblattendance.dateTimeTaken BETWEEN '$fromDate' AND '$toDate' 
                                WHERE DATE(tblattendance.dateTimeTaken) BETWEEN '$fromDate' AND '$toDate'

                                AND tblattendance.admissionNo = '$admissionNumber' 
                                AND tblattendance.classId = '$_SESSION[classId]' 
                                AND tblattendance.classSecId = '$_SESSION[classSecId]'";
                              }
                              else {
                                  echo "<div class='alert alert-danger'>Please select both From and To dates.</div>";
                              }
                            }
                            else {
                              echo "<div class='alert alert-danger'>Please select a valid type.</div>";
                            }

                            // Only run query if it is not empty
                            if(!empty($query)) {
                              $rs = $conn->query($query);
                              if($rs) {
                                $num = $rs->num_rows;
                                $sn = 0;
                                if($num > 0) { 
                                  while ($rows = $rs->fetch_assoc()) {
                                    $status = ($rows['status'] == '1') ? "Present" : "Absent";
                                    $colour = ($rows['status'] == '1') ? "#00FF00" : "#FF0000";
                                    $sn++;
                                    echo "
                                      <tr>
                                        <td>".$sn."</td>
                                        <td>".$rows['studentName']."</td>
                                        <td>".$rows['admissionNumber']."</td>
                                        <td>".$rows['className']."</td>
                                        <td>".$rows['classArmName']."</td>
                                        <td>".$rows['sessionName']."</td>
                                        <td>".$rows['termName']."</td>
                                        <td style='background-color:".$colour."'>".$status."</td>
                                        <td>".$rows['dateTimeTaken']."</td>
                                      </tr>";
                                  }
                                } else {
                                  echo "<tr><td colspan='9'><div class='alert alert-danger' role='alert'>No Record Found!</div></td></tr>";
                                }
                              } else {
                                echo "<tr><td colspan='9'><div class='alert alert-danger' role='alert'>Error executing query.</div></td></tr>";
                              }
                            }

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
