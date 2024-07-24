<?php
session_start();
require_once 'config/dbconnect.php';  // ตรวจสอบให้แน่ใจว่าเส้นทางนี้ถูกต้อง

// ตรวจสอบว่ามี session user_id หรือไม่
if (!isset($_SESSION['user_id'])) {
    header("Location: login/login.php");
    exit();
}

// Query เพื่อดึงข้อมูลผู้ใช้จากฐานข้อมูล
$user_id = $_SESSION['user_id'];
$sql = "SELECT username, profile_image FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $username = $row['username'];
    $profile_image = !empty($row['profile_image']) ? $row['profile_image'] : "path/to/default/profile/image"; // กำหนดรูปโปรไฟล์เริ่มต้น (ถ้าต้องการ)
} else {
    $username = "Guest"; // หากไม่พบข้อมูลผู้ใช้
    $profile_image = "path/to/default/profile/image"; // กำหนดรูปโปรไฟล์เริ่มต้น (ถ้าต้องการ)
}

// รับค่าค้นหาจากฟอร์ม
$search_query = isset($_GET['search']) ? $_GET['search'] : '';

// เตรียมคำค้นหา
$search_query = "%" . $search_query . "%"; // ใช้ % เพื่อค้นหาทั้งคำที่มี

// Query เพื่อดึงข้อมูลลูกค้าตามคำค้นหา
$sql_customers = "SELECT c.id, c.business_type, c.business_name, c.company_name, c.contact_name, c.email, c.phone, c.line_id, c.facebook, p.name_th AS province_name, a.name_th AS amphure_name, d.name_th AS district_name, d.zip_code 
FROM customers_data c
JOIN provinces p ON c.province = p.id
JOIN amphures a ON c.amphure = a.id
JOIN districts d ON c.district = d.id
WHERE c.business_name LIKE ? OR c.contact_name LIKE ?";

$stmt_customers = $conn->prepare($sql_customers);
$stmt_customers->bind_param("ss", $search_query, $search_query);
$stmt_customers->execute();
$result_customers = $stmt_customers->get_result();

// ดึงข้อมูลจังหวัด
$sql_provinces = "SELECT * FROM provinces";
$query_provinces = $conn->query($sql_provinces);

// ปิดการเชื่อมต่อฐานข้อมูล
$conn->close();
?>

<!DOCTYPE html>
<html lang="th">

<head>
  <meta charset="UTF-8">
  <title>จัดการข้อมูลลูกค้า</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
  <script src="thai_area/ajax-functions.js"></script>

  <!-- CSS สำหรับการจัดระเบียบกรอบ -->
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

    .profile-img {
      width: 35px;
      height: 35px;
      border-radius: 50%;
    }
  </style>
</head>

<body>
  <nav class="navbar navbar-default">
    <div class="container-fluid">
      <div class="navbar-header">
        <a class="navbar-brand" href="#">Home</a>
      </div>
      <div class="collapse navbar-collapse">
        <ul class="nav navbar-nav navbar-right">
          <li>
            <form class="navbar-form" action="index.php" method="get">
              <div class="form-group">
                <input type="text" class="form-control" placeholder="ค้นหา" name="search" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
              </div>
              <button type="submit" class="btn btn-default">ค้นหา</button>
            </form>
          </li>
          <li><button type="button" class="btn btn-primary navbar-btn" data-toggle="modal" data-target="#insertModal">เพิ่มข้อมูล</button></li>
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <?php if (!empty($profile_image)) : ?>
                <img src="<?= $profile_image ?>" class="profile-img" alt="Profile Image">
              <?php else : ?>
                โปรไฟล์
              <?php endif; ?>
              <span class="caret"></span>
            </a>
            <ul class="dropdown-menu">
              <li class="dropdown-header">
                <div>
                  <?php if (!empty($profile_image)) : ?>
                    <img src="<?= $profile_image ?>" class="profile-img" alt="Profile Image">
                  <?php else : ?>
                    โปรไฟล์
                  <?php endif; ?>
                  <?= htmlspecialchars($username) ?>
                </div>
              </li>
              <li role="separator" class="divider"></li>
              <li><a href="profile/edit_profile.php">แก้ไขโปรไฟล์</a></li>
              <li><a href="login/logout.php">ออกจากระบบ</a></li>
            </ul>
          </li>
        </ul>
      </div><!-- /.navbar-collapse -->
    </div><!-- /.container-fluid -->
  </nav>

  <div class="container-fluid">
    <h2>จัดการข้อมูลลูกค้า</h2>
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
          <th>การจัดการ</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result_customers->num_rows > 0) : ?>
          <?php while ($row = $result_customers->fetch_assoc()) : ?>
            <tr>
              <td><?= htmlspecialchars($row['id']) ?></td>
              <td><?= htmlspecialchars($row['business_type']) ?></td>
              <td><?= htmlspecialchars($row['business_name']) ?></td>
              <td><?= htmlspecialchars($row['company_name']) ?></td>
              <td><?= htmlspecialchars($row['contact_name']) ?></td>
              <td><?= htmlspecialchars($row['email']) ?></td>
              <td><?= htmlspecialchars($row['phone']) ?></td>
              <td><?= htmlspecialchars($row['line_id']) ?></td>
              <td><?= htmlspecialchars($row['facebook']) ?></td>
              <td><?= htmlspecialchars($row['province_name']) ?></td>
              <td><?= htmlspecialchars($row['amphure_name']) ?></td>
              <td><?= htmlspecialchars($row['district_name']) ?></td>
              <td><?= htmlspecialchars($row['zip_code']) ?></td>
              <td>
                <a href="customers/editdata.php?id=<?= htmlspecialchars($row['id']) ?>" class="btn btn-warning btn-xs">แก้ไข</a>
                <a href="customers/deletedata.php?id=<?= htmlspecialchars($row['id']) ?>" class="btn btn-danger btn-xs" onclick="return confirm('คุณต้องการลบข้อมูลนี้หรือไม่?')">ลบ</a>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else : ?>
          <tr>
            <td colspan="14" class="text-center">ไม่พบข้อมูล</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Modal สำหรับเพิ่มข้อมูล -->
  <div class="modal fade" id="insertModal" tabindex="-1" role="dialog" aria-labelledby="insertModalLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="insertModalLabel">เพิ่มข้อมูลลูกค้า</h4>
        </div>
        <div class="modal-body">
          <form id="insert_form" method="post" action="customers/insertdata.php">
            <div class="form-section">
              <div class="form-group">
                <label for="business_type">ประเภทธุรกิจ</label>
                <input type="text" class="form-control" id="business_type" name="business_type" required>
              </div>
              <div class="form-group">
                <label for="business_name">ชื่อธุรกิจ</label>
                <input type="text" class="form-control" id="business_name" name="business_name" required>
              </div>
              <div class="form-group">
                <label for="company_name">ชื่อบริษัท</label>
                <input type="text" class="form-control" id="company_name" name="company_name" required>
              </div>
              <div class="form-group">
                <label for="contact_name">ชื่อผู้ติดต่อ</label>
                <input type="text" class="form-control" id="contact_name" name="contact_name" required>
              </div>
              <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
              </div>
              <div class="form-group">
                <label for="phone">โทรศัพท์</label>
                <input type="text" class="form-control" id="phone" name="phone" required>
              </div>
              <div class="form-group">
                <label for="line_id">Line ID</label>
                <input type="text" class="form-control" id="line_id" name="line_id">
              </div>
              <div class="form-group">
                <label for="facebook">Facebook</label>
                <input type="text" class="form-control" id="facebook" name="facebook">
              </div>
            </div>
            <div class="form-section">
              <div class="form-group">
                <label for="province">จังหวัด</label>
                <select class="form-control" id="province" name="province" required>
                  <option value="">เลือกจังหวัด</option>
                  <?php while ($province = $query_provinces->fetch_assoc()) : ?>
                    <option value="<?= htmlspecialchars($province['id']) ?>"><?= htmlspecialchars($province['name_th']) ?></option>
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
                <input type="text" class="form-control" id="zip_code" name="zip_code" readonly>
            </div>
          </div>
            <button type="submit" class="btn btn-success">บันทึก</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script>
  $(document).ready(function () {
    $('#province').change(function () {
        let provinceId = $(this).val();
        console.log("Selected Province ID:", provinceId); // ตรวจสอบค่า provinceId
        $.ajax({
            url: 'thai_area/get_amphures.php', // ตรวจสอบเส้นทาง
            type: 'GET',
            data: { province_id: provinceId },
            success: function (data) {
                console.log("Amphures Data:", data); // ตรวจสอบข้อมูลที่ได้รับ
                $('#amphure').html(data);
                $('#district').html('<option value="">เลือกตำบล</option>'); // เคลียร์ตำบล
                $('#zip_code').val(''); // เคลียร์รหัสไปรษณีย์
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error:", status, error);
            }
        });
    });

    $('#amphure').change(function () {
        let amphureId = $(this).val();
        console.log("Selected Amphure ID:", amphureId); // ตรวจสอบค่า amphureId
        $.ajax({
            url: 'thai_area/get_districts.php', // ตรวจสอบเส้นทาง
            type: 'GET',
            data: { amphure_id: amphureId },
            success: function (data) {
                console.log("Districts Data:", data); // ตรวจสอบข้อมูลที่ได้รับ
                $('#district').html(data);
                $('#zip_code').val(''); // เคลียร์รหัสไปรษณีย์
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error:", status, error);
            }
        });
    });

    $('#district').change(function () {
        let districtId = $(this).val();
        console.log("Selected District ID:", districtId); // ตรวจสอบค่า districtId
        $.ajax({
            url: 'thai_area/get_zip_code.php', // ตรวจสอบเส้นทาง
            type: 'GET',
            data: { district_id: districtId },
            success: function (data) {
                console.log("Zip Code Data:", data); // ตรวจสอบข้อมูลที่ได้รับ
                $('#zip_code').val(data);
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error:", status, error);
            }
        });
    });
});
</script>
</body>

</html>
