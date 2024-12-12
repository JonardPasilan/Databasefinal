<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// Fetch all available courses for the dropdown
$courses_sql = "SELECT id, title FROM courses ORDER BY title";
$courses = $conn->query($courses_sql);

// Fetch all lessons with course info
$lessons_sql = "SELECT 
    l.*,
    c.title as course_title,
    COUNT(q.id) as quiz_count
    FROM lessons l
    JOIN courses c ON l.course_id = c.id
    LEFT JOIN quizzes q ON l.id = q.lesson_id
    GROUP BY l.id
    ORDER BY c.title, l.order_number";
$lessons = $conn->query($lessons_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lessons Management - E-Learning Platform</title>
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
                <a href="lessons.php" class="active">Lessons</a>
                <a href="students.php">Students</a>
                <a href="quizzes.php">Quizzes</a>
                <a href="logout.php">Logout</a>
            </nav>
        </div>

        <div class="admin-main">
            <div class="admin-header">
                <h1>Lesson Management</h1>
                <div class="header-actions">
                    <!-- Add course selection dropdown -->
                    <form action="add_lesson.php" method="GET" class="add-lesson-form">
                        <select name="course" required>
                            <option value="">Select Course</option>
                            <?php while ($course = $courses->fetch_assoc()) { ?>
                                <option value="<?php echo $course['id']; ?>">
                                    <?php echo htmlspecialchars($course['title']); ?>
                                </option>
                            <?php } ?>
                        </select>
                        <button type="submit" class="btn-add">Add New Lesson</button>
                    </form>
                </div>
            </div>

            <div class="lessons-list">
                <?php if ($lessons && $lessons->num_rows > 0) { ?>
                    <?php while ($lesson = $lessons->fetch_assoc()) { ?>
                        <div class="lesson-item">
                            <div class="lesson-info">
                                <h3><?php echo htmlspecialchars($lesson['title']); ?></h3>
                                <p>Course: <?php echo htmlspecialchars($lesson['course_title']); ?></p>
                                <p>Order: <?php echo $lesson['order_number']; ?></p>
                                <p>Quizzes: <?php echo $lesson['quiz_count']; ?></p>
                            </div>
                            <div class="lesson-actions">
                                <a href="edit_lesson.php?id=<?php echo $lesson['id']; ?>" class="btn-edit">Edit</a>
                                <form method="POST" action="delete_lesson.php" class="delete-form" 
                                      onsubmit="return confirm('Are you sure you want to delete this lesson?');">
                                    <input type="hidden" name="lesson_id" value="<?php echo $lesson['id']; ?>">
                                    <button type="submit" class="btn-delete">Delete</button>
                                </form>
                            </div>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <div class="no-items">No lessons found. Create your first lesson!</div>
                <?php } ?>
            </div>
        </div>
    </div>
</body>
</html>