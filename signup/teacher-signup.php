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
        $insert_query = "INSERT INTO users (username, password, full_name, email, role) VALUES ('$email', '$password', '$full_name', '$email', 1)";
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
        <a href="index.php">
          <i class="fa-solid fa-arrow-left"></i>
          Thay đổi vai trò
        </a>
        <h3>Bạn đang đăng ký với tư cách là giáo viên</h3>
        <div class="info">
          <p>Thông tin cá nhân</p>
          <div class="input-box"><input type="text" name="full-name" placeholder="Họ tên" required></div>
          <div class="input-box"><input type="text" name="school" placeholder="Trường" required></div>
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

  <?php if (isset($registrationSuccess) && $registrationSuccess): ?>
      <script>
        showSuccessMessage();
      </script>
    <?php endif; ?>
</body>
</html>

