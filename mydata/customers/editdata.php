<?php
session_start();
require_once '../config/dbconnect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

// ตรวจสอบว่าได้รับ customer_id หรือไม่
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$customer_id = intval($_GET['id']);

// Query เพื่อดึงข้อมูลลูกค้าที่ต้องการแก้ไข
$sql_customer = "SELECT c.id, c.business_type, c.business_name, c.company_name, c.contact_name, c.email, c.phone, c.line_id, c.facebook, c.province, c.amphure, c.district, c.zip_code, p.name_th AS province_name, a.name_th AS amphure_name, d.name_th AS district_name
FROM customers_data c
JOIN provinces p ON c.province = p.id
JOIN amphures a ON c.amphure = a.id
JOIN districts d ON c.district = d.id
WHERE c.id = ?";
$stmt_customer = $conn->prepare($sql_customer);
$stmt_customer->bind_param("i", $customer_id);
$stmt_customer->execute();
$result_customer = $stmt_customer->get_result();

if ($result_customer->num_rows === 0) {
    header("Location: index.php");
    exit();
}

$customer = $result_customer->fetch_assoc();

// ดึงข้อมูลจังหวัด
$sql_provinces = "SELECT * FROM provinces";
$query_provinces = $conn->query($sql_provinces);

// ดึงข้อมูลอำเภอ
$sql_amphures = "SELECT * FROM amphures";
$query_amphures = $conn->query($sql_amphures);

// ดึงข้อมูลตำบล
$sql_districts = "SELECT * FROM districts";
$query_districts = $conn->query($sql_districts);

// ปิดการเชื่อมต่อฐานข้อมูล
$conn->close();
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>แก้ไขข้อมูลลูกค้า</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <script src="../thai_area/ajax-functions.js"></script>
    <style>
        .form-section {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            font-weight: bold;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-default">
        <div class="container-fluid">
            <div class="navbar-header">
                <a class="navbar-brand" href="../index.php">Home</a>
            </div>
            <div class="collapse navbar-collapse">
                <ul class="nav navbar-nav navbar-right">
                    <li><a href="../profile/edit_profile.php">แก้ไขโปรไฟล์</a></li>
                    <li><a href="../login/logout.php">ออกจากระบบ</a></li>
                </ul>
            </div><!-- /.navbar-collapse -->
        </div><!-- /.container-fluid -->
    </nav>

    <div class="container-fluid">
        <h2>แก้ไขข้อมูลลูกค้า</h2>
        <form id="edit_form" method="post" action="update_data.php">
            <input type="hidden" name="id" value="<?= htmlspecialchars($customer['id']) ?>">
            <div class="form-section">
                <div class="form-group">
                    <label for="business_type">ประเภทธุรกิจ</label>
                    <input type="text" class="form-control" id="business_type" name="business_type" value="<?= htmlspecialchars($customer['business_type']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="business_name">ชื่อธุรกิจ</label>
                    <input type="text" class="form-control" id="business_name" name="business_name" value="<?= htmlspecialchars($customer['business_name']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="company_name">ชื่อบริษัท</label>
                    <input type="text" class="form-control" id="company_name" name="company_name" value="<?= htmlspecialchars($customer['company_name']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="contact_name">ชื่อผู้ติดต่อ</label>
                    <input type="text" class="form-control" id="contact_name" name="contact_name" value="<?= htmlspecialchars($customer['contact_name']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($customer['email']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="phone">โทรศัพท์</label>
                    <input type="text" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($customer['phone']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="line_id">Line ID</label>
                    <input type="text" class="form-control" id="line_id" name="line_id" value="<?= htmlspecialchars($customer['line_id']) ?>">
                </div>
                <div class="form-group">
                    <label for="facebook">Facebook</label>
                    <input type="text" class="form-control" id="facebook" name="facebook" value="<?= htmlspecialchars($customer['facebook']) ?>">
                </div>
            </div>
            <div class="form-section">
                <div class="form-group">
                    <label for="province">จังหวัด</label>
                    <select class="form-control" id="province" name="province" required>
                        <option value="">เลือกจังหวัด</option>
                        <?php while ($province = $query_provinces->fetch_assoc()) : ?>
                            <option value="<?= htmlspecialchars($province['id']) ?>" <?= $customer['province'] == $province['id'] ? 'selected' : '' ?>><?= htmlspecialchars($province['name_th']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="amphure">อำเภอ</label>
                    <select class="form-control" id="amphure" name="amphure" required>
                        <option value="">เลือกอำเภอ</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="district">ตำบล</label>
                    <select class="form-control" id="district" name="district" required>
                        <option value="">เลือกตำบล</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="zip_code">รหัสไปรษณีย์</label>
                    <input type="text" class="form-control" id="zip_code" name="zip_code" value="<?= htmlspecialchars($customer['zip_code']) ?>" readonly>
                </div>
            </div>
            <button type="submit" class="btn btn-success">บันทึกการเปลี่ยนแปลง</button>
        </form>
    </div>

    <script>
        $(document).ready(function () {
            // เติมข้อมูลอำเภอและตำบลเมื่อเลือกจังหวัด
            $('#province').change(function () {
                let provinceId = $(this).val();
                console.log("Province ID:", provinceId); // ตรวจสอบว่า provinceId ถูกส่งไปถูกต้อง
                $.ajax({
                    url: '../thai_area/get_amphures.php',
                    type: 'GET',
                    data: { province_id: provinceId },
                    success: function (data) {
                        console.log("Amphures Data:", data); // ตรวจสอบข้อมูลที่ส่งกลับจาก get_amphures.php
                        $('#amphure').html(data);
                    },
                    error: function (xhr, status, error) {
                        console.error("AJAX Error:", status, error);
                    }
                });
            });

            $('#amphure').change(function () {
                let amphureId = $(this).val();
                console.log("Amphure ID:", amphureId); // ตรวจสอบว่า amphureId ถูกส่งไปถูกต้อง
                $.ajax({
                    url: '../thai_area/get_districts.php',
                    type: 'GET',
                    data: { amphure_id: amphureId },
                    success: function (data) {
                        console.log("Districts Data:", data); // ตรวจสอบข้อมูลที่ส่งกลับจาก get_districts.php
                        $('#district').html(data);
                    },
                    error: function (xhr, status, error) {
                        console.error("AJAX Error:", status, error);
                    }
                });
            });

            $('#district').change(function () {
                let districtId = $(this).val();
                console.log("District ID:", districtId); // ตรวจสอบว่า districtId ถูกส่งไปถูกต้อง
                $.ajax({
                    url: '../thai_area/get_zip_code.php',
                    type: 'GET',
                    data: { district_id: districtId },
                    success: function (data) {
                        console.log("Zip Code Data:", data); // ตรวจสอบข้อมูลที่ส่งกลับจาก get_zip_code.php
                        $('#zip_code').val(data);
                    },
                    error: function (xhr, status, error) {
                        console.error("AJAX Error:", status, error);
                    }
                });
            });

            // กำหนดค่าอำเภอและตำบลที่เคยเลือกไว้
            $('#amphure').val("<?= htmlspecialchars($customer['amphure']) ?>").trigger('change');
            $('#district').val("<?= htmlspecialchars($customer['district']) ?>").trigger('change');
        });
    </script>
</body>

</html>
