<?php
// include '../Includes/dbcon.php';

// if(isset($_GET['regId'])){
//     $regId = $_GET['regId'];
//     $start = $_GET['start'];
//     $end = $_GET['end'];
// // var_dump($_GET['regId']);

// // exit;

//     // Fetch student attendance
//     $sql = "SELECT s.regId, s.studentName,
//                 IFNULL(SUM(CASE WHEN a.status = '1' THEN 1 ELSE 0 END), 0) AS presentDays,
//                 IFNULL(SUM(CASE WHEN a.status = '0' THEN 1 ELSE 0 END), 0) AS absentDays,
//                 COUNT(a.Id) AS totalDays
//             FROM tblstudents s
//             LEFT JOIN tblattendance a
//               ON s.regId = a.admissionNo
//              AND a.dateTimeTaken BETWEEN ? AND ?
//             WHERE s.regId = ?
//             GROUP BY s.regId, s.studentName";
//     // $sql = "SELECT s.regId, s.studentName,
//     //         IFNULL(SUM(CASE WHEN a.status = '1' THEN 1 ELSE 0 END), 0) AS presentDays,
//     //         IFNULL(SUM(CASE WHEN a.status = '0' THEN 1 ELSE 0 END), 0) AS absentDays,
//     //         COUNT(a.Id) AS totalDays
//     //     FROM tblstudents s
//     //     LEFT JOIN tblattendance a
//     //       ON s.regId = a.admissionNo
//     //      AND a.dateTimeTaken BETWEEN ? AND ?
//     //     WHERE s.regId = ?
//     //     GROUP BY s.regId, s.studentName";


//     $stmt = $conn->prepare($sql);
//     $stmt->bind_param("ssi", $start, $end, $regId);
//     $stmt->execute();
//     $res = $stmt->get_result();

//     header("Content-Type: text/csv");
//     header("Content-Disposition: attachment; filename=attendance_{$regId}.csv");

//     $output = fopen("php://output", "w");
//     fputcsv($output, ['Admission No','Student Name','Present Days','Absent Days','Total Days','Attendance %']);

//     while($row = $res->fetch_assoc()){
//         // var_dump($row);
//         $percent = $row['totalDays']>0 ? round(($row['presentDays']/$row['totalDays'])*100,2) : 0;
//         fputcsv($output, [$row['regId'],$row['studentName'],$row['presentDays'],$row['absentDays'],$row['totalDays'],$percent.'%']);
//     }

//     fclose($output);
//     exit;
// }
?>


<?php
// include '../Includes/dbcon.php';

// if (isset($_GET['regId'])) {
//     $regId = $_GET['regId'];
//     $start = $_GET['start'];
//     $end   = $_GET['end'];

//     $sql = "SELECT s.regId, s.studentName,
//                 IFNULL(SUM(CASE WHEN a.status = '1' THEN 1 ELSE 0 END), 0) AS presentDays,
//                 IFNULL(SUM(CASE WHEN a.status = '0' THEN 1 ELSE 0 END), 0) AS absentDays
//             FROM tblstudents s
//             LEFT JOIN tblattendance a
//               ON s.regId = a.admissionNo
//              AND a.dateTimeTaken BETWEEN ? AND ?
//             WHERE s.regId = ?
//             GROUP BY s.regId, s.studentName";

//     $stmt = $conn->prepare($sql);
//     $stmt->bind_param("ssi", $start, $end, $regId);
//     $stmt->execute();
//     $res = $stmt->get_result();

//     // Excel headers
//     header("Content-Type: text/csv");
//     header("Content-Disposition: attachment; filename=attendance_{$regId}.csv");

//     $output = fopen("php://output", "w");
//     fputcsv($output, ['Admission No', 'Student Name', 'Present Days', 'Absent Days', 'Total Days', 'Attendance %']);

//     while ($row = $res->fetch_assoc()) {
//         $present = $row['presentDays'];
//         $absent  = $row['absentDays'];
//         $total   = $present + $absent;

//         // ✅ Correct percentage calculation
//         $percent = $total > 0 ? round(($present / $total) * 100, 2) : 0;

//         fputcsv($output, [
//             $row['regId'],
//             $row['studentName'],
//             $present,
//             $absent,
//             $total,
//             $percent . '%'
//         ]);
//     }

//     fclose($output);
//     exit;
// }
?>


<?php
include '../Includes/dbcon.php';

if(isset($_GET['regId'])){
    $regId = $_GET['regId'];
    $start = $_GET['start'];
    $end = $_GET['end'];

    // Fetch student attendance (unique dates fix)
    $sql = "SELECT s.regId, s.studentName,
                IFNULL(SUM(CASE WHEN a.status = '1' THEN 1 ELSE 0 END), 0) AS presentDays,
                IFNULL(SUM(CASE WHEN a.status = '0' THEN 1 ELSE 0 END), 0) AS absentDays,
                COUNT(DISTINCT DATE(a.dateTimeTaken)) AS totalDays
            FROM tblstudents s
            LEFT JOIN tblattendance a
              ON s.regId = a.admissionNo
             AND DATE(a.dateTimeTaken) BETWEEN ? AND ?
            WHERE s.regId = ?
            GROUP BY s.regId, s.studentName";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $start, $end, $regId);
    $stmt->execute();
    $res = $stmt->get_result();

    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=attendance_{$regId}.csv");

    $output = fopen("php://output", "w");
    fputcsv($output, ['Admission No','Student Name','Present Days','Absent Days','Total Days','Attendance %']);

    while($row = $res->fetch_assoc()){
        $percent = $row['totalDays']>0 ? round(($row['presentDays']/$row['totalDays'])*100,2) : 0;
        fputcsv($output, [
            $row['regId'],
            $row['studentName'],
            $row['presentDays'],
            $row['absentDays'],
            $row['totalDays'],
            $percent.'%'
        ]);
    }

    fclose($output);
    exit;
}
?>
