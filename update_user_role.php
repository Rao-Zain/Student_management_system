<?php
session_start();
include 'config/connection.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
    header('Location: /student_management_system/auth/login.php?error=Unauthorized access');
    exit();
}

// Promote/Demote a user to admin
if (isset($_POST['make_admin']) && isset($_POST['user_id'])) {
    $userId = $_POST['user_id'];

    $stmt = $conn->prepare("UPDATE users SET role = 'admin' WHERE id = ?");
    $stmt->bind_param("i", $userId);

    if ($stmt->execute()) {
        header('Location: manage_users.php?success=User role updated to Admin successfully.');
    } else {
        header('Location: manage_users.php?error=Failed to update user role.');
    }
    exit();
}

// Promote/Demote a user to teacher
if (isset($_POST['make_teacher']) && isset($_POST['user_id'])) {
    $userId = $_POST['user_id'];

    $stmt = $conn->prepare("UPDATE users SET role = 'Teacher' WHERE id = ?");
    $stmt->bind_param("i", $userId);

    if ($stmt->execute()) {
        header('Location: manage_users.php?success=User role updated to Teacher successfully.');
    } else {
        header('Location: manage_users.php?error=Failed to update user role.');
    }
    exit();
}

// Promote/Demote a user to student
if (isset($_POST['make_student']) && isset($_POST['user_id'])) {
    $userId = $_POST['user_id'];

    $stmt = $conn->prepare("UPDATE users SET role = 'Student' WHERE id = ?");
    $stmt->bind_param("i", $userId);

    if ($stmt->execute()) {
        header('Location: manage_users.php?success=User role updated to Student successfully.');
    } else {
        header('Location: manage_users.php?error=Failed to update user role.');
    }
    exit();
}
