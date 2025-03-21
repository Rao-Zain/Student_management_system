<?php
session_start();
include 'config/connection.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php?error=Unauthorized access');
    exit();
}

// Promote a user to admin
if (isset($_POST['make_admin']) && isset($_POST['user_id'])) {
    $userId = $_POST['user_id'];

    $stmt = $conn->prepare("UPDATE users SET role = 'admin' WHERE id = ?");
    $stmt->bind_param("i", $userId);

    if ($stmt->execute()) {
        header('Location: manage_users.php?success=User promoted to admin successfully.');
    } else {
        header('Location: manage_users.php?error=Failed to promote user.');
    }
    exit();
}

// Promote a user to teacher
if (isset($_POST['make_teacher']) && isset($_POST['user_id'])) {
    $userId = $_POST['user_id'];

    $stmt = $conn->prepare("UPDATE users SET role = 'Teacher' WHERE id = ?");
    $stmt->bind_param("i", $userId);

    if ($stmt->execute()) {
        header('Location: manage_users.php?success=User promoted to Teacher successfully.');
    } else {
        header('Location: manage_users.php?error=Failed to promote user.');
    }
    exit();
}
