<?php 
error_reporting(0);
include '../Includes/dbcon.php';
include '../Includes/session.php';

$status = '';
$message = '';

//------------------------SAVE--------------------------------------------------
if(isset($_POST['save'])){
    $className = $_POST['className'];

    $query = mysqli_query($conn,"SELECT * FROM tblclass WHERE className ='$className'");
    $ret = mysqli_fetch_array($query);

    if($ret > 0){ 
        $status = 'error';
        $message = 'This Class Already Exists!';
    }
    else{
        $query = mysqli_query($conn,"INSERT INTO tblclass(className) VALUE('$className')");
        if ($query) {
            $status = 'success';
            $message = 'Class Created Successfully!';
        } else {
            $status = 'error';
            $message = 'An Error Occurred!';
        }
    }
}

//------------------------EDIT--------------------------------------------------
if (isset($_GET['Id']) && $_GET['action'] == "edit") {
    $Id = $_GET['Id'];
    $query = mysqli_query($conn, "SELECT * FROM tblclass WHERE Id ='$Id'");
    $row = mysqli_fetch_array($query);

    if (isset($_POST['update'])) {
        $className = $_POST['className'];
        $query = mysqli_query($conn, "UPDATE tblclass SET className='$className' WHERE Id='$Id'");

        if ($query) {
            $_SESSION['status'] = 'success';
            $_SESSION['message'] = 'Class Updated Successfully!';
        } else {
            $_SESSION['status'] = 'error';
            $_SESSION['message'] = 'An Error Occurred!';
        }

        header("Location: createClass.php");
        exit();
    }
}

//------------------------DELETE--------------------------------------------------
if (isset($_GET['Id']) && $_GET['action'] == "delete") {
    $Id = $_GET['Id'];
    $query = mysqli_query($conn, "DELETE FROM tblclass WHERE Id='$Id'");

    if ($query == TRUE) {
        $_SESSION['status'] = 'success';
        $_SESSION['message'] = 'Class Deleted Successfully!';
    } else {
        $_SESSION['status'] = 'error';
        $_SESSION['message'] = 'An Error Occurred!';
    }

    header("Location: createClass.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>Create Class</title>
  <link href="img/logo/techShell.jpg" rel="icon">
  <!-- <link href="img/logo/attnlg.jpg" rel="icon"> -->
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
            <h1 class="h3 mb-0 text-gray-800">Create Class</h1>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="./">Home</a></li>
              <li class="breadcrumb-item active" aria-current="page">Create Class</li>
            </ol>
          </div>

          <div class="row">
            <div class="col-lg-12">
              <div class="card mb-4">
                <div class="card-header py-3">
                  <h6 class="m-0 font-weight-bold text-primary">Create Class</h6>
                </div>
                <div class="card-body">
                  <form method="post">
                    <div class="form-group row mb-3">
                      <div class="col-xl-6">
                        <label class="form-control-label">Class Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="className" value="<?php echo $row['className'] ?? ''; ?>" placeholder="Class Name">
                      </div>
                    </div>
                    <?php if (isset($Id)) { ?>
                        <button type="submit" name="update" class="btn btn-warning">Update</button>
                    <?php } else { ?>
                        <button type="submit" name="save" class="btn btn-primary">Save</button>
                    <?php } ?>
                  </form>
                </div>
              </div>

              <div class="card mb-4">
                <div class="card-header py-3">
                  <h6 class="m-0 font-weight-bold text-primary">All Classes</h6>
                </div>
                <div class="table-responsive p-3">
                  <table class="table table-hover" id="dataTableHover">
                    <thead class="thead-light">
                      <tr>
                        <th>#</th>
                        <th>Class Name</th>
                        <th>Edit</th>
                        <th>Delete</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      $query = "SELECT * FROM tblclass";
                      $rs = $conn->query($query);
                      $sn = 0;
                      if($rs->num_rows > 0){
                        while ($rows = $rs->fetch_assoc()) {
                          $sn++;
                          echo "<tr>
                            <td>{$sn}</td>
                            <td>{$rows['className']}</td>
                            <td><a href='?action=edit&Id={$rows['Id']}'><i class='fas fa-edit'></i> Edit</a></td>
                            <td><a href='?action=delete&Id={$rows['Id']}'><i class='fas fa-trash'></i> Delete</a></td>
                          </tr>";
                        }
                      } else {
                        echo "<tr><td colspan='4' class='text-center text-danger'>No Record Found!</td></tr>";
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

  <a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
  </a>

  <!-- Scripts -->
  <script src="../vendor/jquery/jquery.min.js"></script>
  <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
  <script src="js/ruang-admin.min.js"></script>
  <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
  <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>

  <!-- SweetAlert popup after update/delete -->
  <script>
    $(document).ready(function () {
      $('#dataTableHover').DataTable();

      <?php if (isset($_SESSION['status']) && $_SESSION['status'] != ''): ?>
        Swal.fire({
          icon: '<?php echo $_SESSION['status']; ?>',
          title: '<?php echo $_SESSION['message']; ?>',
          confirmButtonText: 'OK'
        });
        <?php 
        unset($_SESSION['status']);
        unset($_SESSION['message']);
        ?>
      <?php endif; ?>

      <?php if (!empty($status) && !empty($message)): ?>
        Swal.fire({
          icon: '<?php echo $status; ?>',
          title: '<?php echo $message; ?>',
          confirmButtonText: 'OK'
        });
      <?php endif; ?>
    });
  </script>
</body>
</html>
