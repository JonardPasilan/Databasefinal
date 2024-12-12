<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// Fetch all quizzes with lesson and course info
$quizzes_sql = "SELECT 
    q.*,
    l.title as lesson_title,
    c.title as course_title
    FROM quizzes q
    JOIN lessons l ON q.lesson_id = l.id
    JOIN courses c ON l.course_id = c.id
    ORDER BY c.title, l.order_number";
$quizzes = $conn->query($quizzes_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quizzes Management - E-Learning Platform</title>
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
                <h1>Quiz Management</h1>
            </div>

            <div class="quizzes-list">
                <?php while ($quiz = $quizzes->fetch_assoc()) { ?>
                    <div class="quiz-item">
                        <div class="quiz-info">
                            <h3><?php echo htmlspecialchars($quiz['question']); ?></h3>
                            <p>Course: <?php echo htmlspecialchars($quiz['course_title']); ?></p>
                            <p>Lesson: <?php echo htmlspecialchars($quiz['lesson_title']); ?></p>
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
            </div>
        </div>
    </div>
</body>
</html>