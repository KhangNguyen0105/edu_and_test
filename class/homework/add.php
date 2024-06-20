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
    <form action="" method="post" style="display: block;">
      <div id="homeword-header">
        <div id="homework-nav">
          <p class="prev">Bài tập</p>
          <i class="fa-solid fa-caret-right"></i>
          <p class="current">Tạo bài tập</p>
        </div>
        <button type="submit">Hoàn tất</button>
      </div>

      <div id="content">
        <div class="info">
          <img src="https://i.pinimg.com/236x/f2/8c/07/f28c074be78a4c506b9c2b5997b965cc.jpg" alt="homework">
          <p>Tên bài tập</p>
          <input type="text" placeholder="Tên bài tập">
          <p>Số câu hỏi</p>
          <input type="number" placeholder="Số câu hỏi">
          <div class="add-question">Tạo câu hỏi</div>
        </div>

        <div class="questions">
          <div class="question">
            <div class="question-text">
              <textarea readonly id="question-text" >Câu 1: 1 + 1 = ?
A. 1
B. 2
C. 3
D. 4
              </textarea>
              <button>Chỉnh sửa</button>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
</body>
</html>