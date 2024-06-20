<?php
  session_start();

  if (isset($_SESSION['user_id'])) {
    $conn = mysqli_connect("localhost", "root", "", "edu_and_test");
    if (!$conn)
      die("Kết nối không thành công: " . mysqli_connect_error());

    // Lấy giá trị course_id từ URL
    $course_id = isset($_GET['course_id']) ? $_GET['course_id'] : '';
    $current_user_role = $_SESSION['role'];
    $search_term = isset($_POST['search']) ? trim($_POST['search']) : '';

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

    // Truy vấn thông tin học sinh và số bài tập đã làm / tổng số bài tập
    $get_all_student_query = "SELECT u.user_id, u.full_name, u.email, 
                             COUNT(DISTINCT g.assignment_id) as assignments_done, 
                             (SELECT COUNT(*) FROM assignments a WHERE a.course_id = ?) as total_assignments, 
                             AVG(g.score) as average_grade
                      FROM users u
                      JOIN enrollments e ON u.user_id = e.user_id
                      LEFT JOIN grades g ON u.user_id = g.user_id AND g.assignment_id IN (SELECT assignment_id FROM assignments WHERE course_id = ?)
                      WHERE e.course_id = ?";

    if ($search_term != '')
      $get_all_student_query .= " AND u.full_name LIKE ?";

    $get_all_student_query .= " GROUP BY u.user_id";

    $get_all_student_stmt = mysqli_prepare($conn, $get_all_student_query);

    if ($search_term != '') {
      $like_search_term = "%" . $search_term . "%";
      mysqli_stmt_bind_param($get_all_student_stmt, "ssss", $course_id, $course_id, $course_id, $like_search_term);
    } else
      mysqli_stmt_bind_param($get_all_student_stmt, "sss", $course_id, $course_id, $course_id);

    mysqli_stmt_execute($get_all_student_stmt);
    $student_result = mysqli_stmt_get_result($get_all_student_stmt);
    mysqli_stmt_close($get_all_student_stmt);

    // Xử lý xoá một sinh viên
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirm-delete']) && isset($_POST['user_id'])) {
      $user_id = $_POST['user_id'];
      
      // Xoá tất cả điểm của học sinh trong lớp khỏi bảng grades
      $delete_grades_query = "DELETE FROM grades WHERE user_id = ?";
      $delete_stmt = mysqli_prepare($conn, $delete_grades_query);
      mysqli_stmt_bind_param($delete_stmt, "i", $user_id);
      $grades_deleted = mysqli_stmt_execute($delete_stmt);
      mysqli_stmt_close($delete_stmt);
      
      // Xoá bản ghi của học sinh khỏi bảng enrollments
      $delete_enrollments_query = "DELETE FROM enrollments WHERE user_id = ? AND course_id = ?";
      $delete_stmt = mysqli_prepare($conn, $delete_enrollments_query);
      mysqli_stmt_bind_param($delete_stmt, "ii", $user_id, $course_id);
      $enrollments_deleted = mysqli_stmt_execute($delete_stmt);
      mysqli_stmt_close($delete_stmt);

      if ($enrollments_deleted && $grades_deleted) {
        echo "<script>alert('Học sinh đã được xoá thành công.'); window.location.href='member.php?course_id=$course_id';</script>";
      } else {
        echo "<script>alert('Lỗi khi xoá học sinh.');</script>";
      }
    }
    
    // Xử lý thêm một học sinh
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add-student-btn'])) {
      $student_email = $_POST['student-email'];
      
      // Kiểm tra xem học sinh có tồn tại trong hệ thống không
      $query = "SELECT user_id FROM users WHERE email = ?";
      $stmt = mysqli_prepare($conn, $query);
      mysqli_stmt_bind_param($stmt, 's', $student_email);
      mysqli_stmt_execute($stmt);
      mysqli_stmt_bind_result($stmt, $student_id);
      mysqli_stmt_fetch($stmt);
      mysqli_stmt_close($stmt);

      if ($student_id) {
        // Học sinh tồn tại, thêm bản ghi vào bảng enrollments
        $course_id = $_GET['course_id'];
        $query = "INSERT INTO enrollments (course_id, user_id) VALUES (?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'ii', $course_id, $student_id);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // Thành công
        if ($result)
          echo "<script>alert('Học sinh đã được thêm vào lớp học thành công.');
                window.location.href = window.location.href;</script>";
          
      } else
        echo "<script>alert('Không tìm thấy học sinh.');</script>";
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
              <?php if ($current_user_role == 1):?>
              <a href="" id="add-student"><i class="fa-solid fa-plus"></i> Thêm học sinh</a>
              <?php endif; ?>
            </div>
          </form>

          <div class="th">
            <div class="row" style="margin-top: 0;">
              <div class="column">Họ và tên</div>
              <div class="column">Email</div>
              <?php if ($current_user_role == 1):?>
              <div class="column">Bài đã làm</div>
              <div class="column">Điểm trung bình</div>
              <?php endif; ?>
            </div>
          </div>
          
          <div class="tb member-tb">
            <?php
              while ($row = mysqli_fetch_assoc($student_result)) {
                $full_name = htmlspecialchars($row['full_name']);
                $email = htmlspecialchars($row['email']);
                $assignments_done = $row['assignments_done'];
                $total_assignments = $row['total_assignments'];
                $average_grade = is_null($row['average_grade']) ? 0 : number_format($row['average_grade'], 1);
                $user_id = htmlspecialchars($row['user_id']);

                echo '<div class="row">';
                echo '<div class="column">' . $full_name . '</div>';
                echo '<div class="column">' . $email . '</div>';
                if ($current_user_role == 1) {
                  echo '<div class="column">' . $assignments_done . '/' . $total_assignments . '</div>';
                  echo '<div class="column">' . $average_grade . '</div>';
                  echo '<i class="fa-solid fa-trash" 
                          data-user-id="' . $user_id . '" 
                          data-full-name="' . $full_name . '" 
                          id="delete-student-' . $user_id . '"></i>';
                }
                echo '</div>';
              }
            ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Confirm Delete Student Modal -->
  <form action="" method="post" class="form-modal" id="confirm-delete-modal">
    <div class="modal">
      <div class="title">
        Xoá học sinh
        <i class="fa-solid fa-xmark" id="close-modal"></i>
      </div>
      <div class="edit-content">
        <p id="delete-student-name"></p>
      </div>
      <div class="confirm">
        <input type="hidden" name="user_id" id="user-id-to-delete">
        <button type="submit" name="confirm-delete" id="confirm-delete-button">Đồng ý</button>
        <button type="button" class="cancel" id="cancel-button">Thoát</button>
      </div>
    </div>
  </form>

  <form action="" method="post" class="form-modal" id="add-student-modal" >
    <div class="modal">
      <div class="title">
        Thêm học sinh vào lớp
        <i class="fa-solid fa-xmark" id="close-add-student-modal"></i>
      </div>
      <div class="edit-content">
        <input type="email" name="student-email" placeholder="Email của học sinh">
      </div>
      <div class="confirm">
        <button type="submit" name="add-student-btn">Thêm vào lớp</button>
      </div>
    </div>
  </form>

  <script>
    // Hiển thị modal và xử lý khi nhấn nút xoá học sinh
    document.addEventListener('DOMContentLoaded', function() {
      const deleteButtons = document.querySelectorAll('.fa-trash');
      deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
          const userId = button.getAttribute('data-user-id');
          const fullName = button.getAttribute('data-full-name');
          const modal = document.getElementById('confirm-delete-modal');
          const deleteStudentName = document.getElementById('delete-student-name');
          const userIdToDelete = document.getElementById('user-id-to-delete');

          deleteStudentName.textContent = `Học sinh ${fullName} sẽ bị xoá khỏi lớp!`;
          userIdToDelete.value = userId;

          modal.style.display = 'flex'; // Hiển thị modal khi nhấn nút xoá
        });
      });

      // Đóng modal khi nhấn nút "Thoát"
      const cancelButton = document.getElementById('cancel-button');
      cancelButton.addEventListener('click', function() {
        const modal = document.getElementById('confirm-delete-modal');
        modal.style.display = 'none'; // Ẩn modal khi nhấn nút thoát
      });

      // Đóng modal khi nhấn vào biểu tượng "x"
      const closeModalButton = document.getElementById('close-modal');
      closeModalButton.addEventListener('click', function() {
        const modal = document.getElementById('confirm-delete-modal');
        modal.style.display = 'none'; // Ẩn modal khi nhấn biểu tượng đóng
      });

      // Đóng modal khi click bên ngoài modal
      window.addEventListener('click', function(event) {
        const modal = document.getElementById('confirm-delete-modal');
        if (event.target === modal) {
          modal.style.display = 'none';
        }
      });
    });

    // Hiển thị modal thêm học sinh
    document.getElementById('add-student').addEventListener('click', function(event) {
      event.preventDefault();
      document.getElementById('add-student-modal').style.display = 'flex';
    });

    // Đóng modal thêm học sinh
    document.getElementById('close-add-student-modal').addEventListener('click', function() {
      document.getElementById('add-student-modal').style.display = 'none';
    });

    window.addEventListener('click', function(event) {
      if (event.target === document.getElementById('add-student-modal')) {
        this.document.getElementById('add-student-modal').style.display = 'none';
      }
    });

  </script>

</body>
</html>
