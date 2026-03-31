<?php
session_start();
if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'Teacher' && $_SESSION['user_role'] !== 'Admin')) {
    header("Location: ../auth/login.php");
    exit();
}
include '../config/connection.php';
include 'header.php';
$teacher_id = $_SESSION['user_id'];


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0f0f0;
            font-family: Arial, sans-serif;
        }
        .card {
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
  
    <!-- Dashboard Content -->
    
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-5">
            <!-- Card 1 -->
           
            <div class="card bg-white rounded-2xl shadow-lg p-6 text-center">
                <h2 class="text-xl font-bold mb-2">Mark Attendance</h2>
                <p class="text-gray-600 mb-4">Easily mark student attendance for your subjects.</p>
                <a href="../attendance/attendance.php" class="bg-blue-500 text-white px-4 py-2 rounded-full hover:bg-blue-600 transition">Go</a>
            </div>

            <!-- Card 2 -->
            <div class="card bg-white rounded-2xl shadow-lg p-6 text-center">
                <h2 class="text-xl font-bold mb-2">View Reports</h2>
                <p class="text-gray-600 mb-4">Check detailed attendance reports of your students.</p>
                <a href="../attendance/view.php" class="bg-blue-500 text-white px-4 py-2 rounded-full hover:bg-blue-600 transition">Go</a>
            </div>

            <!-- Card 3 -->
            <div class="card bg-white rounded-2xl shadow-lg p-6 text-center">
                <h2 class="text-xl font-bold mb-2">Profile Settings</h2>
                <p class="text-gray-600 mb-4">Update your profile and settings here.</p>
                <a href="teacher_profile.php" class="bg-blue-500 text-white px-4 py-2 rounded-full hover:bg-blue-600 transition">Go</a>
            </div>
        </div>
    </div>
</body>
</html>