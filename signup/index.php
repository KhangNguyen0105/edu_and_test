<?php
  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $role = $_POST['role'];

    if ($role == '1') {
      header("Location: teacher-signup.php");
      exit();
    } else if ($role == '2') {
      header("Location: student-signup.php");
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
  <title>Đăng ký tài khoản mới</title>
  <style>
    .login {
      margin-right: 50px;
      padding: 8px 30px;
      border: 1px solid gainsboro;
      border-radius: 4px;
      cursor: pointer;
    }

    .login:hover {
      background-color: rgba(30, 229, 136, 0.04);
    }

    .login a {
      text-decoration: none;
      color: rgb(83, 167, 83);
      font-weight: bold;
    }
  </style>
</head>
<body>
  <div id="login-wrapper">
    <div class="header">
      <a href="../" class="logo" style="text-decoration: none; font-family: Lobster, sans-serif">
        Edu & Test
      </a>

      <div class="login">
        <div class="btn">
          <a href="../login">Đăng nhập</a>
        </div>
      </div>
    </div>

    <form action="" method="post">
      <div class="content">
        <div>
          <h2>Đăng ký tài khoản mới</h2>
        </div>
        <div>
          <p>Chọn vai trò để tiếp tục</p>
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