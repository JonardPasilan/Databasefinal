<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['user_id'];

// Fetch overall progress
$progress_sql = "SELECT 
    c.title as course_title,
    COUNT(DISTINCT l.id) as total_lessons,
    COUNT(DISTINCT CASE WHEN p.completed = 1 THEN l.id END) as completed_lessons,
    AVG(p.score) as average_score
    FROM courses c
    LEFT JOIN lessons l ON c.id = l.course_id
    LEFT JOIN progress p ON l.id = p.lesson_id AND p.student_id = ?
    GROUP BY c.id";

$stmt = $conn->prepare($progress_sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$progress_results = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Progress - E-Learning Platform</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/progress.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <h1>My Progress</h1>
            <div class="nav-right">
                <a href="dashboard.php">Dashboard</a>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="progress-overview">
            <h2>Learning Progress</h2>
            
            <?php while ($progress = $progress_results->fetch_assoc()) { 
                $completion_rate = ($progress['total_lessons'] > 0) 
                    ? ($progress['completed_lessons'] / $progress['total_lessons']) * 100 
                    : 0;
            ?>
                <div class="course-progress-card">
                    <h3><?php echo htmlspecialchars($progress['course_title']); ?></h3>
                    <div class="progress-stats">
                        <div class="stat">
                            <span class="label">Completion</span>
                            <div class="progress-bar">
                                <div class="progress" style="width: <?php echo $completion_rate; ?>%"></div>
                            </div>
                            <span class="value"><?php echo round($completion_rate); ?>%</span>
                        </div>
                        <div class="stat">
                            <span class="label">Lessons Completed</span>
                            <span class="value"><?php echo $progress['completed_lessons']; ?>/<?php echo $progress['total_lessons']; ?></span>
                        </div>
                        <div class="stat">
                            <span class="label">Average Quiz Score</span>
                            <span class="value"><?php echo $progress['average_score'] ? round($progress['average_score'], 1) : 'N/A'; ?>%</span>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>

        <div class="recent-activity">
            <h2>Recent Activity</h2>
            <?php
            $activity_sql = "SELECT 
                l.title as lesson_title,
                c.title as course_title,
                p.score,
                p.completed_at
                FROM progress p
                JOIN lessons l ON p.lesson_id = l.id
                JOIN courses c ON l.course_id = c.id
                WHERE p.student_id = ?
                ORDER BY p.completed_at DESC
                LIMIT 5";
            
            $stmt = $conn->prepare($activity_sql);
            $stmt->bind_param("i", $student_id);
            $stmt->execute();
            $activities = $stmt->get_result();
            ?>

            <div class="activity-list">
                <?php while ($activity = $activities->fetch_assoc()) { ?>
                    <div class="activity-item">
                        <div class="activity-info">
                            <h4><?php echo htmlspecialchars($activity['lesson_title']); ?></h4>
                            <p>Course: <?php echo htmlspecialchars($activity['course_title']); ?></p>
                        </div>
                        <div class="activity-stats">
                            <span class="score">Score: <?php echo $activity['score']; ?>%</span>
                            <span class="date"><?php echo date('M d, Y', strtotime($activity['completed_at'])); ?></span>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</body>
</html>