<?php
session_start();
include '../config/connection.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE verification_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE users SET is_verified = 1 WHERE verification_token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();

        echo "✅ Your email has been verified successfully. You can now <a href='login.php'>Login</a>.";
    } else {
        echo "❌ Invalid or expired verification link.";
    }
} else {
    echo "❌ No verification token provided.";
}
