<?php
session_start();
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check if required data is available
if (!isset($_SESSION['verification_token'], $_SESSION['username'], $_SESSION['email'])) {
    die('Required session data is missing. Please register again.');
}

$verification_token = $_SESSION['verification_token'];
$username = $_SESSION['username'];
$email = $_SESSION['email'];  // ✅ Now using the user's email from the session

// ✅ Generate Verification Link
$verification_link = "http://localhost/student_management_system/auth/verify_email.php?token=$verification_token";

// ✅ Send Verification Email
$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'raozn14112001@gmail.com'; // Your Gmail address
    $mail->Password = 'kqes diwk znye ejeb';       // Your App Password (Keep it safe!)
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Recipients
    $mail->setFrom('raozn14112001@gmail.com', 'Student Management System');
    $mail->addAddress($email);  // ✅ Send to the user's email address

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Verify Your Email Address';
    $mail->Body = "
        Hello $username,<br><br>
        Please click the link below to verify your email address:<br>
        <a href='$verification_link'>$verification_link</a><br><br>
        If you didn't request this, please ignore this email.
    ";

    $mail->send();

    // ✅ Email sent successfully, redirect to login
    header("Location: login.php?success=Registration successful! A verification link has been sent to your email.");
    exit();
} catch (Exception $e) {
    $_SESSION['register_error'] = "Registration successful, but failed to send verification email. Error: {$mail->ErrorInfo}";
    header("Location: register.php");
    exit();
}
