<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require('../Includes/dbcon.php');
require('tfpdf.php');

if(isset($_GET['regId'])){
    $regId = $_GET['regId'];
    $start = $_GET['start'];
    $end = $_GET['end'];

    // Fetch student attendance
    $sql = "SELECT s.regId, s.studentName,
                IFNULL(SUM(CASE WHEN a.status = '1' THEN 1 ELSE 0 END), 0) AS presentDays,
                IFNULL(SUM(CASE WHEN a.status = '0' THEN 1 ELSE 0 END), 0) AS absentDays,
                COUNT(a.Id) AS totalDays
            FROM tblstudents s
            LEFT JOIN tblattendance a
              ON s.regId = a.admissionNo
             AND a.dateTimeTaken BETWEEN ? AND ?
            WHERE s.regId = ?
            GROUP BY s.regId, s.studentName";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $start, $end, $regId);
    $stmt->execute();
    $res = $stmt->get_result();

    $row = $res->fetch_assoc();
    if(!$row) exit('No record found');

    $percent = $row['totalDays']>0 ? round(($row['presentDays']/$row['totalDays'])*100,2) : 0;

    $pdf = new tFPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(0,10,'Attendance Report',0,1,'C');
    $pdf->SetFont('Arial','',12);
    $pdf->Ln(5);

    $pdf->Cell(50,10,'Admission No:',0,0);
    $pdf->Cell(0,10,$row['regId'],0,1);

    $pdf->Cell(50,10,'Student Name:',0,0);
    $pdf->Cell(0,10,$row['studentName'],0,1);

    $pdf->Cell(50,10,'Present Days:',0,0);
    $pdf->Cell(0,10,$row['presentDays'],0,1);

    $pdf->Cell(50,10,'Absent Days:',0,0);
    $pdf->Cell(0,10,$row['absentDays'],0,1);

    $pdf->Cell(50,10,'Total Days:',0,0);
    $pdf->Cell(0,10,$row['totalDays'],0,1);

    $pdf->Cell(50,10,'Attendance %:',0,0);
    $pdf->Cell(0,10,$percent.'%',0,1);

    $pdf->Output('D','Attendance_'.$row['regId'].'.pdf');
}
?>
