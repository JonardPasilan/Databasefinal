<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in (changed from admin_id to user_id)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user's courses and progress
$sql = "SELECT c.*, 
        (SELECT COUNT(*) FROM lessons WHERE course_id = c.id) as total_lessons,
        (SELECT COUNT(*) FROM progress p 
         JOIN lessons l ON p.lesson_id = l.id 
         WHERE l.course_id = c.id AND p.student_id = ? AND p.completed = 1) as completed_lessons
        FROM courses c";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$courses = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Learning Platform - Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <h1>E-Learning Platform</h1>
            <div class="nav-right">
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></span>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="dashboard-grid">
            <div class="dashboard-sidebar">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="dashboard.php" class="active">My Courses</a></li>
                    <li><a href="progress.php">My Progress</a></li>
                    <li><a href="profile.php">Profile</a></li>
                </ul>
            </div>

            <div class="dashboard-main">
                <h2>My Courses</h2>
                <div class="courses-grid">
                    <?php while ($course = $courses->fetch_assoc()) { ?>
                        <div class="course-card">
                            <h3><?php echo htmlspecialchars($course['title']); ?></h3>
                            <p><?php echo htmlspecialchars($course['description']); ?></p>
                            <div class="progress-bar">
                                <?php 
                                $progress = ($course['total_lessons'] > 0) 
                                    ? ($course['completed_lessons'] / $course['total_lessons']) * 100 
                                    : 0;
                                ?>
                                <div class="progress" style="width: <?php echo $progress; ?>%"></div>
                            </div>
                            <p class="progress-text">
                                <?php echo $course['completed_lessons']; ?>/<?php echo $course['total_lessons']; ?> lessons completed
                            </p>
                            <a href="view_course.php?id=<?php echo $course['id']; ?>" class="btn-continue">Continue Learning</a>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>