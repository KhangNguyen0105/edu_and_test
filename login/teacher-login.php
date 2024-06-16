<?php

session_start();

if ($_SERVER['REQUEST_METHOD'] == "POST") {
  $conn = mysqli_connect("localhost", "root", "", "edu_and_test");
  if (!$conn)
    die("Kết nối không thành công" . mysqli_connect_error());

  mysqli_set_charset($conn, "utf8mb4");
  $username = $_POST['username'];
  $input_password = $_POST['password'];

  // Kiểm tra xem thông tin đăng nhập có tồn tại trong cơ sở dữ liệu không
  $query = "SELECT * FROM users WHERE username = '$username' AND role = 1";
  $result = mysqli_query($conn, $query);

  $invalidLogin = false;
  if (mysqli_num_rows($result) == 1) {
    $row = mysqli_fetch_assoc($result);
    $password = $row['password']; // Lấy mật khẩu từ cơ sở dữ liệu
    if ($input_password == $password) {
      $_SESSION['user_id'] = $row['user_id'];
      $_SESSION['username'] = $row['username'];
      $_SESSION['full_name'] = $row['full_name'];
      $_SESSION['role'] = '1';

      // Nếu thông tin hợp lệ, chuyển hướng đến trang class.php
      header("Location: ../class/");
      exit();
    }
  }

  // Nếu thông tin không hợp lệ, đặt biến flag là true
  $invalidLogin = true;
  
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
  <title>Giáo viên đăng nhập</title>
  <style>
    .role .current-role {
      border: 1px solid rgb(173, 173, 173);
      padding: 12px 24px;
      border-radius: 8px;
    }

    .role .current-role:hover {
      border: 1px solid rgb(92, 187, 92);
      cursor: pointer;
    }

    /* Ẩn dropdown menu mặc định */
    .drop-down ul {
      list-style-type: none;
      display: none;
      padding: 0;
      margin: 0;
      position: absolute;
      background-color: #fff;
      border: 1px solid rgb(173, 173, 173);
      border-radius: 8px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
      z-index: 1;
    }

    /* Hiển thị dropdown menu khi rê chuột vào .current-role */
    .role:hover .drop-down ul {
      display: block;
    }

    .drop-down ul li {
      width: 100%;
      padding: 12px 31.5px;
      border-bottom: 1px solid rgb(173, 173, 173);
    }

    .drop-down ul li:hover {
      background-color: #f0f0f0;
    }
    
    .drop-down ul li:last-child {
      border-bottom: none;
    }

    a {
      text-decoration: none;
      color: black;
    }

    .input {
      margin-top: 20px;
    }

    .input input {
      width: 400px;
      padding: 15px;
      border: 1px solid #ccc;
      border-radius: 8px;
      background-color: #f7f7f7;
      margin-bottom: 10px;
    }

    .input input:hover {
      border-color: black;
    }

    .input input:focus {
      outline: none;
      border-color: #6bb8a0;
    }
  </style>
</head>
<body>
  <div id="login-wrapper">
    <div class="header">
    <a href="../" class="logo" style="text-decoration: none; font-family: Lobster, sans-serif">
      Edu & Test
    </a>

      <div class="role">
        <div class="current-role">
          Tôi là giáo viên
          <i class="fa-solid fa-caret-down"></i>
        </div>
        <div class="drop-down">
          <ul>
            <li><a href="#">Tôi là giáo viên</a></li>
            <li><a href="student-login.php">Tôi là học sinh</a></li>
          </ul>
        </div>
      </div>
    </div>

    <form action="" method="post">
      <div class="content">
        <div>
          <h2 style="margin: 0;">Giáo viên đăng nhập</h2>
        </div>
        <div class="input">
          <div class="username"><input type="email" name="username" placeholder="Email"></div>
          <div class="password"><input type="password" name="password" placeholder="Mật khẩu"></div>
        </div>
        <div class="forgot">
          <a href="forgot-password.php">Quên mật khẩu?</a>
        </div>

        <?php if ($_SERVER['REQUEST_METHOD'] == "POST" && $invalidLogin): ?>
          <div class="error-message" style="margin: 48px 0 10px 0; color: red;">Thông tin đăng nhập không chính xác</div>
        <?php endif; ?>


        <div class="continue">
          <button type="submit">Tiếp tục</button>
        </div>
        <div class="signup">
          Chưa có tài khoản? 
          <a href="../signup" style="color: rgb(92, 187, 92);">Đăng ký ngay</a>
        </div>
      </div>
    </form>
  </div>
</body>
</html>
