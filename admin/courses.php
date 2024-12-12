<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// Fetch all courses with lesson count and student count
$courses_sql = "SELECT 
    c.*,
    COUNT(DISTINCT l.id) as lesson_count,
    COUNT(DISTINCT p.student_id) as student_count
    FROM courses c
    LEFT JOIN lessons l ON c.id = l.course_id
    LEFT JOIN progress p ON l.id = p.lesson_id
    GROUP BY c.id
    ORDER BY c.created_at DESC";
$courses = $conn->query($courses_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courses Management - E-Learning Platform</title>
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
                <a href="courses.php" class="active">Courses</a>
                <a href="lessons.php">Lessons</a>
                <a href="students.php">Students</a>
                <a href="quizzes.php">Quizzes</a>
                <a href="logout.php">Logout</a>
            </nav>
        </div>

        <div class="admin-main">
            <div class="admin-header">
                <h1>Courses Management</h1>
                <a href="add_course.php" class="btn-add">Add New Course</a>
            </div>

            <?php if (isset($_SESSION['message'])) { ?>
                <div class="message <?php echo $_SESSION['message_type']; ?>">
                    <?php 
                        echo $_SESSION['message'];
                        unset($_SESSION['message']);
                        unset($_SESSION['message_type']);
                    ?>
                </div>
            <?php } ?>

            <div class="courses-grid">
                <?php if ($courses->num_rows > 0) { ?>
                    <?php while ($course = $courses->fetch_assoc()) { ?>
                        <div class="course-card">
                            <div class="course-header">
                                <h3><?php echo htmlspecialchars($course['title']); ?></h3>
                                <div class="course-actions">
                                    <a href="edit_course.php?id=<?php echo $course['id']; ?>" class="btn-edit">Edit</a>
                                    <form method="POST" action="delete_course.php" class="delete-form" 
                                          onsubmit="return confirm('Are you sure you want to delete this course?');">
                                        <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                        <button type="submit" name="delete_course" class="btn-delete">Delete</button>
                                    </form>
                                </div>
                            </div>
                            <p class="course-description"><?php echo htmlspecialchars($course['description']); ?></p>
                            <div class="course-stats">
                                <span><?php echo $course['lesson_count']; ?> Lessons</span>
                                <span><?php echo $course['student_count']; ?> Students</span>
                                <span>Created: <?php echo date('M d, Y', strtotime($course['created_at'])); ?></span>
                            </div>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <p class="no-courses">No courses available. Click "Add New Course" to create one.</p>
                <?php } ?>
            </div>
        </div>
    </div>
</body>
</html>