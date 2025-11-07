        <?php
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        include '../Includes/dbcon.php';
        include '../Includes/session.php';

        // Helper to escape output safely
        function esc($s) {
            return htmlspecialchars($s, ENT_QUOTES);
        }

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
        </head>
        <style>
    
    .form-select.custom-select {
        height: 48px; 
        font-size: 1rem; 
        padding: 10px 12px; 
        border-radius: 6px; 
        border: 1px solid #ccc; 
    }

    .form-label {
        font-weight: 600;
        margin-bottom: 6px;
    }

    .card-header {
        padding: 15px 20px;
    }
    </style>
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
        <form method="post" id="filterForm">
        <div class="row g-4 align-items-end">
            
            <!-- Class Dropdown -->
            <div class="col-md-3">
            <label for="classId" class="form-label">Class <span class="text-danger">*</span></label>
            <select name="classId" id="classId" class="form-select custom-select" required>
                <option value="">-- Select Class --</option>
                <?php
                $qry = "SELECT * FROM tblclass ORDER BY className ASC";
                $result = $conn->query($qry);
                while ($cls = $result->fetch_assoc()) {
                $selected = ($classId == $cls['Id']) ? "selected" : "";
                echo "<option value='" . esc($cls['Id']) . "' $selected>" . esc($cls['className']) . "</option>";
                }
                ?>
            </select>
            </div>

            <!-- Section Dropdown -->
            <div class="col-md-3">
            <label for="classSecId" class="form-label">Section <span class="text-danger">*</span></label>
            <select name="classSecId" id="classSecId" class="form-select custom-select" required>
                <option value="">-- Select Section --</option>
                <?php
                $qrySec = "SELECT Id, classArmName FROM tblclassarms GROUP BY classArmName ORDER BY classArmName ASC";
                $resSec = $conn->query($qrySec);
                while ($sec = $resSec->fetch_assoc()) {
                $selected = ($classSecId == $sec['Id']) ? 'selected' : '';
                echo "<option value='" . esc($sec['Id']) . "' $selected>" . esc($sec['classArmName']) . "</option>";
                }
                ?>
            </select>
            </div>

            <!-- Start Date -->
            <div class="col-md-2">
            <label class="form-label">Start Date</label>
            <input type="date" name="startDate" class="form-control" value="<?php echo esc($startDate); ?>" required>
            </div>

            <!-- End Date -->
            <div class="col-md-2">
            <label class="form-label">End Date</label>
            <input type="date" name="endDate" class="form-control" value="<?php echo esc($endDate); ?>" required>
            </div>

            <!-- Submit Button -->
            <div class="col-md-2 text-end">
            <button type="submit" class="btn btn-success w-100" style="height:48px;">
                <i class="fas fa-search"></i> Show Records
            </button>
            </div>
        </div>
        </form>
    </div>
    </div>

                    <?php
                    // Validate inputs before querying
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        // Basic validation and sanitization
                        $classId    = intval($classId);
                        $classSecId = intval($classSecId);
                        $startDate  = $conn->real_escape_string($startDate);
                        $endDate    = $conn->real_escape_string($endDate);

                        if ($classId && $classSecId && $startDate && $endDate) {
                            // Query attendance summary per student
                            // $sql = "
                            // SELECT 
                            //     s.regId,
                            //     s.studentName,
                            //     COALESCE(SUM(CASE WHEN a.status = '1' THEN 1 ELSE 0 END), 0) AS presentDays,
                            //     COALESCE(COUNT(a.Id), 0) AS totalDays
                            // FROM tblstudents s
                            // LEFT JOIN tblattendance a 
                            //     ON a.admissionNo = s.regId
                            //     AND DATE(a.dateTimeTaken) BETWEEN '$startDate' AND '$endDate'
                            // WHERE s.classId = '$classId'
                            // AND s.classSecId = '$classSecId'
                            // GROUP BY s.regId, s.studentName
                            // ORDER BY s.studentName ASC
                            // ";
                            $sql="SELECT 
    s.regId,
    s.studentName,
    COALESCE(SUM(CASE WHEN a.status = '1' THEN 1 ELSE 0 END), 0) AS presentDays,
    COALESCE(COUNT(DISTINCT DATE(a.dateTimeTaken)), 0) AS totalDays
FROM tblstudents s
LEFT JOIN tblattendance a 
    ON a.admissionNo = s.regId
    AND DATE(a.dateTimeTaken) BETWEEN '$startDate' AND '$endDate'
WHERE s.classId = '$classId'
AND s.classSecId = '$classSecId'
GROUP BY s.regId, s.studentName
ORDER BY s.studentName ASC
";
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
                            $present = intval($row['presentDays']);
                            $total = intval($row['totalDays']);
                            $percent = ($total > 0) ? round(($present / $total) * 100, 2) : 0;
                            $color = ($percent >= 75) ? 'text-success' : (($percent >= 50) ? 'text-warning' : 'text-danger');
                            echo "<tr>
                                    <td>" . esc($row['regId']) . "</td>
                                    <td>" . esc($row['studentName']) . "</td>
                                    <td>" . $present . "</td>
                                    <td>" . $total . "</td>
                                    <td class='{$color}'><strong>" . $percent . "%</strong></td>
                                    </tr>";
                            }
                            echo '</tbody></table>';

                            // Excel Download Button (posts same filters to export script)
                            echo '<form method="post" action="exportAttendance.php" target="_blank" class="mt-3">';
                            echo '<input type="hidden" name="classId" value="' . esc($classId) . '">';
                            echo '<input type="hidden" name="classSecId" value="' . esc($classSecId) . '">';
                            echo '<input type="hidden" name="startDate" value="' . esc($startDate) . '">';
                            echo '<input type="hidden" name="endDate" value="' . esc($endDate) . '">';
                            echo '<button type="submit" class="btn btn-success"><i class="fas fa-file-excel"></i> Download Excel</button>';
                            echo '</form>';

                            echo '</div></div></div>';
                        } else {
                            echo '<div class="alert alert-warning mt-3">Please select Class, Section and valid Start/End dates.</div>';
                        }
                    }
                    ?>
                </div>
                <!-- </div> -->

                <?php include "Includes/footer.php"; ?>
            </div>
            <!-- </div> -->
        </div>

        <script src="../vendor/jquery/jquery.min.js"></script>
        <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
        <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
        <script src="js/ruang-admin.min.js"></script>
        </body>
        </html>
