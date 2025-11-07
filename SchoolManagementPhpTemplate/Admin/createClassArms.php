<?php 
session_start();
error_reporting(0);
include '../Includes/dbcon.php';
include '../Includes/session.php';

// SAVE
if (isset($_POST['save'])) {
    $classId = $_POST['classId'];
    $classArmName = $_POST['classArmName'];

    $query = mysqli_query($conn, "SELECT * FROM tblclassarms WHERE classArmName ='$classArmName' AND classId = '$classId'");
    $ret = mysqli_fetch_array($query);

    if ($ret > 0) {
        $_SESSION['msg'] = "exists";
    } else {
        $query = mysqli_query($conn, "INSERT INTO tblclassarms(classId, classArmName, isAssigned) VALUE('$classId', '$classArmName', '0')");
        $_SESSION['msg'] = $query ? "success" : "error";
    }
    echo "<script>window.location='createClassArms.php';</script>";
    exit();
}

// EDIT
if (isset($_GET['Id']) && $_GET['action'] == "edit") {
    $Id = $_GET['Id'];
    $query = mysqli_query($conn, "SELECT * FROM tblclassarms WHERE Id ='$Id'");
    $row = mysqli_fetch_array($query);

    if (isset($_POST['update'])) {
        $classId = $_POST['classId'];
        $classArmName = $_POST['classArmName'];

        $query = mysqli_query($conn, "UPDATE tblclassarms SET classId = '$classId', classArmName='$classArmName' WHERE Id='$Id'");
        $_SESSION['msg'] = $query ? "updated" : "error";
        echo "<script>window.location='createClassArms.php';</script>";
        exit();
    }
}

// DELETE
if (isset($_GET['Id']) && $_GET['action'] == "delete") {
    $Id = $_GET['Id'];
    $query = mysqli_query($conn, "DELETE FROM tblclassarms WHERE Id='$Id'");
    $_SESSION['msg'] = $query ? "deleted" : "error";
    echo "<script>window.location='createClassArms.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Create Class Arms</title>
  <!-- <link href="img/logo/attnlg.jpg" rel="icon"> -->
  <link href="img/logo/techShell.jpg" rel="icon">
  <?php include 'includes/title.php';?>
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
            <h1 class="h3 mb-0 text-gray-800">Create Class Arms</h1>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="./">Home</a></li>
              <li class="breadcrumb-item active">Create Class Arms</li>
            </ol>
          </div>

          <div class="row">
            <div class="col-lg-12">
              <div class="card mb-4">
                <div class="card-header py-3">
                  <h6 class="m-0 font-weight-bold text-primary">Create Class Arms</h6>
                </div>
                <div class="card-body">
                  <form method="post">
                    <div class="form-group row mb-3">
                      <div class="col-xl-6">
                        <label>Select Class<span class="text-danger ml-2">*</span></label>
                        <select required name="classId" class="form-control mb-3">
                          <option value="">--Select Class--</option>
                          <?php
                            $qry = "SELECT * FROM tblclass ORDER BY className ASC";
                            $result = $conn->query($qry);
                            while ($rows = $result->fetch_assoc()) {
                                $selected = isset($row) && $row['classId'] == $rows['Id'] ? "selected" : "";
                                echo '<option value="'.$rows['Id'].'" '.$selected.'>'.$rows['className'].'</option>';
                            }
                          ?>
                        </select>
                      </div>
                      <div class="col-xl-6">
                        <label>Class Arm Name<span class="text-danger ml-2">*</span></label>
                        <input type="text" class="form-control" name="classArmName" value="<?php echo $row['classArmName'] ?? ''; ?>" placeholder="Class Arm Name">
                      </div>
                    </div>
                    <?php if (isset($Id)): ?>
                      <button type="submit" name="update" class="btn btn-warning">Update</button>
                    <?php else: ?>
                      <button type="submit" name="save" class="btn btn-primary">Save</button>
                    <?php endif; ?>
                  </form>
                </div>
              </div>

              <div class="card mb-4">
                <div class="card-header py-3">
                  <h6 class="m-0 font-weight-bold text-primary">All Class Arm</h6>
                </div>
                <div class="table-responsive p-3">
                  <table class="table align-items-center table-hover" id="dataTableHover">
                    <thead class="thead-light">
                      <tr>
                        <th>#</th>
                        <th>Class Name</th>
                        <th>Class Arm Name</th>
                        <th>Status</th>
                        <th>Edit</th>
                        <th>Delete</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      $query = "SELECT tblclassarms.Id, tblclassarms.isAssigned, tblclass.className, tblclassarms.classArmName 
                                FROM tblclassarms
                                INNER JOIN tblclass ON tblclass.Id = tblclassarms.classId";
                      $rs = $conn->query($query);
                      $sn = 0;
                      while ($rows = $rs->fetch_assoc()) {
                          $status = $rows['isAssigned'] == '1' ? "Assigned" : "UnAssigned";
                          $sn++;
                          echo "<tr>
                                <td>{$sn}</td>
                                <td>{$rows['className']}</td>
                                <td>{$rows['classArmName']}</td>
                                <td>{$status}</td>
                                <td><a href='?action=edit&Id={$rows['Id']}'><i class='fas fa-edit'></i> Edit</a></td>
                                <td><a href='?action=delete&Id={$rows['Id']}'><i class='fas fa-trash'></i> Delete</a></td>
                              </tr>";
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
      <?php include "Includes/footer.php";?>
    </div>
  </div>

  <a class="scroll-to-top rounded" href="#page-top"><i class="fas fa-angle-up"></i></a>

  <script src="../vendor/jquery/jquery.min.js"></script>
  <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
  <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>
  <script src="js/ruang-admin.min.js"></script>
  <script>
    $(document).ready(function () {
      $('#dataTableHover').DataTable();
    });
  </script>

  <?php if (isset($_SESSION['msg'])): ?>
    <script>
      <?php
        switch ($_SESSION['msg']) {
          case 'success':
            echo "Swal.fire('Success!', 'Class Arm created successfully.', 'success');";
            break;
          case 'updated':
            echo "Swal.fire('Updated!', 'Class Arm updated successfully.', 'success');";
            break;
          case 'deleted':
            echo "Swal.fire('Deleted!', 'Class Arm deleted successfully.', 'success');";
            break;
          case 'exists':
            echo "Swal.fire('Duplicate!', 'This Class Arm already exists.', 'warning');";
            break;
          case 'error':
            echo "Swal.fire('Error!', 'Something went wrong. Try again.', 'error');";
            break;
        }
        unset($_SESSION['msg']);
      ?>
    </script>
  <?php endif; ?>
</body>
</html>
