<?php
// lesson.php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$host = 'localhost';
$dbname = 'elearning_db';
$username = 'root';
$password = '';

$conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (!isset($_GET['id'])) {
    echo "Lesson ID is required.";
    exit();
}

$lesson_id = $_GET['id'];

// Fetch lesson details
$lesson_stmt = $conn->prepare("SELECT * FROM lessons WHERE id = :id");
$lesson_stmt->bindParam(':id', $lesson_id);
$lesson_stmt->execute();
$lesson = $lesson_stmt->fetch(PDO::FETCH_ASSOC);

if (!$lesson) {
    echo "Lesson not found.";
    exit();
}

?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($lesson['title']); ?></title>
</head>
<body>
    <h2><?php echo htmlspecialchars($lesson['title']); ?></h2>
    <p><?php echo htmlspecialchars($lesson['content']); ?></p>

    <a href="view_course.php?id=<?php echo $lesson['course_id']; ?>">Back to Course</a>
    <a href="take_quiz.php?lesson_id=<?php echo $lesson['id']; ?>" class="btn-quiz">Take Quiz</a>
</body>
</html>
