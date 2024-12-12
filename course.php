<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Handle course deletion
if (isset($_POST['delete_course'])) {
    $course_id = $_POST['course_id'];
    $delete_sql = "DELETE FROM courses WHERE id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $course_id);
    if ($stmt->execute()) {
        $success = "Course deleted successfully!";
    } else {
        $error = "Failed to delete course.";
    }
}

// Fetch all courses
$courses_sql = "SELECT 
    c.*,
    COUNT(DISTINCT l.id) as lesson_count,
    COUNT(DISTINCT s.id) as student_count
    FROM courses c
    LEFT JOIN lessons l ON c.id = l.course_id
    LEFT JOIN progress p ON l.id = p.lesson_id
    LEFT JOIN students s ON p.student_id = s.id
    GROUP BY c.id
    ORDER BY c.created_at DESC";
$courses = $conn->query($courses_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Management - E-Learning Platform</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar (same as dashboard) -->
        <div class="admin-sidebar">
            <!-- ... sidebar content ... -->
        </div>

        <div class="admin-main">
            <div class="admin-header">
                <h1>Course Management</h1>
                <a href="add_course.php" class="btn-add">Add New Course</a>
            </div>

            <?php if (isset($success)) { ?>
                <div class="success-message"><?php echo $success; ?></div>
            <?php } ?>
            <?php if (isset($error)) { ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php } ?>

            <div class="courses-grid">
                <?php while ($course = $courses->fetch_assoc()) { ?>
                    <div class="course-card">
                        <div class="course-header">
                            <h3><?php echo htmlspecialchars($course['title']); ?></h3>
                            <div class="course-actions">
                                <a href="edit_course.php?id=<?php echo $course['id']; ?>" class="btn-edit">Edit</a>
                                <form method="POST" action="" class="delete-form" onsubmit="return confirm('Are you sure you want to delete this course?');">
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
            </div>
        </div>
    </div>
</body>
</html>