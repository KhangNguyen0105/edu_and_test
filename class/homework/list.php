<?php
  session_start();

  if (isset($_SESSION['user_id'])) {
    $conn = mysqli_connect("localhost", "root", "", "edu_and_test");
    if (!$conn)
      die("Kết nối không thành công: " . mysqli_connect_error());

    // Lấy giá trị course_id từ URL
    $course_id = isset($_GET['course_id']) ? $_GET['course_id'] : '';
    $role = $_SESSION['role'];

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

      mysqli_close($conn);
    }
  }

  $conn = mysqli_connect("localhost", "root", "", "edu_and_test");
  if (!$conn) die("Kết nối không thành công: " . mysqli_connect_error());

  $course_id = mysqli_real_escape_string($conn, $_GET['course_id']);
  $search_term = '';

  if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search_button'])) {
    $search_term = mysqli_real_escape_string($conn, $_POST['search']);
    $query_assignments = "
      SELECT 
        assignments.assignment_id, 
        assignments.title, 
        COUNT(DISTINCT grades.user_id) AS students_completed, 
        (SELECT COUNT(enrollments.user_id) FROM enrollments WHERE enrollments.course_id = '$course_id') AS total_students
      FROM assignments
      LEFT JOIN grades ON assignments.assignment_id = grades.assignment_id
      WHERE assignments.course_id = '$course_id' AND assignments.title LIKE '%$search_term%'
      GROUP BY assignments.assignment_id 
    ";
  } else {
    $query_assignments = "
      SELECT 
        assignments.assignment_id, 
        assignments.title, 
        COUNT(DISTINCT grades.user_id) AS students_completed, 
        (SELECT COUNT(enrollments.user_id) FROM enrollments WHERE enrollments.course_id = '$course_id') AS total_students
      FROM assignments
      LEFT JOIN grades ON assignments.assignment_id = grades.assignment_id
      WHERE assignments.course_id = '$course_id'
      GROUP BY assignments.assignment_id
    ";
  }

  $result_assignments = mysqli_query($conn, $query_assignments);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Lobster&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../asset/css/class-style.css">
  <link rel="stylesheet" href="../../asset/icon/fontawesome-free-6.5.1-web/css/all.min.css">
  <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
  <title>Edu & Test</title>
</head>
<body>
  <div class="wrapper">
    <div class="position-fixed" style="padding-bottom: 0;">
      <div class="header">
        <a href="../../" class="logo" style="text-decoration: none; font-family: Lobster, sans-serif">
          Edu & Test
        </a>

        <div class="nav">
          <a href="../" class="current">Lớp học</a>
          <a href="../../resource.php">Học liệu</a>
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
                <a href="../../profile.php">
                  <i class="fa-solid fa-user"></i>
                  Thông tin cá nhân
                </a>
              </li>
              <li>
                <a href="../../logout.php">
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
            <?php
            echo '
            <a href="../newsfeed.php?course_id=' .$course_id. '" class="item"><i class="fa-solid fa-newspaper"></i> Bảng tin</a>
            '
            ?>
            <a href="../member.php?course_id=<?php echo $course_id?>" class="item"><i class="fa-regular fa-user"></i> Thành viên</a>
            <a href="" class="item current"><i class="fa-regular fa-file-lines"></i> Bài tập</a>
            <a href="" class="item"><i class="fa-solid fa-chart-simple"></i> Bảng điểm</a>
          </div>
            
          <?php
            echo '
            <a href="../edit.php?course_id=' . $course_id . '" class="settings"><i class="fa-solid fa-gear"></i> Chỉnh sửa lớp học</a>
            '
          ?>
        </div>
      </div>

      <div class="main-content">
        <div class="title" style="border-bottom: none;">
          Bài tập
        </div>

        <div class="homework-wrapper">
          <div class="space" style="height: 64px"></div>

          <form action="" method="post">
            <div class="search-create">
              <input name="search" type="text" placeholder="Tìm kiếm...">
              <button type="submit" name="search_button">
                <i class="fa-solid fa-magnifying-glass"></i>
                Tìm kiếm
              </button>
              <?php if ($role == '1') : ?>
                <a href="add.php?course_id=<?php echo $course_id; ?>">
                  <i class="fa-solid fa-plus"></i>
                  Tạo bài tập
                </a>
              <?php endif ?>  
            </div>
          </form>
              
          <?php
            if ($result_assignments) {
              while ($row = mysqli_fetch_assoc($result_assignments)) {
                
                if ($role == '1')
                  echo '<a href="detail.php?course_id=' . $course_id . '&assignment_id=' . htmlspecialchars($row['assignment_id']) . '" class="homework">';
                else if ($role == '2')
                  echo '<a href="test.php?course_id=' . $course_id . '&assignment_id=' . htmlspecialchars($row['assignment_id']) . '" class="homework">';
                  
                echo '  <div class="homework-header">';
                echo '    <p>' . htmlspecialchars($row['title']) . '</p>';
                echo '  </div>';
                echo '  <div class="homework-status">';
                echo '    <p>' . htmlspecialchars($row['students_completed']) . '/' . htmlspecialchars($row['total_students']) . ' đã làm</p>';
                echo '  </div>';
                echo '</a>';
              }
            }
          ?>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
