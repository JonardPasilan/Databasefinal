<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: login.php");
    exit();
}

$course_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

try {
    // Fetch course details
    $course_sql = "SELECT * FROM courses WHERE id = :course_id";
    $stmt = $conn->prepare($course_sql);
    $stmt->execute(['course_id' => $course_id]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$course) {
        header("Location: dashboard.php");
        exit();
    }

    // Fetch course lessons with progress
    $lessons_sql = "SELECT l.*, 
                    (SELECT completed FROM progress 
                     WHERE lesson_id = l.id AND student_id = :user_id) as completed
                    FROM lessons l 
                    WHERE l.course_id = :course_id
                    ORDER BY l.order_number";
    $stmt = $conn->prepare($lessons_sql);
    $stmt->execute([
        'user_id' => $user_id,
        'course_id' => $course_id
    ]);
    $lessons = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log($e->getMessage());
    $_SESSION['error'] = "An error occurred while loading the course.";
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($course['title']); ?> - E-Learning Platform</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <h1><?php echo htmlspecialchars($course['title']); ?></h1>
            <div class="nav-right">
                <a href="dashboard.php">Back to Dashboard</a>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
    </nav>

    <div class="course-content">
        <div class="course-description">
            <h2>Course Overview</h2>
            <p><?php echo htmlspecialchars($course['description']); ?></p>
        </div>

        <div class="lessons-list">
            <h2>Lessons</h2>
            <?php if ($lessons && count($lessons) > 0) { ?>
                <?php foreach ($lessons as $lesson) { ?>
                    <div class="lesson-item <?php echo $lesson['completed'] ? 'completed' : ''; ?>">
                        <h3><?php echo htmlspecialchars($lesson['title']); ?></h3>
                        <a href="lesson.php?id=<?php echo $lesson['id']; ?>" class="btn-start">
                            <?php echo $lesson['completed'] ? 'Review Lesson' : 'Start Lesson'; ?>
                        </a>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <div class="no-lessons">
                    <p>No lessons available yet.</p>
                    <a href="dashboard.php" class="btn-back">Return to Dashboard</a>
                </div>
            <?php } ?>
        </div>
    </div>
</body>
</html>