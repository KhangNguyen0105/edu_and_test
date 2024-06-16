<?php
  if (isset($_SESSION['course_id'])) {
    $conn = mysqli_connect("localhost", "root", "", "edu_and_test");
    if (!$conn) die("Kết nối không thành công: " . mysqli_connect_error());

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['find_course'])) {
      $input_course_id = $_POST['course-id'];
      if ($input_course_id == "")
        echo '<script>alert("Bạn chưa điền mã lớp!")</script>'; 
      else {
        
      }
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
  <link rel="stylesheet" href="asset/css/class-style.css">
  <link rel="stylesheet" href="asset/icon/fontawesome-free-6.5.1-web/css/all.min.css">
  <title>Edu & Test</title>
</head>
<body>
  <div id="find-wrapper">
    <div id="logo">
      <h1 class="logo" style="text-decoration: none; font-family: Lobster, sans-serif">Edu & Test</h1>
    </div>

    <div id="form">
      <form action="">
        <p class="title">Tham gia lớp bằng mã lớp</p>
        <p class="detail">Mã lớp gồm 4 ký tự, được giáo viên lớp đó cung cấp</p>
        <div class="input">
          <input type="text" name="course_id">
        </div>
        <button type="submit" name="find_course">Tìm lớp</button>
        <div class="nav">
          <a href="class/">
            <i class="fa-solid fa-arrow-left-long"></i>
            <p>Quay lại danh sách lớp</p>
          </a>
        </div>
      </form>
    </div>

  </div>
</body>
</html>