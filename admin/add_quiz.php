<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// Fetch all lessons for the dropdown
$lessons_sql = "SELECT l.id, l.title, c.title as course_title 
                FROM lessons l 
                JOIN courses c ON l.course_id = c.id 
                ORDER BY c.title, l.order_number";
$lessons = $conn->query($lessons_sql);

if (isset($_POST['add_quiz'])) {
    $lesson_id = $_POST['lesson_id'];
    $question = $_POST['question'];
    $correct_answer = $_POST['correct_answer'];
    $options = array(
        $_POST['option1'],
        $_POST['option2'],
        $_POST['option3'],
        $_POST['option4']
    );
    
    // Convert options array to JSON
    $options_json = json_encode($options);
    
    $sql = "INSERT INTO quizzes (lesson_id, question, options, correct_answer) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isss", $lesson_id, $question, $options_json, $correct_answer);
    
    if ($stmt->execute()) {
        $success = "Quiz added successfully!";
    } else {
        $error = "Failed to add quiz.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Quiz - E-Learning Platform</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <div class="admin-sidebar">
            <div class="sidebar-header">
                <h2>Admin Panel</h2>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php">Dashboard</a>
                <a href="courses.php">Courses</a>
                <a href="lessons.php">Lessons</a>
                <a href="students.php">Students</a>
                <a href="quizzes.php" class="active">Quizzes</a>
                <a href="logout.php">Logout</a>
            </nav>
        </div>

        <div class="admin-main">
            <div class="admin-header">
                <h1>Add New Quiz</h1>
                <div class="header-actions">
                    <a href="quizzes.php" class="btn-back">← Back to Quizzes</a>
                </div>
            </div>

            <div class="quiz-form-container">
                <?php if (isset($success)) { ?>
                    <div class="success-message"><?php echo $success; ?></div>
                <?php } ?>
                <?php if (isset($error)) { ?>
                    <div class="error-message"><?php echo $error; ?></div>
                <?php } ?>

                <form method="POST" action="" class="quiz-form">
                    <div class="form-group">
                        <label>Select Lesson</label>
                        <select name="lesson_id" required class="form-control">
                            <option value="">Choose a lesson...</option>
                            <?php while ($lesson = $lessons->fetch_assoc()) { ?>
                                <option value="<?php echo $lesson['id']; ?>">
                                    <?php echo htmlspecialchars($lesson['course_title'] . ' - ' . $lesson['title']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Question</label>
                        <input type="text" name="question" required class="form-control">
                    </div>

                    <div class="options-container">
                        <label>Options</label>
                        <?php for ($i = 1; $i <= 4; $i++) { ?>
                            <div class="option-group">
                                <input type="text" name="option<?php echo $i; ?>" 
                                       placeholder="Option <?php echo $i; ?>" required 
                                       class="form-control">
                                <input type="radio" name="correct_answer" value="<?php echo $i-1; ?>" 
                                       required>
                                <label class="radio-label">Correct Answer</label>
                            </div>
                        <?php } ?>
                    </div>

                    <button type="submit" name="add_quiz" class="btn-submit">Create Quiz</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>