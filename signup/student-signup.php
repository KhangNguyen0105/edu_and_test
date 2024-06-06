<?php
// Kiểm tra xem có dữ liệu được gửi từ form hay không
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Kết nối đến cơ sở dữ liệu
    $conn = mysqli_connect("localhost", "root", "", "edu_and_test");

    // Kiểm tra kết nối
    if (!$conn)
        die("Kết nối không thành công: " . mysqli_connect_error());

    // Lấy dữ liệu từ form
    $full_name = $_POST['full-name'];
    $school = $_POST['school'];
    $dob = $_POST['dob'];
    $address = $_POST['address'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm-password'];

    $is_email_exist = false;
    $check_confirm_password = true;
    $registrationSuccess = false;

    // Kiểm tra email đã tồn tại
    $email_check_query = "SELECT * FROM users WHERE email = '$email'";
    $email_check_result = mysqli_query($conn, $email_check_query);
    if (mysqli_num_rows($email_check_result) > 0) {
      $is_email_exist = true;
    }

    // Kiểm tra mật khẩu trùng khớp
    if ($password != $confirm_password) {
      $check_confirm_password = false;
    } else {
      // Chỉ thêm người dùng mới vào cơ sở dữ liệu nếu không có lỗi về email hoặc mật khẩu
      if (!$is_email_exist && $check_confirm_password) {
        // Thêm người dùng mới vào cơ sở dữ liệu
        $hashed_password = password_hash($password, PASSWORD_DEFAULT); // Mã hoá mật khẩu
        $insert_query = "INSERT INTO users (username, password, full_name, email, role) VALUES ('$email', '$hashed_password', '$full_name', '$email', 2)";
        if (mysqli_query($conn, $insert_query)) {
          $registrationSuccess = true;
        } else {
          
        }
      }
    }

    mysqli_close($conn);
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Lobster&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../asset/css/style.css">
  <link rel="stylesheet" href="../asset/icon/fontawesome-free-6.5.1-web/css/all.min.css">
  <title>Đăng ký tài khoản mới</title>
  <style>
    .content {
      align-items: flex-start;
      width: 500px;
      margin: 200px auto 0 auto;
      height: 100%;
    }

    a {
      text-decoration: none;
      color: rgb(92, 187, 92) !important;
      margin-bottom: 16px;
    }

    p {
      color: black !important;
      font-weight: bold;
      margin-top: 16px;
    }

    h3 {
      font-size: x-large;
    }

    .input-box input,
    .input-wrapper input,
    .input-wrapper select {
      width: 500px;
      padding: 15px;
      border: 1px solid #ccc;
      border-radius: 8px;
      background-color: #f7f7f7;
      margin-top: 10px;
    }

    .input-box input:hover,
    .input-wrapper input:hover,
    .input-wrapper select:hover {
      border-color: black;
    }

    .dob-address {
      display: flex;
      justify-content: space-between;
    }

    .input-wrapper {
      flex-grow: 1;
      margin-right: 5px;
    }

    .input-wrapper input,
    .input-wrapper select {
      width: 100% !important;
    }

    .input-wrapper:last-child {
      margin-right: 0;
    }

    .login {
      text-align: center;
      width: 100%;
    }

    .btn button {
      width: 500px;
      margin-top: 10px;
      margin-bottom: 0;
    }

    .login p,
    .login a {
      font-weight: normal;
    }

    .error-message {
      width: 100%;
      padding-top: 10px;
      text-align: center;
      color: red;
    }
  </style>
</head>
<body>
  <div id="login-wrapper">
    <div class="header" style="background-color: white;">
      <a href="../" class="logo" style="text-decoration: none; font-family: Lobster, sans-serif">
        Edu & Test
      </a>
    </div>

    <form action="" method="post">
      <div class="content">
        <a href="">
          <i class="fa-solid fa-arrow-left"></i>
          Thay đổi vai trò
        </a>
        <h3>Bạn đang đăng ký với tư cách là học sinh</h3>
        <div class="info">
          <p>Thông tin cá nhân</p>
          <div class="input-box"><input type="text" name="full-name" placeholder="Họ tên" required></div>
          <div class="input-box"><input type="text" name="school" placeholder="Trường" required></div>
          <div class="dob-address">
            <div class="input-wrapper">
              <input type="text" id="dob" name="dob" placeholder="Ngày sinh" required>
            </div>
            
            <div class="input-wrapper">
              <select name="address" required>
                <option value="Tỉnh" selected disabled hidden>Tỉnh</option>
                <option value="An Giang">An Giang</option>
                <option value="Bà Rịa - Vũng Tàu">Bà Rịa - Vũng Tàu</option>
                <option value="Bắc Giang">Bắc Giang</option>
                <option value="Bắc Kạn">Bắc Kạn</option>
                <option value="Bạc Liêu">Bạc Liêu</option>
                <option value="Bắc Ninh">Bắc Ninh</option>
                <option value="Bến Tre">Bến Tre</option>
                <option value="Bình Định">Bình Định</option>
                <option value="Bình Dương">Bình Dương</option>
                <option value="Bình Phước">Bình Phước</option>
                <option value="Bình Thuận">Bình Thuận</option>
                <option value="Cà Mau">Cà Mau</option>
                <option value="Cần Thơ">Cần Thơ</option>
                <option value="Cao Bằng">Cao Bằng</option>
                <option value="Đà Nẵng">Đà Nẵng</option>
                <option value="Đắk Lắk">Đắk Lắk</option>
                <option value="Đắk Nông">Đắk Nông</option>
                <option value="Điện Biên">Điện Biên</option>
                <option value="Đồng Nai">Đồng Nai</option>
                <option value="Đồng Tháp">Đồng Tháp</option>
                <option value="Gia Lai">Gia Lai</option>
                <option value="Hà Giang">Hà Giang</option>
                <option value="Hà Nam">Hà Nam</option>
                <option value="Hà Nội">Hà Nội</option>
                <option value="Hà Tĩnh">Hà Tĩnh</option>
                <option value="Hải Dương">Hải Dương</option>
                <option value="Hải Phòng">Hải Phòng</option>
                <option value="Hậu Giang">Hậu Giang</option>
                <option value="Hòa Bình">Hòa Bình</option>
                <option value="Hồ Chí Minh">Hồ Chí Minh</option>
                <option value="Hưng Yên">Hưng Yên</option>
                <option value="Khánh Hòa">Khánh Hòa</option>
                <option value="Kiên Giang">Kiên Giang</option>
                <option value="Kon Tum">Kon Tum</option>
                <option value="Lai Châu">Lai Châu</option>
                <option value="Lâm Đồng">Lâm Đồng</option>
                <option value="Lạng Sơn">Lạng Sơn</option>
                <option value="Lào Cai">Lào Cai</option>
                <option value="Long An">Long An</option>
                <option value="Nam Định">Nam Định</option>
                <option value="Nghệ An">Nghệ An</option>
                <option value="Ninh Bình">Ninh Bình</option>
                <option value="Ninh Thuận">Ninh Thuận</option>
                <option value="Phú Thọ">Phú Thọ</option>
                <option value="Phú Yên">Phú Yên</option>
                <option value="Quảng Bình">Quảng Bình</option>
                <option value="Quảng Nam">Quảng Nam</option>
                <option value="Quảng Ngãi">Quảng Ngãi</option>
                <option value="Quảng Ninh">Quảng Ninh</option>
                <option value="Quảng Trị">Quảng Trị</option>
                <option value="Sóc Trăng">Sóc Trăng</option>
                <option value="Sơn La">Sơn La</option>
                <option value="Tây Ninh">Tây Ninh</option>
                <option value="Thái Bình">Thái Bình</option>
                <option value="Thái Nguyên">Thái Nguyên</option>
                <option value="Thanh Hóa">Thanh Hóa</option>
                <option value="Thừa Thiên Huế">Thừa Thiên Huế</option>
                <option value="Tiền Giang">Tiền Giang</option>
                <option value="Trà Vinh">Trà Vinh</option>
                <option value="Tuyên Quang">Tuyên Quang</option>
                <option value="Vĩnh Long">Vĩnh Long</option>
                <option value="Vĩnh Phúc">Vĩnh Phúc</option>
                <option value="Yên Bái">Yên Bái</option>
              </select>
            </div>
          </div>
        </div>
        <div class="info">
          <p>Thông tin tài khoản</p>
          <?php if ($_SERVER['REQUEST_METHOD'] == "POST" && $is_email_exist): ?>
            <div class="error-message">Email đã tồn tại</div>
          <?php endif; ?>
          <div class="input-box"><input type="email" name="email" placeholder="Email" required></div>
          
          <div class="input-box"><input type="text" name="password" placeholder="Mật khẩu" required></div>
          <?php if ($_SERVER['REQUEST_METHOD'] == "POST" && !$check_confirm_password): ?>
            <div class="error-message">Mật khẩu không khớp</div>
          <?php endif; ?>
          <div class="input-box"><input type="text" name="confirm-password" placeholder="Xác nhận mật khẩu" required></div>

        </div>

        <div class="btn">
          <button type="submit">Đăng ký</button>

          <?php if ($_SERVER['REQUEST_METHOD'] == "POST" && !$is_email_exist && $check_confirm_password): ?>
            <script>
              // Gọi hàm showSuccessMessage() để hiển thị hộp thoại thông báo
              showSuccessMessage();
            </script>
          <?php endif; ?>

        </div>
        <div class="login">
          <p>Đã có tài khoản? <a href="../login/">Đăng nhập ngay</a></p>
        </div>
      </div>
    </form>
  </div>

  
  <script>
    // Hàm để hiển thị hộp thoại thông báo và chuyển hướng sau khi nhấn nút "OK"
    function showSuccessMessage() {
      // Hiển thị hộp thoại thông báo
      var messageBox = confirm("Đăng ký thành công!");
      
      // Nếu người dùng nhấp vào nút "OK", chuyển hướng đến trang đăng nhập
      if (messageBox) {
        window.location.href = "../login";
      }
    }
  </script>
  <!-- Nếu đăng ký thành công thì hiện hộp thoại thông báo -->
  <?php if (isset($registrationSuccess) && $registrationSuccess): ?>
      <script>
        showSuccessMessage();
      </script>
    <?php endif; ?>
</body>
</html>

