<?php
session_start();
require_once '../config/connection.php';
include 'header.php';
// Check if the user is an Admin
if (!isset($_SESSION['user_role']) || strtolower($_SESSION['user_role']) !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teacher_id = trim($_POST['teacher_id']);
    $course_name = trim($_POST['course_name']);

    if (empty($teacher_id) || empty($course_name)) {
        $_SESSION['error_message'] = "Teacher ID and Course Name are required.";
        header("Location: teacher_dashboard.php");
        exit();
    }

    // Check if the teacher exists and has the 'Teacher' role
    $check_teacher = $conn->prepare("SELECT * FROM users WHERE id = ? AND role = 'Teacher'");
    $check_teacher->bind_param("i", $teacher_id);
    $check_teacher->execute();
    $teacher_result = $check_teacher->get_result();
    
    if ($teacher_result->num_rows === 0) {
        $_SESSION['error_message'] = "Teacher not found in the system.";
        header("Location: teacher_dashboard.php");
        exit();
    }

    // Find the course ID
    $course_stmt = $conn->prepare("SELECT id FROM courses WHERE course_name = ?");
    $course_stmt->bind_param("s", $course_name);
    $course_stmt->execute();
    $course_result = $course_stmt->get_result();

    if ($course_result->num_rows === 0) {
        $_SESSION['error_message'] = "Course not found in the system.";
        header("Location: teacher_dashboard.php");
        exit();
    }

    $course_row = $course_result->fetch_assoc();
    $course_id = $course_row['id'];

    // Check if the teacher-course assignment exists
    $check_assignment = $conn->prepare("SELECT * FROM teacher_subjects WHERE teacher_id = ? AND course_id = ?");
    $check_assignment->bind_param("ii", $teacher_id, $course_id);
    $check_assignment->execute();
    $assignment_result = $check_assignment->get_result();

    if ($assignment_result->num_rows === 0) {
        $_SESSION['error_message'] = "No assignment record found for this teacher and subject.";
        header("Location: teacher_dashboard.php");
        exit();
    }

    // Proceed with deletion
    $delete_stmt = $conn->prepare("DELETE FROM teacher_subjects WHERE teacher_id = ? AND course_id = ?");
    $delete_stmt->bind_param("ii", $teacher_id, $course_id);

    if ($delete_stmt->execute()) {
        $_SESSION['success_message'] = "Subject removed successfully!";
    } else {
        $_SESSION['error_message'] = "Error removing subject: " . $delete_stmt->error;
    }

    $delete_stmt->close();
    $check_teacher->close();
    $course_stmt->close();
    $check_assignment->close();
}

header("Location: teacher_dashboard.php");
exit();
