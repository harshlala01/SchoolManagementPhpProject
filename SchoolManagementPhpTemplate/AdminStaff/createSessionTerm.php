<?php 
error_reporting(0);
include '../Includes/dbcon.php';
include '../Includes/session.php';

$statusMsg = '';
$row = ['sessionName' => '', 'termId' => ''];
$Id = null;

// ------------------------ SAVE -----------------------------
if (isset($_POST['save'])) {
    $sessionName = $_POST['sessionName'];
    $termId = $_POST['termId'];
    $dateCreated = date("Y-m-d");

    $query = mysqli_query($conn, "SELECT * FROM tblsessionterm WHERE sessionName='$sessionName' AND termId='$termId'");
    if (mysqli_num_rows($query) > 0) {
        $status = 'error';
        $message = 'This Session and Term Already Exists!';
    } else {
        $query = mysqli_query($conn, "INSERT INTO tblsessionterm(sessionName, termId, isActive, dateCreated) VALUES('$sessionName', '$termId', '0', '$dateCreated')");
       $_SESSION['status'] = $query ? 'success' : 'error';
        $_SESSION['message'] = $query ? 'Session and Term Created Successfully!' : 'An Error Occurred!';
    }
}

// ------------------------ EDIT -----------------------------
if (isset($_GET['Id']) && $_GET['action'] === 'edit') {
    $Id = $_GET['Id'];
    $query = mysqli_query($conn, "SELECT * FROM tblsessionterm WHERE Id='$Id'");
    $row = mysqli_fetch_array($query);

    if (isset($_POST['update'])) {
        $sessionName = $_POST['sessionName'];
        $termId = $_POST['termId'];
        $query = mysqli_query($conn, "UPDATE tblsessionterm SET sessionName='$sessionName', termId='$termId', isActive='0' WHERE Id='$Id'");
        $_SESSION['status'] = $query ? 'success' : 'error';
        $_SESSION['message'] = $query ? 'Session and Term Updated Successfully!' : 'An Error Occurred!';
        header("Location: createSessionTerm.php");
        exit();
    }
}

// ------------------------ DELETE -----------------------------
if (isset($_GET['Id']) && $_GET['action'] === 'delete') {
    $Id = $_GET['Id'];
    $query = mysqli_query($conn, "DELETE FROM tblsessionterm WHERE Id='$Id'");
    $_SESSION['status'] = $query ? 'success' : 'error';
    $_SESSION['message'] = $query ? 'Session and Term Deleted Successfully!' : 'An Error Occurred!';
    header("Location: createSessionTerm.php");
    exit();
}

// ------------------------ ACTIVATE -----------------------------
if (isset($_GET['Id']) && $_GET['action'] === 'activate') {
    $Id = $_GET['Id'];
    $deactivate = mysqli_query($conn, "UPDATE tblsessionterm SET isActive='0' WHERE isActive='1'");
    if ($deactivate) {
        $activate = mysqli_query($conn, "UPDATE tblsessionterm SET isActive='1' WHERE Id='$Id'");
        $statusMsg = $activate 
            ? "<script>window.location = 'createSessionTerm.php';</script>" 
            : "<div class='alert alert-danger'>An error occurred during activation!</div>";
    } else {
        $statusMsg = "<div class='alert alert-danger'>An error occurred during deactivation!</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Create Session and Term</title>
  <link href="img/logo/techShell.jpg" rel="icon">
  <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
  <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/ruang-admin.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body id="page-top">
  <div id="wrapper">
    <?php include "Includes/sidebar.php";?>
    <div id="content-wrapper" class="d-flex flex-column">
      <div id="content">
        <?php include "Includes/topbar.php";?>
        <div class="container-fluid" id="container-wrapper">
          <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Create Session and Term</h1>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="./">Home</a></li>
              <li class="breadcrumb-item active">Create Session and Term</li>
            </ol>
          </div>

          <!-- Form -->
          <div class="card mb-4">
            <div class="card-header">
              <h6 class="m-0 font-weight-bold text-primary">Session and Term Form</h6>
              <?php echo $statusMsg; ?>
            </div>
            <div class="card-body">
              <form method="post">
                <div class="form-group row">
                  <div class="col-xl-6">
                    <label>Session Name <span class="text-danger">*</span></label>
                    <!-- <input type="text" name="sessionName" value="<?php echo $row['sessionName'] ?? ''; ?>" class="form-control" required> -->
                      <select class="form-control" id="session" name="sessionName" onchange="updateFeeAmount()" required>
  <option value="">--Select--</option>
  <?php
    $currentYear = date("Y");
    for ($i = 0; $i < 5; $i++) {
        $start = $currentYear + $i;
        $end = $start + 1;
        echo "<option value='$start-$end'>$start-$end</option>";
    }
  ?>
</select>
                  </div>
                  <div class="col-xl-6">
                    <label>Term <span class="text-danger">*</span></label>
                    <select name="termId" class="form-control" required>
                      <option value="">--Select Term--</option>
                      <?php
                      $qry = "SELECT * FROM tblterm ORDER BY termName ASC";
                      $result = $conn->query($qry);
                      while ($term = $result->fetch_assoc()) {
                        $selected = ($term['Id'] == ($row['termId'] ?? '')) ? 'selected' : '';
                        echo "<option value='{$term['Id']}' $selected>{$term['termName']}</option>";
                      }
                      ?>
                    </select>
                  </div>
                </div>
                <?php if ($Id): ?>
                  <button type="submit" name="update" class="btn btn-warning">Update</button>
                <?php else: ?>
                  <button type="submit" name="save" class="btn btn-primary">Save</button>
                <?php endif; ?>
              </form>
            </div>
          </div>

          <!-- Table -->
          <div class="card">
            <div class="card-header">
              <h6 class="m-0 font-weight-bold text-primary">All Session and Term</h6>
              <small class="text-danger">Note: Click the check icon to activate a session.</small>
            </div>
            <div class="card-body table-responsive">
              <table class="table table-bordered table-hover" id="dataTableHover">
                <thead class="thead-light">
                  <tr>
                    <th>#</th>
                    <th>Session</th>
                    <th>Term</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Activate</th>
                    <th>Edit</th>
                    <th>Delete</th>
                  </tr>
                </thead>
                <tbody>
                <?php
                $query = "SELECT tblsessionterm.Id, tblsessionterm.sessionName, tblsessionterm.isActive, tblsessionterm.dateCreated, tblterm.termName
                          FROM tblsessionterm
                          INNER JOIN tblterm ON tblterm.Id = tblsessionterm.termId";
                $rs = $conn->query($query);
                $sn = 1;
                while ($rows = $rs->fetch_assoc()) {
                    $status = ($rows['isActive'] == '1') ? "Active" : "Inactive";
                    echo "<tr>
                            <td>{$sn}</td>
                            <td>{$rows['sessionName']}</td>
                            <td>{$rows['termName']}</td>
                            <td>{$status}</td>
                            <td>{$rows['dateCreated']}</td>
                            <td><a href='?action=activate&Id={$rows['Id']}'><i class='fas fa-check text-success'></i></a></td>
                            <td><a href='?action=edit&Id={$rows['Id']}'><i class='fas fa-edit text-primary'></i></a></td>
                            <td><a href='?action=delete&Id={$rows['Id']}'><i class='fas fa-trash text-danger'></i></a></td>
                          </tr>";
                    $sn++;
                }
                ?>
                </tbody>
              </table>
            </div>
          </div>

        </div>
      </div>
      <?php include "Includes/footer.php";?>
    </div>
  </div>

  <!-- Scripts -->
  <script src="../vendor/jquery/jquery.min.js"></script>
  <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
  <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>
  <script src="js/ruang-admin.min.js"></script>
  <script>
    $(document).ready(function () {
      $('#dataTableHover').DataTable();
      <?php if (!empty($status) && !empty($message)): ?>
        Swal.fire({
          icon: '<?php echo $status; ?>',
          title: '<?php echo $message; ?>',
          confirmButtonText: 'OK'
        });
      <?php endif; ?>

      <?php if (isset($_SESSION['status']) && isset($_SESSION['message'])): ?>
        Swal.fire({
          icon: '<?php echo $_SESSION['status']; ?>',
          title: '<?php echo $_SESSION['message']; ?>',
          confirmButtonText: 'OK'
        });
        <?php unset($_SESSION['status'], $_SESSION['message']); ?>
      <?php endif; ?>
    });
  </script>
</body>
</html>
