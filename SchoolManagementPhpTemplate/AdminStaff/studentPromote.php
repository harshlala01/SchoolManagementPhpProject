<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../Includes/dbcon.php';
include '../Includes/session.php';

$selectedClassId = '';
$students = null;

// Show SweetAlert success after redirect
$showSuccessAlert = isset($_GET['success']) && $_GET['success'] == 1;

if (isset($_POST['classId'])) {
    $selectedClassId = $_POST['classId'];
    $students = mysqli_query($conn, "
        SELECT s.*, a.classArmName 
        FROM tblstudents s
        LEFT JOIN tblclassarms a ON s.classSecId = a.Id
        WHERE s.classId = '$selectedClassId'
    ");
}

if (isset($_POST['promote']) && isset($_POST['students'])) {
    $studentsToPromote = $_POST['students'];
    $fromClassId = $_POST['fromClassId'];
    $toClassId = $_POST['toClassId'];
    $toClassArmId = $_POST['toClassArmId'];

    foreach ($studentsToPromote as $regId) {
        $res = mysqli_query($conn, "SELECT classId, classSecId FROM tblstudents WHERE regId = '$regId'");
        if ($res && mysqli_num_rows($res) > 0) {
            $row = mysqli_fetch_assoc($res);
            if ($row['classId'] != $toClassId || $row['classSecId'] != $toClassArmId) {
                mysqli_query($conn, "UPDATE tblstudents SET classId = '$toClassId', classSecId = '$toClassArmId' WHERE regId = '$regId'");
            }
        }
    }

    header("Location: " . $_SERVER['PHP_SELF'] . "?success=1");
    exit();
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
<body id="page-top">
<div id="wrapper">
  <?php include "Includes/sidebar.php";?>
  <div id="content-wrapper" class="d-flex flex-column">
    <div id="content">
      <?php include "Includes/topbar.php";?>
      <div class="container-fluid" id="container-wrapper">

        <div class="d-sm-flex align-items-center justify-content-between mb-4">
          <h1 class="h3 mb-0 text-gray-800">Promote Students</h1>
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="./">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Promote Students</li>
          </ol>
        </div>

        <div class="card mb-4">
          <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Select Class to Promote Students</h6>
          </div>
          <div class="card-body">
            <form method="post" class="mb-3">
              <div class="form-group">
                <label>Select Class:</label>
                <select name="classId" class="form-control" onchange="this.form.submit()" required>
                  <option value="">-- Select Class --</option>
                  <?php
                  $classResult = mysqli_query($conn, "SELECT * FROM tblclass");
                  while ($classRow = mysqli_fetch_assoc($classResult)) {
                      $sel = ($selectedClassId == $classRow['Id']) ? 'selected' : '';
                      echo "<option value='{$classRow['Id']}' $sel>{$classRow['className']}</option>";
                  }
                  ?>
                </select>
              </div>
            </form>

            <?php if ($students && mysqli_num_rows($students) > 0) { ?>
              <?php
              $classNameRow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT className FROM tblclass WHERE Id = '$selectedClassId'"));
              // echo "<h5>Students in Class: <strong>" . $classNameRow['className'] . "</strong></h5>";
              ?>

              <!-- Export to Excel Button -->
              <!-- <form action="export_excel.php" method="post" class="mb-2" >
                <input type="hidden" name="classId" value="<?php echo $selectedClassId; ?>">
                <button type="submit" class="btn btn-primary mb-2" >Export to Excel</button>
              </form> -->
              <form method="post" action="export_excel.php" class="mb-3">
  <div class="d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Students in Class: <strong><?php echo $classNameRow['className']; ?></strong></h5>
    <input type="hidden" name="classId" value="<?php echo $selectedClassId; ?>">
    <button type="submit" class="btn btn-primary">Export to Excel</button>
  </div>
</form>


              <form method="post" id="promoteForm">
                <input type="hidden" name="fromClassId" value="<?php echo $selectedClassId; ?>">
                <div class="table-responsive">
                  <table class="table table-bordered" id="dataTable">
                    <thead class="thead-dark">
                      <tr>
                        <th><input type="checkbox" id="selectAll"></th>
                        <th>Reg ID</th>
                        <th>Student Name</th>
                        <th>Gender</th>
                        <th>Section</th>
                        <th>Phone No.</th>
                      </tr>
                    </thead>
                    <tbody>
                    <?php while ($row = mysqli_fetch_assoc($students)) { ?>
                      <tr>
                        <td><input type="checkbox" name="students[]" value="<?php echo $row['regId']; ?>"></td>
                        <td><?php echo $row['regId']; ?></td>
                        <td><?php echo $row['studentName']; ?></td>
                        <td><?php echo $row['gender']; ?></td>
                        <td><?php echo $row['classArmName']; ?></td>
                        <td><?php echo $row['priPhoneNo']; ?></td>
                      </tr>
                    <?php } ?>
                    </tbody>
                  </table>
                </div>

                <div class="form-row mt-3">
                  <div class="form-group col-md-6">
                    <label>Promote to Class:</label>
                    <select name="toClassId" class="form-control" required>
                      <option value="">-- Select Class --</option>
                      <?php
                      $classResult = mysqli_query($conn, "SELECT * FROM tblclass");
                      while ($classRow = mysqli_fetch_assoc($classResult)) {
                          echo "<option value='{$classRow['Id']}'>{$classRow['className']}</option>";
                      }
                      ?>
                    </select>
                  </div>

                  <div class="form-group col-md-6">
                    <label>Assign New Section:</label>
                    <select name="toClassArmId" class="form-control" required>
                      <option value="">-- Select Section --</option>
                      <?php
                      $sectionResult = mysqli_query($conn, "SELECT MIN(Id) as Id, classArmName FROM tblclassarms GROUP BY classArmName ORDER BY classArmName ASC");
                      while ($sectionRow = mysqli_fetch_assoc($sectionResult)) {
                          echo "<option value='{$sectionRow['Id']}'>{$sectionRow['classArmName']}</option>";
                      }
                      ?>
                    </select>
                  </div>
                </div>

                <button type="button" class="btn btn-success mt-3" id="promoteBtn">Promote Selected Students</button>
                <input type="hidden" name="promote" value="1">
              </form>
            <?php } elseif ($selectedClassId) { ?>
              <div class="alert alert-warning">No students found in this class.</div>
            <?php } ?>
          </div>
        </div>
      </div>
    </div>
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
