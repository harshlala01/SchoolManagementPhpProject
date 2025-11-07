  <?php
  // error_reporting(E_ALL);
  // ini_set('display_errors', 1);
  // include '../Includes/dbcon.php';
  // include '../Includes/session.php';

  // // Initialize variables
  // $classId = '';
  // $classSecId = '';
  // $students = [];
  // $error = '';

  // if (isset($_POST['submit'])) {
  //     $classId = intval($_POST['classId']);
  //     $classSecId = intval($_POST['classSecId']);

  //     $query = "SELECT * FROM tblstudents WHERE classId = '$classId' AND classSecId = '$classSecId'";
  //     $result = mysqli_query($conn, $query);

  //     if ($result && mysqli_num_rows($result) > 0) {
  //         while ($row = mysqli_fetch_assoc($result)) {
  //             $students[] = $row;
  //         }
  //     } else {
  //         $error = "No students found for selected class and section.";
  //     }
  // }
  ?>
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../Includes/dbcon.php';
include '../Includes/session.php';

// Initialize
$classId = '';
$classSecId = '';
$students = [];
$error = '';
$data = [];
$regId = $_GET['regId'] ?? '';

// Handle student list fetch by class/section
if (isset($_POST['submit'])) {
    $classId = intval($_POST['classId']);
    $classSecId = intval($_POST['classSecId']);

    $query = "SELECT * FROM tblstudents WHERE classId = '$classId' AND classSecId = '$classSecId'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $students[] = $row;
        }
    } else {
        $error = "No students found for selected class and section.";
    }
}

// Fetch selected student details by regId (for PDF view or preview)
if (!empty($regId)) {
    $query = "SELECT * FROM tblstudents WHERE regId = '$regId'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_assoc($result);
    } else {
        $error = "Student not found.";
    }
}
?>

  <!-- <!DOCTYPE html>
  <html>
  <head>
      <title>Student Marksheet Viewer</title>
      <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  </head>
  <body class="container mt-5"> -->
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
      <h2>Select Class & Section to View Students</h2>
      <form method="post" class="row g-3 mb-4">
          <div class="col-md-4">
              <label for="classId" class="form-label">Class</label>
              <select name="classId" class="form-control" required>
                  <option value="">-- Select Class --</option>
                  <?php
                  $classResult = mysqli_query($conn, "SELECT * FROM tblclass");
                  while ($classRow = mysqli_fetch_assoc($classResult)) {
                      $selected = ($classId == $classRow['Id']) ? 'selected' : '';
                      echo "<option value='{$classRow['Id']}' $selected>{$classRow['className']}</option>";
                  }
                  ?>
              </select>
          </div>

          <div class="col-md-4">
              <label for="classSecId" class="form-label">Section</label>
              <select name="classSecId" class="form-control" required>
                  <option value="">-- Select Section --</option>
                  <?php
                  $sectionQuery = mysqli_query($conn, "SELECT MIN(Id) as Id, classArmName FROM tblclassarms GROUP BY classArmName ORDER BY classArmName ASC");
                  while ($row = mysqli_fetch_assoc($sectionQuery)) {
                      $selected = ($classSecId == $row['Id']) ? 'selected' : '';
                      echo "<option value='{$row['Id']}' $selected>{$row['classArmName']}</option>";
                  }
                  ?>
              </select>
          </div>

          <div class="col-md-4 align-self-end">
              <button type="submit" name="submit" class="btn btn-primary">Show Students</button>
          </div>
      </form>

      <?php if (!empty($error)): ?>
          <div class="alert alert-danger"><?= $error ?></div>
      <?php endif; ?>

      <?php if (!empty($students)): ?>
          <h4>Student List</h4>
          <table class="table table-bordered table-striped">
              <thead>
                  <tr>
                      <th>Reg ID</th>
                      <th>Name</th>
                      <th>Actions</th>
                  </tr>
              </thead>
              <tbody>
                  <?php foreach ($students as $student): ?>
                      <tr>
                          <td><?= $student['regId'] ?></td>
                          <td><?= $student['studentName'] ?></td>
                          <td>
                              <a href="enter_marks.php?regId=<?= $student['regId'] ?>&classId=<?= $student['classId'] ?>" class="btn btn-sm btn-success">Enter Marks</a>
                              <!-- <a href="studentReportCard.php?regId=<?= $student['regId'] ?>" class="btn btn-sm btn-secondary">PDF</a> -->
                             <!-- <a href="generate_pdf.php?regId=<?= $student['regId'] ?>&term=<?= $student['term'] ?>" class="btn btn-sm btn-secondary">PDF</a> -->
<a href="generate_pdf.php?regId=<?= $student['regId'] ?>&term=<?= $term ?>" class="btn btn-sm btn-secondary">Generate PDF</a>

                            <!--   <a href="reportcard.php?regId=<?= $student['regId'] ?>&term=<?= $student['term'] ?>">Download PDF</a> -->

                          </td>
                      </tr>
                  <?php endforeach; ?>
              </tbody>
          </table>
      <?php endif; ?>
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
