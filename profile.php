<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['user_id'];

// Fetch user details
$user_sql = "SELECT * FROM students WHERE id = ?";
$stmt = $conn->prepare($user_sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Handle profile update
if (isset($_POST['update_profile'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    
    if (password_verify($current_password, $user['password'])) {
        $update_sql = "UPDATE students SET name = ?, email = ?";
        $params = ["si", $name, $student_id];
        
        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_sql .= ", password = ?";
            $params = ["ssi", $name, $hashed_password, $student_id];
        }
        
        $update_sql .= " WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param(...$params);
        
        if ($stmt->execute()) {
            $_SESSION['name'] = $name;
            $success = "Profile updated successfully!";
        } else {
            $error = "Failed to update profile.";
        }
    } else {
        $error = "Current password is incorrect.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - E-Learning Platform</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/profile.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <h1>My Profile</h1>
            <div class="nav-right">
                <a href="dashboard.php">Dashboard</a>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="profile-container">
            <?php if (isset($success)) { ?>
                <div class="success-message"><?php echo $success; ?></div>
            <?php } ?>
            <?php if (isset($error)) { ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php } ?>

            <form method="POST" action="" class="profile-form">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="current_password" required>
                </div>

                <div class="form-group">
                    <label>New Password (leave blank to keep current)</label>
                    <input type="password" name="new_password">
                </div>

                <button type="submit" name="update_profile" class="btn-update">Update Profile</button>
            </form>

            <div class="profile-stats">
                <h3>Account Statistics</h3>
                <?php
                $stats_sql = "SELECT 
                    COUNT(DISTINCT p.lesson_id) as completed_lessons,
                    COUNT(DISTINCT l.course_id) as enrolled_courses,
                    AVG(p.score) as average_score
                    FROM progress p
                    JOIN lessons l ON p.lesson_id = l.id
                    WHERE p.student_id = ? AND p.completed = 1";
                
                $stmt = $conn->prepare($stats_sql);
                $stmt->bind_param("i", $student_id);
                $stmt->execute();
                $stats = $stmt->get_result()->fetch_assoc();
                ?>
                <div class="stats-grid">
                    <div class="stat-item">
                        <span class="stat-value"><?php echo $stats['completed_lessons']; ?></span>
                        <span class="stat-label">Lessons Completed</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value"><?php echo $stats['enrolled_courses']; ?></span>
                        <span class="stat-label">Courses Enrolled</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value"><?php echo round($stats['average_score'], 1); ?>%</span>
                        <span class="stat-label">Average Score</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>