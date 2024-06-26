<?php
  session_start();

  $result = true;
  $course_id = $_GET['course_id'] ?? null;

  // Kết nối tới cơ sở dữ liệu
  $conn = mysqli_connect("localhost", "root", "", "edu_and_test");
  if (!$conn) die("Kết nối không thành công: " . mysqli_connect_error());

  // Lấy thông tin lớp học hiện tại
  if ($course_id) {
    $query = "SELECT * FROM courses WHERE course_id = '" . mysqli_real_escape_string($conn, $course_id) . "'";
    $result_course = mysqli_query($conn, $query);
    if ($result_course && mysqli_num_rows($result_course) > 0) {
      $course = mysqli_fetch_assoc($result_course);
    }
  }

  // Xử lý sự kiện chỉnh sửa hoặc xoá lớp học
  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_SESSION['user_id']) && $course_id) {
      $user_id = $_SESSION['user_id'];

      if (isset($_POST['confirm-edit'])) {
        // Chỉnh sửa lớp học
        $class_name = mysqli_real_escape_string($conn, $_POST['class-name']);
        $class_description = mysqli_real_escape_string($conn, $_POST['description']);

        $query_update = "UPDATE courses SET course_name='$class_name', description='$class_description' WHERE course_id='$course_id'";
        if (mysqli_query($conn, $query_update)) {
          echo '<script>
                  window.location.href = "newsfeed.php?course_id=' . $course_id . '";
                </script>';
          exit();
        } else {
          $result = false;
        }
      } elseif (isset($_POST['delete'])) {
        // Xóa các dữ liệu liên quan đến course_id trong các bảng khác
        $query_delete_assignments = "DELETE FROM assignments WHERE course_id='$course_id'";
        $query_delete_discussions = "DELETE FROM discussions WHERE course_id='$course_id'";
        $query_delete_enrollments = "DELETE FROM enrollments WHERE course_id='$course_id'";

        // Xóa dữ liệu trong bảng courses
        $query_delete_course = "DELETE FROM courses WHERE course_id='$course_id'";

        // Thực hiện các truy vấn xóa
        mysqli_query($conn, $query_delete_assignments);
        mysqli_query($conn, $query_delete_discussions);
        mysqli_query($conn, $query_delete_enrollments);
        if (mysqli_query($conn, $query_delete_course)) {
          echo '<script>
                  window.location.href = "index.php";
                </script>';
        } else {
          echo '<script>alert("Đã có lỗi xảy ra. Vui lòng thử lại.");</script>';
        }
      }
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
          <input type="text" name="class-name" class="input" required placeholder="Cấu trúc dữ liệu và giải thuật - CNTT45B..." value="<?php echo isset($course['course_name']) ? htmlspecialchars($course['course_name']) : ''; ?>">
        </div>

        <div class="class-description">
          <p style="display: block;">Mô tả</p>
          <textarea name="description" class="input" rows="3"><?php echo isset($course['description']) ? htmlspecialchars($course['description']) : ''; ?></textarea>
        </div>

        <?php if ($_SERVER['REQUEST_METHOD'] == "POST" && $result === false):?>
          <div class="error-message">Đã có lỗi xảy ra!</div>
        <?php endif; ?>
        <div class="btn">
          <button type="submit" name="confirm-edit">Lưu lại</button>    
        </div>
        <div class="btn">
          <button type="submit" name="delete" class="delete"><i class="fa-solid fa-trash"></i> Xoá lớp học</button>
        </div>
      </div>
    </form>
  </div>
</body>
</html>
