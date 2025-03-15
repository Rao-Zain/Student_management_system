<?php
session_start();
include '../config/connection.php';

require '../vendor/autoload.php';  // Include Composer's autoloader

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error = ""; // Variable to store error messages

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // ✅ Check if all fields are filled
    if (empty($username) || empty($email) || empty($password)) {
        $error = "Please fill all the fields.";
    } else {
        // ✅ Check if email already exists
        $checkEmailQuery = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($checkEmailQuery);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "This email is already registered. Please use a different email.";
        } else {
            // ✅ Securely hash the password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // ✅ Generate a unique verification token
            $verification_token = bin2hex(random_bytes(32));
            $is_verified = 0;

            // ✅ Insert new user if email is unique
            $query = "INSERT INTO users (username, email, password, verification_token, is_verified) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssssi", $username, $email, $hashedPassword, $verification_token, $is_verified);

            if ($stmt->execute()) {
                // ✅ Store user details temporarily in the session for `send_email.php`
                $_SESSION['verification_token'] = $verification_token;
                $_SESSION['username'] = $username;
                $_SESSION['email'] = $email;

                // ✅ Redirect to `send_email.php` to send the verification email
                header("Location: send_email.php");
                exit();
            } else {
                $error = "Error! Unable to register. Please try again.";
            }
        }
    }
}

// ✅ Store error in session to show in form
if (!empty($error)) {
    $_SESSION['register_error'] = $error;
    header("Location: register.php");
    exit();
}
