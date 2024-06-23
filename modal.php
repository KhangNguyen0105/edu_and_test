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
  <style>
    .form-modal {
      display: flex;
    }
  </style>
</head>
<body>
  <form action="" method="post" class="form-modal" id="leave-modal">
    <div class="modal">
      <div class="title">
        Lưu ý
        <i class="fa-solid fa-xmark" id="close-modal"></i>
      </div>
      <div class="edit-content">
        <p>Bạn có muốn thoát khỏi trang làm bài hiện tại ?</p>
      </div>
      <div class="confirm">
        <button type="submit" name="confirm-leave" id="confirm-delete-button">Đồng ý</button>
        <button type="button" class="cancel" id="cancel-button">Thoát</button>
      </div>
    </div>
  </form>
</body>
</html>