<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

if (isset($_POST['add_course'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    
    $sql = "INSERT INTO courses (title, description) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $title, $description);
    
    if ($stmt->execute()) {
        $course_id = $conn->insert_id;
        header("Location: edit_course.php?id=$course_id&success=created");
        exit();
    } else {
        $error = "Failed to create course.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Course - E-Learning Platform</title>
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
                <h1>Add New Course</h1>
                <a href="courses.php" class="btn-back">← Back to Courses</a>
            </div>

            <div class="course-form-container">
                <?php if (isset($error)) { ?>
                    <div class="error-message"><?php echo $error; ?></div>
                <?php } ?>

                <form method="POST" action="" class="course-form">
                    <div class="form-group">
                        <label>Course Title</label>
                        <input type="text" name="title" required>
                    </div>

                    <div class="form-group">
                        <label>Course Description</label>
                        <textarea name="description" rows="5" required></textarea>
                    </div>

                    <button type="submit" name="add_course" class="btn-submit">Create Course</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>