<?php
include '../Includes/dbcon.php';

if (isset($_POST['classId'])) {
    $classId = $_POST['classId'];

    header("Content-Type: application/vnd.ms-excel"); //Tell the browser that the content being sent is an Excel file.
    header("Content-Disposition: attachment; filename=class_{$classId}_students.xls");//Tell the browser to download the file as an attachment with the name class_[classId]_students.xls.
    header("Pragma: no-cache");
    header("Expires: 0");

    $query = mysqli_query($conn, "
        SELECT s.regId, s.studentName, s.gender, a.classArmName, s.priPhoneNo
        FROM tblstudents s
        LEFT JOIN tblclassarms a ON s.classSecId = a.Id
        WHERE s.classId = '$classId'
    ");

    echo "<table border='1'>";
    echo "<tr>
        <th>Reg ID</th>
        <th>Student Name</th>
        <th>Gender</th>
        <th>Section</th>
        <th>Phone No.</th>
    </tr>";

    while ($row = mysqli_fetch_assoc($query)) {
        echo "<tr>
            <td>{$row['regId']}</td>
            <td>{$row['studentName']}</td>
            <td>{$row['gender']}</td>
            <td>{$row['classArmName']}</td>
            <td>{$row['priPhoneNo']}</td>
        </tr>";
    }

    echo "</table>";
    exit;
}
?>
