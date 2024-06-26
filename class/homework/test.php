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

  // Truy vấn thông tin bài tập
  $get_questions_query = "
      SELECT q.question_id, q.content, q.correct_answer, q.max_score
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

  // Xử lý khi người dùng nộp bài
  if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajax_submit'])) {
    $submitted_answers = json_decode($_POST['answers'], true);
    $total_score = 0;
    $correct_count = 0;
  
    foreach ($questions as $question) {
      $question_id = $question['question_id'];
      $correct_answer = $question['correct_answer'];
      if (isset($submitted_answers[$question_id]) && $submitted_answers[$question_id] === $correct_answer) {
        $total_score += $question['max_score'];
        $correct_count++;
      }
    }
    $incorrect_count = count($questions) - $correct_count;
  
    $user_id = $_SESSION['user_id'];
    $submission_date = date('Y-m-d H:i:s');
    $insert_grade_query = "INSERT INTO grades (assignment_id, user_id, score, submission_date) VALUES (?, ?, ?, ?)";
    $insert_grade_stmt = mysqli_prepare($conn, $insert_grade_query);
    mysqli_stmt_bind_param($insert_grade_stmt, "ssds", $assignment_id, $user_id, $total_score, $submission_date);
    mysqli_stmt_execute($insert_grade_stmt);
    mysqli_stmt_close($insert_grade_stmt);
  
    echo json_encode([
      'total_score' => $total_score,
      'correct_count' => $correct_count,
      'incorrect_count' => $incorrect_count,
      'submission_date' => $submission_date
    ]);
    exit;
  }
  
  if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm-leave'])) {
    header("Location: list.php?course_id=" . $course_id);
  }


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
        <button type="submit" id="submit">Nộp bài</button>
      </div>
    </div>
    <div class="content">
      <div class="question-text">
        <?php
          if (!empty($questions)) {
            $count = 1;
            foreach ($questions as $question) {
              $question_text = htmlspecialchars($question['content']);
              $question_id = $question['question_id'];
              echo '
                <div class="question-text" data-question="' . $count . '" data-question-id="' . $question_id . '">
                    <p class="count">Câu ' . $count . '</p>
                    <textarea readonly id="question-text">' . $question_text . '</textarea>
                    <div class="answers">
                        <button type="button" name="answer_' . $question_id . '" data-answer="A">A</button>
                        <button type="button" name="answer_' . $question_id . '" data-answer="B">B</button>
                        <button type="button" name="answer_' . $question_id . '" data-answer="C">C</button>
                        <button type="button" name="answer_' . $question_id . '" data-answer="D">D</button>
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
  

  <div class="result-modal form-modal" id="result-modal">
    <div class="wrapper">
    <div class="score">
      <p>Điểm số:</p>
      <p></p>
    </div>
    <div class="result-info">
      <div class="item">
        <p>Ngày nộp:</p>
        <p></p>
      </div>
      <div class="item">
        <p>Số câu đúng:</p>
        <p></p>
      </div>
      <div class="item">
        <p>Số câu sai:</p>
        <p></p>
      </div>
    </div>
    <div class="confirm">
      <a href="list.php?course_id=<?php echo $course_id?>">Đồng ý</a>
    </div>
  </div>

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

      localStorage.clear();

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
          const questionId = button.closest('.question-text').dataset.questionId;
          const answer = button.getAttribute('data-answer');

          // Lưu câu trả lời vào localStorage
          localStorage.setItem(`question_${questionNumber}`, answer);

          button.parentNode.querySelectorAll('button').forEach(btn => {
            btn.classList.remove('selected');
          });
          button.classList.add('selected');

          document.querySelector(`.cell[data-question="${questionNumber}"]`).classList.add('selected');
        });
      });


      const submitButton = document.getElementById('submit');
      submitButton.addEventListener('click', function(event) {
        event.preventDefault();

        const answers = {};
        answerButtons.forEach(button => {
          const questionId = button.closest('.question-text').dataset.questionId;
          if (button.classList.contains('selected')) {
            answers[questionId] = button.getAttribute('data-answer');
          }
        });

        fetch('test.php?course_id=<?php echo $course_id; ?>&assignment_id=<?php echo $assignment_id; ?>', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: new URLSearchParams({
            ajax_submit: true,
            answers: JSON.stringify(answers)
          })
        })
        .then(response => response.json())
        .then(data => {
          document.querySelector('.result-modal .score p:last-child').innerText = data.total_score + '/10';
          document.querySelector('.result-modal .result-info .item:nth-child(1) p:last-child').innerText = data.submission_date;
          document.querySelector('.result-modal .result-info .item:nth-child(2) p:last-child').innerText = data.correct_count;
          document.querySelector('.result-modal .result-info .item:nth-child(3) p:last-child').innerText = data.incorrect_count;
          document.getElementById('result-modal').style.display = 'flex';
        });
      });
    });

  </script>

</body>
</html>
