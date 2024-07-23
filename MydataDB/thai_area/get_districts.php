<?php
require_once '../config/dbconnect.php'; 

if (isset($_POST['amphure_code'])) {
    $amphure_code = $_POST['amphure_code'];

    $sql_districts = "SELECT id, name_th FROM districts WHERE amphure_id = ?";
    $stmt = $conn->prepare($sql_districts);
    $stmt->bind_param("i", $amphure_code);
    $stmt->execute();
    $result = $stmt->get_result();

    echo '<option value="">เลือกตำบล</option>';
    while ($row = $result->fetch_assoc()) {
        echo '<option value="' . $row['id'] . '">' . $row['name_th'] . '</option>';
    }

    $stmt->close();
}
$conn->close();
?>
