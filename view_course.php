<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: login.php");
    exit();
}

$course_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Fetch course details
$course_sql = "SELECT * FROM courses WHERE id = ?";
$stmt = $conn->prepare($course_sql);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();
$course = $result->fetch_assoc();

// Fetch lessons
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
    <title>Course: <?php echo htmlspecialchars($course['title']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            background-color: #f5f7fa;
            color: #333;
        }

        .navbar {
            background: #2c3e50;
            color: white;
            padding: 1rem 0;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 100;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .nav-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            transition: background 0.3s;
        }

        .nav-links a:hover {
            background: rgba(255,255,255,0.1);
        }

        .main-content {
            margin-top: 80px;
            padding: 2rem 0;
        }

        .course-header {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .course-title {
            font-size: 2rem;
            color: #2c3e50;
            margin-bottom: 1rem;
        }

        .course-description {
            color: #666;
            margin-bottom: 1rem;
        }

        .lessons-grid {
            display: grid;
            gap: 1.5rem;
        }

        .lesson-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .lesson-card:hover {
            transform: translateY(-5px);
        }

        .lesson-title {
            font-size: 1.25rem;
            color: #2c3e50;
            margin-bottom: 1rem;
        }

        .lesson-number {
            background: #3498db;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
            display: inline-block;
        }

        .start-button {
            display: inline-block;
            background: #3498db;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 5px;
            text-decoration: none;
            transition: background 0.3s;
        }

        .start-button:hover {
            background: #2980b9;
        }

        .no-lessons {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        @media (max-width: 768px) {
            .nav-content {
                flex-direction: column;
                gap: 1rem;
            }

            .course-title {
                font-size: 1.5rem;
            }

            .lessons-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-content">
                <h1><?php echo htmlspecialchars($course['title']); ?></h1>
                <div class="nav-links">
                    <a href="dashboard.php">Dashboard</a>
                    <a href="logout.php">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="main-content">
        <div class="container">
            <div class="course-header">
                <h2 class="course-title">Welcome to <?php echo htmlspecialchars($course['title']); ?></h2>
                <p class="course-description"><?php echo htmlspecialchars($course['description']); ?></p>
            </div>

            <?php if ($lessons->num_rows > 0) { ?>
                <div class="lessons-grid">
                    <?php 
                    $lesson_number = 1;
                    while ($lesson = $lessons->fetch_assoc()) { 
                    ?>
                        <div class="lesson-card">
                            <span class="lesson-number">Lesson <?php echo $lesson_number; ?></span>
                            <h3 class="lesson-title"><?php echo htmlspecialchars($lesson['title']); ?></h3>
                            <a href="lesson.php?id=<?php echo $lesson['id']; ?>" class="start-button">
                                Start Lesson
                            </a>
                        </div>
                    <?php 
                        $lesson_number++;
                    } 
                    ?>
                </div>
            <?php } else { ?>
                <div class="no-lessons">
                    <h3>No lessons available yet</h3>
                    <p>Please check back later for course content.</p>
                    <a href="dashboard.php" class="start-button">Return to Dashboard</a>
                </div>
            <?php } ?>
        </div>
    </div>
</body>
</html>