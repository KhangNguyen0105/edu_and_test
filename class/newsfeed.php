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

      mysqli_close($conn);
    }
  }

  // Xử lý sự kiên đăng tin
  if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new-post'])) {
    // Kết nối tới cơ sở dữ liệu
    $conn = mysqli_connect("localhost", "root", "", "edu_and_test");
    if (!$conn)
      die("Kết nối không thành công: " . mysqli_connect_error());

    // Lấy nội dung từ textarea
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    $date_created = date("Y-m-d H:i:s");

    // Thêm bản ghi mới vào bảng discussions
    $user_id = $_SESSION['user_id'];
    $query = "INSERT INTO discussions (course_id, user_id, title, content, date_created)
              VALUES ('$course_id', '$user_id', NULL, '$content', '$date_created')";

    if (mysqli_query($conn, $query)) {
      // echo "Đăng tin thành công!";
    }
    
    mysqli_close($conn);
  }

  // Xử lý sự kiện chỉnh sửa bài viết
  if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirm'])) {
    // Kết nối tới cơ sở dữ liệu
    $conn = mysqli_connect("localhost", "root", "", "edu_and_test");
    if (!$conn)
      die("Kết nối không thành công: " . mysqli_connect_error());

    // Lấy nội dung từ textarea và discussion_id
    $discussion_id = mysqli_real_escape_string($conn, $_POST['discussion_id']);
    $content = mysqli_real_escape_string($conn, $_POST['edit-content']);
    $date_created = date("Y-m-d H:i:s");

    // Cập nhật cơ sở dữ liệu với nội dung mới
    $query = "UPDATE discussions SET content='$content', date_created='$date_created' WHERE discussion_id='$discussion_id'";
    if (mysqli_query($conn, $query)) {}
      // echo '<script>alert("Thành công!");</script>';

    // Đóng kết nối
    mysqli_close($conn);
  }

  // Xử lý sự kiện xoá bài viết
  if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_discussion_id'])) {
    $conn = mysqli_connect("localhost", "root", "", "edu_and_test");
    if (!$conn) die("Kết nối không thành công: " . mysqli_connect_error());

    $discussion_id = mysqli_real_escape_string($conn, $_POST['delete_discussion_id']);
    $course_id = mysqli_real_escape_string($conn, $_GET['course_id']);

    $query = "DELETE FROM discussions WHERE discussion_id='$discussion_id' AND course_id='$course_id'";
    if (mysqli_query($conn, $query)) {
      
    } else {
      echo '<script>alert("Đã có lỗi xảy ra. Vui lòng thử lại.");</script>';
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
  <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@200..700&display=swap" rel="stylesheet">
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
          Edu <span style="font-family: Oswald, sans-serif;">&</span> Test
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
            <a href="" class="item current"><i class="fa-solid fa-newspaper"></i> Bảng tin</a>
            <a href="member.php?course_id=<?php echo $course_id?>"  class="item"><i class="fa-regular fa-user"></i> Thành viên</a>
            <?php
              echo '
              <a href="homework/list.php?course_id=' . $course_id . '" class="item"><i class="fa-regular fa-file-lines"></i> Bài tập</a>
              '
            ?>
            <a href="" class="item"><i class="fa-solid fa-chart-simple"></i> Bảng điểm</a>
          </div>
            
          <?php if ($_SESSION['role'] == '1') : ?>
            <a href="edit.php?course_id=' . $course_id . '" class="settings"><i class="fa-solid fa-gear"></i> Chỉnh sửa lớp học</a>
          <?php endif ?>
        </div>
      </div>

      <div class="main-content">
        <div class="title">
          Bảng tin
        </div>

        <div class="news-wrapper">
          <div class="space" style="height: 64px"></div>
          <form action="" method="post" style="display: block;">
            <div class="news">
              <div class="input">
                <textarea placeholder="Nhập nội dung thảo luận với lớp học..." name="content"></textarea>
              </div>
              <div class="create">
                <button type="submit" name="new-post">Đăng tin</button>
              </div>
            </div>
          </form>
          

          <?php
            // Kết nối tới cơ sở dữ liệu
            $conn = mysqli_connect("localhost", "root", "", "edu_and_test");
            if (!$conn)
              die("Kết nối không thành công: " . mysqli_connect_error());

            // Truy vấn lấy tất cả bản ghi từ bảng discussions
            $query = "SELECT discussions.discussion_id, discussions.course_id, discussions.user_id, discussions.
                      title, discussions.content, discussions.date_created, users.full_name
                      FROM discussions
                      JOIN users ON discussions.user_id = users.user_id
                      WHERE discussions.course_id = '" . mysqli_real_escape_string($conn, $course_id) . "'
                      ORDER BY discussions.date_created DESC";
            $result = mysqli_query($conn, $query);

            if ($result) {
              while ($row = mysqli_fetch_assoc($result)) {
                echo '<div class="news" data-discussion-id="' . htmlspecialchars($row['discussion_id']) . '">';
                echo '  <div class="news-header">';
                echo '    <div class="author"><p>' . htmlspecialchars($row['full_name']) . '</p></div>';
                echo '    <div class="options">';
                echo '      <i class="fa-solid fa-ellipsis-vertical"></i>';
                echo '      <div class="dropdown-menu">';
                echo '        <button class="edit">Chỉnh sửa</button>';
                echo '        <button class="delete">Xóa bài viết</button>';
                echo '      </div>';
                echo '    </div>';
                echo '  </div>';
                echo '  <div class="date"><p>' . htmlspecialchars($row['date_created']) . '</p></div>';
                echo '  <div class="news-content"><p>' . htmlspecialchars($row['content']) . '</p></div>';
                echo '</div>';
              }
            }

            mysqli_close($conn);
          ?>
        </div>
      </div>
    </div>
  </div>

  <form action="" method="post" class="form-modal" id="edit-modal" style="display: none;">
    <div class="modal">
      <div class="title">
        Chỉnh sửa bài viết
        <i class="fa-solid fa-xmark" id="close-modal"></i>
      </div>
      <div class="edit-content">
        <textarea name="edit-content" id="edit-content"></textarea>
      </div>
      <input type="hidden" name="discussion_id" id="discussion-id">
      <div class="confirm">
        <button type="submit" name="confirm">Chỉnh sửa</button>
      </div>
    </div>
  </form>


  <script src="../asset/script/script.js"></script>
</body>
</html>
