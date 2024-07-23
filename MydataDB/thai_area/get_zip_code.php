<?php
require_once '../config/dbconnect.php';

if (isset($_POST['district_code'])) {
    $district_code = $_POST['district_code'];

    $sql_zip_code = "SELECT zip_code FROM districts WHERE id = ?";
    $stmt = $conn->prepare($sql_zip_code);
    $stmt->bind_param("s", $district_code);
    $stmt->execute();
    $result = $stmt->get_result();

    $zip_code = '';
    if ($row = $result->fetch_assoc()) {
        $zip_code = $row['zip_code'];
    }

    echo $zip_code;

    $stmt->close();
}
$conn->close();
?>