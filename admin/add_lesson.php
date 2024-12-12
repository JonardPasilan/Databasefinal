<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['admin_id']) || !isset($_GET['course'])) {
    header("Location: index.php");
    exit();
}

$course_id = $_GET['course'];

// Get the next order number for the lesson
$order_sql = "SELECT MAX(order_number) as max_order FROM lessons WHERE course_id = ?";
$stmt = $conn->prepare($order_sql);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$next_order = ($result['max_order'] ?? 0) + 1;

// Fetch course details for reference
$course_sql = "SELECT title FROM courses WHERE id = ?";
$stmt = $conn->prepare($course_sql);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();

if (isset($_POST['add_lesson'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $order = $_POST['order_number'];
    
    $sql = "INSERT INTO lessons (course_id, title, content, order_number) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issi", $course_id, $title, $content, $order);
    
    if ($stmt->execute()) {
        header("Location: edit_course.php?id=$course_id&success=lesson_added");
        exit();
    } else {
        $error = "Failed to create lesson.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Lesson - E-Learning Platform</title>
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
                <h1>Add New Lesson</h1>
                <div class="header-actions">
                    <a href="edit_course.php?id=<?php echo $course_id; ?>" class="btn-back">← Back to Course</a>
                </div>
            </div>

            <div class="course-form-container">
                <div class="course-info">
                    Adding lesson to course: <strong><?php echo htmlspecialchars($course['title']); ?></strong>
                </div>

                <?php if (isset($error)) { ?>
                    <div class="error-message"><?php echo $error; ?></div>
                <?php } ?>

                <form method="POST" action="" class="lesson-form">
                    <div class="form-group">
                        <label>Lesson Title</label>
                        <input type="text" name="title" required class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Order Number</label>
                        <input type="number" name="order_number" value="<?php echo $next_order; ?>" required class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Lesson Content</label>
                        <textarea name="content" rows="10" required class="form-control content-area"></textarea>
                    </div>

                    <button type="submit" name="add_lesson" class="btn-submit">Create Lesson</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>