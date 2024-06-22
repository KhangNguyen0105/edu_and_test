<?php
  session_start();

  $conn = mysqli_connect("localhost", "root", "", "edu_and_test");
  if (!$conn) die("Kết nối không thành công: " . mysqli_connect_error());
  
  $course_found = false;
  $course_info = [];
  $teacher_name = "";
  
  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['find_course'])) {
      $input_course_id = trim($_POST['course_id']);
      if ($input_course_id == "") {
        echo '<script>alert("Bạn chưa điền mã lớp!")</script>';
      } else {
        // Tìm kiếm thông tin lớp học
        $query = "SELECT * FROM courses WHERE course_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $input_course_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($row = mysqli_fetch_assoc($result)) {
          $course_found = true;
          $course_info = $row;
  
          // Tìm kiếm tên giáo viên
          $teacher_query = "SELECT full_name FROM users WHERE user_id = ?";
          $teacher_stmt = mysqli_prepare($conn, $teacher_query);
          mysqli_stmt_bind_param($teacher_stmt, "s", $course_info['teacher_id']);
          mysqli_stmt_execute($teacher_stmt);
          $teacher_result = mysqli_stmt_get_result($teacher_stmt);
          if ($teacher_row = mysqli_fetch_assoc($teacher_result)) {
            $teacher_name = $teacher_row['full_name'];
          }
          
          mysqli_stmt_close($teacher_stmt);
        } else {
          echo '<script>alert("Không tìm thấy lớp học!")</script>';
        }
        mysqli_stmt_close($stmt);
      }
    } elseif (isset($_POST['join'])) {
      $user_id = $_SESSION['user_id'];
      $course_id = $_POST['course_id'];
  
      // Kiểm tra xem người dùng đã tham gia lớp học hay chưa
      $check_query = "SELECT * FROM enrollments WHERE user_id = ? AND course_id = ?";
      $check_stmt = mysqli_prepare($conn, $check_query);
      mysqli_stmt_bind_param($check_stmt, "ss", $user_id, $course_id);
      mysqli_stmt_execute($check_stmt);
      $check_result = mysqli_stmt_get_result($check_stmt);
      
      if (mysqli_fetch_assoc($check_result)) {
        // Người dùng đã tham gia lớp học, chuyển đến trang newsfeed
        header("Location: class/newsfeed.php?course_id=" . $course_id);
        exit();
      } else {
        // Người dùng chưa tham gia lớp học, thêm bản ghi vào bảng enrollments
        $query = "INSERT INTO enrollments (user_id, course_id) VALUES (?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ss", $user_id, $course_id);
        if (mysqli_stmt_execute($stmt)) {
          echo '<script>alert("Tham gia lớp thành công!"); window.location.href="class/newsfeed.php?course_id=' . $course_id . '";</script>';
        } 

        mysqli_stmt_close($stmt);
      }
      mysqli_stmt_close($check_stmt);
    }
  }
  
  mysqli_close($conn);
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
<body>
  <div id="find-wrapper">
    <div id="logo">
      <h1 class="logo" style="text-decoration: none; font-family: Lobster, sans-serif">Edu & Test</h1>
    </div>

    <div class="form" id="find-class-form" style="<?php echo $course_found ? 'display:none;' : ''; ?>">
      <form action="" method="post">
        <p class="title">Tham gia lớp bằng mã lớp</p>
        <p class="detail">Mã lớp gồm 4 ký tự, được giáo viên lớp đó cung cấp</p>
        <div class="input">
          <input type="text" name="course_id" minlength="1" required>
        </div>
        <button type="submit" name="find_course" id="find-course">Tìm lớp</button>
        <div class="nav">
          <a href="class/">
            <i class="fa-solid fa-arrow-left-long"></i>
            <p>Quay lại danh sách lớp</p>
          </a>
        </div>
      </form>
    </div>

    <div class="form" id="class-found" style="<?php echo $course_found ? '' : 'display:none;'; ?>">
      <form action="" method="post">
        <p class="title">
          Tìm thấy một lớp học
        </p>
        <p id="class-name"><?php echo htmlspecialchars($course_info['course_name']); ?></p>
        <div class="course_id">
          <p class="sub-title">Mã lớp</p>
          <p class="data"><?php echo htmlspecialchars($course_info['course_id']); ?></p>
        </div>
        <div class="teacher">
          <p class="sub-title">Giáo viên</p>
          <p class="data"><?php echo htmlspecialchars($teacher_name); ?></p>
        </div>
        <input type="hidden" name="course_id" value="<?php echo htmlspecialchars($course_info['course_id']); ?>">
        <button type="submit" name="join">Tham gia lớp</button>
        <a href="" class="nav" id="back">
          <i class="fa-solid fa-arrow-left-long"></i>
          <p>Quay lại</p>
        </a>
      </form>
    </div>
  </div>

  <script src="asset/script/script.js"></script>

</body>
</html>