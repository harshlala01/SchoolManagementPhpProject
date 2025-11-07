<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../Includes/dbcon.php';
include '../Includes/session.php';

$classId    = $_POST['classId']    ?? '';
$classSecId = $_POST['classSecId'] ?? '';
$startDate  = $_POST['startDate']  ?? '';
$endDate    = $_POST['endDate']    ?? '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <title>Take Attendance</title>
  <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" />
  <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
  <link href="css/ruang-admin.min.css" rel="stylesheet" />
  <link href="img/logo/attnlg.jpg" rel="icon" />

<!-- Bootstrap + DataTables CSS -->
<!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"> -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">

<body id="page-top">
  <div id="wrapper">
    <?php include "Includes/sidebar.php"; ?>
    <div id="content-wrapper" class="d-flex flex-column">
      <div id="content">
        <?php include "Includes/topbar.php"; ?>

        <div class="container-fluid" id="container-wrapper">
          <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Attendance Record</h1>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="./">Home</a></li>
              <li class="breadcrumb-item active" aria-current="page">Attendance</li>
            </ol>
          </div>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Attendance Report</h4>
        </div>
        <div class="card-body">
            <form method="post" class="row g-3">
                <!-- Class Dropdown -->
                <div class="col-md-4">
                    <label for="classId" class="form-label">Class <span class="text-danger">*</span></label>
                    <select name="classId" id="classId" class="form-select" required onchange="this.form.submit()">
                        <option value="">--Select Class--</option>
                        <?php
                        $qry = "SELECT * FROM tblclass ORDER BY className ASC";
                        $result = $conn->query($qry);
                        while ($cls = $result->fetch_assoc()) {
                            $selected = ($classId == $cls['Id']) ? "selected" : "";
                            echo "<option value='{$cls['Id']}' $selected>{$cls['className']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- Section Dropdown (No Filter) -->
                <div class="col-md-4">
                    <label for="classSecId" class="form-label">Section <span class="text-danger">*</span></label>
                    <select name="classSecId" id="classSecId" class="form-select" required>
                        <option value="">--Select Section--</option>
                        <?php
                        $qrySec = "SELECT MIN(Id) as Id, classArmName FROM tblclassarms GROUP BY classArmName ORDER BY classArmName ASC";
                        $resSec = $conn->query($qrySec);
                        while ($sec = $resSec->fetch_assoc()) {
                            $selected = ($classSecId == $sec['Id']) ? 'selected' : '';
                            echo "<option value='{$sec['Id']}' $selected>{$sec['classArmName']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- Date Pickers -->
                <div class="col-md-2">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="startDate" class="form-control" value="<?php echo $startDate; ?>" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">End Date</label>
                    <input type="date" name="endDate" class="form-control" value="<?php echo $endDate; ?>" required>
                </div>

                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-success">Show Records</button>
                </div>
            </form>
        </div>
    </div>

    <?php
    if ($classId && $classSecId && $startDate && $endDate) {
     $sql = "
SELECT 
    s.regId,
    s.studentName,
    COALESCE(SUM(CASE WHEN a.status = '1' THEN 1 ELSE 0 END), 0) AS presentDays,
    COALESCE(COUNT(a.id), 0) AS totalDays
FROM tblstudents s
LEFT JOIN tblattendance a 
    ON a.admissionNo = s.regId
    AND DATE(a.dateTimeTaken) BETWEEN '$startDate' AND '$endDate'
WHERE s.classId = '$classId'
  AND s.classSecId = '$classSecId'
GROUP BY s.regId, s.studentName
ORDER BY s.studentName ASC
";
// inside the while($row = $res->fetch_assoc()) loop — top line
// echo "<pre>RAW ROW: "; var_dump($sql); echo "</pre>";
 // sirf 1 row dikhe, debugging ke liye


        $res = $conn->query($sql);

        echo '<div class="card mt-4 shadow">';
        echo '<div class="card-header bg-secondary text-white"><h5 class="mb-0">Attendance Records</h5></div>';
        echo '<div class="card-body p-0">';
        echo '<div class="table-responsive p-3">';
        echo '<table id="attendanceTable" class="table table-bordered table-hover">';
        echo '<thead class="table-dark">
                <tr>
                    <th>Reg ID</th>
                    <th>Name</th>
                    <th>Present Days</th>
                    <th>Total Days</th>
                    <th>Attendance %</th>
                </tr>
              </thead><tbody>';
        while ($row = $res->fetch_assoc()) {
            $percent = ($row['totalDays'] > 0) 
                ? round(($row['presentDays'] / $row['totalDays']) * 100, 2) 
                : 0;
            $color = ($percent >= 75) ? 'text-success' : (($percent >= 50) ? 'text-warning' : 'text-danger');
            echo "<tr>
                    <td>{$row['regId']}</td>
                    <td>{$row['studentName']}</td>
                    <td>{$row['presentDays']}</td>
                    <td>{$row['totalDays']}</td>
                    <td class='{$color}'><strong>{$percent}%</strong></td>
                  </tr>";
        }
        echo '</tbody></table></div></div></div>';
    }
    ?>
</div>
</div>

        <?php include "Includes/footer.php"; ?>
      </div>
    </div>
  </div>
<!-- JS Libraries -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<script>
$(document).ready(function() {
    $('#attendanceTable').DataTable({
        dom: 'Bfrtip',
        buttons: [
            'excelHtml5',
            'pdfHtml5',
            'print'
        ]
    });
});
</script>
 

  <script src="../vendor/jquery/jquery.min.js"></script>
  <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
  <script src="js/ruang-admin.min.js"></script>
</body>

</html>

