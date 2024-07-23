<?php
require_once 'config/dbconnect.php'; 
// ตรวจสอบให้แน่ใจว่าเส้นทางนี้ถูกต้อง

$sql_customers = "SELECT c.id, c.business_type, c.business_name, c.company_name, c.contact_name, c.email, c.phone, c.line_id, c.facebook, p.name_th AS province_name, a.name_th AS amphure_name, d.name_th AS district_name, c.zip_code 
FROM customers_data c
JOIN provinces p ON CONVERT(c.province USING utf8) = CONVERT(p.code USING utf8)
JOIN amphures a ON CONVERT(c.amphure USING utf8) = CONVERT(a.code USING utf8)
JOIN districts d ON CONVERT(c.district USING utf8) = CONVERT(d.id USING utf8)";

$result_customers = $conn->query($sql_customers);

if (!$result_customers) {
    die("Query failed: " . $conn->error);
}

// แสดงผลข้อมูลสำหรับตรวจสอบ
while ($row = $result_customers->fetch_assoc()) {
    print_r($row);
}
?>
