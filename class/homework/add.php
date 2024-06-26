<?php
  session_start();
  if (isset($_SESSION['user_id'])) {
    $conn = mysqli_connect("localhost", "root", "", "edu_and_test");
    if (!$conn) die("Kết nối không thành công: " . mysqli_connect_error());

    $course_id = $_GET['course_id'];

    // Initialize the session variable if it doesn't exist
    if (!isset($_SESSION['question_ids']))
      $_SESSION['question_ids'] = [];

      // Thêm câu hỏi
      if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['content']) && isset($_POST['correct_answer'])) {
        $content = $_POST['content'];
        $correct_answer = $_POST['correct_answer'];

        $create_question_query = "INSERT INTO questions (content, correct_answer) VALUES (?, ?)";
        $create_question_stmt = mysqli_prepare($conn, $create_question_query);
        mysqli_stmt_bind_param($create_question_stmt, "ss", $content, $correct_answer);
        mysqli_stmt_execute($create_question_stmt);

        $question_id = mysqli_insert_id($conn); // Lưu ID của câu hỏi vừa tạo
        $_SESSION['question_ids'][] = $question_id; // Lưu ID vào session
        mysqli_stmt_close($create_question_stmt);

        echo json_encode(["question_id" => $question_id]); // Trả về ID của câu hỏi đã được thêm
        exit;
      }

      // Xác nhận tạo bài tập
      if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm-create-assignment'])) {
        $assignment_title = $_POST['assignment-title'];
        $due_date = $_POST['due-date'];

        // Kiểm tra ngày có hợp lệ không (phải lớn hơn ngày hiện tại)
        if (!empty($assignment_title) && !empty($due_date)) {
          $current_date = date('Y-m-d H:i:s');
          if ($due_date <= $current_date) {
            echo '<script>alert("Hạn nộp không hợp lệ.");</script>';
          } else {
            // Thêm một bản ghi vào bảng assignment
            $create_assignment_query = "INSERT INTO assignments (course_id, title, due_date) VALUES (?, ?, ?)";
            $create_assignment_stmt = mysqli_prepare($conn, $create_assignment_query);
            mysqli_stmt_bind_param($create_assignment_stmt, "iss", $course_id, $assignment_title, $due_date);
            mysqli_stmt_execute($create_assignment_stmt);

            $assignment_id = mysqli_insert_id($conn); // Lưu ID của assignment vừa tạo
            mysqli_stmt_close($create_assignment_stmt);

            // Thêm từng question_id vào bảng assignment_questions
            foreach ($_SESSION['question_ids'] as $question_id) {
              $insert_assignment_question_query = "INSERT INTO assignment_questions (assignment_id, question_id) VALUES (?, ?)";
              $insert_assignment_question_stmt = mysqli_prepare($conn, $insert_assignment_question_query);
              mysqli_stmt_bind_param($insert_assignment_question_stmt, "ii", $assignment_id, $question_id);
              mysqli_stmt_execute($insert_assignment_question_stmt);
              mysqli_stmt_close($insert_assignment_question_stmt);
            }

            // Kiểm tra và cập nhật max_score cho các câu hỏi
            if (count($_SESSION['question_ids']) > 0) {
              $num_questions = count($_SESSION['question_ids']);
                $max_score = 10 / $num_questions;

                foreach ($_SESSION['question_ids'] as $question_id) {
                    $update_question_score_query = "UPDATE questions SET max_score = ? WHERE question_id = ?";
                    $update_question_score_stmt = mysqli_prepare($conn, $update_question_score_query);
                    mysqli_stmt_bind_param($update_question_score_stmt, "di", $max_score, $question_id);
                    mysqli_stmt_execute($update_question_score_stmt);
                    mysqli_stmt_close($update_question_score_stmt);
              }
            } else
              echo '<script>alert("Hãy tạo ít nhất một câu hỏi.");</script>';

              // Clear the session variable after assignment creation
              $_SESSION['question_ids'] = [];

              mysqli_close($conn);
              header("Location: list.php?course_id=" . $course_id);
              exit;
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
  <link rel="stylesheet" href="../../asset/css/class-style.css">
  <link rel="stylesheet" href="../../asset/icon/fontawesome-free-6.5.1-web/css/all.min.css">
  <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <title>Edu & Test</title>
</head>
<body>
  <div class="wrapper">
    <form action="" method="post" style="display: block;">
      <div id="homeword-header">
        <div id="homework-nav">
          <a href="list.php?course_id=<?php echo $course_id ?>" class="prev">Bài tập</a>
          <i class="fa-solid fa-caret-right"></i>
          <p class="current">Tạo bài tập</p>
        </div>
        <button type="button" id="confirm-create-assignment">Hoàn tất</button>
      </div>

      <div id="content">
        <div class="info">
          <img src="https://i.pinimg.com/236x/f2/8c/07/f28c074be78a4c506b9c2b5997b965cc.jpg" alt="homework">
          <form action="" method="post">
            <div class="edit-content">
              <textarea name="content" id="edit-content" placeholder="Nhập nội dung câu hỏi và các đáp án ..."></textarea>
            </div>
            <div class="answers">
              <div class="answer" data-answer="A">A</div>
              <div class="answer" data-answer="B">B</div>
              <div class="answer" data-answer="C">C</div>
              <div class="answer" data-answer="D">D</div>
            </div>
            <div class="confirm">
              <button type="button" class="add-question" name="create-question" id="confirm-create-question">Tạo câu hỏi</button>
            </div>
          </form>
          
        </div>

        <div class="questions">
          
        </div>
      </div>
    </form>
  </div>

  <form action="" method="post" class="form-modal" id="create-assignment-modal">
    <div class="modal">
      <div class="title">
        Tạo bài tập
      </div>
      <div class="edit-content">
        <div class="title">
          <p>Nhập tên bài tập</p>
          <input type="text" name="assignment-title" required>
        </div>
        <div class="due-date">
          <p>Hạn nộp</p>
          <input type="datetime-local" name="due-date" id="due-date" required>
        </div>
      </div>
      <div class="confirm">
        <button type="submit" name="confirm-create-assignment">Đồng ý</button>
        <button type="button" class="cancel" id="cancel-button">Quay lại</button>
      </div>
    </div>
  </form>

  <script>
    document.getElementById('confirm-create-assignment').addEventListener('click', function() {
      document.getElementById('create-assignment-modal').style.display = 'flex';
    });

    window.addEventListener('click', function(even) {
      if (even.target === document.getElementById('create-assignment-modal'))
        document.getElementById('create-assignment-modal').style.display = 'none';
    });

    // Khởi tạo datetime picker cho ô input "due-date"
    flatpickr("#due-date", {
      enableTime: true,
      dateFormat: "Y-m-d H:i",
    });

    // Lưu đáp án đúng
    let correctAnswer = "";

    // Chọn đáp án đúng thì sẽ đổi màu đáp án đúng
    document.querySelectorAll('.answer').forEach(answer => {
      answer.addEventListener('click', function() {
        // Bỏ màu các đáp án khác
        document.querySelectorAll('.answer').forEach(ans => ans.classList.remove('selected'));
        // Đổi màu đáp án được chọn
        this.classList.add('selected');
        // Lưu đáp án đúng
        correctAnswer = this.getAttribute('data-answer');
      });
    });

    document.getElementById('confirm-create-question').addEventListener('click', function() {
      // Lấy nội dung câu hỏi
      const questionText = document.getElementById('edit-content').value;
      const questionCount = document.querySelectorAll('.questions .question-text').length + 1;

      // Kiểm tra thông tin
      if (questionText.trim() === "") {
        alert("Vui lòng nhập nội dung câu hỏi.");
        return;
      } 
      if (correctAnswer === "") {
        alert("Vui lòng chọn đáp án đúng.");
        return;
      }
      
      // Tạo phần tử mới cho câu hỏi
      const newQuestion = document.createElement('div');
      newQuestion.classList.add('question-text');
      newQuestion.setAttribute('data-question', questionCount);
      newQuestion.innerHTML = `
          <p class="count">Câu ${questionCount}</p>
          <button type="button" id="edit-question">Chỉnh sửa</button>
          <textarea readonly id="question-text">${questionText}</textarea>
          <div class="answers" style="padding: 0 80px;">
              <button type="button" data-answer="A">A</button>
              <button type="button" data-answer="B">B</button>
              <button type="button" data-answer="C">C</button>
              <button type="button" data-answer="D">D</button>
          </div>
      `;

      // Thêm câu hỏi mới vào phần .questions
      document.querySelector('.questions').appendChild(newQuestion);
      newQuestion.querySelector(`button[data-answer="${correctAnswer}"]`).classList.add('selected');

      // Gửi dữ liệu câu hỏi đến PHP để lưu vào cơ sở dữ liệu
      const formData = new FormData();
      formData.append('content', questionText);
      formData.append('correct_answer', correctAnswer);

      fetch('', {
        method: 'POST',
        body: formData
      }).then(response => response.text())
        .then(data => {
          console.log(data); // Xử lý kết quả trả về nếu cần thiết
        }).catch(error => {
          console.error('Error:', error);
        });

      // Xóa nội dung trong textarea và bỏ màu đáp án sau khi thêm câu hỏi
      document.getElementById('edit-content').value = '';
      document.querySelectorAll('.answer').forEach(ans => ans.classList.remove('selected'));
      correctAnswer = '';
    });

    
  </script>
</body>
</html>
