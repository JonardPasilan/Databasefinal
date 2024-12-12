<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// Fetch all students with their progress
$students_sql = "SELECT 
    s.*,
    COUNT(DISTINCT p.lesson_id) as completed_lessons,
    AVG(p.score) as average_score
    FROM students s
    LEFT JOIN progress p ON s.id = p.student_id
    GROUP BY s.id
    ORDER BY s.created_at DESC";
$students = $conn->query($students_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students Management - E-Learning Platform</title>
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
                <a href="dashboard.php">Dashboard</a>
                <a href="courses.php">Courses</a>
                <a href="lessons.php">Lessons</a>
                <a href="students.php" class="active">Students</a>
                <a href="quizzes.php">Quizzes</a>
                <a href="logout.php">Logout</a>
            </nav>
        </div>

        <div class="admin-main">
            <div class="admin-header">
                <h1>Students Management</h1>
            </div>

            <div class="students-list">
                <?php if ($students->num_rows > 0) { ?>
                    <div class="table-responsive">
                        <table class="students-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Completed Lessons</th>
                                    <th>Average Score</th>
                                    <th>Joined Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($student = $students->fetch_assoc()) { ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($student['name']); ?></td>
                                        <td><?php echo htmlspecialchars($student['email']); ?></td>
                                        <td><?php echo $student['completed_lessons']; ?></td>
                                        <td><?php echo $student['average_score'] ? round($student['average_score'], 1) . '%' : 'N/A'; ?></td>
                                        <td><?php echo date('M d, Y', strtotime($student['created_at'])); ?></td>
                                        <td>
                                            <a href="view_student.php?id=<?php echo $student['id']; ?>" class="btn-view">View Progress</a>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php } else { ?>
                    <p class="no-students">No students registered yet.</p>
                <?php } ?>
            </div>
        </div>
    </div>
</body>
</html>