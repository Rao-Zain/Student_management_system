<?php
session_start(); // Always start session

include '../config/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        header('Location: login.php?error=Please fill in all fields.');
        exit();
    }

    // Check if the user exists and is verified
    $query = "SELECT * FROM users WHERE email = ? AND is_verified = 1 LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verify the password
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['username'] = $user['username'];
          
            // Redirect based on user role
            if ($user['role'] === 'Admin') {
                header('Location: ../index.php');
            } elseif ($user['role'] === 'Teacher') {
                header('Location: ../teachers/teacher_dashboard.php');
            } elseif ($user['role'] === 'Student') {
                header('Location: ../index.php');
            }
            
            exit();
        } else {
            header('Location: login.php?error=Invalid password.');
            exit();
        }
    } else {
        header('Location: login.php?error=Your email is not verified or account does not exist.');
        exit();
    }
}

header('Location: login.php?error=Invalid request.');
exit();
