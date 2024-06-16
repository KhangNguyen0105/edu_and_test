<?php
  session_start();

  if (isset($_SESSION['user_id'])) {
    $conn = mysqli_connect("localhost", "root", "", "edu_and_test");
    if (!$conn)
      die("Kết nối không thành công: " . mysqli_connect_error());

    // Lấy giá trị course_id từ URL
    $course_id = isset($_GET['course_id']) ? $_GET['course_id'] : '';

    // Truy vấn thông tin lớp học
    if ($course_id != '') {
      $query = "SELECT course_name, description FROM courses WHERE course_id = ?";
      $stmt = mysqli_prepare($conn, $query);
      mysqli_stmt_bind_param($stmt, "s", $course_id);
      mysqli_stmt_execute($stmt);
      $result = mysqli_stmt_get_result($stmt);
      $class_info = mysqli_fetch_assoc($result);

      // Kiểm tra và xử lý trường hợp description có giá trị null
      $course_name = htmlspecialchars($class_info['course_name']);
      $description = is_null($class_info['description']) ? '' : htmlspecialchars($class_info['description']);
    }


    // Đếm số học sinh trong lớp
    $count_student_query = "SELECT COUNT(*) as student_count FROM enrollments WHERE course_id = ?";
    $count_student_stmt = mysqli_prepare($conn, $count_student_query);
    mysqli_stmt_bind_param($count_student_stmt, "s", $course_id);
    mysqli_stmt_execute($count_student_stmt);
    $count_student_result = mysqli_stmt_get_result($count_student_stmt);
    $student_count_array = mysqli_fetch_assoc($count_student_result);
    $student_count = $student_count_array['student_count'];
    mysqli_stmt_close($count_student_stmt);
    
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
  <link rel="stylesheet" href="../asset/css/class-style.css">
  <link rel="stylesheet" href="../asset/icon/fontawesome-free-6.5.1-web/css/all.min.css">
  <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
  <title>Edu & Test</title>
</head>
<body>
  <div class="wrapper">
    <div class="position-fixed" style="padding-bottom: 0;">
      <div class="header">
        <a href="../" class="logo" style="text-decoration: none; font-family: Lobster, sans-serif">
          Edu & Test
        </a>

        <div class="nav">
          <a href="../class" class="current">Lớp học</a>
          <a href="resource.php">Học liệu</a>
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

    <div class="newsfeed-content">
      <div class="side-bar">
        <div class="w3-sidebar w3-bar-block">
          <div class="class-info">
            <?php if ($course_id != '' && $class_info): ?>
              <div class="class-name">
                <p><?php echo $course_name; ?></p>
              </div>
              <div class="class-id">
                <p><strong>Mã lớp: </strong> <?php echo htmlspecialchars($course_id); ?></p>
              </div>
              <div class="class-description">
                <p><strong>Mô tả: </strong> <?php echo $description; ?></p>
              </div>
            <?php else: ?>
              <p>Không tìm thấy thông tin lớp học.</p>
            <?php endif; ?>
          </div>
          <div class="list">  
            <a href="newsfeed.php?course_id=<?php echo $course_id?>" class="item"><i class="fa-solid fa-newspaper"></i> Bảng tin</a>
            <a href="" class="item current"><i class="fa-regular fa-user"></i> Thành viên</a>
            <?php
              echo '
              <a href="homework/list.php?course_id=' . $course_id . '" class="item"><i class="fa-regular fa-file-lines"></i> Bài tập</a>
              '
            ?>
            <a href="" class="item"><i class="fa-solid fa-chart-simple"></i> Bảng điểm</a>
          </div>
            
          <?php
            echo '
            <a href="edit.php?course_id=' . $course_id . '" class="settings"><i class="fa-solid fa-gear"></i> Chỉnh sửa lớp học</a>
            '
          ?>
        </div>
      </div>

      <div class="main-content">
        <div class="title">
          Thành viên lớp học (<?php echo $student_count ?>)
        </div>

        <div class="news-wrapper">
          <div class="space" style="height: 64px"></div>
          
        </div>
      </div>
    </div>
  </div>

  <script src="../asset/script/script.js"></script>
</body>
</html>
