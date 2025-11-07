
<?php
// // session_start();
// error_reporting(E_ALL);
// // error_reporting(0);
// ini_set('display_errors', 1);
// // if (session_status() === PHP_SESSION_NONE) {
// //     session_start();
// // }


// include '../Includes/dbcon.php';
// include '../Includes/session.php';

// $submittedMessage = "";
// $photoPath = "";

// // Get regId and className
// $regId = $_GET['regId'] ?? ($_SESSION['regId'] ?? '');
// // $className = strtoupper(trim($_GET['className'] ?? ($_SESSION['className'] ?? '')));
// $className = intval($_GET['className'] ?? 0);

// // Sanitize inputs
// $regId = mysqli_real_escape_string($conn, $regId);
// $className = mysqli_real_escape_string($conn, $className);
// // Subject mapping
// // $subjectMap = [
// //     'I' => ['English', 'Hindi/Bengali', 'Math', 'Computer', 'EVS', 'Practical', 'Drawing'],
// //     'II' => ['English', 'Hindi/Bengali', 'Math', 'Computer', 'EVS', 'Practical', 'Drawing'],
// //     'III' => ['English', 'Hindi/Bengali', 'Math', 'Computer', 'EVS', 'Practical', 'Drawing'],
// //     'IV' => ['English', 'Hindi/Bengali', 'Math', 'Computer', 'EVS', 'Practical', 'Drawing'],
// //     'V' => ['English', 'Hindi/Bengali', 'Math', 'Computer', 'EVS', 'Practical', 'Drawing'],
// //     'VI' => ['English', 'Hindi/Bengali', 'Math', 'Computer', 'SST', 'Science', 'Practical', 'Drawing'],
// //     'VII' => ['English', 'Hindi/Bengali', 'Math', 'Computer', 'SST', 'Science', 'Practical', 'Drawing'],
// //     'VIII' => ['English', 'Hindi/Bengali', 'Math', 'Computer', 'SST', 'Science', 'Practical', 'Drawing'],
// //     'X' => ['English', 'Hindi/Bengali', 'Math', 'Computer', 'SST', 'Science', 'Practical']
// // ];

// $subjects = $subjectMap[$className] ?? [];

// // Fetch student
// $studentDetails = [];
// if (!empty($regId)) {
//     $studentQuery = "SELECT * FROM tblstudents WHERE regId = '" . mysqli_real_escape_string($conn, $regId) . "' LIMIT 1";
//     $studentResult = mysqli_query($conn, $studentQuery);
//     if ($studentResult && mysqli_num_rows($studentResult) > 0) {
//         $studentDetails = mysqli_fetch_assoc($studentResult);
//         $className = strtoupper(trim($studentDetails['className'] ?? 'I'));
//         $subjects = $subjectMap[$className] ?? [];
//     }
// }
// // $subjects = [];
// // $subjectQuery = "SELECT * FROM subject_master WHERE className = '$className'";
// $subjectQuery = "SELECT * FROM subject_master WHERE className = '" . mysqli_real_escape_string($conn, $className) . "'";

// $subjectResult = mysqli_query($conn, $subjectQuery);
// if ($subjectResult && mysqli_num_rows($subjectResult) > 0) {
//     while ($row = mysqli_fetch_assoc($subjectResult)) {
//         $subjects[] = $row;
//     }
// } else {
//     echo "<div class='alert alert-warning'>No subjects found for class <strong>$className</strong>.</div>";
// }
// function calculateGrade($percentage) {
//     if ($percentage >= 90) return 'A+';
//     if ($percentage >= 80) return 'A';
//     if ($percentage >= 70) return 'B+';
//     if ($percentage >= 60) return 'B';
//     if ($percentage >= 50) return 'C';
//     if ($percentage >= 40) return 'D';
//     return 'F';
// }

// if ($_SERVER["REQUEST_METHOD"] == "POST") {
//     // ✅ Handle photo upload
//     if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
//         $photoTmpPath = $_FILES['photo']['tmp_name'];
//         $photoName = basename($_FILES['photo']['name']);
//         $targetDir = '../uploads/';
//         $newFileName = time() . '_' . $photoName;
//         $photoPath = $targetDir . $newFileName;

//         if (!file_exists($targetDir)) {
//             mkdir($targetDir, 0777, true);
//         }

//         if (!move_uploaded_file($photoTmpPath, $photoPath)) {
//             $photoPath = '';
//             $submittedMessage = "❌ Photo upload failed.";
//         }
//     }

//     // Marks & meta
//     $totalMarks = isset($_POST['total_marks']) ? intval($_POST['total_marks']) : 0;
//     $percentage = isset($_POST['percentage']) ? floatval($_POST['percentage']) : 0.0;
//     $grade = calculateGrade($percentage);

//     $columns = [
//         'regId', 'studentName', 'classId', 'classSecId', 'className', 'classArmName', 'session',
//         'motherName', 'fatherName', 'address', 'dob',
//         'total_marks', 'percentage', 'grade', 'photo', 'created_at'
//     ];

//     $values = [
//         "'" . mysqli_real_escape_string($conn, $regId) . "'",
//         "'" . mysqli_real_escape_string($conn, $studentDetails['studentName'] ?? '') . "'",
//         intval($studentDetails['classId'] ?? 0),
//         intval($studentDetails['classSecId'] ?? 0),
//         "'" . mysqli_real_escape_string($conn, $className) . "'",
//         "'" . mysqli_real_escape_string($conn, $studentDetails['classArmName'] ?? '') . "'",
//         "'" . mysqli_real_escape_string($conn, $studentDetails['session'] ?? '') . "'",
//         "'" . mysqli_real_escape_string($conn, $studentDetails['motherName'] ?? '') . "'",
//         "'" . mysqli_real_escape_string($conn, $studentDetails['fatherName'] ?? '') . "'",
//         "'" . mysqli_real_escape_string($conn, $studentDetails['address'] ?? '') . "'",
//         "'" . mysqli_real_escape_string($conn, $studentDetails['dob'] ?? '') . "'",
//         intval($totalMarks),
//         floatval($percentage),
//         "'" . mysqli_real_escape_string($conn, $grade) . "'",
//         "'" . mysqli_real_escape_string($conn, $photoPath) . "'",
//         "'" . date('Y-m-d H:i:s') . "'"
//     ];

//     // Add subjects
//     foreach ($subjects as $subj) {
//         $key = strtolower(str_replace([' ', '/', '-'], '_', $subj));
//         $val = isset($_POST[$key]) ? intval($_POST[$key]) : 0;
//         $columns[] = $key;
//         $values[] = $val;
//     }

//     // SQL insert
//     $columnsStr = implode(', ', $columns);
//     $valuesStr = implode(', ', $values);

//     $insertQuery = "INSERT INTO student_marks ($columnsStr) VALUES ($valuesStr)";
//     $result = mysqli_query($conn, $insertQuery);

//     // echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
//     // if ($result) {
//     //     echo "<script>
//     //         Swal.fire({
//     //             title: '✅ Success!',
//     //             text: 'Marks and photo submitted successfully.',
//     //             icon: 'success',
//     //             confirmButtonText: 'OK'
//     //         });
//     //     </script>";
//     // } else {
//     //     echo "<script>
//     //         Swal.fire({
//     //             title: '❌ Error!',
//     //             text: 'Database error: " . mysqli_error($conn) . "',
//     //             icon: 'error',
//     //             confirmButtonText: 'Close'
//     //         });
//     //     </script>";
//     // }
//     if ($result) {
//     echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
//     echo "<script>
//         document.addEventListener('DOMContentLoaded', function() {
//             Swal.fire({
//                 title: '✅ Success!',
//                 text: 'Marks and photo submitted successfully.',
//                 icon: 'success',
//                 confirmButtonText: 'OK'
//             }).then(function() {
//                 window.location.href = 'studentMarksheet.php';
//             });
//         });
//     </script>";
// }

// }
?>
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../Includes/dbcon.php';
include '../Includes/session.php';

$submittedMessage = "";
$photoPath = "";



// Get regId and className
$regId = $_GET['regId'] ?? ($_SESSION['regId'] ?? '');
$className = $_GET['className'] ?? '';

// Sanitize inputs
$regId = mysqli_real_escape_string($conn, $regId);
$className = mysqli_real_escape_string($conn, $className);

// Fetch student details
$studentDetails = [];
if (!empty($regId)) {
    $studentQuery = "SELECT * FROM tblstudents WHERE regId = '$regId' LIMIT 1";
    $studentResult = mysqli_query($conn, $studentQuery);
    if ($studentResult && mysqli_num_rows($studentResult) > 0) {
        $studentDetails = mysqli_fetch_assoc($studentResult);
        $className = mysqli_real_escape_string($conn, $studentDetails['className']);
    }
}

// Fetch subjects from DB
// $subjects = [];
// $subjectQuery = "SELECT * FROM subject_master WHERE className = '$className'";
// $subjectResult = mysqli_query($conn, $subjectQuery);
// if ($subjectResult && mysqli_num_rows($subjectResult) > 0) {
//     while ($row = mysqli_fetch_assoc($subjectResult)) {
//         $subjects[] = $row;
//     }
// } else {
//     echo "<div class='alert alert-warning'>No subjects found for class <strong>$className</strong>.</div>";
// }

$classId = (int)($_GET['classId'] ?? '');

// 1. Fetch subjects
$query = "SELECT * FROM subject_master WHERE className = $classId";
$result = mysqli_query($conn, $query);

$subjects = [];
while ($row = mysqli_fetch_assoc($result)) {
    $subjects[] = $row;
}
// echo "<pre>Insert Columns: "; print_r($columns); echo "</pre>";
// echo "<pre>Insert Values: "; print_r($values); echo "</pre>";


// 2. Debug: show if subjects found
if (empty($subjects)) {
    echo "<div class='alert alert-danger'>❌ No subjects found for class $classId.</div>";
} else {
  // print_r( $subjects);
    // echo "<div class='alert alert-success'>✅ Subjects loaded for class $classId.</div>";
}

function calculateGrade($percentage) {
    if ($percentage >= 90) return 'A+';
    if ($percentage >= 80) return 'A';
    if ($percentage >= 70) return 'B+';
    if ($percentage >= 60) return 'B';
    if ($percentage >= 50) return 'C';
    if ($percentage >= 40) return 'D';
    return 'F';
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // $term=$_POST['term'];
$term = $_POST['term'] ?? '';
    // Handle photo upload
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $photoTmpPath = $_FILES['photo']['tmp_name'];
        $photoName = basename($_FILES['photo']['name']);
        $targetDir = '../uploads/';
        $newFileName = time() . '_' . $photoName;
        $photoPath = $targetDir . $newFileName;

        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        if (!move_uploaded_file($photoTmpPath, $photoPath)) {
            $photoPath = '';
            $submittedMessage = "❌ Photo upload failed.";
        }
    }

    // Marks & meta
    $totalMarks = isset($_POST['total_marks']) ? intval($_POST['total_marks']) : 0;
    $percentage = isset($_POST['percentage']) ? floatval($_POST['percentage']) : 0.0;
    $grade = calculateGrade($percentage);

    // Prepare insert
    $columns = [
        'regId', 'studentName', 'classId', 'classSecId', 'className', 'classArmName', 'session',
        'motherName', 'fatherName', 'address', 'dob',
        'total_marks', 'percentage', 'grade', 'photo', 'created_at','term'
    ];

    $values = [
        "'$regId'",
        "'" . mysqli_real_escape_string($conn, $studentDetails['studentName'] ?? '') . "'",
        intval($studentDetails['classId'] ?? 0),
        intval($studentDetails['classSecId'] ?? 0),
        "'" . mysqli_real_escape_string($conn, $className) . "'",
        "'" . mysqli_real_escape_string($conn, $studentDetails['classArmName'] ?? '') . "'",
        "'" . mysqli_real_escape_string($conn, $studentDetails['session'] ?? '') . "'",
        "'" . mysqli_real_escape_string($conn, $studentDetails['motherName'] ?? '') . "'",
        "'" . mysqli_real_escape_string($conn, $studentDetails['fatherName'] ?? '') . "'",
        "'" . mysqli_real_escape_string($conn, $studentDetails['address'] ?? '') . "'",
        "'" . mysqli_real_escape_string($conn, $studentDetails['dob'] ?? '') . "'",
        $totalMarks,
        $percentage,
        "'$grade'",
        "'$photoPath'",
        "'" . date('Y-m-d H:i:s') . "'",
         "'" . mysqli_real_escape_string($conn, $_POST['term'] ?? '') . "'"
    ];

    // Add dynamic subject marks
    foreach ($subjects as $subj) {
        $subjectName = $subj['subjectName'];
        $inputBase = strtolower(str_replace([' ', '/', '-'], '_', $subjectName));

        if ($subj['subjectTheory'] == 1) {
            $columns[] = $inputBase . '_theory';
            $values[] = intval($_POST[$inputBase . '_theory'] ?? 0);
        }

        if (strtolower($subj['subjectPractical']) === 'yes') {
            $columns[] = $inputBase . '_practical';
            $values[] = intval($_POST[$inputBase . '_practical'] ?? 0);
        }
    }

    $insertQuery = "INSERT INTO student_marks (" . implode(',', $columns) . ") VALUES (" . implode(',', $values) . ")";
    $result = mysqli_query($conn, $insertQuery);

    if ($result) {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: '✅ Success!',
                    text: 'Marks and photo submitted successfully.',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(function() {
                    window.location.href = 'studentMarksheet.php';
                });
            });
        </script>";
    } else {
        echo "<div class='alert alert-danger'>❌ Database Error: " . mysqli_error($conn) . "</div>";
    }
}
?>




    <!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Promote Students</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <?php include 'includes/title.php'; ?>
  <link href="img/logo/techShell.jpg" rel="icon">
  <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
  <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/ruang-admin.min.css" rel="stylesheet">
  <link href="../vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
</head>
<style>
        .form-control[readonly] {
            background-color: #eef1f7;
        }
    </style>
<body id="page-top">
<div id="wrapper">
  <?php include "Includes/sidebar.php";?>
  <div id="content-wrapper" class="d-flex flex-column">
    <div id="content">
      <?php include "Includes/topbar.php";?>
      <div class="container-fluid" id="container-wrapper">

        <div class="d-sm-flex align-items-center justify-content-between mb-4">
          <h1 class="h3 mb-0 text-gray-800">Student MarkSheet</h1>
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="./">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Student MarkSheet</li>
          </ol>
        </div>
<div class="container mt-4">
    <h4 class="text-primary mb-3">Enter Subject Marks (Class <?= htmlspecialchars($className) ?>)</h4>

    <?php if (!empty($submittedMessage)): ?>
        <div class="alert alert-info"><?= htmlspecialchars($submittedMessage) ?></div>
    <?php endif; ?>

    <?php if (!empty($studentDetails)): ?>
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <strong>Student Information</strong>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Admission No</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($studentDetails['regId']) ?>" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($studentDetails['studentName']) ?>" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Class</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($className) ?>" readonly>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Section</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($studentDetails['classArmName']) ?>" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Session</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($studentDetails['session']) ?>" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Mother's Name</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($studentDetails['motherName']) ?>" readonly>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Father's Name</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($studentDetails['fatherName']) ?>" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date of Birth</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($studentDetails['dob']) ?>" readonly>
                    </div>
                    <!-- <div class="col-md-4">
                        <label class="form-label">Roll No.</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($studentDetails['rollNo']) ?>" readonly>
                    </div> -->
                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="form-label">Address</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($studentDetails['address']) ?>" readonly>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <p class="text-danger">Student details not found for Reg ID: <?= htmlspecialchars($regId) ?></p>
    <?php endif; ?>

   
    <form action="" method="post" enctype="multipart/form-data"> 
     <input type="hidden" name="regId" value="<?= htmlspecialchars($regId) ?>">
    <input type="hidden" name="className" value="<?= htmlspecialchars($className) ?>">

    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            <strong>Enter Subject Marks</strong>
        </div>
        <div class="card-body">
            <div class="row">
                
                <?php foreach ($subjects as $subject): 
    $subjectName = $subject['subjectName'];
    $inputName = strtolower(str_replace([' ', '/', '-'], '_', $subjectName));
?>
    <div class="col-md-4 mb-3">
        <label><?= htmlspecialchars($subjectName) ?> Marks</label>
        <input type="number" name="<?= htmlspecialchars($inputName) ?>" class="form-control marks-input" min="0" max="100" required>
    </div>
<?php endforeach; ?>

            </div>
<div class="form-group">
    <label for="term">Select Term</label>
    <select class="form-control" name="term" id="term" required>
        <option value="">--Select Term--</option>
        <option value="Term 1">Term 1</option>
        <option value="Term 2">Term 2</option>
        <option value="Final Term">Final Term</option>
    </select>
</div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label>Total Marks</label>
                    <input type="number" class="form-control" name="total_marks" id="total_marks" readonly required>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Percentage (%)</label>
                    <input type="text" class="form-control" name="percentage" id="percentage" readonly required>
                </div>
            </div>
          </div>
        


    </div>

<!-- <div class="container" style="margin-left:0px " >
  <div class="row" >
  <div class="card shadow-sm mb-4">
    <div class="card-header bg-primary text-white font-weight-bold">
      Upload Student Photo
    </div>
    <div class="card-body" style="display: flex; align-items: center; justify-content: space-between;">
      
      
      <div class="col-md-7" 
           ondrop="handleDrop(event)" 
           ondragover="event.preventDefault()" 
           style="border: 2px dashed #007bff; padding: 20px; text-align: center; border-radius: 10px; background-color: #f9f9f9;">
        
        <label class="form-control-label font-weight-bold">
          Drag & Drop or Choose File <span class="text-danger ml-2">*</span>
        </label>
        
        <input type="file"
               class="form-control mt-2"
               name="photo"
               id="paymentImage"
               accept=".jpg,.jpeg,.png"
               onchange="displayImage(this)"
               required>

        <small class="form-text text-muted mt-2" style="display: block; margin-top: 5px;">
          Accepted formats: JPG, JPEG, PNG
        </small>

        <?php if (!empty($row['photo'])): ?>
          <small class="mt-2 d-block">Current: 
            <a href="uploads/<?php echo htmlspecialchars($row['photo']); ?>" target="_blank">View</a>
          </small>
        <?php endif; ?>
      </div>

     
      <div class="col-md-5 d-flex justify-content-center">
        <div class="card" style="padding: 10px; border-radius: 12px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); border: 1px solid #ccc; width: fit-content; background-color: #fff;">
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
  </div> -->

  <!-- <script>
  function triggerClick() {
    document.querySelector('#paymentImage').click();
  }

  function displayImage(input) {
    if (input.files && input.files[0]) {
      var reader = new FileReader();
      reader.onload = function(e) {
        document.querySelector('#paymentDisplay').src = e.target.result;
      };
      reader.readAsDataURL(input.files[0]);
    }
  }

  function handleDrop(e) {
    e.preventDefault();
    const fileInput = document.getElementById('paymentImage');
    fileInput.files = e.dataTransfer.files;
    displayImage(fileInput);
  }
</script> -->
    <button type="submit" class="btn btn-success mt-3">
    Submit
</button>
<a href="studentMarksheet.php" class="btn btn-secondary ms-3 mt-3" role="button" aria-label="Back to Student Marksheet">
    Back
</a>

</form>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const inputs = document.querySelectorAll('.marks-input');
    const totalField = document.getElementById('total_marks');
    const percentageField = document.getElementById('percentage');

    function calculateTotals() {
        let total = 0;
        let count = 0;

        inputs.forEach(input => {
            const value = parseFloat(input.value);
            if (!isNaN(value)) {
                total += value;
                count++;
            }
        });

        totalField.value = total;
        if (count > 0) {
            const percentage = (total / (count * 100)) * 100;
            percentageField.value = percentage.toFixed(2);
        } else {
            percentageField.value = '';
        }
    }

    inputs.forEach(input => {
        input.addEventListener('input', calculateTotals);
    });
});
</script>

<!-- </body>
</html> -->
 <?php include "Includes/footer.php";?>
  </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../vendor/jquery/jquery.min.js"></script>
<script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../vendor/datatables/jquery.dataTables.min.js"></script>
<script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>
<script>
$(document).ready(function () {
  $('#dataTable').DataTable();

  // Select All Checkbox
  document.getElementById('selectAll')?.addEventListener('click', function () {
    const checkboxes = document.querySelectorAll('input[name="students[]"]');
    checkboxes.forEach(cb => cb.checked = this.checked);
  });

  // SweetAlert Confirm Popup Before Submit
  document.getElementById('promoteBtn')?.addEventListener('click', function () {
    Swal.fire({
      title: 'Are you sure?',
      text: "You are about to promote the selected students.",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#28a745',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Yes, promote them!'
    }).then((result) => {
      if (result.isConfirmed) {
        document.getElementById('promoteForm').submit();
      }
    });
  });

  <?php if ($showSuccessAlert): ?>
  Swal.fire({
    title: 'Success!',
    text: 'Students promoted successfully!',
    icon: 'success',
    confirmButtonText: 'OK'
  });
  <?php endif; ?>
});
</script>
</body>
</html>
    