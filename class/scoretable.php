<?php
session_start();

if (isset($_SESSION['user_id'])) {
  $conn = mysqli_connect("localhost", "root", "", "edu_and_test");
  if (!$conn) {
    die("Kết nối không thành công: " . mysqli_connect_error());
  }

  // Lấy giá trị course_id từ URL
  $course_id = isset($_GET['course_id']) ? $_GET['course_id'] : '';
  $current_user_role = $_SESSION['role'];
  $search_term = isset($_POST['search']) ? trim($_POST['search']) : '';

  // Khởi tạo biến mô tả để tránh lỗi khi không có dữ liệu
  $course_name = '';
  $description = '';

  // Truy vấn thông tin lớp học nếu course_id tồn tại
  if ($course_id != '') {
    $query = "SELECT course_name, description FROM courses WHERE course_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $course_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $class_info = mysqli_fetch_assoc($result);

    if ($class_info) {
      $course_name = htmlspecialchars($class_info['course_name']);
      $description = htmlspecialchars($class_info['description']);
    }
    mysqli_stmt_close($stmt);
  }

  // Đếm số học sinh trong lớp
  $student_count = 0;
  if ($course_id != '') {
    $count_student_query = "SELECT COUNT(*) as student_count FROM enrollments WHERE course_id = ?";
    $count_student_stmt = mysqli_prepare($conn, $count_student_query);
    mysqli_stmt_bind_param($count_student_stmt, "s", $course_id);
    mysqli_stmt_execute($count_student_stmt);
    $count_student_result = mysqli_stmt_get_result($count_student_stmt);
    $student_count_array = mysqli_fetch_assoc($count_student_result);
    $student_count = $student_count_array['student_count'];
    mysqli_stmt_close($count_student_stmt);
  }

  // Lấy danh sách các bài tập trong lớp
  $assignments = [];
  if ($course_id != '') {
    $assignments_query = "SELECT assignment_id, title FROM assignments WHERE course_id = ?";
    $assignments_stmt = mysqli_prepare($conn, $assignments_query);
    mysqli_stmt_bind_param($assignments_stmt, "s", $course_id);
    mysqli_stmt_execute($assignments_stmt);
    $assignments_result = mysqli_stmt_get_result($assignments_stmt);

    while ($assignment = mysqli_fetch_assoc($assignments_result)) {
      $assignments[] = $assignment;
    }

    mysqli_stmt_close($assignments_stmt);
  }

  // Truy vấn thông tin học sinh và điểm số từng bài tập
  $students = [];
  if ($course_id != '') {
    $get_all_student_query = "SELECT u.user_id, u.full_name, u.email, AVG(g.score) as average_grade
                              FROM users u
                              JOIN enrollments e ON u.user_id = e.user_id
                              LEFT JOIN grades g ON u.user_id = g.user_id AND g.assignment_id IN (SELECT assignment_id FROM assignments WHERE course_id = ?)
                              WHERE e.course_id = ?";

    if ($search_term != '') {
      $get_all_student_query .= " AND u.full_name LIKE ?";
      $like_search_term = "%" . $search_term . "%";
    }

    $get_all_student_query .= " GROUP BY u.user_id";

    $get_all_student_stmt = mysqli_prepare($conn, $get_all_student_query);

    if ($search_term != '') {
      mysqli_stmt_bind_param($get_all_student_stmt, "sss", $course_id, $course_id, $like_search_term);
    } else {
      mysqli_stmt_bind_param($get_all_student_stmt, "ss", $course_id, $course_id);
    }

    mysqli_stmt_execute($get_all_student_stmt);
    $student_result = mysqli_stmt_get_result($get_all_student_stmt);

    while ($student = mysqli_fetch_assoc($student_result)) {
      $student_id = $student['user_id'];
      $student['assignments'] = [];

      foreach ($assignments as $assignment) {
        $assignment_id = $assignment['assignment_id'];

        $score_query = "SELECT score FROM grades WHERE user_id = ? AND assignment_id = ?";
        $score_stmt = mysqli_prepare($conn, $score_query);
        mysqli_stmt_bind_param($score_stmt, "ss", $student_id, $assignment_id);
        mysqli_stmt_execute($score_stmt);
        $score_result = mysqli_stmt_get_result($score_stmt);
        $score_row = mysqli_fetch_assoc($score_result);
        $score = '-';
        if (!is_null($score_row) && array_key_exists('score', $score_row))
          $score = $score_row['score'];

        $student['assignments'][$assignment_id] = $score;

        mysqli_stmt_close($score_stmt);
      }

      $students[] = $student;
    }

    mysqli_stmt_close($get_all_student_stmt);
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
            <?php if ($course_id != '' && $course_name != ''): ?>
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
            <a href="member.php?course_id=<?php echo $course_id ?>" class="item"><i class="fa-regular fa-user"></i> Thành viên</a>
            <a href="homework/list.php?course_id=<?php echo $course_id ?>"class="item"><i class="fa-regular fa-file-lines"></i> Bài tập</a>
            <?php if ($_SESSION['role'] == 1) : ?>
              <a href="" class="item current"><i class="fa-solid fa-chart-simple"></i> Bảng điểm</a>
            <?php endif ?>
          </div>
            
          <?php if ($_SESSION['role'] == '1') : ?>
            <a href="edit.php?course_id=<?php echo $course_id; ?>" class="settings"><i class="fa-solid fa-gear"></i> Chỉnh sửa lớp học</a>
          <?php endif; ?>
        </div>
      </div>

      <div class="main-content" style="background-color: #fff;">
        <div class="title">
          Thành viên lớp học (<?php echo $student_count ?>)
        </div>
              
        <div class="news-wrapper">
          <div class="space" style="height: 64px"></div>
          
          <form action="" method="post">
            <div class="search-create" style="padding: 16px">
              <input name="search" type="text" placeholder="Tìm kiếm...">
              <button type="submit" name="search_button">
                <i class="fa-solid fa-magnifying-glass"></i>
                Tìm kiếm
              </button>
            </div>
          </form>

          <div class="th">
            <div class="row" style="margin-top: 0;">
              <div class="column" style="flex: 1;">Họ và tên</div>
              <div class="column">Trung bình</div>
              <?php foreach ($assignments as $assignment): ?>
                <div class="column"><?php echo htmlspecialchars($assignment['title']); ?></div>
              <?php endforeach; ?>
            </div>
          </div>

          <div class="tb member-tb">
            <?php foreach ($students as $student): ?>
              <div class="row">
                <div class="column" style="flex: 1;"><?php echo htmlspecialchars($student['full_name']); ?></div>
                <div class="column"><?php echo !is_null($student['average_grade']) ? round($student['average_grade'], 1) : '-'; ?></div>
                <?php foreach ($assignments as $assignment): ?>
                  <div class="column">
                    <?php echo isset($student['assignments'][$assignment['assignment_id']]) ? htmlspecialchars($student['assignments'][$assignment['assignment_id']]) : '-'; ?>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endforeach; ?>
          </div>

        </div>
      </div>
    </div>
  </div>
</body>
</html>

