<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id']) || !isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$course_id = $_GET['id'];

// Handle course update
if (isset($_POST['update_course'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    
    $sql = "UPDATE courses SET title = ?, description = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $title, $description, $course_id);
    
    if ($stmt->execute()) {
        $success = "Course updated successfully!";
    } else {
        $error = "Failed to update course.";
    }
}

// Fetch course details
$sql = "SELECT * FROM courses WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();

// Fetch course lessons
$lessons_sql = "SELECT * FROM lessons WHERE course_id = ? ORDER BY order_number";
$stmt = $conn->prepare($lessons_sql);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$lessons = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Course - E-Learning Platform</title>
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
                <h1>Edit Course</h1>
                <div class="header-actions">
                    <a href="add_lesson.php?course=<?php echo $course_id; ?>" class="btn-add">Add Lesson</a>
                    <a href="courses.php" class="btn-back">← Back to Courses</a>
                </div>
            </div>

            <div class="course-form-container">
                <?php if (isset($success)) { ?>
                    <div class="success-message"><?php echo $success; ?></div>
                <?php } ?>
                <?php if (isset($error)) { ?>
                    <div class="error-message"><?php echo $error; ?></div>
                <?php } ?>

                <form method="POST" action="" class="course-form">
                    <div class="form-group">
                        <label>Course Title</label>
                        <input type="text" name="title" value="<?php echo htmlspecialchars($course['title']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Course Description</label>
                        <textarea name="description" rows="5" required><?php echo htmlspecialchars($course['description']); ?></textarea>
                    </div>

                    <button type="submit" name="update_course" class="btn-submit">Update Course</button>
                </form>

                <div class="lessons-section">
                    <h2>Course Lessons</h2>
                    <div class="lessons-list">
                        <?php if ($lessons->num_rows > 0) { ?>
                            <?php while ($lesson = $lessons->fetch_assoc()) { ?>
                                <div class="lesson-item">
                                    <div class="lesson-info">
                                        <span class="lesson-order"><?php echo $lesson['order_number']; ?></span>
                                        <span class="lesson-title"><?php echo htmlspecialchars($lesson['title']); ?></span>
                                    </div>
                                    <div class="lesson-actions">
                                        <a href="edit_lesson.php?id=<?php echo $lesson['id']; ?>" class="btn-edit">Edit</a>
                                        <form method="POST" action="delete_lesson.php" class="delete-form" onsubmit="return confirm('Are you sure you want to delete this lesson?');">
                                            <input type="hidden" name="lesson_id" value="<?php echo $lesson['id']; ?>">
                                            <button type="submit" class="btn-delete">Delete</button>
                                        </form>
                                    </div>
                                </div>
                            <?php } ?>
                        <?php } else { ?>
                            <p class="no-lessons">No lessons added yet.</p>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>