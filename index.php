<?php
session_start();
require_once __DIR__ . '/config/database.php';

// Fetch featured courses
$courses_sql = "SELECT 
    c.*,
    COUNT(DISTINCT l.id) as lesson_count,
    COUNT(DISTINCT s.id) as student_count
    FROM courses c
    LEFT JOIN lessons l ON c.id = l.course_id
    LEFT JOIN progress p ON l.id = p.lesson_id
    LEFT JOIN students s ON p.student_id = s.id
    GROUP BY c.id
    ORDER BY c.created_at DESC
    LIMIT 6";
$featured_courses = $conn->query($courses_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to E-Learning Platform</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="main-header">
        <div class="container">
            <div class="header-content">
                <h1 class="logo">E-Learning Platform</h1>
                <nav class="main-nav">
                    <?php if (isset($_SESSION['user_id'])) { ?>
                        <a href="dashboard.php">Dashboard</a>
                        <a href="profile.php">Profile</a>
                        <a href="progress.php">My Progress</a>
                        <a href="logout.php">Logout</a>
                    <?php } else { ?>
                        <a href="login.php">Login</a>
                        <a href="register.php">Register</a>
                    <?php } ?>
                </nav>
            </div>
        </div>
    </header>

    <main>
        <section class="hero">
            <div class="container">
                <h2>Start Your Learning Journey Today</h2>
                <p>Access quality education from anywhere, at any time.</p>
                <?php if (!isset($_SESSION['user_id'])) { ?>
                    <div class="cta-buttons">
                        <a href="register.php" class="btn-primary">Get Started</a>
                        <a href="login.php" class="btn-secondary">Login</a>
                    </div>
                <?php } ?>
            </div>
        </section>

        <section class="featured-courses">
            <div class="container">
                <h2>Featured Courses</h2>
                <div class="courses-grid">
                    <?php if ($featured_courses && $featured_courses->num_rows > 0) { ?>
                        <?php while ($course = $featured_courses->fetch_assoc()) { ?>
                            <div class="course-card">
                                <div class="course-content">
                                    <h3><?php echo htmlspecialchars($course['title']); ?></h3>
                                    <p><?php echo htmlspecialchars($course['description']); ?></p>
                                    <div class="course-meta">
                                        <span><?php echo $course['lesson_count']; ?> Lessons</span>
                                        <span><?php echo $course['student_count']; ?> Students</span>
                                    </div>
                                </div>
                                <a href="course.php?id=<?php echo $course['id']; ?>" class="btn-course">View Course</a>
                            </div>
                        <?php } ?>
                    <?php } else { ?>
                        <p class="no-courses">No courses available yet.</p>
                    <?php } ?>
                </div>
            </div>
        </section>

        <section class="features">
            <div class="container">
                <h2>Why Choose Us</h2>
                <div class="features-grid">
                    <div class="feature-card">
                        <h3>Quality Content</h3>
                        <p>Expert-crafted courses designed for effective learning.</p>
                    </div>
                    <div class="feature-card">
                        <h3>Learn at Your Pace</h3>
                        <p>Access content anytime, anywhere, and learn at your own speed.</p>
                    </div>
                    <div class="feature-card">
                        <h3>Track Progress</h3>
                        <p>Monitor your learning journey with detailed progress tracking.</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="main-footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> E-Learning Platform. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>