<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "edu_and_test");
if (!$conn) die("Kết nối không thành công: " . mysqli_connect_error());

mysqli_set_charset($conn, "utf8mb4");
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$course_count = 0;
$search_term = '';

if ($role == '1') { // Giáo viên
  // Truy vấn đếm số lớp của giáo viên
  $query = "SELECT COUNT(*) AS total_classes FROM courses WHERE teacher_id = '$user_id'";
  $result = mysqli_query($conn, $query);

  if ($result) {
    $row = mysqli_fetch_assoc($result);
    $course_count = $row['total_classes'];
  } else
    echo "Đã xảy ra lỗi khi truy vấn cơ sở dữ liệu.";

} else if ($role == '2') { // Học sinh
  // Truy vấn đếm số lớp mà học sinh tham gia
  $query = "
    SELECT COUNT(DISTINCT courses.course_id) AS total_classes 
    FROM courses
    JOIN enrollments ON courses.course_id = enrollments.course_id
    WHERE enrollments.user_id = '$user_id'
  ";
  $result = mysqli_query($conn, $query);

  if ($result) {
    $row = mysqli_fetch_assoc($result);
    $course_count = $row['total_classes'];
  } else
    echo "Đã xảy ra lỗi khi truy vấn cơ sở dữ liệu.";
}

// Kiểm tra nếu có tìm kiếm
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search_button'])) {
  $search_term = mysqli_real_escape_string($conn, $_POST['search']);
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
          <a href="" class="current">Lớp học</a>
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

      <div class="class">
        <?php
          if ($role == '1')
            echo "<p>Lớp của bạn: $course_count</p>";
          else if ($role == '2')
            echo "<p>Lớp của bạn: $course_count</p>";
        ?>
      </div>
      
      <form action="" method="post">
        <div class="search-create">
          <input name="search" type="text" placeholder="Tìm kiếm..." value="<?php echo htmlspecialchars($search_term); ?>">
          <button type="submit" name="search_button">
              <i class="fa-solid fa-magnifying-glass"></i>
              Tìm kiếm
          </button>
          <?php
          if ($role == 1) {
            echo '<a href="add.php"><i class="fa-solid fa-plus"></i> Tạo lớp học</a>';
          } else if ($role == 2) {
            echo '<a href="../find.php"><i class="fa-solid fa-plus"></i> Tìm lớp học</a>';
          }
          ?>
        </div>
      </form>

      <div class="th">
        <div class="row">
          <div class="column">Tên lớp</div>
          <div class="column">Học sinh</div>
          <div class="column">Bài tập</div>
          <div class="column">Học liệu</div>
        </div>
      </div>
    </div>

    <div class="content">
      <div class="tb">
        <?php
          if ($role == 1) {
            // Truy vấn lớp học của giáo viên
            if ($search_term) {
              $query = "
                  SELECT
                    courses.course_id,
                    courses.course_name,
                    COUNT(DISTINCT enrollments.user_id) AS total_students,
                    COUNT(DISTINCT assignments.assignment_id) AS total_assignments,
                    COUNT(DISTINCT materials.material_id) AS total_materials
                  FROM
                    courses
                  LEFT JOIN enrollments ON courses.course_id = enrollments.course_id
                  LEFT JOIN assignments ON courses.course_id = assignments.course_id
                  LEFT JOIN materials ON courses.course_id = materials.course_id
                  WHERE
                    courses.teacher_id = ? AND courses.course_name LIKE ?
                  GROUP BY
                    courses.course_id
                ";
                $stmt = mysqli_prepare($conn, $query);
                $search_term = "%" . $search_term . "%";
                mysqli_stmt_bind_param($stmt, "is", $user_id, $search_term);
            } else {
              $query = "
                  SELECT
                      courses.course_id,
                      courses.course_name,
                      COUNT(DISTINCT enrollments.user_id) AS total_students,
                      COUNT(DISTINCT assignments.assignment_id) AS total_assignments,
                      COUNT(DISTINCT materials.material_id) AS total_materials
                  FROM
                      courses
                  LEFT JOIN enrollments ON courses.course_id = enrollments.course_id
                  LEFT JOIN assignments ON courses.course_id = assignments.course_id
                  LEFT JOIN materials ON courses.course_id = materials.course_id
                  WHERE
                      courses.teacher_id = ?
                  GROUP BY
                      courses.course_id
                  ";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "i", $user_id);
              }
          } else if ($role == 2) {
            // Truy vấn lớp học mà học sinh tham gia
            if ($search_term) {
              $query = "
                  SELECT
                    courses.course_id,
                    courses.course_name,
                    COUNT(DISTINCT enrollments.user_id) AS total_students,
                    COUNT(DISTINCT assignments.assignment_id) AS total_assignments,
                    COUNT(DISTINCT materials.material_id) AS total_materials
                  FROM
                    enrollments
                  INNER JOIN courses ON enrollments.course_id = courses.course_id
                  LEFT JOIN assignments ON courses.course_id = assignments.course_id
                  LEFT JOIN materials ON courses.course_id = materials.course_id
                  WHERE
                    enrollments.user_id = ? AND courses.course_name LIKE ?
                  GROUP BY
                    courses.course_id
                ";
              $stmt = mysqli_prepare($conn, $query);
              $search_term = "%" . $search_term . "%";
              mysqli_stmt_bind_param($stmt, "is", $user_id, $search_term);
            } else {
              $query = "
                  SELECT
                    courses.course_id,
                    courses.course_name,
                    COUNT(DISTINCT enrollments.user_id) AS total_students,
                    COUNT(DISTINCT assignments.assignment_id) AS total_assignments,
                    COUNT(DISTINCT materials.material_id) AS total_materials
                  FROM
                    enrollments
                  INNER JOIN courses ON enrollments.course_id = courses.course_id
                  LEFT JOIN assignments ON courses.course_id = assignments.course_id
                  LEFT JOIN materials ON courses.course_id = materials.course_id
                  WHERE
                    enrollments.user_id = ?
                  GROUP BY
                    courses.course_id
                ";
              $stmt = mysqli_prepare($conn, $query);
              mysqli_stmt_bind_param($stmt, "i", $user_id);
            }
          }

          mysqli_stmt_execute($stmt);
          $result = mysqli_stmt_get_result($stmt);

          if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
              echo '
                <a href="newsfeed.php?course_id=' . $row['course_id'] . '">
                  <div class="row">
                    <div class="column">
                      <p>' . htmlspecialchars($row['course_name']) . '</p>
                      <div class="class-id">Mã lớp: ' . htmlspecialchars($row['course_id']) . '</div>
                    </div>
                    <div class="column">' . $row['total_students'] . '</div>
                    <div class="column">' . $row['total_assignments'] . '</div>
                    <div class="column">' . $row['total_materials'] . '</div>
                  </div>
                </a>
              ';
            }
          }
          mysqli_close($conn);
        ?>
      </div>
    </div>
  </div>
</body>
</html>