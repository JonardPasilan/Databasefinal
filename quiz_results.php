<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['quiz_results'])) {
    header("Location: dashboard.php");
    exit();
}

$results = $_SESSION['quiz_results'];
$lesson_id = $_GET['lesson_id'] ?? null;
unset($_SESSION['quiz_results']); // Clear results from session
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Result - E-Learning Platform</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <h1>Quiz Result</h1>
            <div class="nav-right">
                <a href="dashboard.php">Dashboard</a>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="result-card">
            <h2>Your Quiz Results</h2>
            
            <div class="score-circle-container">
                <div class="score-circle">
                    <div class="percentage"><?php echo round($results['percentage']); ?>%</div>
                    <div class="score-text">
                        <?php echo $results['score']; ?> out of <?php echo $results['total']; ?> correct
                    </div>
                </div>
            </div>

            <?php if ($results['percentage'] >= 70): ?>
                <div class="result-message success">
                    <h3>🎉 Congratulations!</h3>
                    <p>You've successfully passed this quiz.</p>
                </div>
            <?php else: ?>
                <div class="result-message warning">
                    <h3>Keep Learning!</h3>
                    <p>Review the material and try again to improve your score.</p>
                </div>
            <?php endif; ?>

            <div class="action-buttons">
                <?php if ($lesson_id): ?>
                    <a href="lesson.php?id=<?php echo $lesson_id; ?>" class="btn btn-secondary">Back to Lesson</a>
                <?php endif; ?>
                <a href="dashboard.php" class="btn btn-primary">Go to Dashboard</a>
            </div>
        </div>
    </div>
</body>
</html>