<?php
require_once '../config/dbconnect.php';

if (isset($_POST['province_code'])) {
    $province_code = $_POST['province_code'];

    $sql_amphures = "SELECT id, name_th FROM amphures WHERE province_id = ?";
    $stmt = $conn->prepare($sql_amphures);
    $stmt->bind_param("i", $province_code);
    $stmt->execute();
    $result = $stmt->get_result();

    echo '<option value="">เลือกอำเภอ</option>';
    while ($row = $result->fetch_assoc()) {
        echo '<option value="' . $row['id'] . '">' . $row['name_th'] . '</option>';
    }

    $stmt->close();
}
$conn->close();
?>