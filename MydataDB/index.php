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

// ดึงข้อมูลลูกค้า
$sql_customers = "SELECT * FROM customers_data";
$result_customers = $conn->query($sql_customers);

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
                <input type="text" class="form-control" placeholder="ค้นหา" name="search">
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
                  <?= $username ?>
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
              <td><?= $row['id'] ?></td>
              <td><?= $row['business_type'] ?></td>
              <td><?= $row['business_name'] ?></td>
              <td><?= $row['company_name'] ?></td>
              <td><?= $row['contact_name'] ?></td>
              <td><?= $row['email'] ?></td>
              <td><?= $row['phone'] ?></td>
              <td><?= $row['line_id'] ?></td>
              <td><?= $row['facebook'] ?></td>
              <td><?= $row['province'] ?></td>
              <td><?= $row['amphure'] ?></td>
              <td><?= $row['district'] ?></td>
              <td><?= $row['zip_code'] ?></td>
              <td>
                <a href="customers/editdata.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-xs">แก้ไข</a>
                <a href="customers/deletedata.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-xs" onclick="return confirm('คุณต้องการลบข้อมูลนี้หรือไม่?')">ลบ</a>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else : ?>
          <tr>
            <td colspan="14">ไม่พบข้อมูล</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Modal สำหรับเพิ่มข้อมูล -->
  <div id="insertModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">เพิ่มข้อมูลลูกค้าใหม่</h4>
        </div>
        <div class="modal-body">
          <form action="customers/insertdata.php" method="post">
            <!-- ส่วนที่ 1: ประเภทธุรกิจ และ ชื่อธุรกิจ -->
            <div class="form-section">
              <div class="form-group">
                <label for="business_type">ประเภทธุรกิจ:</label>
                <input type="text" class="form-control" id="business_type" name="business_type" required>
              </div>
              <div class="form-group">
                <label for="business_name">ชื่อธุรกิจ:</label>
                <input type="text" class="form-control" id="business_name" name="business_name" required>
              </div>
            </div>

            <!-- ส่วนที่ 2: ชื่อบริษัท และ ชื่อผู้ติดต่อ -->
            <div class="form-section">
              <div class="form-group">
                <label for="company_name">ชื่อบริษัท:</label>
                <input type="text" class="form-control" id="company_name" name="company_name" required>
              </div>
              <div class="form-group">
                <label for="contact_name">ชื่อผู้ติดต่อ:</label>
                <input type="text" class="form-control" id="contact_name" name="contact_name" required>
              </div>
            </div>

            <!-- ส่วนที่ 3: Email, โทรศัพท์, Line ID และ Facebook -->
            <div class="form-section">
              <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" class="form-control" id="email" name="email" required>
              </div>
              <div class="form-group">
                <label for="phone">โทรศัพท์:</label>
                <input type="text" class="form-control" id="phone" name="phone" required>
              </div>
              <div class="form-group">
                <label for="line_id">Line ID:</label>
                <input type="text" class="form-control" id="line_id" name="line_id">
              </div>
              <div class="form-group">
                <label for="facebook">Facebook:</label>
                <input type="text" class="form-control" id="facebook" name="facebook">
              </div>
            </div>

            <!-- ส่วนที่ 4: จังหวัด, อำเภอ, ตำบล และ รหัสไปรษณีย์ -->
            <div class="form-section">
              <div class="form-group">
                <label for="province">จังหวัด:</label>
                <select class="form-control" id="provinces" name="province">
                  <option value="" selected disabled>-กรุณาเลือกจังหวัด-</option>
                  <?php while ($province = $query_provinces->fetch_assoc()) { ?>
                    <option value="<?= $province['id'] ?>"><?= $province['name_th'] ?></option>
                  <?php } ?>
                </select>
              </div>
              <div class="form-group">
                <label for="amphure">อำเภอ:</label>
                <select class="form-control" id="amphures" name="amphure">
                </select>
              </div>
              <div class="form-group">
                <label for="district">ตำบล:</label>
                <select class="form-control" id="districts" name="district">
                </select>
              </div>
              <div class="form-group">
                <label for="zip_code">รหัสไปรษณีย์:</label>
                <input type="text" id="zip_code" name="zip_code" class="form-control" readonly>
              </div>
            </div>

            <div class="form-group">
              <button type="submit" class="btn btn-primary">เพิ่มข้อมูล</button>
              <button type="button" class="btn btn-default" data-dismiss="modal">ปิด</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

</body>

</html>