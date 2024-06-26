<?php
  session_start();
  $result = true;

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_SESSION['user_id'])) {
      $conn = mysqli_connect("localhost", "root", "", "edu_and_test");
      if (!$conn) die("Kết nối không thành công: " . mysqli_connect_error());

      // SQL Injection Protection
      $class_name = mysqli_real_escape_string($conn, $_POST['class-name']);
      $class_description = mysqli_real_escape_string($conn, $_POST['description']);
      $user_id = $_SESSION['user_id'];
      $create_date = date("Y-m-d");

      $query = "INSERT INTO courses (course_name, description, teacher_id, date_created) VALUES ('$class_name','$class_description','$user_id','$create_date')";

      if (mysqli_query($conn, $query)) {
        // Truy vấn để lấy course_id vừa được tạo
        $query_get_id = "SELECT course_id FROM courses WHERE teacher_id = '$user_id' AND course_name = '$class_name' ORDER BY date_created DESC LIMIT 1";
        $result_get_id = mysqli_query($conn, $query_get_id);
  
        if ($result_get_id && mysqli_num_rows($result_get_id) > 0) {
          $row = mysqli_fetch_assoc($result_get_id);
          $course_id = $row['course_id'];
  
          echo '<script>
                  window.location.href = "newsfeed.php?course_id=' . $course_id . '";
                </script>';
          exit();
        } else
          echo '<script>alert("Đã có lỗi xảy ra khi lấy course_id.");</script>';
      } else
        $result = false; // Đặt kết quả là false nếu xảy ra lỗi
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
  <link rel="stylesheet" href="../asset/css/class-style.css">
  <link rel="stylesheet" href="../asset/icon/fontawesome-free-6.5.1-web/css/all.min.css">
  <title>Edu & Test</title>
</head>
<body>
  <div class="wrapper">
    <div class="position-fixed">
      <div class="header">
        <a href="../" class="logo" style="text-decoration: none; font-family: Lobster, sans-serif">
          Edu & Test
        </a>

        <div class="nav">
          <a href="index.php" class="current">Lớp học</a>
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
                <a href="../profile.php">
                  <i class="fa-solid fa-user"></i>
                  Thông tin cá nhân
                </a>
              </li>
              <li>
                <a href="../logout.php">
                  <i class="fa-solid fa-left-long"></i>
                  Đăng xuất
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>

      
  
    </div>

    <form action="" method="post">
      <div class="content">
        <div class="class-name">
          <p style="display: block;">Tên lớp học</p>
          <input type="text" name="class-name" class="input" placeholder="Cấu trúc dữ liệu và giải thuật - CNTT45B..." required>
        </div>

        <div class="class-description">
          <p style="display: block;">Mô tả</p>
          <textarea name="description" class="input" rows="3" ></textarea>
        </div>

        <?php if ($_SERVER['REQUEST_METHOD'] == "POST" && $result === false):?>
          <div class="error-message">Không thể tạo lớp!</div>
        <?php endif; ?>
        <div class="btn">
          <button type="submit">Tạo lớp</button>    
        </div>
      </div>
    </form>
  </div>
</body>
</html>