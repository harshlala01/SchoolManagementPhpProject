<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../Includes/dbcon.php';
include '../Includes/session.php';

// Get POST data and sanitize
$classId    = intval($_POST['classId'] ?? 0);
$classSecId = intval($_POST['classSecId'] ?? 0);
$startDate  = $conn->real_escape_string($_POST['startDate'] ?? '');
$endDate    = $conn->real_escape_string($_POST['endDate'] ?? '');

if (!$classId || !$classSecId || !$startDate || !$endDate) {
    die("Invalid parameters provided.");
}

// Query attendance data (same as on page)
$sql = "
SELECT 
    s.regId,
    s.studentName,
    COALESCE(SUM(CASE WHEN a.status = '1' THEN 1 ELSE 0 END), 0) AS presentDays,
    COALESCE(COUNT(a.Id), 0) AS totalDays
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

// Send headers to force download as Excel file (HTML table)
$filename = "Attendance_Report_{$startDate}_to_{$endDate}.xls";
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

// Optional: BOM for UTF-8 (helps Excel open UTF-8 chars correctly)
echo "\xEF\xBB\xBF";

echo "<table border='1'>";
echo "<tr>
        <th>Reg ID</th>
        <th>Name</th>
        <th>Present Days</th>
        <th>Total Days</th>
        <th>Attendance %</th>
      </tr>";

while ($row = $res->fetch_assoc()) {
    $present = intval($row['presentDays']);
    $total = intval($row['totalDays']);
    $percent = ($total > 0) ? round(($present / $total) * 100, 2) : 0;

    // echo each row (no HTML classes inside excel)
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['regId'], ENT_QUOTES) . "</td>";
    echo "<td>" . htmlspecialchars($row['studentName'], ENT_QUOTES) . "</td>";
    echo "<td>$present</td>";
    echo "<td>$total</td>";
    echo "<td>$percent%</td>";
    echo "</tr>";
}

echo "</table>";
exit;
