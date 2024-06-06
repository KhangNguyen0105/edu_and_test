<?php
  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $role = $_POST['role'];

    if ($role == '1') {
      header("Location: teacher-login.php");
      exit();
    } else if ($role == '2') {
      header("Location: student-login.php");
      exit();
    }
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
  <title>Đăng nhập vào tài khoản của bạn</title>
</head>
<body>
  <div id="login-wrapper">
    <div class="header">
    <a href="../" class="logo" style="text-decoration: none; font-family: Lobster, sans-serif">
      Edu & Test
    </a>

      <div class="home-signup">
        <div class="home btn">
          <a href="../">Trang chủ</a>
        </div>
        <div class="signup btn">
          <a href="../signup/">Đăng ký</a>
        </div>
      </div>
    </div>

    <form action="" method="post">
      <div class="content">
        <div>
          <h2>Chào mừng bạn đến với Edu & Test</h2>
        </div>
        <div>
          <p>Chọn vai trò của bạn</p>
        </div>
        <div>
          <select name="role">
            <option value="1" selected>Tôi là giáo viên</option>
            <option value="2">Tôi là học sinh</option>
          </select>
        </div>
        <div class="continue">
          <button type="submit">Tiếp tục</button>
        </div>
      </div>
    </form>
  </div>
</body>
</html>