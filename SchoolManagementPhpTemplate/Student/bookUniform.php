<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../Includes/dbcon.php';
include '../Includes/session.php';

$statusMsg = "";

if (isset($_POST['save'])) {
    // Generate unique payment ID
    $payment_id = uniqid("PAY");
    $class_code = $_POST['class_code'] ?? '';

     $session = $_SESSION['session'] ?? '';
    // Student session data
    $regId = $_SESSION['regId'] ?? '';
    $studentName = $_SESSION['studentName'] ?? '';
    $className = $_SESSION['className'] ?? '';
    $payment_mode = $_POST['payment_mode'] ?? 'Cash';
// $bookItems = $_POST['book_items'] ?? [];
// $notebookItems = $_POST['notebook_items'] ?? [];
// echo "<pre>";
// print_r($bookItems);
// print_r($notebookItems);
// echo "</pre>";
// print_r($_POST['book_items']);

// if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//     echo "<h3>Received Book Items:</h3>";
//     print_r($_POST['book_items'] ?? []);

//     echo "<h3>Received Notebook Items:</h3>";
//     print_r($_POST['notebook_items'] ?? []);
// }



 $status = 'Pending';
$month = $_POST['month'];
    $date = date('Y-m-d');

    // Payment details
    $grand_total = isset($_POST['grand_total']) ? floatval($_POST['grand_total']) : 0.00;
    $amount_paying = isset($_POST['amount_paying']) ? floatval($_POST['amount_paying']) : 0.00;
    $due = $grand_total - $amount_paying;

    // Handle missing payment_type safely
    $valid_payment_types = ['Books/Uniform', 'monthly', 'admission'];
    $payment_type = isset($_POST['payment_type']) && in_array($_POST['payment_type'], $valid_payment_types)
        ? $_POST['payment_type']
        : 'Books/Uniform';

    // Upload image
    $targetDir = "img/upload/";
    $originalFile = basename($_FILES["paymentImage"]["name"]);
    $fileName = time() . '_' . $originalFile;
    $targetFilePath = $targetDir . $fileName;
    $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
    $allowedTypes = array('jpg', 'jpeg', 'png', 'pdf');

    // Debug
//      echo "Payment ID being used: $payment_id<br>";

// echo "<pre>";
// print_r($_POST['book_items']);
// print_r($_POST['notebook_items']);
// echo "</pre>";

//           echo '<pre>';
//   print_r($_SESSION);
//   echo '</pre>';
//   echo '<pre>';
// print_r($_POST);
// echo '</pre>';

    // --- INSERT: payment_books ---
    // if (!empty($_POST['book_items']) && is_array($_POST['book_items'])) {
    //     foreach ($_POST['book_items'] as $book) {
    //         if (!empty($book['book_name']) && !empty($book['price'])) {
    //             $stmt = $conn->prepare("INSERT INTO payment_books (payment_id, class_code, book_name, price) VALUES (?, ?, ?, ?)");
    //             $stmt->bind_param("sssd", $payment_id, $class_code, $book['book_name'], $book['price']);
    //             $stmt->execute();
    //         }
    //     }
    // }

    // // --- INSERT: payment_notebooks ---
    // if (!empty($_POST['notebook_items']) && is_array($_POST['notebook_items'])) {
    //     foreach ($_POST['notebook_items'] as $note) {
    //         if (!empty($note['subject'])) {
    //             $stmt = $conn->prepare("INSERT INTO payment_notebooks (payment_id, class_code, subject, notebook_type, quantity, price) VALUES (?, ?, ?, ?, ?, ?)");
    //             $stmt->bind_param("ssssid", $payment_id, $class_code, $note['subject'], $note['notebook_type'], $note['quantity'], $note['price']);
    //             $stmt->execute();
    //         }
    //     }
    // }
 
// var_dump($_POST['book_items']);

    // --- INSERT: payment_uniforms ---
    $uniform_stmt = $conn->prepare("INSERT INTO payment_uniforms (regId,
        payment_id, torso_type, torso_size, torso_price, bottom_type, bottom_size, bottom_price,
        tie_size, tie_price, belt_size, belt_price, socks_size, socks_price, other_amount,book_list_selector,amount_paying, grand_total, due
    ) VALUES (?,?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?,?,?)");

  
// Assign all posted data FIRST
$uniform_type = $_POST['uniform_type'];
$uniform_size = $_POST['uniform_size'];
$uniform_price = $_POST['uniform_price'];
$bottom_type = $_POST['bottom_uniform_type'];
$bottom_size = $_POST['bottom_uniform_size'];
$bottom_price = $_POST['bottom_uniform_price'];
$tie_size = $_POST['tie_size'];
$tie_price = $_POST['tie_price'];
$belt_size = $_POST['belt_size'];
$belt_price = $_POST['belt_price'];
$socks_size = $_POST['socks_size'];
$socks_price = $_POST['socks_price'];
$other_amount = $_POST['other_amount'];
$book_list_selector = $_POST['book_list_selector'] ?? '';

$amount_paying = isset($_POST['amount_paying']) ? floatval($_POST['amount_paying']) : 0;
$grand_total = isset($_POST['grand_total']) ? floatval($_POST['grand_total']) : 0;
$due = $grand_total - $amount_paying;

// Now bind the variables AFTER assignment ✅
$uniform_stmt->bind_param("sssssssssssdssdsddd",
    $regId,
    $payment_id,
    $uniform_type,
    $uniform_size,
    $uniform_price,
    $bottom_type,
    $bottom_size,
    $bottom_price,
    $tie_size,
    $tie_price,
    $belt_size,
    $belt_price,
    $socks_size,
    $socks_price,
    $other_amount,
    $book_list_selector,
    $amount_paying,
    $grand_total,
    $due
);




// echo "amount_paying: $amount_paying<br>";
// echo "grand_total: $grand_total<br>";
// echo "due: $due<br>";

    $uniform_stmt->execute();

    // --- File upload & insert into payments ---
    if (in_array($fileType, $allowedTypes)) {
        if (move_uploaded_file($_FILES["paymentImage"]["tmp_name"], $targetFilePath)) {

$sql = "INSERT INTO payments 
    (payment_id, regId, studentName,className,payment_mode, payment_type, grand_total, amount_paying, due, photo, created_at,month,status)
    VALUES 
    ('$payment_id', '$regId', '$studentName','$className','$payment_mode', '$payment_type', '$grand_total', '$amount_paying', '$due', '$fileName', '$date','$month','$status')";

            $query = mysqli_query($conn, $sql);

            // echo $sql;
            // die();
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
                // $statusMsg = "<div class='alert alert-success'>Payment inserted successfully with photo!</div>";
            } else {
                $statusMsg = "<div class='alert alert-danger'>Database insert failed! Error: " . mysqli_error($conn) . "</div>";
            }

        } else {
            $statusMsg = "<div class='alert alert-danger'>File upload failed!</div>";
        }
    } else {
        $statusMsg = "<div class='alert alert-danger'>Only JPG, JPEG, PNG, and PDF files are allowed!</div>";
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
  $product_name = "Check Shirts";
  $sizes = [20, 22, 24, 26, 28, 30, 32, 34, 36, 38, 40, 42, 44];
  $quantities = [222, 234, 247, 260, 273, 291, 309, 330, 340, 355, 0, 0, 0]; // sample quantities

  // Insert product
  $stmt = $conn->prepare("INSERT INTO products (product_name) VALUES (?)");
  $stmt->bind_param("s", $product_name);
  $stmt->execute();
  $product_id = $stmt->insert_id;
  $stmt->close();

  // Insert size-wise quantities
  $stmt2 = $conn->prepare("INSERT INTO product_sizes (product_id, size, quantity) VALUES (?, ?, ?)");
  for ($i = 0; $i < count($sizes); $i++) {
      $stmt2->bind_param("iii", $product_id, $sizes[$i], $quantities[$i]);
      $stmt2->execute();
  }
  $stmt2->close();


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
            <h1 class="h3 mb-0 text-gray-800">Book & Uniform</h1>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="./">Home</a></li>
              <li class="breadcrumb-item active" aria-current="page">Book & Uniform</li>
            </ol>
          </div>

          <div class="row">
            <div class="col-lg-12">
              <!-- Form Basic -->
              <div class="card mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                  <h6 class="m-0 font-weight-bold text-primary">Pay Book & Uniform Fees</h6>
                    <?php echo $statusMsg; ?>
                </div>
                <div class="card-body" >
                <form method="post" enctype="multipart/form-data" onsubmit="return beforeSubmit()">
                  <div class="form-group row mb-3">
                  <div class="col-xl-8" >
                     <div class="form-group row mb-3" > 
                        <div class="col-xl-4" >
                            <div class="card p-4" style="width:113vh;left:3vh;" >
                    <div class="row">
                   
                     <div class="col-md-4" >
                            
                     
                           <?php
                           
// Book List
  $bookData = [];
  $result = $conn->query("SELECT * FROM book_list");
  while ($row = $result->fetch_assoc()) {
      $code = $row['class_code'];
      $bookData[$code][] = [
          'book_name' => $row['book_name'],
          'amount' => $row['price']
      ];
  }

  // Notebook List
  $notebookData = [];
  //  global $result2; 
  $result2 = $conn->query("SELECT * FROM notebook_list");
  if (!$result2) {
      die("Notebook Query Failed: " . $conn->error);
  }

  while ($row = $result2->fetch_assoc()) {
      $code = $row['class_code'];
      $notebookData[$code][] = [
          'subject' => $row['subject'],
          'quantity' => $row['quantity'],
          'details' => $row['notebook_type'],
          'amount' => $row['price']
      ];
  }
?>
<?php
// Book list selected from dropdown
// $selectedBookList = $_POST['book_list_selector'] ?? '';
// $books = $bookData[$selectedBookList] ?? [];
// $notebooks = $notebookData[$selectedBookList] ?? [];
?>

<!-- HTML -->
<!-- Selectors Section -->
    <div style="display: flex; gap: 30px; margin: 20px;">
        <!-- Select Class -->
        <!-- <div> -->
            <!-- <label for="class_selector" style="font-weight: 500; font-size: 16px;">Class</label><br>
            <select id="class_selector" onchange="updateBookList()"
                style="width: 220px; padding: 8px; border-radius: 6px; border: 1px solid #ccc; margin-top: 5px;">
                <option value="">-- Select Class --</option>
                <option value="Nursery">Nursery</option>
                <option value="LKG">LKG</option>
                <option value="UKG">UKG</option>
                <?php for ($i = 1; $i <= 10; $i++): ?>
                    <option value="Class <?= $i ?>">Class <?= $i ?></option>
                <?php endfor; ?>
            </select>
        </div> -->
        
    <div class="form-group">
        <label>Class</label>
        <input type="text" placeholder="Auto-filled" name="classId" id="classId"  style="width: 220px; padding: 8px; border-radius: 6px; border: 1px solid #ccc; margin-top: 5px;" class="form-control" value="<?= $_SESSION['className'] ?? '' ?>" readonly>
        </div>
        <!-- Select Book List -->
        <div>
            <label for="book_list_selector" style="font-weight: 500; font-size: 16px;">Select Book List</label><br>
            <select id="book_list_selector" required  name="book_list_selector" onchange="renderData()"
          

                style="width: 220px; padding: 8px; border-radius: 6px; border: 1px solid #ccc; margin-top: 5px;">
                <option value="">-- Select Book List --</option>
                    
            <option value="nursery_A">Nursery - Book List A</option>
            <option value="nursery_B">Nursery - Book List B</option>
            <option value="lkg_A">LKG - Book List A</option>
            <option value="lkg_B">LKG - Book List B</option>
            <option value="ukg_A">UKG - Book List A</option>
            <option value="ukg_B">UKG - Book List B</option>
                <?php for ($i = 1; $i <= 10; $i++): ?>
                    <option value="class<?= $i ?>_A">Class <?= $i ?> - Book List A</option>
                    <option value="class<?= $i ?>_B">Class <?= $i ?> - Book List B</option>
                <?php endfor; ?>
            </select>
        
        </div>
         <div class="form-group">
                        <label class="font-weight" for="payment_mode" style="margin-left:10px" >Payment Mode<span class="text-danger ml-2">*</span></label>
                        <select class="form-control" id="payment_mode" name="payment_mode" required style="width: 35vh; max-width: 350px; padding: 10px ; margin-left:10px">
                          <option value="">-- Select Payment Mode --</option>
                          <option value="UPI/QR"> UPI/QR</option>
                          <option value="Account Transation"> Account Transation</option>
                          <option value="Cash"> Cash</option>
                        </select>
                        </div>
    </div>

    
    <!-- Tables Section -->
    <div style="display: flex; gap: 40px; margin: 20px;">
        <!-- Book List Table -->
          <!-- <div>
              <h4 style="margin-bottom: 10px;">Book List</h4>
              <table id="book_table" class="table table-bordered" style="border-collapse: collapse; width: 300px; font-size: 15px;">
                  <thead>
                      <tr>
                          <th style="background-color: #f0f0f0; padding: 8px; border: 1px solid #ccc;">Book Name</th>
                          <th style="background-color: #f0f0f0; padding: 8px; border: 1px solid #ccc;">Amount</th>
                      </tr>
                  </thead>
                  <tbody></tbody>
                  <tfoot>
                      <tr>
                          <th style="padding: 8px; border: 1px solid #ccc;">Total</th>
                          <th style="padding: 8px; border: 1px solid #ccc;">₹<span id="book_total">0</span></th>
                      </tr>
                  </tfoot>
              </table>
          </div>
          -->
        <!-- <div>
  <h4 style="margin-bottom: 10px;">Book List</h4>
  <table id="book_table" class="table table-bordered" style="border-collapse: collapse; width: 300px; font-size: 15px;">
    <thead>
      <tr>
        <th style="background-color: #f0f0f0; padding: 8px; border: 1px solid #ccc;">Book Name</th>
        <th style="background-color: #f0f0f0; padding: 8px; border: 1px solid #ccc;">Amount</th>
      </tr>
    </thead>
  
    <tbody style="display: block; height: 400px; overflow-y: auto; width: 100%;">
    </tbody>
    <tfoot style="display: table; width: 100%; table-layout: fixed;">
      <tr>
        <th style="padding: 8px; border: 1px solid #ccc;">Total</th>
        <th style="padding: 8px; border: 1px solid #ccc;">₹<span id="book_total">0</span></th>
      </tr>
    </tfoot>
  </table>
</div> -->
<!-- Book List -->
<div style="width: 110vh;">
  <h4 style="margin-bottom: 10px;">Book List</h4>
  <table class="table table-bordered"  style="border-collapse: collapse; width: 40vh; font-size: 15px;">
    
    <thead>
      <tr>
        <!-- <th>Book Name</th>
        <th>Amount</th> -->
         <th style="background-color: #f0f0f0; padding: 8px; width: 20vh; border: 1px solid #ccc;">Book Name</th>
        <th style="background-color: #f0f0f0; padding: 8px;width: 20vh; border: 1px solid #ccc;">Amount</th>
      </tr>
    </thead>
  </table>

  <!-- Scrollable Body -->
 <div style="max-height: 505px; overflow-y: auto; border: 1px solid #dee2e6;">
  <table class="table table-bordered" style="margin-bottom: 0; border-collapse: collapse; width: 40vh; font-size: 15px;">
   
      <tbody id="book_body">
        <!-- Book rows inserted here dynamically -->
        <!-- Example:
        <tr><td>Math Book</td><td>₹120.00</td></tr>
        -->
      </tbody>
    </table>
  </div>

  <!-- Total -->
  <table class="table table-bordered" style="border-collapse: collapse; width: 100%; font-size: 15px;">
    <tbody>
      <tr>
        <th>Total</th>
        <th id="book_total">₹0.00</th>
      </tr>
    </tbody>
  </table>
</div>


        
        <!-- Notebook List Table -->
        <div>
            <h4 style="margin-bottom: 10px;">Notebook List</h4>
            <table id="notebook_table" class="table table-bordered" style="border-collapse: collapse; width: 380px; font-size: 15px;">
                <thead>
                    <tr>
                        <th style="background-color: #f0f0f0; padding: 8px; border: 1px solid #ccc;">Subject</th>
                        <th style="background-color: #f0f0f0; padding: 8px; border: 1px solid #ccc;">Qty</th>
                        <th style="background-color: #f0f0f0; padding: 8px; border: 1px solid #ccc;">Details</th>
                        <th style="background-color: #f0f0f0; padding: 8px; border: 1px solid #ccc;">Amount</th>
                    </tr>
                </thead>
                <tbody>  </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" style="padding: 8px; border: 1px solid #ccc;">Total</th>
                        <th style="padding: 8px; border: 1px solid #ccc;">₹<span id="notebook_total">0</span></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>


 <script>
  const bookData = <?php echo json_encode($bookData); ?>;
  const notebookData = <?php echo json_encode($notebookData); ?>;

  const reverseClassMap = {
    "Nursery": "Nursery",
    "LKG": "LKG",
    "UKG": "UKG",
    "I": "Class 1",
    "II": "Class 2",
    "III": "Class 3",
    "IV": "Class 4",
    "V": "Class 5",
    "VI": "Class 6",
    "VII": "Class 7",
    "VIII": "Class 8",
    "IX": "Class 9",
    "X": "Class 10"
  };

  document.addEventListener("DOMContentLoaded", () => {
    const classValue = document.getElementById("classId").value; // Already filled (e.g. I)
    const readableClass = reverseClassMap[classValue] || classValue; // e.g. Class 1
    const classKey = readableClass.toLowerCase().replace(/\s+/g, ""); // e.g. class1
    const bookListSelector = document.getElementById("book_list_selector");
    bookListSelector.innerHTML = '<option value="">-- Select Book List --</option>';

    const hasA = bookData[classKey + "_A"] || notebookData[classKey + "_A"];
    const hasB = bookData[classKey + "_B"] || notebookData[classKey + "_B"];

    if (hasA) {
      const optionA = document.createElement("option");
      optionA.value = classKey + "_A";
      optionA.text = `${readableClass} - Book List A`;
      bookListSelector.appendChild(optionA);
    }

    if (hasB) {
      const optionB = document.createElement("option");
      optionB.value = classKey + "_B";
      optionB.text = `${readableClass} - Book List B`;
      bookListSelector.appendChild(optionB);
    }

    // Clear table data if any
    clearRenderedData();

    // Render data when book list is selected
    bookListSelector.addEventListener("change", renderData);
  });

  function renderData() {
    const selectedCode = document.getElementById("book_list_selector").value;
    const books = bookData[selectedCode] || [];
    let bookRows = '', bookTotal = 0;
    books.forEach(book => {
      bookRows += `<tr><td>${book.book_name}</td><td>₹${book.amount}</td></tr>`;
      bookTotal += parseFloat(book.amount);
    });
    // document.querySelector("#book_table tbody").innerHTML = bookRows;
    document.querySelector("#book_body").innerHTML = bookRows;
    document.getElementById("book_total").textContent = bookTotal.toFixed(2);

    const notes = notebookData[selectedCode] || [];
    let noteRows = '', noteTotal = 0;
    notes.forEach(note => {
      noteRows += `<tr>
        <td>${note.subject}</td>
        <td>${note.quantity}</td>
        <td>${note.details}</td>
        <td>₹${note.amount}</td>
      </tr>`;
      noteTotal += parseFloat(note.amount);
    });
    document.querySelector("#notebook_table tbody").innerHTML = noteRows;
    document.getElementById("notebook_total").textContent = noteTotal.toFixed(2);

    const totalAmount = bookTotal + noteTotal;
    document.getElementById("total_amount").textContent = totalAmount.toFixed(2);
     
  }

  function clearRenderedData() {
    document.querySelector("#book_body").innerHTML = "";
    document.querySelector("#book_table tbody").innerHTML = "";
    document.getElementById("book_total").textContent = "0.00";
    document.querySelector("#notebook_table tbody").innerHTML = "";
    document.getElementById("notebook_total").textContent = "0.00";
    document.getElementById("total_amount").textContent = "0.00";
  }
  
</script>




  


  
                        </div>
                        
 <!-- Torso Wear    -->
     <?php
	$torsoData = [];
	$result = $conn->query("SELECT * FROM torso_wear ORDER BY uniform_type, size");
 
	while ($row = $result->fetch_assoc()) {
		$type = $row['uniform_type'];
   
		$size = $row['size'];
		$price =(int) $row['price'];
		$torsoData[$type][$size] = $price;
	}
  
	?>

<!-- Torso Wear Dropdown -->
<div class="row">
    <div class="col-md-4">
        <label style="margin-left:10px;margin-top:15px">Torso Wear</label>
        <select class="form-control" required id="uniform_type" name="uniform_type" onchange="populateSizes()"
        style="width: 35vh; max-width: 350px; padding: 10px ; margin-left:10px">
            <option value="">-- Select Uniform --</option>
            <?php foreach ($torsoData as $type => $sizes): ?>
                <option value="<?= htmlspecialchars($type) ?>"><?= htmlspecialchars($type) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Size Dropdown -->
    <div class="col-md-4">
        <label style="margin-left:5px; margin-top:15px">Size</label>
        <select class="form-control" required id="uniform_size" name="uniform_size" onchange="updatePrice()"
        style="width: 255px; max-width: 350px; padding: 10px ; margin-left:2px;">
            <option value="">-- Select Size --</option>
        </select>
    </div>

    <!-- Price Display -->
    <div class="col-md-4">
        <label style="margin-top:15px">Price</label>
        <input type="text" class="form-control" id="uniform_price" name="uniform_price" readonly placeholder="Auto-filled"
       style="width: 34vh; max-width: 350px; padding: 10px ; margin-right:2px">
    </div>
</div>


<script>
    const torsoData = <?= json_encode($torsoData); ?>;

    function populateSizes() {
        const type = document.getElementById('uniform_type').value;
        const sizeDropdown = document.getElementById('uniform_size');
        const priceInput = document.getElementById('uniform_price');

        // Reset
        sizeDropdown.innerHTML = '<option value="">-- Select Size --</option>';
        priceInput.value = '';

        if (torsoData[type]) {
            for (const size in torsoData[type]) {
                const opt = document.createElement('option');
                opt.value = size;
                opt.text = size;
                sizeDropdown.appendChild(opt);
            }
        }
    }

    function updatePrice() {
        const type = document.getElementById('uniform_type').value;
        const size = document.getElementById('uniform_size').value;
        const price = torsoData[type] && torsoData[type][size] ? torsoData[type][size] : '';
        document.getElementById('uniform_price').value = price;
    }
</script>


<!-- Buttom Wear -->
<?php
$bottomData = [];
$result = $conn->query("SELECT * FROM bottom_wear ORDER BY uniform_type, size");

while ($row = $result->fetch_assoc()) {
    $type = $row['uniform_type'];
    $size = $row['size'];
    $price = $row['price'];
    $bottomData[$type][$size] = $price;
}
?>
<!-- Bottom Wear Dropdown -->
<div class="row">
    <div class="col-md-4">
        <label style="margin-left:10px;margin-top:15px">Bottom Wear</label>
        <select class="form-control" required id="bottom_uniform_type" name="bottom_uniform_type" onchange="populateBottomSizes()"
        style="width: 35vh; max-width: 350px; padding: 10px ; margin-left:10px">
            <option value="">-- Select Uniform --</option>
            <?php foreach ($bottomData as $type => $sizes): ?>
                <option value="<?= htmlspecialchars($type) ?>"><?= htmlspecialchars($type) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Size Dropdown -->
    <div class="col-md-4">
        <label style="margin-left:5px; margin-top:15px">Size</label>
        <select class="form-control" required id="bottom_uniform_size" name="bottom_uniform_size" onchange="updateBottomPrice()"
        style="width: 255px; max-width: 350px; padding: 10px ; margin-left:2px;">
            <option value="">-- Select Size --</option>
        </select>
    </div>

    <!-- Price Display -->
        <div class="col-md-4">
            <label style="margin-top:15px">Price</label>
            <input type="text" class="form-control" id="bottom_uniform_price" name="bottom_uniform_price" readonly placeholder="Auto-filled"
            style="width: 34vh; max-width: 350px; padding: 10px ; margin-right:2px">
        </div>
</div>
<script>
    const bottomData = <?= json_encode($bottomData); ?>;

    function populateBottomSizes() {
        const type = document.getElementById('bottom_uniform_type').value;
        const sizeDropdown = document.getElementById('bottom_uniform_size');
        const priceInput = document.getElementById('bottom_uniform_price');

        sizeDropdown.innerHTML = '<option value="">-- Select Size --</option>';
        priceInput.value = '';

        if (bottomData[type]) {
            for (const size in bottomData[type]) {
                const opt = document.createElement('option');
                opt.value = size;
                opt.text = size;
                sizeDropdown.appendChild(opt);
            }
        }
    }

    function updateBottomPrice() {
        const type = document.getElementById('bottom_uniform_type').value;
        const size = document.getElementById('bottom_uniform_size').value;
        const price = bottomData[type] && bottomData[type][size] ? bottomData[type][size] : '';
        document.getElementById('bottom_uniform_price').value = price;
    }
</script>
<?php
// Tie Data
$tieData = [];
$result = $conn->query("SELECT * FROM tie_wear ORDER BY size");
while ($row = $result->fetch_assoc()) {
    $tieData[$row['size']] = $row['price'];
}

// Belt Data
$beltData = [];
$result = $conn->query("SELECT * FROM belt_wear ORDER BY size");
while ($row = $result->fetch_assoc()) {
    $beltData[$row['size']] = $row['price'];
}

// Socks Data
$socksData = [];
$result = $conn->query("SELECT * FROM socks_wear ORDER BY size");
while ($row = $result->fetch_assoc()) {
    $socksData[$row['size']] = $row['price'];
}
?>
<!-- Tie -->
<div class="row">
    
    <div class="col-md-6">
        <label style="margin-left:10px; margin-top:15px;">Tie</label>
        <select class="form-control" required id="tie_size" name="tie_size" onchange="updateTiePrice()"
            style="width: 100%; padding: 10px; margin-left:10px;">
            <option value="">-- Select Tie Size --</option>
            <?php foreach ($tieData as $size => $price): ?>
                <option value="<?= htmlspecialchars($size) ?>"><?= htmlspecialchars($size) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div class="col-md-6">
        <label style="margin-top:15px;">Price</label>
        <input type="text" class="form-control" id="tie_price" name="tie_price" readonly placeholder="Auto-filled"
            style="width: 100%; padding: 10px; margin-right:130px;">
    </div>
</div>
<div class="col-md-3">
            <label style="margin-top:15px;margin-left:10px">Other</label>
            <input type="number" required class="form-control" id="other_amount" name="other_amount" placeholder="Enter amount"
                oninput="calculateGrandTotal()" style="width:34vh">
        </div>

<!-- Belt -->
<div class="row">
    <div class="col-md-6">
        <label style="margin-left:10px; margin-top:15px;">Belt</label>
        <select class="form-control" required id="belt_size" name="belt_size" onchange="updateBeltPrice()"
            style="width: 100%; padding: 10px; margin-left:10px;">
            <option value="">-- Select Belt Size --</option>
            <?php foreach ($beltData as $size => $price): ?>
                <option value="<?= htmlspecialchars($size) ?>"><?= htmlspecialchars($size) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-6">
        <label style="margin-top:15px;">Price</label>
        <input type="text" class="form-control" id="belt_price" name="belt_price" readonly placeholder="Auto-filled"
            style="width: 100%; padding: 10px;margin-right:128px;">
    </div>
</div>
                        
<div class="form-group">
  <label>Month</label>
  <input type="text" style="width: 32vh; max-width: 350px; padding: 10px ; margin-left:10px" name="month" class="form-control" value="NA" readonly>
</div>
<!-- <div class="col-md-3">
            <label style="margin-top:15px;margin-left:10px">Other</label>
            <input type="number" class="form-control" id="other_amount" name="other_amount" placeholder="Enter amount"
                oninput="calculateGrandTotal()" style="width:34vh">
        </div> -->


<!-- Socks -->
<div class="row">
    <div class="col-md-6">
        <label style="margin-left:10px; margin-top:15px;">Socks</label>
        <select class="form-control" required id="socks_size" name="socks_size" onchange="updateSocksPrice()"
            style="width: 100%; padding: 10px; margin-left:10px;">
            <option value="">-- Select Socks Size --</option>
            <?php foreach ($socksData as $size => $price): ?>
                <option value="<?= htmlspecialchars($size) ?>"><?= htmlspecialchars($size) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-6">
        <label style="margin-top:15px;">Price</label>
        <input type="text" class="form-control" id="socks_price" name=" socks_price" readonly placeholder="Auto-filled"
            style="width: 100%; padding: 10px;margin-right:118px;">
    </div>
</div>



<div class="form-group" style="margin-top:10px">
  <label >Session</label>
  <input type="text" name="session"  class="form-control"  style="width: 32vh; max-width: 350px; padding: 10px ; margin-left:10px" placeholder="Auto-filled" value="<?= $_SESSION['session'] ?? '' ?>" readonly>
</div>

<script>
    const tieData = <?= json_encode($tieData); ?>;
    const beltData = <?= json_encode($beltData); ?>;
    const socksData = <?= json_encode($socksData); ?>;

    function updateTiePrice() {
        const size = document.getElementById('tie_size').value;
        document.getElementById('tie_price').value = tieData[size] ?? '';
    }

    function updateBeltPrice() {
        const size = document.getElementById('belt_size').value;
        document.getElementById('belt_price').value = beltData[size] ?? '';
    }

    function updateSocksPrice() {
        const size = document.getElementById('socks_size').value;
        document.getElementById('socks_price').value = socksData[size] ?? '';
    }
</script>



<script>
function calculateGrandTotal() {
    let total = 0;

    // Uniform and other item prices
    const priceFields = [
        'uniform_price',
        'bottom_uniform_price',
        'tie_price',
        'belt_price',
        'socks_price',
        'other_amount'
    ];

    priceFields.forEach(id => {
        const value = parseFloat(document.getElementById(id)?.value) || 0;
        total += value;
    });

    // Add book and notebook totals
    const bookTotal = parseFloat(document.getElementById('book_total')?.textContent) || 0;
    const notebookTotal = parseFloat(document.getElementById('notebook_total')?.textContent) || 0;

    total += bookTotal + notebookTotal;

    // Update Grand Total field
    const grandTotalField = document.getElementById('grand_total');
    if (grandTotalField) {
        grandTotalField.value = total.toFixed(2);
    }

    // Calculate due amount
    const amountPaying = parseFloat(document.getElementById('amount_paying')?.value) || 0;
    const dueAmount = total - amountPaying;

    const dueAmountField = document.getElementById('due_amount');
    if (dueAmountField) {
        dueAmountField.value = dueAmount.toFixed(2);
    }
}

// Recalculate every second
document.addEventListener('DOMContentLoaded', function() {
    calculateGrandTotal();

    // Auto update every second
    setInterval(calculateGrandTotal, 1000);

    // Also update when user changes "Amount Paying"
    document.getElementById('amountPaying').addEventListener('input', calculateGrandTotal);
});
</script>


<div class="col-md-6">
    <label style="margin-top:15px; font-weight: bold;">Grand Total</label>
    <input type="text" class="form-control" id="grand_total" name="grand_total" readonly placeholder="Total"
        style="width: 36vh; padding: 10px; background: #f9f9f9; font-weight: bold;">
</div>
<!-- Due Amount -->
<div class="col-md-6" style="right:17vh">
    <label style="margin-top:15px; font-weight: bold; right:5vh">Due Amount</label>
    <input type="text" class="form-control" id="due_amount" name="due_amount" readonly
        placeholder="Due"
        style="width: 36vh; padding: 10px; background: #f9f9f9; font-weight: bold;margin-right:5vh;">
</div>
     <div style="margin-left:2vh;margin-top:10px;">
                        <label style="font-weight: bold;display: inline; font-size: 15px;">Amount Paying (₹)</label><br>
                        <input type="number" name="amount_paying" id="amount_paying"
                            placeholder="Enter amount"
                            style="padding: 10px 12px;  border-radius: 6px; border: 1px solid #ccc; width: 36vh; font-size: 14px;" required>
                    </div>



                      </div>
                  </div>
                 

<div class="form-group row mb-3" style="margin-bottom: 1.5rem;">
    <div style="display: flex; align-items: flex-start; gap: 40px; margin-bottom: 30px;">

        <!-- Left Side -->
        <!-- <div style="flex: 1;"> -->

            <!-- Grand Total + Amount Paying side-by-side -->
            <!-- <div style="display: flex; gap: 20px; margin-bottom: 20px;"> -->
            

                <!-- Amount Paying input -->
                    
                    </div>
                    <!-- </div> -->
                    
            </div>
            </div>
            </div>
            </div>
            
 <div class="card shadow-sm mb-4 mx-auto" style="width: 290px; height: 760px; ">
 <!-- <div class="card shadow-sm mb-4 mx-auto" style="width: 290px; height: 760px; "> -->
  <div class="card-body ">
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


            <!-- Container Row -->
<div class="container" style="margin-left:3px; ">
  <div class="row" >
    
    <!-- Card with Limited Width -->
    <div class="col-xl-6 col-lg-7 col-md-8 col-sm-12">
     
      <div class="card shadow-sm mb-4"  >
  <div class="card-header bg-primary text-white font-weight-bold" >
    Upload Payment Receipt
  </div>
  <div class="card-body" style="margin-left:10px">
    <div class="form-group row" style="display: flex; align-items: center; justify-content: space-between;">

      <!-- File Drop Zone and Input -->
      <div class="col-md-7 mb-3" 
           ondrop="handleDrop(event)" 
           ondragover="event.preventDefault()" 
           style="border: 2px dashed #007bff; padding: 20px;  height:23vh; margin-top:10px; text-align: center; border-radius: 10px; background-color: #f9f9f9;">

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
        <button type="submit" name="save"  class="btn btn-primary px-4 py-2">
          Save
        </button>
      </form>
      </div>

    </div>
  </div>
</div>
        <script>
function triggerClick() {
    document.querySelector('#paymentImage').click();
}

function displayImage(e) {
    if (e.files && e.files[0]) {
        const reader = new FileReader();
        reader.onload = function (event) {
            document.querySelector('#paymentDisplay').setAttribute('src', event.target.result);
        }
        reader.readAsDataURL(e.files[0]);
    }
}
</script>
 

                    
                  </div>
                <!-- </div> -->

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