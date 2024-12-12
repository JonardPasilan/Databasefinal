<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id']) || !isset($_POST['lesson_id'])) {
    header("Location: index.php");
    exit();
}

$lesson_id = $_POST['lesson_id'];

// Get course ID before deleting lesson
$course_sql = "SELECT course_id FROM lessons WHERE id = ?";
$stmt = $conn->prepare($course_sql);
$stmt->bind_param("i", $lesson_id);
$stmt->execute();
$course_id = $stmt->get_result()->fetch_assoc()['course_id'];

// Delete associated quizzes first
$delete_quizzes = "DELETE FROM quizzes WHERE lesson_id = ?";
$stmt = $conn->prepare($delete_quizzes);
$stmt->bind_param("i", $lesson_id);
$stmt->execute();

// Delete lesson
$delete_lesson = "DELETE FROM lessons WHERE id = ?";
$stmt = $conn->prepare($delete_lesson);
$stmt->bind_param("i", $lesson_id);

if ($stmt->execute()) {
    header("Location: edit_course.php?id=$course_id&success=lesson_deleted");
} else {
    header("Location: edit_course.php?id=$course_id&error=delete_failed");
}
exit();