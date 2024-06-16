<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Lobster&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="asset/css/style.css">
  <link rel="stylesheet" href="asset/css/class-style.css">
  <link rel="stylesheet" href="../asset/icon/fontawesome-free-6.5.1-web/css/all.min.css">
  <title>Trang Chủ</title>
  <style>
    .content {
      text-align: center;
      margin: 20px;
    }

    .content_logo {
      font-family: 'Lobster', sans-serif;
      color: #2c3e50;
      font-size: 20px;
      margin-top: 100px;
    }

    .content h1 {
      color:  rgb(83, 167, 83);
      margin-bottom: 20px;
    }

    .content_Join {
      width: 180px;
    }

    .content_Advertise {
      flex-direction: column;
      align-items: flex-start;
      padding: 10px;
    }

    .content_sub {
      font-weight: bold;
      color: #34495e;
      border: 1px solid rgb(83, 167, 83);
      border-radius: 6px;
      display: grid;
      padding: 20px;
      margin: 20px 0px;
      box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.2);
    }

    .content_Img {
      width: 50%;
      margin-left: 10px;
      /* Khoảng cách từ nội dung đến hình ảnh */
    }

    .content_Img img {
      width: 100%;
      border-radius: 8px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .div-content {
      display: flex;
      justify-content: space-around;
      align-items: flex-start;
      /* Căn đầu dòng */
      /* flex-wrap: wrap; */
      /* Xuống hàng trên màn hình nhỏ */
    }
    
    .content .c_logo {
      margin-top: 76px;
    }

    .continue-btn {
      display: block;
      padding: 16px 8px;
      width: 400px;
      border-radius: 8px;
      margin-top: 20px;
      background-color: rgb(92, 187, 92);
      border: none;
      color: white;
      font-size: 16px;
      font-weight: bolder;
    }

    .continue-btn:hover {
      cursor: pointer;
      background-color: rgb(63, 131, 63);
    }
  </style>
</head>

<body>
  <div class="wrapper">
    <div class="position-fixed">

    <div class="header">
      <a href="../" class="logo" style="text-decoration: none; font-family: Lobster, sans-serif">
        Edu & Test
      </a>

      <div class="home-signup">
        <div class="home btn">
          <a href="login">Đăng nhập</a>
        </div>
        <div class="signup btn">
          <a href="../signup/">Đăng ký</a>
        </div>
      </div>
    </div>

    <div class="content">
      <div class="c_logo">
        <p class="content_logo" style="font-family: Lobster, sans-serif"> Edu and Test</p>
        <h1> Giảng dạy hiệu quả với phương pháp dạy và học trực tuyến!</h1>
      </div>

      <div class="div-content">
        <div class="content_Advertise">

          <p class="content_sub">Cung cấp các tài nguyên cho học sinh</p>
          <p class="content_sub">Khai thác học liệu</p>
          <p class="content_sub">Giao bài tập</p>
          <p class="content_sub">Tổ chức lớp học trực tuyến</p>
          <p class="content_sub">Tạo nhiệm vụ học tập</p>
          <a href="signup/" class="continue-btn"> Tham gia ngay</a>
        </div>

        <div class="content_Img">
          <img src="https://cdn.pixabay.com/photo/2024/01/26/10/29/homework-8533770_640.png" height="500px" ;>
        </div>
      </div>
    </div>
  </div>
</body>

</html>