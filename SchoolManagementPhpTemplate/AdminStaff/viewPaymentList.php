
<?php 
error_reporting(0);
include '../Includes/dbcon.php';
include '../Includes/session.php';

//--------------------EDIT------------------------------------------------------------

 if (isset($_GET['payment_id']) && isset($_GET['action']) && $_GET['action'] == "edit")
	{
        $payment_id= $_GET['payment_id'];

        $query=mysqli_query($conn,"select * from payments where payment_id ='$payment_id'");
        $row=mysqli_fetch_array($query);

        //------------UPDATE-----------------------------
        if (isset($_POST['update'])) {
          $regId = $_POST['regId'];
          $studentName = $_POST['studentName'];
          $amount_paying = $_POST['amount_paying'];
          $status = $_POST['status']; 
      
          $query = mysqli_query($conn, "UPDATE payments 
              SET regId='$regId', studentName='$studentName', amount_paying='$amount_paying', status='$status'
              WHERE payment_id='$payment_id'");
      
      if ($query) {
        // $statusMsg = "<div class='alert alert-success'>Payment record updated successfully!</div>";
        // echo $statusMsg;
    
        // JS redirect after 2 seconds
        // echo "<script>
        //     setTimeout(function() {
        //         window.location.href = 'viewPaymentList.php';
        //     }, 2000);
        // </script>";
        // exit;
          // header("Location: " . $_SERVER['PHP_SELF'] . "?update=1");
            echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Updated',
                    text: 'Form submitted successfully updated!'
                }).then(function() {
                    window.location = 'viewPaymentList.php';
                });
            });
        </script>";
    }
     else {
              $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>An error Occurred!</div>";
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
<?php include 'includes/title.php';?>
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
            <h1 class="h3 mb-0 text-gray-800">Received/Not Received</h1>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="./">Home</a></li>
              <li class="breadcrumb-item active" aria-current="page">Received/Not Received</li>
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
                
                <div class="card-body">
                  <form method="post">
                   <div class="form-group row mb-3">
                   <div class="col-xl-6">
                        <label class="form-control-label">Reg number<span class="text-danger ml-2">*</span></label>
                      <input type="text" class="form-control"readonly name="regId" value="<?php echo $row['regId'];?>" id="exampleInputFirstName" >
                        </div>
                        <div class="col-xl-6">
                        <label class="form-control-label">Firstname<span class="text-danger ml-2">*</span></label>
                        <input type="text" class="form-control"readonly name="studentName" value="<?php echo $row['studentName'];?>" id="exampleInputFirstName" >
                        </div>
                        
                    </div>
                     <div class="form-group row mb-3">
                        <div class="col-xl-6">
                        <label class="form-control-label">Amount Paid<span class="text-danger ml-2">*</span></label>
                        <input type="text" class="form-control" readonly name="amount_paying" value="<?php echo $row['amount_paying'];?>" id="exampleInputFirstName" >
                        </div>
                        <!-- <div class="col-xl-6">
                        <label class="form-control-label">Status<span class="text-danger ml-2">*</span></label>
                      <input type="text" class="form-control" required name="status" value="<?php echo $row['status'];?>" id="exampleInputFirstName" >
                        </div> -->
                        <div class="col-xl-6">
                        <label class="form-control-label">Status <span class="text-danger ml-2">*</span></label>
                        <select class="form-control" name="status" required>
                          <option value="">-- Select Status --</option>
                          <option value="Received" <?php if($row['status'] == 'Received') echo 'selected'; ?>>Received</option>
                          <option value="Not Received" <?php if($row['status'] == 'Not Received') echo 'selected'; ?>>Not Received</option>
                        </select>
                      </div>

                    </div>
               
                      <?php
                    if (isset($payment_id))
                    {
                    ?>
                    <button type="submit" name="update" class="btn btn-warning">Update</button>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <?php
                    } else {           
                    ?>
                    <?php
                    }         
                    ?>
                  </form>
                </div>

              </div>
              <?php endif; ?>

              <!-- Input Group -->
                 <div class="row">
              <div class="col-lg-12">
              <div class="card mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                  <h6 class="m-0 font-weight-bold text-primary">All Student</h6>
                </div>
                <div class="table-responsive p-3">
                  <table class="table align-items-center table-flush table-hover" id="dataTableHover">
                    <thead class="thead-light">
                      <tr>
                        <th>#</th>
                        <th>Registration Number</th>
                        <th>Name</th>
                        <th>Payment Type</th>
                        <th>Payment Mode</th>
                        <th>Amount Paid</th>
                        <th>Total Amount</th>
                        <th>Due</th>
                        <th>Date</th>
                        <th>Photo</th>
                        <th>Status</th>
                         <th>Edit</th>
                      </tr>
                    </thead>
                
                    <tbody>

                  <?php
                      $query = "SELECT payment_id,regId, studentName, payment_type,payment_mode, amount_paying,grand_total,due,created_at,photo, status FROM payments ";
// echo $query;
// die();

                      $rs = $conn->query($query);
                      $num = $rs->num_rows;
                      $sn=0;
                      $status="";
                      if($num > 0)
                      { 
    //                     while ($rows = $rs->fetch_assoc())
    //                       {
    //                          $sn = $sn + 1;
                             
    //                         echo"
    //                           <tr>
    //                             <td>".$sn."</td>
    //                             <td>".$rows['regId']."</td>
    //                             <td>".$rows['studentName']."</td>
    //                             <td>".$rows['payment_type']."</td>
    //                             <td>".$rows['payment_mode']."</td>
    //                             <td>".$rows['amount_paying']."</td>
    //                             <td>".$rows['grand_total']."</td>
    //                             <td>".$rows['due']."</td>
    //                             <td>".$rows['created_at']."</td>
    //                             <td>";
                                
    // if ($isReceived) {
    //     echo "<a href='generateReceipt.php?payment_id={$paymentId}' target='_blank' class='btn btn-sm btn-light rounded-pill' title='Download Receipt'>
    //             <i class='fas fa-download'></i>
    //           </a>";
    // } else {
    //     // Same icon, but trigger JS modal
    //     // echo "<a href='#' onclick='showPendingNotice()' class='btn btn-sm btn-light rounded-pill' title='Receipt not available for pending payments'>
    //     //         <i class='fas fa-download'></i>
    //     //       </a>";
    //     echo "<a href='#' data-toggle='modal' data-target='#pendingModal' class='btn btn-sm btn-light rounded-pill' title='Receipt not available for pending payments'>
    //     <i class='fas fa-download'></i>
    //   </a>";

    // }
// $sn = 0;
// while ($rows = $rs->fetch_assoc()) {
//     $sn++;

//     $paymentId = $rows['payment_id'];
//     $status = strtolower(trim($rows['status'])); // status clean kiya
//     $isReceived = ($status === 'received');

//     echo "<tr>
//         <td>{$sn}</td>
//         <td>{$rows['regId']}</td>
//         <td>{$rows['studentName']}</td>
//         <td>{$rows['payment_type']}</td>
//         <td>{$rows['payment_mode']}</td>
//         <td>{$rows['amount_paying']}</td>
//         <td>{$rows['grand_total']}</td>
//         <td>{$rows['due']}</td>
//         <td>{$rows['created_at']}</td>
//         <td>";

//     // if ($isReceived) {
//     //     echo "<a href='generateReceipt.php?payment_id={$paymentId}' target='_blank'  title='Download Receipt'>
//     //             <i class='fas fa-download' style='font-size:18px; color:#007bff;'></i>
//     //           </a>";
//     // } else {
//     //     echo "<a href='#' data-toggle='modal' data-target='#pendingModal'  title='Receipt not available for pending payments'>
//     //              <i class='fas fa-download' style='font-size:18px; color:#007bff;'></i>
//     //           </a>";
//     // }
// if ($isReceived) {
//     $receiptLink = "#";
//     $title = "No receipt available";

//     if ($payment_type === 'admission' || $payment_type === 'monthly') {
//         // ✅ Updated filename here
//         $receiptLink = "receipt_generate.php?type={$payment_type}&payment_id={$paymentId}";
//         $title = "Download " . ucfirst($payment_type) . " Receipt";
//     } elseif ($payment_type === 'Books/Uniform') {
//         // Check if uniform data exists
//         $stmt = $conn->prepare("SELECT id FROM payment_uniforms WHERE regId = ?");
//         $stmt->bind_param("s", $regId);
//         $stmt->execute();
//         $hasUniform = $stmt->get_result()->num_rows > 0;
//         $stmt->close();

//         if ($hasUniform) {
//             // ✅ Updated filename here
//             $receiptLink = "receipt_generate.php?type=uniform&regId={$regId}";
//             $title = "Download Uniform/Book Receipt";
//         }
//     }

//     echo "<a href='{$receiptLink}' target='_blank' title='{$title}'>
//             <i class='fas fa-download' style='font-size:18px; color:#007bff;'></i>
//           </a>";
// } else {
//     echo "<a href='#' data-toggle='modal' data-target='#pendingModal' title='Receipt not available for pending payments'>
//             <i class='fas fa-download' style='font-size:18px; color:#007bff;'></i>
//           </a>";
// }

while ($rows = $rs->fetch_assoc()) {
    $sn++;

    $paymentId = $rows['payment_id'];
    $payment_type = strtolower(trim($rows['payment_type'])); // ✅ fixed
    $regId = $rows['regId']; // ✅ fixed
    $status = strtolower(trim($rows['status']));
    $isReceived = ($status === 'received');

    echo "<tr>
        <td>{$sn}</td>
        <td>{$rows['regId']}</td>
        <td>{$rows['studentName']}</td>
        <td>{$rows['payment_type']}</td>
        <td>{$rows['payment_mode']}</td>
        <td>{$rows['amount_paying']}</td>
        <td>{$rows['grand_total']}</td>
        <td>{$rows['due']}</td>
        <td>{$rows['created_at']}</td>
        <td>";

    if ($isReceived) {
        $receiptLink = "#";
        $title = "No receipt available";

        if ($payment_type === 'admission' || $payment_type === 'monthly') {
     $receiptLink = "generateReceipt.php?type={$payment_type}&payment_id={$paymentId}";

            $title = "Download " . ucfirst($payment_type) . " Receipt";
        } elseif ($payment_type === 'books/uniform') {
            $stmt = $conn->prepare("SELECT id FROM payment_uniforms WHERE regId = ?");
            $stmt->bind_param("s", $regId);
            $stmt->execute();
            $hasUniform = $stmt->get_result()->num_rows > 0;
            $stmt->close();

            if ($hasUniform) {
                // $receiptLink = "receipt_generate.php?type=uniform&regId={$regId}";
                $receiptLink = "generateReceipt.php?type=uniform&regId={$regId}";

                $title = "Download Uniform/Book Receipt";
            }
        }

        echo "<a href='{$receiptLink}' target='_blank' title='{$title}'>
             <i class='fas fa-download' style='font-size:18px; color:#007bff;'></i>
           </a>";
} else {
    echo "<a href='#' data-toggle='modal' data-target='#pendingModal' title='Receipt not available for pending payments'>
            <i class='fas fa-download' style='font-size:18px; color:#007bff;'></i>
          </a>";
}



                    
                              echo "</td>


                                <td>".$rows['status']."</td>
                                <td><a href='?action=edit&payment_id=".$rows['payment_id']."&action=edit'><i class='fas fa-fw fa-edit'></i></a></td>
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