<?php
require_once '../config/dbconnect.php'; 

// รับค่าค้นหาจากฟอร์ม
$search_query = isset($_GET['search']) ? $_GET['search'] : '';

// ตรวจสอบว่ามีค่าค้นหาหรือไม่
if (!empty($search_query)) {
    $search_query = "%{$search_query}%"; // เตรียมค่าค้นหา

    // สร้างคำสั่ง SQL เพื่อตรวจสอบข้อมูลลูกค้า
    $sql = "SELECT c.id, c.business_type, c.business_name, c.company_name, c.contact_name, c.email, c.phone, c.line_id, c.facebook, p.name_th AS province_name, a.name_th AS amphure_name, d.name_th AS district_name, c.zip_code 
            FROM customers_data c
            JOIN provinces p ON CONVERT(c.province USING utf8) = CONVERT(p.code USING utf8)
            JOIN amphures a ON CONVERT(c.amphure USING utf8) = CONVERT(a.code USING utf8)
            JOIN districts d ON CONVERT(c.district USING utf8) = CONVERT(d.id USING utf8)
            WHERE c.business_name LIKE ? OR c.contact_name LIKE ?";

    // เตรียมการเชื่อมต่อฐานข้อมูล
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("ss", $search_query, $search_query);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result) {
        die("Query failed: " . $conn->error);
    }
} else {
    // หากไม่มีการค้นหา แสดงข้อมูลทั้งหมด
    $sql = "SELECT c.id, c.business_type, c.business_name, c.company_name, c.contact_name, c.email, c.phone, c.line_id, c.facebook, p.name_th AS province_name, a.name_th AS amphure_name, d.name_th AS district_name, c.zip_code 
            FROM customers_data c
            JOIN provinces p ON CONVERT(c.province USING utf8) = CONVERT(p.code USING utf8)
            JOIN amphures a ON CONVERT(c.amphure USING utf8) = CONVERT(a.code USING utf8)
            JOIN districts d ON CONVERT(c.district USING utf8) = CONVERT(d.id USING utf8)";
    
    $result = $conn->query($sql);
    if (!$result) {
        die("Query failed: " . $conn->error);
    }
}

// ปิดการเชื่อมต่อฐานข้อมูล
$conn->close();
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>ค้นหาข้อมูลลูกค้า</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</head>

<body>
    <div class="container">
        <h2>ค้นหาข้อมูลลูกค้า</h2>

        <form class="form-inline" method="get" action="search.php">
            <div class="form-group">
                <input type="text" class="form-control" name="search" placeholder="ค้นหาชื่อธุรกิจ หรือ ชื่อผู้ติดต่อ" value="<?= htmlspecialchars($search_query) ?>">
            </div>
            <button type="submit" class="btn btn-primary">ค้นหา</button>
        </form>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>ประเภทธุรกิจ</th>
                    <th>ชื่อธุรกิจ</th>
                    <th>ชื่อบริษัท</th>
                    <th>ชื่อผู้ติดต่อ</th>
                    <th>Email</th>
                    <th>โทรศัพท์</th>
                    <th>Line ID</th>
                    <th>Facebook</th>
                    <th>จังหวัด</th>
                    <th>อำเภอ</th>
                    <th>ตำบล</th>
                    <th>รหัสไปรษณีย์</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0) : ?>
                    <?php while ($row = $result->fetch_assoc()) : ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= $row['business_type'] ?></td>
                            <td><?= $row['business_name'] ?></td>
                            <td><?= $row['company_name'] ?></td>
                            <td><?= $row['contact_name'] ?></td>
                            <td><?= $row['email'] ?></td>
                            <td><?= $row['phone'] ?></td>
                            <td><?= $row['line_id'] ?></td>
                            <td><?= $row['facebook'] ?></td>
                            <td><?= $row['province_name'] ?></td>
                            <td><?= $row['amphure_name'] ?></td>
                            <td><?= $row['district_name'] ?></td>
                            <td><?= $row['zip_code'] ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="13" class="text-center">ไม่พบข้อมูล</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>

</html>
