

<?php 
error_reporting(0);
include '../Includes/dbcon.php';
include '../Includes/session.php';

$regId = $_SESSION['regId'];



 if (isset($_GET['payment_id']) && isset($_GET['action']) && $_GET['action'] == "edit")
	{
        $payment_id= $_GET['payment_id'];

        $query=mysqli_query($conn,"select * from payments where payment_id ='$payment_id'");
        $row=mysqli_fetch_array($query);
          //------------UPDATE-----------------------------
    //     if (isset($_POST['update'])) {
    //       $regId = $_POST['regId'];
    //       $studentName = $_POST['studentName'];
    //       // $amount_paying = $_POST['amount_paying'];
    //       $due = $_POST['due'];
    //       $status = $_POST['status']; 
      
    //       $query = mysqli_query($conn, "UPDATE payments 
    //           SET regId='$regId', studentName='$studentName', due='$due', status='$status'
    //           WHERE payment_id='$payment_id'");
      
    //   if ($query) {
      
    //         echo "<script>
    //         document.addEventListener('DOMContentLoaded', function() {
    //             Swal.fire({
    //                 icon: 'success',
    //                 title: 'Payment Submitted',
    //                 text: 'Form submitted successfully updated!'
    //             }).then(function() {
    //                 window.location = 'paymentHistory.php';
    //             });
    //         });
    //     </script>";
    // }
    //  else {
    //           $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>An error Occurred!</div>";
    //       }
    //   }
    if (isset($_POST['update'])) {
    $regId = $_POST['regId'];
    $studentName = $_POST['studentName'];
    $newAmount = floatval($_POST['amount']); // new amount entered from form

    // Step 1: Fetch old values from DB
    $getOld = mysqli_query($conn, "SELECT amount_paying, grand_total FROM payments WHERE payment_id = '$payment_id'");
    $old = mysqli_fetch_assoc($getOld);

    $oldPaid = floatval($old['amount_paying']);
    $grandTotal = floatval($old['grand_total']);

    // Step 2: Add new amount
    $updatedPaid = $oldPaid + $newAmount;
    $updatedDue = $grandTotal - $updatedPaid;

    // Step 3: Update status
    $updatedStatus = ($updatedDue <= 0) ? 'Received' : 'Pending';

    // Step 4: Run update query
    $query = mysqli_query($conn, "UPDATE payments 
        SET regId='$regId', studentName='$studentName', amount_paying='$updatedPaid', due='$updatedDue', status='$updatedStatus'
        WHERE payment_id='$payment_id'");

    if ($query) {
        echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'Payment Updated',
                text: 'Payment record updated successfully!'
            }).then(function() {
                window.location = 'paymentHistory.php';
            });
        });
        </script>";
    } else {
        echo "<div class='alert alert-danger'>An error occurred while updating.</div>";
    }
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
  <title>Dashboard</title>
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
            <h1 class="h3 mb-0 text-gray-800">Payments History</h1>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="./">Home</a></li>
              <li class="breadcrumb-item active" aria-current="page">payment History</li>
            </ol>
          </div>

           <div class="row">
            <div class="col-lg-12">
              <!-- Form Basic -->
                <?php if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['payment_id'])): ?>
              <div class="card mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                  <h6 class="m-0 font-weight-bold text-primary">Received/Not Received</h6>

                  <?php if (!empty($statusMsg)) echo $statusMsg; ?>

                </div>
                  <!-- <div class="card-body"> -->
                  <form method="post">
                  
<div class="form-group">
  
    

    <!-- Row 1: Reg Number | First Name | QR Code -->
    <div style="display: flex; margin-left: 20px;height:80vh" class="col-md-10" >
    
<div class="d-flex justify-content-start align-items-start" style="gap: 20px; margin-top: 30px; flex-wrap: wrap;">


  <!-- Right: QR Code with bank details -->
<div class="d-flex justify-content-between align-items-start flex-wrap" style="gap: 20vh;">

  
  <!-- Right side: Form -->
  
   <div style="flex: 1; width: 680px;">
    <form>
        <div class="card shadow-sm" style="flex: 1; padding: 20px 30px;margin-left:10vh; width:100%">
    <form>    
     <div class="form-group">
        <label>Reg Number *</label>
        <input type="text" class="form-control" name="regId" value="<?php echo $row['regId']; ?>" readonly>
      </div>
      <div class="form-group">
        <label>First Name *</label>
        <input type="text" class="form-control" name="studentName" value="<?php echo $row['studentName']; ?>" readonly>
      </div>

       
    


     <div class="form-group">
        <label>Amount To Be Paid *</label>
        <input type="text" class="form-control" name="amountToBePaid" id="amountToBePaid" value="<?php echo $row['due']; ?>" readonly>
      </div>
      <div class="form-group">
        <label>Due *</label>
        <input type="text" class="form-control" name="due" id="due" value="<?php echo $row['due']; ?>" readonly>
      </div>
      <div class="form-group">
        <label>Amount *</label>
        <input type="number" name="amount" id="amount" class="form-control" value="<?php echo $row['amount']; ?>"  oninput="calculateDue()">
      </div>
    </div>
  </div>
  <script>
    function calculateDue() {
      const total = parseFloat(document.getElementById('amountToBePaid').value) || 0;
    const paid = parseFloat(document.getElementById('amount').value) || 0;
    const due = total - paid;
    document.getElementById('due').value = due.toFixed(2); // 2 decimal points
  }
</script>
<div class="card shadow-sm" style="width: 300px; padding: 15px; text-align: center;">
  <img src="img/schoolQR.JPG" style="width: 100%; border: 1px solid #ccc; border-radius: 8px;" />
  <div class="mt-3" >
    <label class="form-control-label">Bank Name<span style="color: red;">*</span></label>
    <input type="text" style="text-align: center" class="form-control mb-2" readonly value="Punjab National Bank">
    
    <label class="form-control-label">Account Number<span style="color: red;">*</span></label>
    <input type="text" style="text-align: center" class="form-control" readonly value="0564056000010">
    
    <label class="form-control-label">RTGS/NEFT IFSC Code<span style="color: red;">*</span></label>
    <input type="text" style="text-align: center" class="form-control" readonly value="PUNB0056420">
  </div>
</div>
</div>
     
    </div>
  </div>
  
   
    

</div>


                 
  
           <!-- Container Row -->
<div class="container" style="margin-left:85px;" >
  <div class="row" >
    
    <!-- Card with Limited Width -->
    <div class="col-xl-6 col-lg-7 col-md-8 col-sm-12" style="bottom:50px">
      
      <div class="card shadow-sm mb-4" >
  <div class="card-header bg-primary text-white font-weight-bold" >
    Upload Payment Receipt
  </div>
  <div class="card-body" style="margin-left:10px">
    <div class="form-group row" style="display: flex; align-items: center; justify-content: space-between;">

      
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

<!-- Update button placed just below the file input -->
<?php if (isset($payment_id)) { ?>
  <div style="margin-top: 20px;margin-left:10px">
    <button type="submit" name="update" class="btn btn-primary px-4 py-2" style="width: 30%; padding: 10px 12px;">
      Update
    </button>
  </div>
  <?php } ?>
</form>
</div>
 </div>
</div>
     
</div>
<!-- </div>

</div> -->
<?php endif; ?>
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


              <!-- Input Group -->
                 <div class="row">
              <div class="col-lg-12">
              <div class="card mb-4">
                
                <div class="table-responsive p-3">
                  <table class="table align-items-center table-flush table-hover" id="dataTableHover">
                    <thead class="thead-light">
                      <tr>
                        <th>#</th>
                        <th>Registration Number</th>
                        <th>Name</th>
                        <th>Payment Type</th>
                        <th>Month</th>
                        <th>Date</th>
                        <th>Payment Mode</th>
                        <!-- <th>Amount</th> -->
                        <th>Amount Paid</th>
                        <th>Total Amount</th>
                        <th>Due</th>
                        <th>Status</th>
                        <th>Action</th>
                        <th>Edit</th>
                      </tr>
                    </thead>
                    
                      <tbody>

                    <?php
                      //  $query = "SELECT studentName, payment_type, amount, created_at, status FROM payments WHERE regId = '$regId' ORDER BY created_at DESC";
                      $query = "SELECT payment_id,regId, studentName,class,payment_type,month,payment_mode,amount_paying,grand_total,due, created_at, status FROM payments WHERE regId = '$regId'"; 

                        $rs = $conn->query($query);
                        $num = $rs->num_rows;
                        $sn=0;
                        $status="";
                        if($num > 0)

while ($rows = $rs->fetch_assoc()) {
$sn++;
$status = strtolower(trim($rows['status']));
$isReceived = ($status === 'received');
$paymentId = $rows['payment_id'];
$regId = $rows['regId'];
$payment_type = strtolower(trim($rows['payment_type'])); // IMPORTANT LINE

echo "<tr>
    <td>{$sn}</td>
    <td>{$rows['regId']}</td>
    <td>{$rows['studentName']}</td>
    <td>{$rows['payment_type']}</td>
    <td>" . ($rows['month'] == 'NA' ? 'N/A' : $rows['month']) . "</td>
    <td>{$rows['created_at']}</td>
    <td>{$rows['payment_mode']}</td>
    <td>{$rows['amount_paying']}</td>
    <td>{$rows['grand_total']}</td>
    <td>{$rows['due']}</td>
    <td>{$rows['status']}</td>
    <td>";

if ($isReceived) {
    $receiptLink = "#";
    $title = "No receipt available";

    if ($payment_type === 'admission') {
        $receiptLink = "generateReceipt.php?type=admission&payment_id={$paymentId}";
        $title = "Download Admission Receipt";
    } elseif ($payment_type === 'monthly') {
        $receiptLink = "generateReceipt.php?type=monthly&payment_id={$paymentId}";
        $title = "Download Monthly Receipt";
    } else {
        // Uniform/Book check
        $stmt = $conn->prepare("SELECT id FROM payment_uniforms WHERE regId = ?");
        $stmt->bind_param("s", $regId);
        $stmt->execute();
        $hasUniform = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        if ($hasUniform) {
            $receiptLink = "generateReceipt.php?type=uniform&regId={$regId}";
            $title = "Download Uniform/Book Receipt";
        }
    }

    echo "<a href='{$receiptLink}' target='_blank' class='btn btn-sm btn-light rounded-pill' title='{$title}'>
            <i class='fas fa-download'></i>
          </a>";
} else {
    echo "<a href='#' data-toggle='modal' data-target='#pendingModal' class='btn btn-sm btn-light rounded-pill' title='Receipt not available for pending payments'>
            <i class='fas fa-download'></i>
          </a>";
}

echo "</td>
    <td><a href='?action=edit&payment_id={$paymentId}'><i class='fas fa-fw fa-edit'></i></a></td>
</tr>";
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
                    </table>
                  </div>
                </div>
              </div>
              </div>
            </div>
            </div>



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
  </script>
</body>

<div class="modal fade" id="pendingModal" tabindex="-1" role="dialog" aria-labelledby="pendingModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document"> 
    <div class="modal-content">
      <div class="modal-header text-white" style="background-color: #007bff;">
        <h5 class="modal-title" id="pendingModalLabel">
          <i class="fas fa-exclamation-triangle mr-2"></i>Payment Pending
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true" style="font-size: 30px;">&times;</span>
        </button>
      </div>
      <div class="modal-body text-center">
        <p style="font-size: 18px;">🛑 Receipt is not available until payment is marked as <strong>Received</strong>.</p>
      </div>
      <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-secondary px-4" data-dismiss="modal">Okay</button>
      </div>
    </div>
  </div>
</div>


</html>