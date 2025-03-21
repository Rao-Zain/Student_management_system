<?php
session_start();
require_once '../config/connection.php';

// Check if the user is an Admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Get the data from the form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teacher_id = $_POST['teacher_id'];
    $course_name = $_POST['course_name'];

    // Find the course ID based on the course name
    $course_stmt = $conn->prepare("SELECT id FROM courses WHERE course_name = ?");
    $course_stmt->bind_param("s", $course_name);
    $course_stmt->execute();
    $course_result = $course_stmt->get_result();
    $course_row = $course_result->fetch_assoc();
    $course_id = $course_row['id'];

    if ($course_id) {
        // Prepare and execute the delete query
        $stmt = $conn->prepare("DELETE FROM teacher_subjects WHERE teacher_id = ? AND course_id = ?");
        $stmt->bind_param("ii", $teacher_id, $course_id);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Subject removed successfully!";
        } else {
            $_SESSION['error_message'] = "Error removing subject: " . $stmt->error;
        }
        $stmt->close();
    }
    $course_stmt->close();
}

header("Location: teacher_dashboard.php");
exit();
