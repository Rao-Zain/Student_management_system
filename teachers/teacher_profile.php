<?php
session_start();
include '../config/connection.php';
include 'header.php';
// Check if the user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Teacher') {
    header('Location: login.php?error=Unauthorized access');
    exit();
}

// Get teacher information from the database
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto mt-10">
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h1 class="text-3xl font-bold mb-4">Teacher Profile</h1>
            <div>
                <p><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                <p><strong>Role:</strong> Teacher</p>
            </div>
            <div class="mt-6">
                <a href="edit_teacher_profile.php" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600 transition">Edit Profile</a>
                <a href="teacher_dashboard.php" class="ml-4 text-gray-600 hover:text-gray-800">Back to Dashboard</a>
            </div>
        </div>
    </div>
</body>
</html>
