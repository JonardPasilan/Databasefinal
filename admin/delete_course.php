<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

if (isset($_POST['course_id'])) {
    $course_id = $_POST['course_id'];
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // First, delete all quizzes associated with the course's lessons
        $delete_quizzes = "DELETE q FROM quizzes q 
                          INNER JOIN lessons l ON q.lesson_id = l.id 
                          WHERE l.course_id = ?";
        $stmt = $conn->prepare($delete_quizzes);
        $stmt->bind_param("i", $course_id);
        $stmt->execute();
        
        // Delete progress records associated with the course's lessons
        $delete_progress = "DELETE p FROM progress p 
                           INNER JOIN lessons l ON p.lesson_id = l.id 
                           WHERE l.course_id = ?";
        $stmt = $conn->prepare($delete_progress);
        $stmt->bind_param("i", $course_id);
        $stmt->execute();
        
        // Delete all lessons associated with the course
        $delete_lessons = "DELETE FROM lessons WHERE course_id = ?";
        $stmt = $conn->prepare($delete_lessons);
        $stmt->bind_param("i", $course_id);
        $stmt->execute();
        
        // Finally, delete the course
        $delete_course = "DELETE FROM courses WHERE id = ?";
        $stmt = $conn->prepare($delete_course);
        $stmt->bind_param("i", $course_id);
        $stmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        // Set success message in session
        $_SESSION['message'] = "Course and all associated content deleted successfully.";
        $_SESSION['message_type'] = "success";
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        
        // Set error message in session
        $_SESSION['message'] = "Failed to delete course. Error: " . $e->getMessage();
        $_SESSION['message_type'] = "error";
    }
    
} else {
    // Set error message in session
    $_SESSION['message'] = "Invalid request. Course ID not provided.";
    $_SESSION['message_type'] = "error";
}

// Redirect back to courses page
header("Location: courses.php");
exit();