<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id']) || !isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$quiz_id = $_GET['id'];

// Fetch quiz details
$quiz_sql = "SELECT q.*, l.title as lesson_title, l.id as lesson_id, c.title as course_title 
             FROM quizzes q
             JOIN lessons l ON q.lesson_id = l.id
             JOIN courses c ON l.course_id = c.id
             WHERE q.id = ?";
$stmt = $conn->prepare($quiz_sql);
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$quiz = $stmt->get_result()->fetch_assoc();

if (isset($_POST['update_quiz'])) {
    $question = $_POST['question'];
    $options = json_encode($_POST['options']);
    $correct_answer = $_POST['correct_answer'];
    
    $sql = "UPDATE quizzes SET question = ?, options = ?, correct_answer = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $question, $options, $correct_answer, $quiz_id);
    
    if ($stmt->execute()) {
        $success = "Quiz updated successfully!";
        // Refresh quiz data
        $stmt = $conn->prepare($quiz_sql);
        $stmt->bind_param("i", $quiz_id);
        $stmt->execute();
        $quiz = $stmt->get_result()->fetch_assoc();
    } else {
        $error = "Failed to update quiz.";
    }
}

$options = json_decode($quiz['options'], true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Quiz Question - E-Learning Platform</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <div class="admin-sidebar">
            <!-- ... sidebar content ... -->
        </div>

        <div class="admin-main">
            <div class="admin-header">
                <h1>Edit Quiz Question</h1>
                <div class="header-actions">
                    <a href="edit_lesson.php?id=<?php echo $quiz['lesson_id']; ?>" class="btn-back">← Back to Lesson</a>
                </div>
            </div>

            <div class="course-form-container">
                <div class="course-info">
                    Course: <?php echo htmlspecialchars($quiz['course_title']); ?><br>
                    Lesson: <?php echo htmlspecialchars($quiz['lesson_title']); ?>
                </div>

                <?php if (isset($success)) { ?>
                    <div class="success-message"><?php echo $success; ?></div>
                <?php } ?>
                <?php if (isset($error)) { ?>
                    <div class="error-message"><?php echo $error; ?></div>
                <?php } ?>

                <form method="POST" action="" class="quiz-form">
                    <div class="form-group">
                        <label>Question</label>
                        <input type="text" name="question" value="<?php echo htmlspecialchars($quiz['question']); ?>" required>
                    </div>

                    <div class="options-container">
                        <label>Options</label>
                        <?php foreach ($options as $index => $option) { ?>
                            <div class="option-group">
                                <input type="text" name="options[]" value="<?php echo htmlspecialchars($option); ?>" required>
                                <input type="radio" name="correct_answer" value="<?php echo $index; ?>" 
                                       <?php echo ($quiz['correct_answer'] == $index) ? 'checked' : ''; ?> required>
                                <label class="radio-label">Correct Answer</label>
                            </div>
                        <?php } ?>
                    </div>

                    <button type="submit" name="update_quiz" class="btn-submit">Update Quiz Question</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>