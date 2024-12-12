<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['admin_id']) || !isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$lesson_id = $_GET['id'];

// Handle lesson update
if (isset($_POST['update_lesson'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $order = $_POST['order_number'];
    
    $sql = "UPDATE lessons SET title = ?, content = ?, order_number = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssii", $title, $content, $order, $lesson_id);
    
    if ($stmt->execute()) {
        $success = "Lesson updated successfully!";
    } else {
        $error = "Failed to update lesson.";
    }
}

// Fetch lesson details
$sql = "SELECT l.*, c.title as course_title, c.id as course_id 
        FROM lessons l 
        JOIN courses c ON l.course_id = c.id 
        WHERE l.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $lesson_id);
$stmt->execute();
$lesson = $stmt->get_result()->fetch_assoc();

// Fetch quizzes for this lesson
$quiz_sql = "SELECT * FROM quizzes WHERE lesson_id = ? ORDER BY id";
$stmt = $conn->prepare($quiz_sql);
$stmt->bind_param("i", $lesson_id);
$stmt->execute();
$quizzes = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Lesson - E-Learning Platform</title>
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
                <a href="quizzes.php">Quizzes</a>
                <a href="logout.php">Logout</a>
            </nav>
        </div>

        <div class="admin-main">
            <div class="admin-header">
                <h1>Edit Lesson</h1>
                <div class="header-actions">
                    <a href="add_quiz.php?lesson=<?php echo $lesson_id; ?>" class="btn-add">Add Quiz</a>
                    <a href="edit_course.php?id=<?php echo $lesson['course_id']; ?>" class="btn-back">← Back to Course</a>
                </div>
            </div>

            <div class="course-form-container">
                <div class="course-info">
                    Course: <?php echo htmlspecialchars($lesson['course_title']); ?>
                </div>

                <?php if (isset($success)) { ?>
                    <div class="success-message"><?php echo $success; ?></div>
                <?php } ?>
                <?php if (isset($error)) { ?>
                    <div class="error-message"><?php echo $error; ?></div>
                <?php } ?>

                <form method="POST" action="" class="lesson-form">
                    <div class="form-group">
                        <label>Lesson Title</label>
                        <input type="text" name="title" value="<?php echo htmlspecialchars($lesson['title']); ?>" required class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Order Number</label>
                        <input type="number" name="order_number" value="<?php echo $lesson['order_number']; ?>" required class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Lesson Content</label>
                        <textarea name="content" rows="10" required class="form-control content-area"><?php echo htmlspecialchars($lesson['content']); ?></textarea>
                    </div>

                    <button type="submit" name="update_lesson" class="btn-submit">Update Lesson</button>
                </form>

                <!-- Quiz Section -->
                <div class="quizzes-section">
                    <h2>Lesson Quizzes</h2>
                    <div class="quizzes-list">
                        <?php if ($quizzes->num_rows > 0) { ?>
                            <?php while ($quiz = $quizzes->fetch_assoc()) { ?>
                                <div class="quiz-item">
                                    <div class="quiz-info">
                                        <span class="quiz-question"><?php echo htmlspecialchars($quiz['question']); ?></span>
                                    </div>
                                    <div class="quiz-actions">
                                        <a href="edit_quiz.php?id=<?php echo $quiz['id']; ?>" class="btn-edit">Edit</a>
                                        <form method="POST" action="delete_quiz.php" class="delete-form" 
                                              onsubmit="return confirm('Are you sure you want to delete this quiz?');">
                                            <input type="hidden" name="quiz_id" value="<?php echo $quiz['id']; ?>">
                                            <button type="submit" class="btn-delete">Delete</button>
                                        </form>
                                    </div>
                                </div>
                            <?php } ?>
                        <?php } else { ?>
                            <p class="no-quizzes">No quizzes added yet.</p>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>