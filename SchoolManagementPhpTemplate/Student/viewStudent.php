 <?php 
error_reporting(0);
include '../Includes/dbcon.php';
include '../Includes/session.php';

// $query = "SELECT tblclass.className,tblclassarms.classArmName 
//     FROM tblclassteacher
//     INNER JOIN tblclass ON tblclass.Id = tblclassteacher.classId
//     INNER JOIN tblclassarms ON tblclassarms.Id = tblclassteacher.classArmId
//     Where tblclassteacher.Id = '$_SESSION[userId]'";

//     $rs = $conn->query($query);
//     $num = $rs->num_rows;
//     $rrw = $rs->fetch_assoc();

$query = "SELECT tblstudents.Id, tblclass.className, tblclassarms.classArmName,
tblstudents.studentName, tblstudents.regId, tblstudents.studentPhoto, tblstudents.fatherName, tblstudents.motherName, tblstudents.priPhoneNo, tblstudents.secPhoneNo, tblstudents.address, tblstudents.zone, tblstudents.secLang, tblstudents.dob, tblstudents.commute,tblstudents.admissionType,tblstudents.admissionConcessionType,tblstudents.monthlyConcessionType,tblstudents.session  
FROM tblstudents 
INNER JOIN tblclass ON tblclass.Id = tblstudents.classId 
INNER JOIN tblclassarms ON tblclassarms.Id = tblstudents.classSecId 
WHERE tblstudents.Id = '$_SESSION[userId]'";
$rs = $conn->query($query);

$data = $rs->fetch_assoc(); 
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
    <style>
      .cv-container {
        background: #f9f9f9;
        padding: 20px;
        box-shadow: 0 0 10px #ccc;
        border-radius: 8px;
      }
      .cv-header {
        text-align: center;
        margin-bottom: 20px;
      }
      /* .cv-header img {
        border-radius: 10px;
        height: 120px;
        width: 120px;
        object-fit: cover;
        border: 2px solid #007bff;
      } */
      .cv-section h5 {
        border-bottom: 1px solid #007bff;
        padding-bottom: 5px;
        color: #007bff;
      }
      .table-section {
        margin-bottom: 40px;
      }
      .cv-section p {
        margin: 0;
      }
      .cv-section .row > div {
    margin-bottom: 10px;
  }
        .student-photo-fixed {
      width: 150px;
      height: 150px;
      object-fit: cover;
      border-radius: 10px;
      border: 2px solid #007bff;
      box-shadow: 0 0 12px rgba(0,0,0,0.2);
      margin-top: 10px;
      margin-right: 100px;
    }  
    </style>


    <script>
      function classArmDropdown(str) {
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
          xmlhttp.open("GET","ajaxClassArms2.php?cid="+str,true);
          xmlhttp.send();
      }
  }
  </script>
  </head>

  <body id="page-top">
    <div id="wrapper">
    
        <?php include "Includes/sidebar.php";?>
    
      <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
      
        <?php include "Includes/topbar.php";?>
        
          <div class="container-fluid" id="container-wrapper">
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
              <!-- <h1 class="h3 mb-0 text-gray-800">Admission Preview Details</h1> -->
                <h1 class="h3 mb-0 text-gray-800">Personal Info</h1>
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="./">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Admission Preview Details</li>
              </ol>
            </div>

            <div class="row">
              <div class="col-lg-12">
                
                  <div class="row">
                    <div class="col-md-7 table-section">
        <!-- <h3>Admission Preview Table</h3> -->
        <!-- <table class="table table-bordered table-hover mt-3"> -->
          <thead class="thead-dark">
                <div class="col-lg-12">
                <div class="card mb-4">
                  
                  <!-- <div class="table-responsive p-3"> -->
                    <!-- <table class="table align-items-center table-flush table-hover" >
                      <thead class="thead-light">
                        <tr>
                          <th>#</th>
                          <th>Registration No.</th>
                          <th>Student Name</th>
                          <th>Class Name</th>
                          <th>Class Section</th>
                          <th>Student Photo</th>
                          <th>Father's Name</th>
                          <th>Mother's Name</th>
                          <th>Primary Phone No.</th>
                          <th>Secondary Phone No.</th>
                          <th>Address</th>
                          <th>Zone</th>
                          <th>Second Language</th>
                          <th>Date of Birth</th>
                          <th>Mode of Commute</th>
                        </tr>
                      </thead>
                      
                      <tbody>

                    <?php
                        
                        $query = "SELECT tblstudents.Id, tblclass.className,tblclassarms.classArmName,tblclassarms.Id, tblstudents.fatherName AS classArmId,tblstudents.studentName, 
                        tblstudents.regId,tblstudents.studentPhoto,tblstudents.dateCreated, tblstudents.fatherName, tblstudents.motherName , tblstudents.priPhoneNo, tblstudents.secPhoneNo, tblstudents.address, tblstudents.zone, tblstudents.secLang, tblstudents.dob, tblstudents.commute,tblstudents.admissionType,tblstudents.admissionConcessionType,tblstudents.monthlyConcessionType,tblstudents.session 
                        FROM tblstudents INNER JOIN tblclass ON tblclass.Id = tblstudents.classId INNER JOIN tblclassarms ON tblclassarms.Id = tblstudents.classSecId
                        where tblstudents.Id = '$_SESSION[userId]'";
                        $rs = $conn->query($query);
                        $num = $rs->num_rows;
                        $sn=0;
                        $status="";
                        if($num > 0)
                        { 
                          while ($rows = $rs->fetch_assoc())
                            {
                              $sn = $sn + 1;
                              echo"
                                <tr>
                                  <td>".$sn."</td>
                                  <td>".$rows['regId']."</td>
                                  <td>".$rows['studentName']."</td>
                                  <td>".$rows['className']."</td>
                                  <td>".$rows['classArmName']."</td>
                                  <td><img src=".$rows['studentPhoto']." width=\"100\" height=\"100\"/></td>
                                  <td>".$rows['fatherName']."</td>
                                  <td>".$rows['motherName']."</td>
                                  <td>".$rows['priPhoneNo']."</td>
                                    <td>".$rows['secPhoneNo']."</td>
                                    <td>".$rows['address']."</td>
                                      <td>".$rows['zone']."</td>
                                      <td>".$rows['secLang']."</td>
                                      <td>".$rows['dob']."</td>
                                      <td>".$rows['commute']."</td>
                                      <td>".$rows['admissionType']."</td>
                                      <td>".$rows['admissionConcessionType']."</td>
                                      <td>".$rows['monthlyConcessionType']."</td>
                                      <td>".$rows['session']."</td>
                                </tr>";
                            }
                        }
                        else
                        {
                            echo   
                            "<div class='alert alert-danger' role='alert'>
                              No Record Found!
                              </div>";
                        }
                        
                        ?>
                      </tbody>
                    </table> -->
                  <!-- </div> -->
                </div>
              </div>
              </div>
            </div>
          
<div class="col-md-5">
  <!-- <div class="cv-container" style="width: 140%; margin-left: 40vh; margin-bottom: 30px;"> -->
    <div id="cvSection" class="cv-container" style="width: 140%; margin-left: 40vh; margin-bottom: 30px;">

    <div class="cv-header">
      <h4>Student CV Format</h4>
      <div style="display: flex; justify-content: space-between;  margin-top: 30px;">
        <!-- Left Side: Reg ID and Student Name -->
        <div>
          <strong style="display: block; font-weight: 600;white-space: nowrap;margin-top:60px;margin-right:100px;">Reg No:<?php echo $data['regId']; ?></strong>
           <h5 style="margin-top: 5px;  white-space: nowrap;margin-right:100px; overflow: hidden; text-overflow: ellipsis;"><strong>Name:</strong><?php echo $data['studentName']; ?></h5>
        </div>

        <!-- Right Side: Student Photo -->
        <!-- <div>
          <?php if ($data['studentPhoto']) {
            // echo '<img src="' . $data['studentPhoto'] . '" alt="Student Photo" style="width: 100%; height: 50%;left:20px; border-radius: 5px;">';
            echo '<img src="'.$data['studentPhoto'].'" alt="Student Photo" style="width: 60%; height: 160%;margin-top:10px;margin-right:50px; border-radius: 10px; box-shadow: 0 0 12px rgba(0,0,0,0.2);">';
          } ?>
        </div> -->
         <div>
          <?php if ($data['studentPhoto']) { ?>
            <img src="<?php echo $data['studentPhoto']; ?>" alt="Student Photo" class="student-photo-fixed">
          <?php } ?>
        </div>
      </div>
      </div>
   

          <!-- <div class="col-md-5">
    <div class="cv-container" style="width:140%;margin-left:40vh; margin-bottom:30px;">

          <div class="cv-header">
            <h4>Student CV Format</h4>
            
            <?php if ($data['studentPhoto']) {
              echo '<img src="'.$data['studentPhoto'].'" alt="Student Photo">';
            } ?>
            
            <div style="margin-top: 10px;">
    <small style="display: block; font-weight: 600;">Reg No: <?php echo $data['regId']; ?></small>
    <h5 style="margin-top: 5px;"><?php echo $data['studentName']; ?></h5>
  </div>  
          </div>  -->
      <!-- <div class="cv-header">
    <h4 style="margin-bottom: 20px;">Student CV Format</h4>

    <div class="d-flex align-items-start">
  
      <?php if ($data['studentPhoto']) { ?>
        <div>
          <img src="<?php echo $data['studentPhoto']; ?>" alt="Student Photo"
              style="height: 100px; width: 100px; object-fit: cover; border-radius: 10px; border: 2px solid #007bff;">
        </div>
      <?php } ?>

      <div style="margin-left: 20px;">
        <small style="font-weight: bold; font-size: 15px;">Reg No: <?php echo $data['regId']; ?></small>
        <h5 style="margin-top: 5px; font-weight: 600;"><?php echo $data['studentName']; ?></h5>
      </div>
    </div>
  </div> -->



          <div class="cv-section" style="margin-top:150px">
    <h5 >Academic Information</h5>
    <div class="row">
      <div class="col-md-6"><p><strong>Class:</strong> <?php echo $data['className']; ?></p></div>
      <div class="col-md-6"><p><strong>Section:</strong> <?php echo $data['classArmName']; ?></p></div>
      <div class="col-md-6"><p><strong>Second Language:</strong> <?php echo $data['secLang']; ?></p></div>
      <div class="col-md-6"><p><strong>Admission Type:</strong> <?php echo $data['admissionType']; ?></p></div>
      <div class="col-md-6"><p><strong>Admission Concession Type:</strong> <?php echo $data['admissionConcessionType']; ?></p></div>
      <div class="col-md-6"><p><strong>Monthly Concession Type:</strong> <?php echo $data['monthlyConcessionType']; ?></p></div>
      <div class="col-md-6"><p><strong>Session:</strong> <?php echo $data['session']; ?></p></div>
    </div>
  </div>
  
  <div class="cv-section">
    <h5>Personal Information</h5>
    <div class="row">
      <div class="col-md-6"><p><strong>Father:</strong> <?php echo $data['fatherName']; ?></p></div>
      <div class="col-md-6"><p><strong>Mother:</strong> <?php echo $data['motherName']; ?></p></div>
      <div class="col-md-6"><p><strong>Address:</strong> <?php echo $data['address']; ?></p></div>
      <div class="col-md-6"><p><strong>Zone:</strong> <?php echo $data['zone']; ?></p></div>
      <div class="col-md-6"><p><strong>DOB:</strong> <?php echo $data['dob']; ?></p></div>
    </div>
  </div>

  <div class="cv-section">
    <h5>Contact Details</h5>
    <div class="row">
      <div class="col-md-6"><p><strong>Primary Phone:</strong> <?php echo $data['priPhoneNo']; ?></p></div>
      <div class="col-md-6"><p><strong>Secondary Phone:</strong> <?php echo $data['secPhoneNo']; ?></p></div>
      <div class="col-md-6"><p><strong>Commute:</strong> <?php echo $data['commute']; ?></p></div>
          </div>
  </div>

        </div>
      </div>
          </div>
          </div>
           <div class="d-flex justify-content-center mt-4 mb-4">
    <button onclick="printCV()" class="btn btn-primary">🖨️ Print CV</button>
  </div>
  <script>
function printCV() {
  var content = document.getElementById("cvSection").innerHTML;
  var win = window.open('', '', 'height=700,width=900');
  win.document.write('<html><head><title>Print CV</title>');
  win.document.write('<link rel="stylesheet" href="../vendor/bootstrap/css/bootstrap.min.css">');
  win.document.write('<style>body{font-family:Arial; padding:20px;}.cv-container{box-shadow:none;}</style>');
  win.document.write('</head><body>');
  win.document.write(content);
  win.document.write('</body></html>');
  win.document.close();
  win.focus();
  win.print();
  win.close();
}
</script>
        
      
        <?php include "Includes/footer.php";?>
        
      </div>
    </div>

    
    <a class="scroll-to-top rounded" href="#page-top">
      <i class="fas fa-angle-up"></i>
    </a>

    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/ruang-admin.min.js"></script>
    
    <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>


    <script>
      $(document).ready(function () {
        $('#dataTable').DataTable(); 
        $('#dataTableHover').DataTable(); 
      });
    </script>
  </body>

  </html>
  <!-- 
  <?php 
  error_reporting(0);
  include '../Includes/dbcon.php';
  include '../Includes/session.php';

  $query = "SELECT tblstudents.Id, tblclass.className,tblclassarms.classArmName,
  tblstudents.studentName, tblstudents.regId,tblstudents.studentPhoto, tblstudents.dateCreated, 
  tblstudents.fatherName, tblstudents.motherName, tblstudents.priPhoneNo, tblstudents.secPhoneNo, 
  tblstudents.address, tblstudents.zone, tblstudents.secLang, tblstudents.dob, tblstudents.commute 
  FROM tblstudents 
  INNER JOIN tblclass ON tblclass.Id = tblstudents.classId 
  INNER JOIN tblclassarms ON tblclassarms.Id = tblstudents.classSecId 
  WHERE tblstudents.Id = '$_SESSION[userId]'";
  $rs = $conn->query($query);
  $data = $rs->fetch_assoc();
  ?>
