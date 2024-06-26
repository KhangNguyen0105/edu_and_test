<?php
  session_start();

  if (isset($_SESSION['user_id'])) {
    $conn = mysqli_connect("localhost", "root", "", "edu_and_test");
    if (!$conn) die("Kết nối không thành công: " . mysqli_connect_error());

    $user_id = $_SESSION['user_id'];
    $query = "SELECT * FROM users WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    $username = $user['username'];
    $email = $user['email'];
    $password = $user['password'];
    $full_name = $user['full_name'];

    // Xử lý chỉnh sửa email
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirm-edit-email'])) {
      $new_email = $_POST['new-email'];
      $update_email_query = "UPDATE users SET email = ? WHERE user_id = ?";
      $update_email_stmt = mysqli_prepare($conn, $update_email_query);
      mysqli_stmt_bind_param($update_email_stmt, "ss", $new_email, $user_id);

      if (mysqli_stmt_execute($update_email_stmt)) {
        $_SESSION['email'] = $new_email; // Cập nhật email mới trong session
        header("Location: profile.php"); // Reload trang
      }
      mysqli_stmt_close($update_email_stmt);
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirm-change-password'])) {
      $current_password = $_POST['current-password'];
      $new_password = $_POST['new-password'];
      $confirm_password = $_POST['confirm-password'];

      if ($current_password == "" || $new_password == "" || $confirm_password == "")
        echo '<script>alert("Vui lòng điền đầy đủ thông tin!")</script>'; 
      else if ($current_password != $password)
        echo '<script>alert("Mật khẩu sai!")</script>';
      else if ($new_password != $confirm_password)
        echo '<script>alert("Mật khẩu nhập lại không khớp!")</script>';
      else {
        $change_password_query = "UPDATE users SET password = ? WHERE user_id = ?";
        $change_password_stmt = mysqli_prepare($conn, $change_password_query);
        mysqli_stmt_bind_param($change_password_stmt, "ss", $new_password, $user_id);

        if (mysqli_stmt_execute($change_password_stmt))
          header("Location: profile.php"); // Reload trang
        
        mysqli_stmt_close($change_password_stmt);
      }
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirm-edit-fullname'])) {
      $new_fullname = $_POST['fullname'];
      $edit_fullname_query = "UPDATE users SET full_name = ? WHERE user_id = ?";
      $edit_fullname_stmt = mysqli_prepare($conn, $edit_fullname_query);
      mysqli_stmt_bind_param($edit_fullname_stmt, "ss", $new_fullname, $user_id);
      
      if (mysqli_stmt_execute($edit_fullname_stmt)) {
        $_SESSION['full_name'] = $new_fullname;
        header("Location: profile.php"); // Reload trang
      }
        
      mysqli_stmt_close($edit_fullname_stmt);
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
  <link rel="stylesheet" href="asset/css/class-style.css">
  <link rel="stylesheet" href="asset/icon/fontawesome-free-6.5.1-web/css/all.min.css">
  <title>Edu & Test</title>
</head>
<body style="background-color: rgb(240, 242, 245);">
  <div class="position-fixed" style="padding-bottom: 0;">
    <div class="header">
      <a href="index.php" class="logo" style="text-decoration: none; font-family: Lobster, sans-serif">
        Edu & Test
      </a>

      <div class="nav">
        <a href="class/" class="current">Lớp học</a>
      </div>

      <div class="current-user">
        <div class="full-name">
          <p>
            <?php
              if (isset($_SESSION['user_id'])) {
                $full_name = $_SESSION['full_name'];
                echo "$full_name";
              }
            ?>
            <i class="fa-solid fa-caret-down"></i>
          </p>
        </div>
        <div class="drop-down">
          <ul>
            <li>
              <a href="">
                <i class="fa-solid fa-user"></i>
                Thông tin cá nhân
              </a>
            </li>
            <li>
              <a href="logout.php">
                <i class="fa-solid fa-left-long"></i>
                Đăng xuất
              </a>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <div id="profile-wrapper">
    <div class="content">
      <div class="title">
        <p>Hồ sơ của tôi</p>
      </div>
      <div class="item">
        <div class="sub-title">
          <i class="fa-solid fa-address-card"></i> <p>Tên đăng nhập</p>
        </div>
        <?php echo '<p class="data">' . $username . '</p>'; ?>
        <button style="color: #fff; cursor: auto;">Chỉnh sửa</button>
      </div>

      <div class="item">
        <div class="sub-title">
          <i class="fa-solid fa-envelope"></i> <p>Email</p>
        </div>
        <?php echo '<p class="data">' . $email . '</p>'; ?>
        <button id="edit-email-btn">Chỉnh sửa</button>
      </div>

      <div class="item">
        <div class="sub-title">
          <i class="fa-solid fa-lock"></i> <p>Mật khẩu</p>
        </div>
        <?php echo '<p class="data">' . $password . '</p>'; ?>
        <button id="change-password-btn">Chỉnh sửa</button>
      </div>

      <div class="item">
        <div class="sub-title">
          <i class="fa-solid fa-user"></i> <p>Họ và tên</p>
        </div>
        <?php echo '<p class="data">' . $full_name . '</p>'; ?>
        <button id="edit-fullname-btn">Chỉnh sửa</button>
      </div>

    </div>
  </div>

  <!-- Change Email Modal -->
  <form action="" method="post" class="form-modal" id="edit-email-modal" >
    <div class="modal">
      <div class="title">
        Chỉnh sửa email
        <i class="fa-solid fa-xmark" id="close-modal"></i>
      </div>
      <div class="edit-content">
        <?php echo '<input type="email" name="new-email" value="' . $email . '">' ?>
      </div>
      <div class="confirm">
        <button type="submit" name="confirm-edit-email">Chỉnh sửa</button>
      </div>
    </div>
  </form>
  
  <!-- Change Password Modal -->
  <form action="" method="post" class="form-modal" id="change-password-modal" >
    <div class="modal">
      <div class="title">
        Chỉnh sửa mật khẩu
        <i class="fa-solid fa-xmark" id="close-change-password-modal"></i>
      </div>
      <div class="edit-content">
        <input type="text" name="current-password" id="current-password" placeholder="Mật khẩu hiện tại">
        <input type="text" name="new-password" id="new-password" placeholder="Mật khẩu mới">
        <input type="text" name="confirm-password" id="confirm-password" placeholder="Nhập lại mật khẩu mới">
      </div>
      <div class="confirm">
        <button type="submit" name="confirm-change-password">Chỉnh sửa</button>
      </div>
    </div>
  </form>

  <!-- Edit Fullname Modal -->
  <form action="" method="post" class="form-modal" id="edit-fullname-modal" >
    <div class="modal">
      <div class="title">
        Chỉnh sửa Họ tên
        <i class="fa-solid fa-xmark" id="close-edit-fullname-modal"></i>
      </div>
      <div class="edit-content">
        <?php echo '<input type="text" name="fullname" id="confirm-password" value="' . $full_name . '">' ?>
      </div>
      <div class="confirm">
        <button type="submit" name="confirm-edit-fullname">Chỉnh sửa</button>
      </div>
    </div>
  </form>

  <script src="asset/script/script.js"></script>
</body>
</html>