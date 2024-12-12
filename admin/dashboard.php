<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch statistics
$stats_sql = "SELECT 
    (SELECT COUNT(*) FROM courses) as total_courses,
    (SELECT COUNT(*) FROM students) as total_students,
    (SELECT COUNT(*) FROM lessons) as total_lessons";
$stats = $conn->query($stats_sql)->fetch_assoc();

// Fetch recent activities
$activities_sql = "SELECT 
    s.name as student_name,
    c.title as course_title,
    l.title as lesson_title,
    p.completed_at
    FROM progress p
    JOIN students s ON p.student_id = s.id
    JOIN lessons l ON p.lesson_id = l.id
    JOIN courses c ON l.course_id = c.id
    ORDER BY p.completed_at DESC
    LIMIT 5";
$activities = $conn->query($activities_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - E-Learning Platform</title>
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
                <a href="dashboard.php" class="active">Dashboard</a>
                <a href="courses.php">Courses</a>
                <a href="lessons.php">Lessons</a>
                <a href="students.php">Students</a>
                <a href="quizzes.php">Quizzes</a>
                <a href="logout.php">Logout</a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="admin-main">
            <div class="admin-header">
                <h1>Dashboard</h1>
                <div class="admin-user">
                    Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?>
                </div>
            </div>

            <div class="dashboard-stats">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $stats['total_courses']; ?></div>
                    <div class="stat-label">Total Courses</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $stats['total_students']; ?></div>
                    <div class="stat-label">Total Students</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $stats['total_lessons']; ?></div>
                    <div class="stat-label">Total Lessons</div>
                </div>
            </div>

            <div class="recent-activities">
                <h2>Recent Activities</h2>
                <div class="activity-list">
                    <?php while ($activity = $activities->fetch_assoc()) { ?>
                        <div class="activity-item">
                            <div class="activity-info">
                                <strong><?php echo htmlspecialchars($activity['student_name']); ?></strong>
                                completed lesson "<?php echo htmlspecialchars($activity['lesson_title']); ?>"
                                in course "<?php echo htmlspecialchars($activity['course_title']); ?>"
                            </div>
                            <div class="activity-time">
                                <?php echo date('M d, Y H:i', strtotime($activity['completed_at'])); ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>