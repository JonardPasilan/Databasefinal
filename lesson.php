<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$lesson_id = $_GET['id'];

// Fetch lesson details with course info
$lesson_sql = "SELECT l.*, c.title as course_title, c.id as course_id 
               FROM lessons l 
               JOIN courses c ON l.course_id = c.id 
               WHERE l.id = ?";
$stmt = $conn->prepare($lesson_sql);
$stmt->bind_param("i", $lesson_id);
$stmt->execute();
$result = $stmt->get_result();
$lesson = $result->fetch_assoc();

if (!$lesson) {
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($lesson['title']); ?> - E-Learning</title>
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
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .container {
            max-width: 1000px;
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
            margin-left: 1rem;
        }

        .nav-links a:hover {
            background: rgba(255,255,255,0.1);
        }

        .main-content {
            margin-top: 80px;
            padding: 2rem 0;
        }

        .lesson-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .lesson-title {
            font-size: 1.8rem;
            color: #2c3e50;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #eee;
        }

        .lesson-content {
            color: #444;
            font-size: 1.1rem;
            line-height: 1.8;
            margin-bottom: 2rem;
        }

        .lesson-navigation {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 2px solid #eee;
        }

        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            border-radius: 5px;
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .btn-primary {
            background: #3498db;
            color: white;
        }

        .btn-primary:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #95a5a6;
            color: white;
        }

        .btn-secondary:hover {
            background: #7f8c8d;
            transform: translateY(-2px);
        }

        .course-info {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 2rem;
        }

        .course-info p {
            color: #666;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .nav-content {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .nav-links {
                display: flex;
                flex-direction: column;
                gap: 0.5rem;
            }

            .nav-links a {
                margin: 0;
                width: 100%;
            }

            .lesson-title {
                font-size: 1.5rem;
            }

            .lesson-navigation {
                flex-direction: column;
                gap: 1rem;
            }

            .btn {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-content">
                <h1><?php echo htmlspecialchars($lesson['course_title']); ?></h1>
                <div class="nav-links">
                    <a href="view_course.php?id=<?php echo $lesson['course_id']; ?>">Back to Course</a>
                    <a href="dashboard.php">Dashboard</a>
                    <a href="logout.php">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="main-content">
        <div class="container">
            <div class="lesson-container">
                <div class="course-info">
                    <p>Course: <?php echo htmlspecialchars($lesson['course_title']); ?></p>
                </div>
                
                <h1 class="lesson-title"><?php echo htmlspecialchars($lesson['title']); ?></h1>
                
                <div class="lesson-content">
                    <?php echo nl2br(htmlspecialchars($lesson['content'])); ?>
                </div>

                <div class="lesson-navigation">
                    <a href="view_course.php?id=<?php echo $lesson['course_id']; ?>" class="btn btn-secondary">
                        Back to Course
                    </a>
                    <a href="take_quiz.php?lesson_id=<?php echo $lesson['id']; ?>" class="btn btn-primary">
                        Take Quiz
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>