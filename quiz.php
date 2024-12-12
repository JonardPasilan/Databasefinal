<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['lesson'])) {
    header("Location: dashboard.php");
    exit();
}

$lesson_id = $_GET['lesson'];
$student_id = $_SESSION['user_id'];

// Fetch quiz questions
$quiz_sql = "SELECT * FROM quizzes WHERE lesson_id = ?";
$stmt = $conn->prepare($quiz_sql);
$stmt->bind_param("i", $lesson_id);
$stmt->execute();
$quizzes = $stmt->get_result();

// Handle quiz submission
if (isset($_POST['submit_quiz'])) {
    $score = 0;
    $total_questions = 0;
    
    foreach ($_POST['answers'] as $quiz_id => $answer) {
        $check_sql = "SELECT correct_answer FROM quizzes WHERE id = ?";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("i", $quiz_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if ($result['correct_answer'] === $answer) {
            $score++;
        }
        $total_questions++;
    }
    
    // Calculate percentage
    $percentage = ($score / $total_questions) * 100;
    
    // Update progress
    $progress_sql = "INSERT INTO progress (student_id, lesson_id, quiz_id, score, completed) 
                    VALUES (?, ?, NULL, ?, TRUE) 
                    ON DUPLICATE KEY UPDATE score = ?, completed = TRUE";
    $stmt = $conn->prepare($progress_sql);
    $stmt->bind_param("iiii", $student_id, $lesson_id, $score, $score);
    $stmt->execute();
    
    $_SESSION['quiz_result'] = [
        'score' => $score,
        'total' => $total_questions,
        'percentage' => $percentage
    ];
    
    header("Location: quiz.php?lesson=" . $lesson_id . "&result=1");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lesson Quiz - E-Learning Platform</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/quiz.css">
</head>
<body>
    <div class="container">
        <?php if (isset($_GET['result']) && isset($_SESSION['quiz_result'])) { ?>
            <div class="quiz-result">
                <h2>Quiz Results</h2>
                <div class="result-details">
                    <p>Score: <?php echo $_SESSION['quiz_result']['score']; ?> out of <?php echo $_SESSION['quiz_result']['total']; ?></p>
                    <p>Percentage: <?php echo round($_SESSION['quiz_result']['percentage'], 2); ?>%</p>
                </div>
                <a href="course.php?id=<?php echo $course_id; ?>&lesson=<?php echo $lesson_id; ?>" class="btn-return">Return to Lesson</a>
            </div>
        <?php } else { ?>
            <div class="quiz-container">
                <h2>Lesson Quiz</h2>
                <form method="POST" action="" class="quiz-form">
                    <?php 
                    $question_num = 1;
                    while ($quiz = $quizzes->fetch_assoc()) { 
                        $options = json_decode($quiz['options'], true);
                    ?>
                        <div class="quiz-question">
                            <h3>Question <?php echo $question_num; ?></h3>
                            <p><?php echo htmlspecialchars($quiz['question']); ?></p>
                            <div class="options">
                                <?php foreach ($options as $option) { ?>
                                    <label class="option">
                                        <input type="radio" name="answers[<?php echo $quiz['id']; ?>]" value="<?php echo htmlspecialchars($option); ?>" required>
                                        <?php echo htmlspecialchars($option); ?>
                                    </label>
                                <?php } ?>
                            </div>
                        </div>
                    <?php 
                        $question_num++;
                    } 
                    ?>
                    <button type="submit" name="submit_quiz" class="btn-submit">Submit Quiz</button>
                </form>
            </div>
        <?php } ?>
    </div>
</body>
</html>