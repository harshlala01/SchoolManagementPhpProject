

    <?php 
    error_reporting(0);
    include '../Includes/dbcon.php';
    include '../Includes/session.php';
    session_start(); 
    // $statusMsg = "";

    if (isset($_POST['save'])) {
      $regId = $_SESSION['regId'];
      $studentName = $_SESSION['studentName'];
      $session = $_SESSION['session'] ?? '';

    $admissionType = $_SESSION['admissionType'] ?? '';
    $monthlyConcessionType = $_SESSION['monthlyConcessionType'] ?? '';
        //  $className = $_SESSION['className'] ?? '';
        $grand_total = $_POST['grand_total'] ?? 0;
        $amount_paying = $_POST['amount_paying'] ?? 0;
        $due = $_POST['due'] ?? 0;
        // Fix 1: className, classSecId from session or post
        // REMOVE "//" from these lines (VERY IMPORTANT)
    $className = $_SESSION['className'] ?? '';
    $classArmName = $_SESSION['classArmName'] ?? '';
    $classSecId = $_SESSION['classSecId'] ?? '';
    $classId = $_SESSION['classId'] ?? '';
    $gender = $_SESSION['gender'] ?? '';

    
        $payment_type = $_POST['payment_type'];
        $payment_mode = $_POST['payment_mode'];
        $month = $_POST['month'];
        $status = 'Pending';
        $date = date('Y-m-d');
    //           echo '<pre>';
    //   print_r($_SESSION);
    //   echo '</pre>';
    //   echo '<pre>';
    // print_r($_POST);
    // echo '</pre>';
    // print_r($_SESSION);
    // print_r($_POST);
    // echo "DEBUG: classId = $classId, classSecId = $classSecId, className = $className, classArmName = $classArmName";


        // $dateCreated = date("Y-m-d");
    // $dateCreated = $data['dateCreated'];
        $payment_id = 'PAY' . uniqid();

        $targetDir = "img/upload/";
        $originalFile = basename($_FILES["paymentImage"]["name"]);
        $fileName = time() . '_' . $originalFile;
        $targetFilePath = $targetDir . $fileName;
        $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

        $allowedTypes = array('jpg', 'jpeg', 'png', 'pdf');
    $checkSql = "SELECT * FROM payments WHERE regId = ? AND session = ? AND className = ? AND month = ? AND payment_type = 'Monthly'";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("ssss", $regId, $session, $className, $month);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
    if ($checkResult->num_rows > 0) {
            // $statusMsg = "<div class='alert alert-danger'>Payment for this month already exists!</div>";
            $statusMsg = "

        <div class='row justify-content-center' style='margin-right:500px'>
            <div class='col-md-15'>
                <div class='alert alert-danger text-center'>
                    Payment for this month already exists!
                </div>
            </div>
        </div>
    ";

        } else {
        if (in_array($fileType, $allowedTypes)) {
            if (move_uploaded_file($_FILES["paymentImage"]["tmp_name"], $targetFilePath)) {
              $sql = "INSERT INTO payments 
              (payment_id,regId, studentName,classId, classSecId,className,classArmName, session,admissionType,monthlyConcessionType, grand_total, amount_paying, due, payment_type, status, payment_mode,month, photo, created_at,gender)
              VALUES 
              ('$payment_id','$regId', '$studentName','$classId', '$classSecId', '$className','$classArmName', '$session','$admissionType','$monthlyConcessionType', '$grand_total','$amount_paying', '$due', '$payment_type', '$status', '$payment_mode','$month', '$fileName', '$date','$gender')";
              $query = mysqli_query($conn, $sql);
              
              //  echo $sql;
              //  die();
                if ($query) {
                  $statusMsg = "
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'Payment Submitted',
                text: 'Your payment has been successfully submitted!',
                confirmButtonText: 'OK'
            });
        });
    </script>";

                    // $statusMsg = "<div class='alert alert-success'>Payment record created successfully!</div>";
                } else {
                    $statusMsg = "<div class='alert alert-danger'>Database insert failed!</div>";
                }
            } else {
                $statusMsg = "<div class='alert alert-danger'>File upload failed!</div>";
            }
            
        } else {
            $statusMsg = "<div class='alert alert-danger'>Only JPG, JPEG, PNG, and PDF files are allowed!</div>";
        }
    }
    }
    //---------------------------------------EDIT-------------------------------------------------------------






    //--------------------EDIT------------------------------------------------------------

    if (isset($_GET['Id']) && isset($_GET['action']) && $_GET['action'] == "edit")
      {
            $Id= $_GET['Id'];

            $query=mysqli_query($conn,"select * from tblstudents where Id ='$Id'");
            $row=mysqli_fetch_array($query);

            //------------UPDATE-----------------------------

            if(isset($_POST['update'])){
        
                $firstName=$_POST['firstName'];
      $lastName=$_POST['lastName'];
      $otherName=$_POST['otherName'];

      $admissionNumber=$_POST['admissionNumber'];
      $classId=$_POST['classId'];
      $classArmId=$_POST['classArmId'];
      $dateCreated = date("Y-m-d");

    $query=mysqli_query($conn,"update tblstudents set firstName='$firstName', lastName='$lastName',
        otherName='$otherName', admissionNumber='$admissionNumber',password='12345', classId='$classId',classArmId='$classArmId'
        where Id='$Id'");
                if ($query) {
                    
                    echo "<script type = \"text/javascript\">
                    window.location = (\"createStudents.php\")
                    </script>"; 
                }
                else
                {
                    $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>An error Occurred!</div>";
                }
            }
        }


    //--------------------------------DELETE------------------------------------------------------------------

      if (isset($_GET['Id']) && isset($_GET['action']) && $_GET['action'] == "delete")
      {
            $Id= $_GET['Id'];
            $classArmId= $_GET['classArmId'];

            $query = mysqli_query($conn,"DELETE FROM tblstudents WHERE Id='$Id'");

            if ($query == TRUE) {

                echo "<script type = \"text/javascript\">
                window.location = (\"createStudents.php\")
                </script>";
            }
            else{

                $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>An error Occurred!</div>"; 
            }
          
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
          <!-- <link href="img/logo/attnlg.jpg" rel="icon"> -->
        <?php include 'includes/title.php';?>
          <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
          <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
          <link href="css/ruang-admin.min.css" rel="stylesheet">



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
                    <h1 class="h3 mb-0 text-gray-800">Make Monthly Payment</h1>
                    <ol class="breadcrumb">
                      <li class="breadcrumb-item"><a href="./">Home</a></li>
                      <li class="breadcrumb-item active" aria-current="page">Make Monthly Payment</li>
                    </ol>
                  </div>

                  <div class="row">
                    <div class="col-lg-12">
                      <!-- Form Basic -->
                      <div class="card mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                          <h6 class="m-0 font-weight-bold text-primary">Pay Monthly Fees</h6>
                            <?php echo $statusMsg; ?>
                            

                        </div>
                        <div class="card-body">
                        <form method="post" enctype="multipart/form-data">
                          <div class="form-group row mb-3">
                          <div class="col-xl-8">
                          <div class="form-group row mb-3">
                          <div class="col-xl-4">
                            <div class="card p-4" style="width:113vh;left:3vh;" >
                            <div class="row">
                            <!-- Left: Admission Payment Form -->
                            <div class="col-md-4">
                                <label class="form-control-label">Reg Number<span class="text-danger ml-2">*</span></label>
                              
                                <input type="text" class="form-control"  id="regId" readonly value="<?php echo $_SESSION['regId']; ?>">
                                </div>

                                <div class="col-md-4">
                                <label class="form-control-label">Student Name<span class="text-danger ml-2">*</span></label>
                                <input type="text" class="form-control" readonly value="<?php echo $_SESSION['studentName']; ?>">
                                </div>

                                <div class="col-md-4">
                                <label class="form-control-label">Paymemt Type<span class="text-danger ml-2">*</span></label>
                                <input type="text" class="form-control" name="payment_type" readonly value="Monthly" id="exampleInputFirstName">
                                </div>
                  
                              
                            </div>
                          
                            <div class="form-group row mb-3">
                              

                                <div class="form-group" style="margin-top:10px">
                                <label class="font-weight" for="payment_mode" style="margin-left:10px" >Payment Mode<span class="text-danger ml-2">*</span></label>
                                <select class="form-control" id="payment_mode" name="payment_mode" required style="width: 35vh; max-width: 350px; padding: 10px ; margin-left:10px">
                                  <option value="">-- Select Payment Mode --</option>
                                  <option value="UPI/QR"> UPI/QR</option>
                                  <option value="Account Transation"> Account Transation</option>
                                  <option value="Cash"> Cash</option>
                                </select>
                                </div>
        <!-- 
        <?php

        // Dropdown month logic
        $regId = $_SESSION['regId'] ?? '';
        $className = $_SESSION['className'] ?? '';
        $session = $_SESSION['session'] ?? '';

        $allMonths = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
        $paidMonths = [];
        $unpaidMonths = [];

        if (!empty($regId)) {

          // echo "999999999999999=".$regId;
          $query = "SELECT month FROM payments WHERE regId = ?  and payment_type = 'Monthly' and status= 'Received' order by created_at desc limit 0,1";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $regId);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                $lastpaidMonth = trim($row['month']);
              
            }
          // echo $lastpaidMonth;
          //  $lastpaidMonth = ucfirst(strtolower(trim($row['month']))); 
          $index = array_search($lastpaidMonth, $allMonths);
          // echo  "index==".$index;
          

        }


        ?>  -->
        <!-- 
        <?php
        // $regId = $_SESSION['regId'] ?? '';
        // $className = $_SESSION['className'] ?? '';
        // $session = $_SESSION['session'] ?? '';

        $allMonths = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
        $paidMonths = [];
        $unpaidMonths = [];

        if (!empty($regId)) {
            $query = "SELECT  month FROM payments WHERE regId = ? AND payment_type = 'Monthly' AND status = 'Received'";
              //  $query = "SELECT month FROM payments WHERE regId = ?  and payment_type = 'Monthly' and status= 'Received' order by created_at desc limit 0,1";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $regId);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                $paidMonths[] = ucfirst(strtolower(trim($row['month'])));
            }

            // Remove paid months from allMonths
            $unpaidMonths = array_values(array_diff($allMonths, $paidMonths));
        }
        ?> -->
        <!-- <?php
        // $allMonths = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
        // $index = -1; // default index, agar koi payment record na ho
        $regId = $_SESSION['regId'] ?? '';
        $allMonths = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
        $lastpaidMonth = null;
        $index = -1;
        if (!empty($regId)) {
            // ✅ Sirf last paid month nikal rahe ho
            $query = "SELECT month FROM payments WHERE regId = ? AND payment_type = 'Monthly' AND status = 'Received'";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $regId);
            $stmt->execute();
            $result = $stmt->get_result();

            // if ($row = $result->fetch_assoc()) {
            //   $paidMonths[] = ucfirst(strtolower(trim($row['month'])));
            //     // $lastpaidMonth = ucfirst(strtolower(trim($row['month']))); // proper casing
            //     $index = array_search($lastpaidMonth, $allMonths); // last paid month ka index
            // }
            // $unpaidMonths = array_values(array_diff($allMonths, $paidMonths));
              while ($row = $result->fetch_assoc()) {
                $paidMonths[] = ucfirst(strtolower(trim($row['month'])));
            }

            // Remove paid months from allMonths
            $unpaidMonths = array_values(array_diff($allMonths, $paidMonths));
        }
        ?> -->
        <?php
        $regId = $_SESSION['regId'] ?? '';
        $allMonths = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
        $paidMonths = [];
        $unpaidMonths = [];

        if (!empty($regId)) {
            $query = "SELECT month FROM payments WHERE regId = ? AND payment_type = 'Monthly' AND status = 'Received'";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $regId);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                $paidMonths[] = ucfirst(strtolower(trim($row['month'])));
            }

            $unpaidMonths = array_values(array_diff($allMonths, $paidMonths));
        } else {
            $unpaidMonths = $allMonths; // fallback if no regId
        }
        ?>
        <div class="col-xl-4" style="margin-top:10px">
            <label for="month">Choose a month <span class="text-danger ml-2">*</span></label>
            <select name="month" id="month" class="form-control mb-3" required>
                <option value="">--Select month--</option>
                <?php
                foreach ($unpaidMonths as $month) {
                    echo "<option value=\"$month\">$month</option>";
                }
                ?>
            </select>
        </div>



        <!-- <label for="month">Choose a month <span class="text-danger ml-2">*</span></label>
        <select name="month" id="month" class="form-control mb-3">
          <option value="">--Select month--</option>
          <?php 
                // ✅ Agar last paid month mila, toh uske baad wale months dikhao
                for ($i = $index + 1; $i < count($allMonths); $i++) {
                  echo "<option value=\"" . $allMonths[$i] . "\">" . $allMonths[$i] . "</option>";
                }
                // ✅ Agar koi month paid nahi hai (index = -1), toh saare months dikhao
                if ($index == -1) {
                  for ($i = 0; $i < count($allMonths); $i++) {
                    echo "<option value=\"" . $allMonths[$i] . "\">" . $allMonths[$i] . "</option>";
                  }
                }
                ?> -->
                <!-- <div class="col-xl-4" style="margin-top:10px">
            <label for="month">Choose a month <span class="text-danger ml-2">*</span></label>
            <select name="month" id="month" class="form-control mb-3" required>
                <option value="">--Select month--</option>
                <?php
                if ($index === false || $index === -1) {
                    // Show all months if nothing paid
                    foreach ($allMonths as $month) {
                        echo "<option value=\"$month\">$month</option>";
                    }
                } else {
                    // Show only remaining months
                    for ($i = $index + 1; $i < count($allMonths); $i++) {
                        echo "<option value=\"" . $allMonths[$i] . "\">" . $allMonths[$i] . "</option>";
                    }
                }
                ?>
            </select>
        </div> -->
                <!-- <div class="col-xl-4" style="margin-top:10px">
            <label for="month">Choose a month <span class="text-danger ml-2">*</span></label>
            <select name="month" id="month" class="form-control mb-3">
                <option value="">--Select month--</option>
                <?php 
                foreach ($unpaidMonths as $month) {
                    echo "<option value=\"$month\">$month</option>";
                }
                ?> -->
          
                                <!-- <div class="col-xl-4" style="margin-top:10px">
                                <label for="month">Choose a month <span class="text-danger ml-2">*</span></label>
                                  <select name="month" id="month" class="form-control mb-3">
                                  <option value="">--Select month--</option>
                                  <?php 
                                  for ($i = $index; $i < count($allMonths); $i++) {
                                echo  "<option value=".$allMonths[$i].">".$allMonths[$i]."</option>";
                                }
                                  

                                    ?> -->
                                    <!-- <option value="February"><?php echo $lastpaidMonth ;?></option>
                                    <option value="March">March</option>
                                    <option value="April">April</option>
                                    <option value="May">May</option>
                                    <option value="June">June</option>
                                    <option value="July">July</option>
                                    <option value="August">August</option>
                                    <option value="September">September</option>
                                    <option value="October">October</option>
                                    <option value="November">November</option>
                                    <option value="December">December</option> -->
                                  <!-- </select>
                                </div> -->
        <div class="form-group" style="margin-top:10px">
          <label style="margin-left:10px">Session</label>
          <input type="text" name="session" id="session" class="form-control" style="width: 35vh; max-width: 350px; padding: 10px ; margin-left:10px" placeholder="Auto-filled" value="<?= $_SESSION['session'] ?? '' ?>" readonly>
        </div>
        <div class="form-group">
              <label style="margin-left:10px">Class</label>
              <input type="text" name="classId" id="classId" class="form-control" style="width: 35vh; max-width: 350px; padding: 10px ; margin-left:10px" placeholder="Auto-filled" value="<?= $_SESSION['className'] ?? '' ?>" readonly>
            </div>
            
          <div class="form-group" >
          <label style="margin-left:10px">Section</label>
          <!-- <input type="hidden" name="classArmName" value="<?= $_SESSION['classSecId'] ?? '' ?>">
        <input type="text" value="<?= $_SESSION['classArmName'] ?? '' ?>" readonly> -->

          <input type="text" name="classSecId" class="form-control" style="width: 35vh; max-width: 350px; padding: 10px ; margin-left:10px" placeholder="Auto-filled" value="<?= $_SESSION['classArmName'] ?? '' ?>" readonly>
        </div>
        <!-- <div class="form-group">
          <label style="margin-left:10px">Section</label>


        <input type="hidden" name="classSecId" value="<?= $_SESSION['classSecId'] ?? '' ?>">


        <input type="text" class="form-control" style="width: 35vh; max-width: 350px; padding: 10px ; margin-left:10px" 
          placeholder="Auto-filled" value="<?= $_SESSION['classArmName'] ?? '' ?>" readonly>

        </div> -->

        <div class="form-group">
          <label style="margin-left:10px">Admission Type</label>
          <input type="text" name="admissionType" id="admissionType" style="width: 35vh; max-width: 350px; padding: 10px ; margin-left:10px" placeholder="Auto-filled" class="form-control" value="<?= $_SESSION['admissionType'] ?? '' ?>" readonly>
        </div>
        <div class="form-group">
          <label style="margin-left:10px">Monthly Concession Type</label>
          <input type="text" name="monthlyConcessionType" placeholder="Auto-filled" class="form-control"  style="width: 35vh; max-width: 350px; padding: 10px ; margin-left:10px" value="<?= $_SESSION['monthlyConcessionType'] ?? '' ?>" readonly>
        </div>


        <div class="form-group">
          <label class="font-weight" for="grand_total" style="margin-left:10px">Grand Total<span class="text-danger ml-2">*</span></label>
          <input type="text" class="form-control" id="grand_total" name="grand_total" placeholder="Total Amount" readonly
            style="width: 35vh; max-width: 350px; padding: 10px; margin-left:10px" />
        </div>

        <div class="form-group" >
          <label class="font-weight"  style="margin-left:10px">Gender<span class="text-danger ml-2">*</span></label>
            <input type="text" class="form-control"  name="gender" placeholder="Auto-filled" value="<?= $_SESSION['gender'] ?? '' ?>"  readonly
            style="width: 35vh; max-width: 350px; padding: 10px; margin-left:10px" />
        </div>

        <div class="form-group">
          <label style="margin-left:10px">Final Admission Amount (After Concession)</label>
          <input type="text" style="width: 35vh; max-width: 350px; padding: 10px ; margin-left:10px" id="final_amount" class="form-control" readonly>
        </div>

        <!-- Amount Paying -->
        <div class="form-group">
          <label class="font-weight" for="amount_paying" style="margin-left:10px">Amount Paying<span class="text-danger ml-2">*</span></label>
          <input type="number" class="form-control" id="amount_paying" name="amount_paying" placeholder="Enter Paid Amount" oninput="calculateDue()"
            style="width: 35vh; max-width: 350px; padding: 10px; margin-left:10px" />
        </div>

        <!-- Due Amount -->
        <div class="form-group">
          <label class="font-weight" for="due" style="margin-left:10px">Due Amount<span class="text-danger ml-2">*</span></label>
          <input type="text" class="form-control" id="due" name="due" placeholder="Due Amount" readonly
            style="width: 35vh; max-width: 350px; padding: 10px; margin-left:10px" />
        </div>
        </div>
        </div>
        </div>

        <script>
        function getBaseAmount(admissionType, className) {
            const cl = className.toLowerCase().trim();

            if (admissionType === 'Re-Admission' || admissionType === 'Re Admission') {
                if (cl.includes('nur') || cl.includes('lkg') || cl.includes('ukg')) return 6500;
                if (cl === '1' || cl === 'i' || cl.includes('1') || cl.includes('i ')) return 7000;
                if (cl === '2' || cl === 'ii' || cl.includes('2')) return 7000;
                if (cl === '3' || cl === 'iii' || cl === '4' || cl === 'iv') return 7500;
                if (cl === '5' || cl === 'v' || cl === '6' || cl === 'vi') return 8000;
                if (cl === '7' || cl === 'vii' || cl === '8' || cl === 'viii') return 8500;
                if (cl === '9' || cl === 'ix' || cl === '10' || cl === 'x') return 10000;
            }

            if (admissionType === 'New Admission') {
                if (cl.includes('nur') || cl.includes('lkg') || cl.includes('ukg')) return 10000;
                return 12000;
            }

            return 0;
        }

        function getConcessionPercent(admissionType, concessionType) {
            console.log("Admission Type:", admissionType, "Concession Type:", concessionType);

            const type = concessionType.toLowerCase();

            if (type.includes('staff')) return 50;
            if (type.includes('sibling')) return 5;
            if (admissionType === 'Re Admission' && type.includes('ref')) return 50;
            if (admissionType === 'New Admission' && type.includes('ref')) return 5;

            return 0;
        }

        function calculateFee() {
            const className = document.getElementById('classId').value.trim();
            const admissionType = document.getElementById('admissionType').value.trim();
            const concessionType = document.querySelector('[name="monthlyConcessionType"]').value.trim(); // use this now

            const baseAmount = getBaseAmount(admissionType, className);
            const concessionPercent = getConcessionPercent(admissionType, concessionType);
            const concessionAmount = baseAmount * (concessionPercent / 100);
            const finalAmount = baseAmount - concessionAmount;

            document.getElementById('grand_total').value = baseAmount.toFixed(2);
            document.getElementById('final_amount').value = finalAmount.toFixed(2);

            console.log("Class:", className);
            console.log("Base Amount:", baseAmount);
            console.log("Concession (%):", concessionPercent);
            console.log("Final Amount:", finalAmount);

            calculateDue(); // Update due
        }

        function calculateDue() {
            const finalAmount = parseFloat(document.getElementById('final_amount').value) || 0;
            const payingAmount = parseFloat(document.getElementById('amount_paying').value) || 0;
            const dueAmount = finalAmount - payingAmount;

            document.getElementById('due').value = dueAmount.toFixed(2);
        }

        // Auto run on page load
        window.onload = () => {
            calculateFee();
            document.getElementById('amount_paying').addEventListener('input', calculateDue);
        };
        </script>

                        
                                                      
        <!-- <div class="form-group">
          <label class="font-weight" for="admission_type" style="margin-left:10px">Admission Type<span class="text-danger ml-2">*</span></label>
          <select class="form-control" id="admission_type" name="admission_type" onchange="updateFeeAmount()" required style="width: 35vh; max-width: 350px; padding: 10px ; margin-left:10px">
            <option value="">--Select--</option>
            <option value="New Admission">New Admission</option>
            <option value="Re-Admission">Re-Admission</option>
          </select>
        </div> -->


                            </div>
                              

                            <div class="form-group row mb-3">
                                <!-- <div class="col-xl-4">
                                <label class="form-control-label">Paymemt Type<span class="text-danger ml-2">*</span></label>
                                <input type="text" class="form-control" name="payment_type" readonly value="Monthly" id="exampleInputFirstName">
                                </div> -->
                            
                                


                            </div>
                        </div>
                        <!-- <div class="col-xl-4">
                            <img src="img/schoolQR.JPG" name="qr" style="height: 452px;width: 260px;"/>
                          </div> -->
                          <div class="card shadow-sm mb-4 mx-auto" style="width: 290px; height: 760px;">
          <div class="card-body text-center">
            <img src="img/schoolQR.JPG"
                name="qr"
                alt="QR Code"
                class="img-fluid rounded shadow-sm"
                style="width: 260px; height: 452px;">
          
          
                            
                                <div class="text-center">
          <div class="mb-3">
            <label class="form-control-label " style="margin-top:10px">Bank Name</label>
            <input type="text" class="form-control mx-auto text-center" style="max-width: 300px;" readonly value="Punjab National Bank">
          </div>

          <div class="mb-3">
            <label class="form-control-label ">Account Number</label>
            <input type="text" class="form-control mx-auto text-center" style="max-width: 300px;" readonly value="0564056000010">
          </div>

          <div class="mb-3">
            <label class="form-control-label ">RTGS/NEFT IFSC Code</label>
            <input type="text" class="form-control mx-auto text-center" style="max-width: 300px;" readonly value="PUNB0056420">
          </div>
        </div>

        </div>
        </div>
                        </div>
                      <!-- <div class="form-group row mb-3">
                      <div class="col-xl-4"> <label class="form-control-label">Upload Payment receipt<span class="text-danger ml-2">*</span></label>
                      <input type="file"  name="paymentImage" onChange="displayImage(this)" id="paymentImage" class="form-control"  class="form-control" required></div><div class="col-xl-4"><img src="img/logo/icon.png"  style="width: 150px; height: 150px; border: 2px solid #ccc; border-radius: 10px; object-fit: cover; cursor: pointer; box-shadow: 0 2px 6px rgba(0,0,0,0.1);" onClick="triggerClick()" id="paymentDisplay"></div>
                      </div>
                          
                          
                            <button type="submit" name="save" class="btn btn-primary">Save</button> -->

        <!-- Upload Payment Receipt Section -->
        <!-- <div class="form-group row">
          
            <div class="col-xl-10 col-lg-6 col-md-6 col-sm-12">
                <label for="paymentImage" class="form-control-label font-weight-bold">
                    Upload Payment Receipt <span class="text-danger ml-2">*</span>
                </label>
                <input type="file" 
                      name="paymentImage" 
                      id="paymentImage" 
                      class="form-control" 
                      accept=".jpg,.jpeg,.png,.pdf"
                      onchange="displayImage(this)" 
                      required>
            </div>

            
            <div class="col-xl-2 col-lg-6 col-md-6 col-sm-12 mt-3 mt-xl-0">
                <img src="img/logo/attnlg.jpg" 
                    onclick="triggerClick()" 
                    id="paymentDisplay" 
                    alt="Click to upload"
                    style="width: 150px; height: 150px; border: 2px solid #ccc; border-radius: 10px; object-fit: cover; cursor: pointer; box-shadow: 0 2px 6px rgba(0,0,0,0.1);">
            </div>
        </div>

        <div class="form-group row mt-4">
            <div class="col-12 text-left">
                <button type="submit" name="save" class="btn btn-primary px-4 py-2">
                    Save
                </button>
            </div>
            
        </div> -->

        <!-- Container Row -->
        <div class="container" style="margin-left:3px;">
          <div class="row" >
            
            <!-- Card with Limited Width -->
            <div class="col-xl-6 col-lg-7 col-md-8 col-sm-12">
              <!-- <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white font-weight-bold">
                  Upload Payment Receipt
                </div>
                <div class="card-body">
                  <div class="form-group row">

                    <div class="col-md-7 mb-3">
                      <label for="paymentImage" class="form-control-label font-weight-bold">
                        Choose File <span class="text-danger ml-2">*</span>
                      </label>
                      <input type="file"
                            name="paymentImage"
                            id="paymentImage"
                            class="form-control"
                            accept=".jpg,.jpeg,.png,.pdf"
                            onchange="displayImage(this)"
                            required>
                      <small class="form-text text-muted">Accepted formats: JPG, JPEG, PNG, PDF</small>
                    </div>

                  
                    <div class="col-md-5 text-center">
                      <img src="img/logo/receipt image.png"
                          onclick="triggerClick()"
                          id="paymentDisplay"
                          alt="Click to upload"
                          style="width: 150px; height: 150px; border: 2px solid #ccc; border-radius: 10px; object-fit: cover; cursor: pointer; box-shadow: 0 2px 6px rgba(0,0,0,0.1);">
                    </div>
                  </div>
                </div>
              </div> -->
              <div class="card shadow-sm mb-4" >
          <div class="card-header bg-primary text-white font-weight-bold" >
            Upload Payment Receipt
          </div>
          <div class="card-body" style="margin-left:10px">
            <div class="form-group row" style="display: flex; align-items: center; justify-content: space-between;">

              <!-- File Drop Zone and Input -->
              <div class="col-md-7 mb-3" 
                  ondrop="handleDrop(event)" 
                  ondragover="event.preventDefault()" 
                  style="border: 2px dashed #007bff; padding: 20px; height:23vh; margin-top:10px; text-align: center; border-radius: 10px; background-color: #f9f9f9;">

                <label for="paymentImage" class="form-control-label font-weight-bold" style="display: block; ">
                  Drag & Drop or Choose File <span class="text-danger ml-2">*</span>
                </label>

                <input type="file"
                      name="paymentImage"
                      id="paymentImage"
                      class="form-control"
                      accept=".jpg,.jpeg,.png,.pdf"
                      onchange="displayImage(this)"
                      required
                      style="margin-top: 10px;">

                <small class="form-text text-muted" style="display: block; margin-top: 5px;">
                  Accepted formats: JPG, JPEG, PNG, PDF
                </small>
              </div>

              <!-- Image Preview -->
              <!-- <div class="col-md-5 text-center">
                <img src="img/logo/receipt image.png"
                    onclick="triggerClick()"
                    id="paymentDisplay"
                    alt="Click to upload"
                    style="width: 150px; height: 150px; border: 2px solid #ccc; border-radius: 10px; object-fit: cover; cursor: pointer; box-shadow: 0 2px 6px rgba(0,0,0,0.1);">
              </div> -->
              <!-- Image Preview Card (inside main row) -->
        <div class="col-md-5 d-flex justify-content-center">
          <div class="card"
              style="padding: 10px; border-radius: 12px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); border: 1px solid #ccc; width: fit-content; background-color: #fff;">

            <div class="card-body p-0 d-flex justify-content-center align-items-center">
              <img src="img/logo/receipt image.png"
                  onclick="triggerClick()"
                  id="paymentDisplay"
                  alt="Click to upload"
                  style="width: 160px; height: 160px; border: 2px solid #ccc; border-radius: 10px; object-fit: cover; cursor: pointer;">
            </div>

          </div>
        </div>

            </div>
          </div>
        </div>

              <!-- Save Button Outside Card -->
              <div class="text-end mb-3">
                <button type="submit" name="save" class="btn btn-primary px-4 py-2">
                  Save
                </button>
              </div>

            </div>
          </div>
        </div>


        <!-- JS Functions -->
        <script>
        function triggerClick() {
            document.querySelector('#paymentImage').click();
        }

        function displayImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.querySelector('#paymentDisplay').src = e.target.result;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
        </script>

                          </form>
                        </div>
                      </div>

                      <!-- Input Group -->
                    
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
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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

            //display image
            function triggerClick(e) {
          document.querySelector('#paymentImage').click();
        }
        function displayImage(e) {
          if (e.files[0]) {
            //alert("hhhhhhhhhhhhh")
            var reader = new FileReader();
            reader.onload = function(e){
              document.querySelector('#paymentDisplay').setAttribute('src', e.target.result);
            }
            reader.readAsDataURL(e.files[0]);
          }
        }
          </script>
        </body>

        </html>