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

    // Lấy tên bài tập
    $assignment_id = $_GET['assignment_id'];
    $get_assignment_info_query = "SELECT * FROM assignments WHERE assignment_id = ?";
    $get_assignment_stmt = mysqli_prepare($conn, $get_assignment_info_query);
    mysqli_stmt_bind_param($get_assignment_stmt, "s", $assignment_id);
    mysqli_stmt_execute($get_assignment_stmt);
    $assignment_info = mysqli_fetch_assoc(mysqli_stmt_get_result($get_assignment_stmt));

    // Xử lý tìm kiếm học sinh theo tên
    $search_term = "";
    $filter = "all"; // Mặc định là xem tất cả học sinh

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
      if (isset($_POST['search_button']))
        $search_term = mysqli_real_escape_string($conn, $_POST['search']);
  
      if (isset($_POST['filter']))
        $filter = $_POST['filter'];
    }

    // Truy vấn dữ liệu từ cơ sở dữ liệu
    $query = "
        SELECT users.full_name, grades.score, grades.submission_date 
        FROM users
        JOIN enrollments ON users.user_id = enrollments.user_id
        LEFT JOIN grades ON users.user_id = grades.user_id AND grades.assignment_id = ?
        WHERE enrollments.course_id = ?
    ";

    // Nếu có tìm kiếm
    if (!empty($search_term))
      $query .= " AND users.full_name LIKE '%$search_term%'";

    // Xem những học sinh chưa làm bài tập
    if ($filter == "not_submitted")
      $query .= " AND grades.submission_date IS NULL";

    // Xem những học sinh đã làm bài tập
    elseif ($filter == "submitted")
      $query .= " AND grades.submission_date IS NOT NULL";

    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ss", $assignment_id, $course_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

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
            <a href="../newsfeed.php?course_id=<?php echo $course_id?>" class="item"><i class="fa-solid fa-newspaper"></i> Bảng tin</a>
            <a href="../member.php?course_id=<?php echo $course_id?>" class="item"><i class="fa-regular fa-user"></i> Thành viên</a>
            <a href="" class="item current"><i class="fa-regular fa-file-lines"></i> Bài tập</a>
            <?php if ($_SESSION['role'] == 1) : ?>
              <a href="" class="item"><i class="fa-solid fa-chart-simple"></i> Bảng điểm</a>
            <?php endif ?>
          </div>
            
          <?php if ($_SESSION['role'] == '1') : ?>
            <a href="../edit.php?course_id=<?php echo $course_id; ?>" class="settings"><i class="fa-solid fa-gear"></i> Chỉnh sửa lớp học</a>
          <?php endif; ?>
        </div>
      </div>

      <div class="main-content" style="background-color: #fff;">
        <div class="title" style="border-bottom: none;">
          <div id="#nav">
            <a href="list.php?course_id=<?php echo $course_id ?>">Bài tập</a>
            <i class="fa-solid fa-caret-right" style="margin: 0 4px;"></i>
            <p style="font-size: 16px;"><?php echo $assignment_info['title'] ?></p>
          </div>
          <div id="views">
            <button class="current" id="score-board">Bảng Điểm</button>
            <button id="homework-details">Đề bài</button>
          </div>
        </div>

        <div class="homework-wrapper">
          <div class="space" style="height: 64px"></div>

          <form action="" method="post">
            <div id="options">
              <button type="submit" name="filter" value="all" class="current" id="all">Tất cả</button>
              <button type="submit" name="filter" value="not_submitted" id="not-submitted">Chưa làm</button>
              <button type="submit" name="filter" value="submitted" id="submitted">Đã làm</button>
            </div>
            <div class="search-create">
              <input name="search" type="text" placeholder="Tìm kiếm...">
              <button type="submit" name="search_button">
                <i class="fa-solid fa-magnifying-glass"></i>
                Tìm kiếm
              </button>
            </div>
          </form>
              
          <div class="th">
            <div class="row" style="margin-top: 0;">
              <div class="column">Họ và tên</div>
              <div class="column">Điểm</div>
              <div class="column">Ngày nộp</div>
              <div class="column"></div>
            </div>
          </div>

          <div class="tb details-tb">         
            <?php
              if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                  $full_name = htmlspecialchars($row['full_name']);
                  $grade = $row['score'] ? htmlspecialchars($row['score']) : "Chưa làm";
                  $submission_date = $row['submission_date'] ? htmlspecialchars($row['submission_date']) : "Chưa nộp";

                  echo '<div class="row">';
                  echo '    <div class="column">' . $full_name . '</div>';
                  echo '    <div class="column">' . $grade . '</div>';
                  echo '    <div class="column">' . $submission_date . '</div>';
                  echo '    <div class="column"><button>Chi tiết</button></div>';
                  echo '</div>';
                }
              }
            ?>
          </div>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const filterButtons = document.querySelectorAll('#options button');
      
      // Mặc định là nút "Tất cả"
      if (!localStorage.getItem('selectedFilter'))
        localStorage.setItem('selectedFilter', 'all');

      // Lấy trạng thái của nút được chọn
      const selectedFilter = localStorage.getItem('selectedFilter');
      if (selectedFilter)
        document.getElementById(selectedFilter).classList.add('active');

      // Thêm sự kiện cho các nút
      filterButtons.forEach(button => {
        button.addEventListener('click', function() {
          // Lưu trạng thái nút được chọn
          localStorage.setItem('selectedFilter', this.id);

          // Loại bỏ lớp 'active' từ tất cả các nút
          filterButtons.forEach(btn => btn.classList.remove('active'));

          // Thêm lớp 'active' vào nút được nhấn
          this.classList.add('active');
        });
      });
    });
  </script>

  </script>
</body>
</html>
