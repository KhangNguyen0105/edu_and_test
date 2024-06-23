<?php
  session_start();

  if (isset($_SESSION['user_id'])) {
    $conn = mysqli_connect("localhost", "root", "", "edu_and_test");
    if (!$conn) die("Kết nối không thành công: " . mysqli_connect_error());

    $course_id = $_GET['course_id'];
    $assignment_id = $_GET['assignment_id'];

    // Lấy thông tin bài tập
    $get_assignment_info_query = "SELECT * FROM assignments WHERE assignment_id = ?";
    $get_assignment_stmt = mysqli_prepare($conn, $get_assignment_info_query);
    mysqli_stmt_bind_param($get_assignment_stmt, "s", $assignment_id);
    mysqli_stmt_execute($get_assignment_stmt);
    $assignment_info = mysqli_fetch_assoc(mysqli_stmt_get_result($get_assignment_stmt));
    
    // Đếm số câu hỏi trong bài tập
    $count_questions_query = "SELECT question_id FROM assignment_questions WHERE assignment_id = ?";
    $count_questions_stmt = mysqli_prepare($conn, $count_questions_query);
    mysqli_stmt_bind_param($count_questions_stmt, "s", $assignment_id);
    mysqli_stmt_execute($count_questions_stmt);
    $result = mysqli_stmt_get_result($count_questions_stmt);
    $questions = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($count_questions_stmt);


    // Truy vấn thông tin bài tập
    $get_questions_query = "
        SELECT q.question_id, q.content 
        FROM questions q
        JOIN assignment_questions aq ON q.question_id = aq.question_id
        WHERE aq.assignment_id = ?
    ";
    $get_questions_stmt = mysqli_prepare($conn, $get_questions_query);
    mysqli_stmt_bind_param($get_questions_stmt, "s", $assignment_id);
    mysqli_stmt_execute($get_questions_stmt);
    $result = mysqli_stmt_get_result($get_questions_stmt);

    $questions = [];
    while ($row = mysqli_fetch_assoc($result))
      $questions[] = $row;


    // Xử lý khi nhấn nút xác nhận thoát
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm-leave']))
      header("Location: list.php?course_id=" . $course_id);

    mysqli_close($conn);
  }
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../../asset/css/class-style.css">
  <link rel="stylesheet" href="../../asset/icon/fontawesome-free-6.5.1-web/css/all.min.css">
  <title>Edu & Test</title>
</head>
<body>
  <form action="" method="post" id="test">
    <div class="assignment-info">
      <div class="name">
        <p><?php echo $assignment_info['title'] ?></p>
      </div>

      <div class="questions">
        <?php
          if (!empty($questions)) {
            $count = 1;
            foreach ($questions as $question) {
              echo '<div class="cell" data-question="' . $count . '">' . $count . '</div>';
              $count++;
            }
          }
        ?>
      </div>

      <div class="cancel-submit">
        <button id="leave-btn">Rời khỏi</button>
        <button type="submit">Nộp bài</button>
      </div>
    </div>
    <div class="content">
      <div class="question-text">
        <?php
          if (!empty($questions)) {
            $count = 1;
            foreach ($questions as $question) {
              $question_text = htmlspecialchars($question['content']);
              echo '
                  <div class="question-text" data-question="' . $count . '">
                      <p class="count">Câu ' . $count . '</p>
                      <textarea readonly id="question-text">' . $question_text . '</textarea>
                      <div class="answers">
                          <button type="button" data-answer="A">A</button>
                          <button type="button" data-answer="B">B</button>
                          <button type="button" data-answer="C">C</button>
                          <button type="button" data-answer="D">D</button>
                      </div>
                  </div>
              ';
              $count++;
            }
          }
        ?>
      </div>
    </div>
  </form>

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
        <button type="submit" name="confirm-leave" id="confirm-leave-button">Đồng ý</button>
        <button type="button" class="cancel" id="cancel-button">Thoát</button>
      </div>
    </div>
  </form>

  <script>
    // Hiển thị modal xác nhận thoát
    const leave_btn = document.getElementById('leave-btn');
    const leave_modal = document.getElementById('leave-modal');
    const cancel_leave = document.getElementById('cancel-button');

    leave_btn.addEventListener('click', function(even) {
      even.preventDefault();
      leave_modal.style.display = 'flex';
    });

    // Đóng modal
    const close_modal = document.getElementById('close-modal');
    close_modal.addEventListener('click', function() {
      leave_modal.style.display = 'none';
    });

    cancel_leave.addEventListener('click', function() {
      leave_modal.style.display = 'none';
    });

    window.addEventListener('click', function(even) {
      if (even.target === leave_modal)
        leave_modal.style.display = 'none'
    });

    document.addEventListener('DOMContentLoaded', function() {
      const questions = document.querySelectorAll('.question-text');
      const cells = document.querySelectorAll('.cell');

      cells.forEach(cell => {
        const questionNumber = cell.getAttribute('data-question');
        const selectedAnswer = localStorage.getItem(`question_${questionNumber}`);

        if (selectedAnswer) {
          cell.classList.add('selected');
          document.querySelector(`.question-text[data-question="${questionNumber}"] button[data-answer="${selectedAnswer}"]`).classList.add('selected');
        }

        cell.addEventListener('click', function() {
          questions.forEach(question => {
            question.style.display = 'none';
          });
          document.querySelector(`.question-text[data-question="${questionNumber}"]`).style.display = 'block';
        });
      });

      const answerButtons = document.querySelectorAll('.answers button');
      answerButtons.forEach(button => {
        button.addEventListener('click', function() {
          const questionNumber = button.closest('.question-text').getAttribute('data-question');
          const answer = button.getAttribute('data-answer');

          localStorage.setItem(`question_${questionNumber}`, answer);

          button.parentNode.querySelectorAll('button').forEach(btn => {
            btn.classList.remove('selected');
          });
          button.classList.add('selected');

          document.querySelector(`.cell[data-question="${questionNumber}"]`).classList.add('selected');
        });
      });
    });

    
  </script>

</body>
</html>