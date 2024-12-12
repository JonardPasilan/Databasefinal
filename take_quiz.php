<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['lesson_id'])) {
    header("Location: login.php");
    exit();
}

$lesson_id = $_GET['lesson_id'];

// Fetch lesson and quiz details
$quiz_sql = "SELECT q.*, l.title as lesson_title 
             FROM quizzes q 
             JOIN lessons l ON q.lesson_id = l.id 
             WHERE q.lesson_id = ?";
$stmt = $conn->prepare($quiz_sql);
$stmt->bind_param("i", $lesson_id);
$stmt->execute();
$quizzes = $stmt->get_result();

// Handle quiz submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $score = 0;
    $total_questions = 0;
    
    foreach ($_POST['answers'] as $quiz_id => $answer) {
        $check_sql = "SELECT correct_answer FROM quizzes WHERE id = ?";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("i", $quiz_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if ($result['correct_answer'] == $answer) {
            $score++;
        }
        $total_questions++;
    }
    
    // Calculate percentage
    $percentage = ($score / $total_questions) * 100;
    
    // Update progress
    $update_sql = "INSERT INTO progress (student_id, lesson_id, score, completed) 
                  VALUES (?, ?, ?, 1) 
                  ON DUPLICATE KEY UPDATE score = ?, completed = 1";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("iiii", $_SESSION['user_id'], $lesson_id, $score, $score);
    $stmt->execute();
    
    // Store results in session for display
    $_SESSION['quiz_results'] = [
        'score' => $score,
        'total' => $total_questions,
        'percentage' => $percentage
    ];
    
    header("Location: quiz_results.php?lesson_id=" . $lesson_id);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lesson Quiz - E-Learning Platform</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <h1>Lesson Quiz</h1>
            <div class="nav-right">
                <a href="lesson.php?id=<?php echo $lesson_id; ?>">Back to Lesson</a>
                <a href="dashboard.php">Dashboard</a>
            </div>
        </div>
    </nav>

    <div class="container quiz-container">
        <?php if ($quizzes->num_rows > 0) { ?>
            <form method="POST" class="quiz-form">
                <?php 
                $question_number = 1;
                while ($quiz = $quizzes->fetch_assoc()) { 
                    $options = json_decode($quiz['options'], true);
                ?>
                    <div class="quiz-question">
                        <h3>Question <?php echo $question_number; ?></h3>
                        <p><?php echo htmlspecialchars($quiz['question']); ?></p>
                        
                        <div class="options-list">
                            <?php foreach ($options as $index => $option) { ?>
                                <div class="option-item">
                                    <input type="radio" 
                                           name="answers[<?php echo $quiz['id']; ?>]" 
                                           value="<?php echo $index; ?>" 
                                           id="q<?php echo $quiz['id']; ?>o<?php echo $index; ?>"
                                           required>
                                    <label for="q<?php echo $quiz['id']; ?>o<?php echo $index; ?>">
                                        <?php echo htmlspecialchars($option); ?>
                                    </label>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                <?php 
                    $question_number++;
                } 
                ?>
                
                <button type="submit" class="btn-submit">Submit Answers</button>
            </form>
        <?php } else { ?>
            <div class="no-quiz">
                <p>No quiz questions available for this lesson.</p>
                <a href="lesson.php?id=<?php echo $lesson_id; ?>" class="btn-back">Back to Lesson</a>
            </div>
        <?php } ?>
    </div>
</body>
</html>