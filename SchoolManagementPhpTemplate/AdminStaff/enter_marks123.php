<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../Includes/dbcon.php';
include '../Includes/session.php';

$regId = $_GET['regId'] ?? $_POST['regId'] ?? '';
$classId = $_GET['classId'] ?? $_POST['classId'] ?? '';

if (!$regId || !$classId) {
    die("Missing student or class ID.");
}

$term = $_POST['term'] ?? $_GET['term'] ?? '';
$subjects = [];
$studentDetails = [];

// Get class name
if (!empty($classId)) {
    $stmt = $conn->prepare("SELECT className FROM tblclass WHERE Id = ?");
    $stmt->bind_param("i", $classId);
    $stmt->execute();
    $stmt->bind_result($className);
    $stmt->fetch();
    $stmt->close();
}

// Fetch student details
if (!empty($regId)) {
    $studentQuery = "
        SELECT s.*, c.className, cs.classArmName
        FROM tblstudents s
        JOIN tblclass c ON s.classId = c.id
        JOIN tblclassarms cs ON s.classSecId = cs.id
        WHERE s.regId = '$regId' LIMIT 1";
    $result = mysqli_query($conn, $studentQuery);
    if ($result && mysqli_num_rows($result) > 0) {
        $studentDetails = mysqli_fetch_assoc($result);
    }
}

// Load subjects based on class and term
if (!empty($className) && !empty($term)) {
    $subjectSql = "SELECT * FROM subject_master WHERE className = '$classId' AND Term = '$term'";
    $subjectResult = mysqli_query($conn, $subjectSql);

    if ($subjectResult && mysqli_num_rows($subjectResult) > 0) {
        while ($row = mysqli_fetch_assoc($subjectResult)) {
            $subjects[] = $row;
        }
    }

    $allowedSubjectIds = ['english', 'math', 'hindi_bengali', 'computer', 'evs', 'drawing'];
    $classWithSciSST = ['VI', 'VII', 'VIII', 'IX', 'X'];

    if (in_array(strtoupper($className), $classWithSciSST)) {
        $allowedSubjectIds[] = 'science';
        $allowedSubjectIds[] = 'sst';
    }

    $subjects = array_filter($subjects, function ($subj) use ($allowedSubjectIds) {
        return in_array(strtolower($subj['subjectId']), $allowedSubjectIds);
    });

    $subjects = array_values($subjects);
}
echo '<pre>';
print_r($subjects);
echo '</pre>';

function calculateGrade($percentage) {
    if ($percentage >= 90) return 'A+';
    if ($percentage >= 80) return 'A';
    if ($percentage >= 70) return 'B+';
    if ($percentage >= 60) return 'B';
    if ($percentage >= 50) return 'C';
    if ($percentage >= 40) return 'D';
    return 'F';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_marks'])) {
    $theoryMarks = $_POST['theory'] ?? [];
    $practicalMarks = $_POST['practical'] ?? [];
    $subjectMarks = [];
    $totalMarks = 0;
    $maxMarks = 0;
// echo '<pre>';
// print_r($_POST);
//   echo "\n🟢 Student Details (From DB):\n";
//     // print_r($studentDetails);

//     echo "\n🔵 Subjects Fetched (From DB):\n";
//     print_r($subjects);

//     echo "\n🟠 Subject Class Match:\n";
//     echo "Class in subject_master: " . ($_POST['classId'] ?? 'N/A') . "\n";
//     echo "Class of student: " . ($studentDetails['classId'] ?? 'N/A') . "\n";
//     echo "Class Name of student: " . ($studentDetails['className'] ?? 'N/A') . "\n";

//     echo '</pre>';
//      exit;
    foreach ($subjects as $subject) {
        $subId = $subject['subjectId'];

        if ($subject['subjectTheory']) {
            $theory = (int)($theoryMarks[$subId] ?? 0);
            $subjectMarks[$subId . '_theory'] = $theory;
            $totalMarks += $theory;
            $maxMarks += 100;
        }

        if (strtolower($subject['subjectPractical']) === 'yes') {
            $practical = (int)($practicalMarks[$subId] ?? 0);
            $subjectMarks[$subId . '_practical'] = $practical;
            $totalMarks += $practical;
            $maxMarks += 100;
        }
    }

    $percentage = $maxMarks > 0 ? round(($totalMarks / $maxMarks) * 100, 2) : 0;
    $grade = calculateGrade($percentage);

    $checkQuery = "SELECT * FROM student_marks WHERE regId='$regId' AND classId='$classId' AND term='$term'";
    $checkResult = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($checkResult) > 0) {
        // UPDATE
        $setParts = [];

        foreach ($subjectMarks as $key => $val) {
            $setParts[] = "$key = '$val'";
        }

        $setParts[] = "total_marks = '$totalMarks'";
        $setParts[] = "percentage = '$percentage'";
        $setParts[] = "grade = '$grade'";

        $updateSql = "UPDATE student_marks SET " . implode(", ", $setParts) . " 
                      WHERE regId='$regId' AND classId='$classId' AND term='$term'";
        mysqli_query($conn, $updateSql);
    } else {
        // INSERT
        $columns = [
            'regId', 'studentName', 'classId', 'classSecId', 'className', 'classArmName', 'session',
            'motherName', 'fatherName', 'address', 'dob', 'total_marks', 'percentage', 'grade', 'created_at', 'term'
        ];

        $values = [
            "'$regId'",
            "'" . mysqli_real_escape_string($conn, $studentDetails['studentName']) . "'",
            (int)$studentDetails['classId'],
            (int)$studentDetails['classSecId'],
            "'" . mysqli_real_escape_string($conn, $studentDetails['className']) . "'",
            "'" . mysqli_real_escape_string($conn, $studentDetails['classArmName']) . "'",
            "'" . mysqli_real_escape_string($conn, $studentDetails['session']) . "'",
            "'" . mysqli_real_escape_string($conn, $studentDetails['motherName']) . "'",
            "'" . mysqli_real_escape_string($conn, $studentDetails['fatherName']) . "'",
            "'" . mysqli_real_escape_string($conn, $studentDetails['address']) . "'",
            "'" . mysqli_real_escape_string($conn, $studentDetails['dob']) . "'",
            $totalMarks,
            $percentage,
            "'$grade'",
            "'" . date('Y-m-d H:i:s') . "'",
            "'$term'"
        ];

        foreach ($subjectMarks as $key => $val) {
            $columns[] = $key;
            $values[] = "'$val'";
        }

        $insertSql = "INSERT INTO student_marks (" . implode(",", $columns) . ") VALUES (" . implode(",", $values) . ")";
        mysqli_query($conn, $insertSql);
    }

        // echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        // echo "<script>
        //     document.addEventListener('DOMContentLoaded', function() {
        //         Swal.fire({
        //             title: '✅ Success!',
        //             text: 'Marks submitted successfully.',
        //             icon: 'success',
        //             confirmButtonText: 'OK'
        //         }).then(function() {
        //             window.location.href = 'studentMarksheet.php';
        //         });
        //     });
        // </script>";
        // exit;
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
<?php if (!empty($studentDetails)): ?>
    <div class="card shadow border rounded mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Student Information</h5>
        </div>
        <div class="card-body">
            <div class="row g-3 mb-3">
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
                    <input type="text" class="form-control" value="<?= htmlspecialchars($studentDetails['className'] ?? '') ?>" readonly>
                </div>
            </div>

            <div class="row g-3 mb-3">
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

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label">Father's Name</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($studentDetails['fatherName']) ?>" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Date of Birth</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($studentDetails['dob']) ?>" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Roll No.</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($studentDetails['rollNo'] ?? '') ?>" readonly>
                </div>
            </div>

            <div class="row g-3">
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




<!-- <form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="regId" value="<?= htmlspecialchars($regId) ?>">
    <input type="hidden" name="classId" value="<?= htmlspecialchars($classId) ?>">
    <input type="hidden" name="className" value="<?= htmlspecialchars($className) ?>">
     <input type="hidden" name="className" value="<?= htmlspecialchars($className ?? '', ENT_QUOTES, 'UTF-8') ?>">


    <label><strong>Select Term:</strong></label>
    <select name="term" class="form-control mb-4" onchange="this.form.submit()" required>
        <option value="">-- Select Term --</option>
        <option value="Term 1" <?= $term == 'Term 1' ? 'selected' : '' ?>>Term 1</option>
        <option value="Term 2" <?= $term == 'Term 2' ? 'selected' : '' ?>>Term 2</option>
        <option value="Term 3" <?= $term == 'Term 3' ? 'selected' : '' ?>>Term 3</option>
    </select>

    <?php if (!empty($subjects)): ?>
        <?php $realPracticalSubjects = ['drawing']; ?>

        <div class="card shadow-sm mb-4">
           
            <div class="card-body">
                <div class="table-responsive mb-3">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Subject</th>
                                <th>Theory Marks</th>
                                <th>Practical Marks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($subjects as $subject): ?>
                                <?php
                                    $subjectId = $subject['subjectId'];
                                    $subjectName = $subject['subjectName'];
                                    $hasTheory = $subject['subjectTheory'] == 1;
                                    $hasPractical = strtolower($subject['subjectPractical']) === 'yes';
                                    $subjectKey = strtolower(trim($subjectName));
                                    $isRealPractical = in_array($subjectKey, $realPracticalSubjects);
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($subjectName) ?>
                                        <input type="hidden" name="subjectId[]" value="<?= $subjectId ?>">
                                    </td>
                                    <td>
                                        <?php if ($hasTheory): ?>
                                            <input type="number" name="theory[<?= $subjectId ?>]" class="form-control" min="0" max="100" placeholder="Enter Theory Marks" required>
                                        <?php else: ?>
                                            <input type="text" class="form-control" value="N/A" readonly style="background-color: #f0f0f0; color: gray;">
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($hasPractical && $isRealPractical): ?>
                                            <input type="number" name="practical[<?= $subjectId ?>]" class="form-control" min="0" max="100" placeholder="Enter Practical Marks" required>
                                        <?php else: ?>
                                            <input type="text" class="form-control" value="N/A" readonly style="background-color: #f0f0f0; color: gray;">
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                Total & Percentage in 2 columns
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label><strong>Total Marks:</strong></label>
                        <input type="text" id="total_marks" class="form-control" readonly style="background-color: #f9f9f9;">
                    </div>
                    <div class="col-md-6">
                        <label><strong>Percentage:</strong></label>
                        <input type="text" id="percentage" class="form-control" readonly style="background-color: #f9f9f9;">
                    </div>
                </div>

                <div class="d-flex justify-content-start gap-2">
                    <button type="submit" name="save_marks" class="btn btn-success">Submit</button>
                    <a href="studentMarksheet.php" class="btn btn-secondary">Back</a>
                </div>
            </div>
        </div>
    <?php elseif ($term): ?>
        <div class="alert alert-warning">No subjects available for this class and term.</div>
    <?php endif; ?>
</form> -->

 <form method="post">
     <div class="card-header bg-primary text-white">
                <strong>Enter Subject Marks</strong>
            </div>
            <div class="form-group">
                <!-- <label>Select Term:</label>
                <select name="term" class="form-control" onchange="this.form.submit()" required>
                    <option value="">-- Select Term --</option>
                    <option value="Term 1" <?= $term == 'Term 1' ? 'selected' : '' ?>>Term 1</option>
                    <option value="Term 2" <?= $term == 'Term 2' ? 'selected' : '' ?>>Term 2</option>
                    <option value="Term 3" <?= $term == 'Term 3' ? 'selected' : '' ?>>Term 3</option>
                </select> -->
                 <label><strong>Select Term:</strong></label>
    <select name="term" class="form-control mb-4" onchange="this.form.submit()" required>
        <option value="">-- Select Term --</option>
        <option value="Term 1" <?= $term == 'Term 1' ? 'selected' : '' ?>>Term 1</option>
        <option value="Term 2" <?= $term == 'Term 2' ? 'selected' : '' ?>>Term 2</option>
        <option value="Term 3" <?= $term == 'Term 3' ? 'selected' : '' ?>>Term 3</option>
    </select>
            </div>
            <div class="form-group">
    <label><strong>Exam Type:</strong></label>
    <input type="text" id="examTypeDisplay" class="form-control" readonly style="background-color:#f5f5f5;">
</div>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const termSelect = document.querySelector("select[name='term']");
    const examTypeDisplay = document.getElementById("examTypeDisplay");

    function updateExamType() {
        const selectedTerm = termSelect.value;
        if (selectedTerm === "Term 1") {
            examTypeDisplay.value = "Half Yearly";
        } else if (selectedTerm === "Term 2") {
            examTypeDisplay.value = "Annual";
        } else {
            examTypeDisplay.value = "";
        }
    }

    // Trigger on load (if term was already selected)
    updateExamType();

    // Optional: Add manual trigger in case user changes it before auto-submit
    termSelect.addEventListener("change", updateExamType);
});
</script>

        </form>

        <?php if (!empty($subjects)): ?>
            <form method="post">
                <input type="hidden" name="term" value="<?= $term ?>">
                <table class="table table-bordered mt-4">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Theory Marks</th>
                            <th>Practical Marks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subjects as $subject): ?>
                            <?php
                                $subjectId = $subject['subjectId'];
                                $subjectName = $subject['subjectName'];
                            ?>
                            <tr>
                                <td><?= $subjectName ?></td>
                                <td>
                                    <input type="number" name="theory[<?= $subjectId ?>]" class="form-control" min="0" max="100" placeholder="Enter Theory Marks" required>
                                </td>
                                <td>
                                    <!-- <input type="number" name="practical[<?= $subjectId ?>]" class="form-control" min="0" max="100" placeholder="Enter Practical Marks" required> -->
                                     
    <?php if (strtolower($subject['subjectPractical']) === 'yes'): ?>
        <input type="number" name="practical[<?= $subjectId ?>]" class="form-control" min="0" max="100" placeholder="Enter Practical Marks" required>
    <?php else: ?>
        <input type="text" class="form-control" value="N/A" readonly style="background:#f0f0f0;">
        <input type="hidden" name="practical[<?= $subjectId ?>]" value="0">
    <?php endif; ?>


                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                 <!-- Total & Percentage in 2 columns -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label><strong>Total Marks:</strong></label>
                        <input type="text" id="total_marks" class="form-control" readonly style="background-color: #f9f9f9;">
                    </div>
                    <div class="col-md-6">
                        <label><strong>Percentage:</strong></label>
                        <input type="text" id="percentage" class="form-control" readonly style="background-color: #f9f9f9;">
                    </div>
                </div>

                <div class="d-flex justify-content-start gap-2">
                    <button type="submit" name="save_marks" class="btn btn-success">Submit</button>
                    <a href="studentMarksheet.php" class="btn btn-secondary">Back</a>
                </div>
                <!-- <button type="submit" name="save_marks" class="btn btn-success">Save Marks</button> -->
            </form>
        <?php elseif ($term): ?>
            <div class="alert alert-warning">No subjects found for the selected term.</div>
        <?php endif; ?>
   


<script>
document.addEventListener('DOMContentLoaded', function () {
    const totalField = document.getElementById('total_marks');
    const percentageField = document.getElementById('percentage');

    function calculateTotals() {
        const inputs = document.querySelectorAll('input[type="number"]:not([readonly])');
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

    // Attach input event listener to all numeric inputs
    document.querySelectorAll('input[type="number"]:not([readonly])').forEach(input => {
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
<!-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> -->
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
    