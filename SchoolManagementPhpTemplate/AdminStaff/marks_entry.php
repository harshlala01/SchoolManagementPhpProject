<!-- <?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../Includes/dbcon.php';

$submittedMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $regId = $_POST['regId'] ?? '';
    $className = $_POST['className'] ?? '';
    $totalMarks = $_POST['total_marks'] ?? 0;
    $percentage = $_POST['percentage'] ?? 0;

    // Define subjects
    if (in_array($className, ['I', 'II', 'III', 'IV', 'V'])) {
        $subjects = ['english', 'hindi_bengali', 'math', 'computer', 'evs', 'practical', 'drawing'];
    } elseif (in_array($className, ['VI', 'VII', 'VIII'])) {
        $subjects = ['english', 'hindi_bengali', 'math', 'computer', 'sst', 'science', 'practical', 'drawing'];
    } elseif ($className == 'X') {
        $subjects = ['english', 'hindi_bengali', 'math', 'computer', 'sst', 'science', 'practical'];
    } else {
        $subjects = [];
    }

    $columns = ['regId', 'className', 'total_marks', 'percentage'];
    $values = ["'$regId'", "'$className'", "'$totalMarks'", "'$percentage'"];

    foreach ($subjects as $sub) {
        $val = $_POST[$sub] ?? 0;
        $columns[] = $sub;
        $values[] = "'$val'";
    }

    $columnsStr = implode(', ', $columns);
    $valuesStr = implode(', ', $values);

    $query = "INSERT INTO student_marks ($columnsStr) VALUES ($valuesStr)";
    $result = mysqli_query($conn, $query);

    if ($result) {
        $submittedMessage = "Marks submitted successfully!";
    } else {
        $submittedMessage = "Error: " . mysqli_error($conn);
    }
}

$className = strtoupper(trim($_SESSION['className'] ?? 'I'));
$regId = $_SESSION['regId'] ?? '';

if (in_array($className, ['I', 'II', 'III', 'IV', 'V'])) {
    $subjects = ['English', 'Hindi/Bengali', 'Math', 'Computer', 'EVS', 'Practical', 'Drawing'];
} elseif (in_array($className, ['VI', 'VII', 'VIII'])) {
    $subjects = ['English', 'Hindi/Bengali', 'Math', 'Computer', 'SST', 'Science', 'Practical', 'Drawing'];
} elseif ($className == 'X') {
    $subjects = ['English', 'Hindi/Bengali', 'Math', 'Computer', 'SST', 'Science', 'Practical'];
} else {
    $subjects = [];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Enter Subject Marks</title>
    <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h4 class="text-primary mb-3">Enter Subject Marks (Class <?= htmlspecialchars($className) ?>)</h4>

    <?php if (!empty($submittedMessage)): ?>
        <div class="alert alert-info"><?= htmlspecialchars($submittedMessage) ?></div>
    <?php endif; ?>

    <form action="" method="post">
        <input type="hidden" name="regId" value="<?= htmlspecialchars($regId) ?>">
        <input type="hidden" name="className" value="<?= htmlspecialchars($className) ?>">

        <div class="row">
            <?php foreach ($subjects as $subject): ?>
                <div class="col-md-4 mb-3">
                    <label><?= $subject ?> Marks</label>
                    <input type="number"
                           name="<?= strtolower(str_replace([' ', '/'], '_', $subject)) ?>"
                           class="form-control marks-input"
                           min="0" max="100" required>
                </div>
            <?php endforeach; ?>
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

        <button type="submit" class="btn btn-success mt-2">Submit Marks</button>
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
</body>
</html> -->
