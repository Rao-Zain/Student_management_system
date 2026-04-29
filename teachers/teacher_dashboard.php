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
    <title>Teacher Dashboard - EduManage</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f3f4f6;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .dashboard-container {
            flex: 1;
        }
        .card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-radius: 1rem;
        }
        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .card-icon-container {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        .welcome-banner {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            border-radius: 1rem;
            color: white;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
  
    <div class="dashboard-container container mx-auto px-4 py-8">
        
        <!-- Welcome Banner -->
        <div class="welcome-banner flex flex-col md:flex-row items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">Welcome Back, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Teacher'); ?>! 👋</h1>
                <p class="text-blue-100 opacity-90 text-lg">Manage your classes, students, and grades all in one place.</p>
            </div>
            <div class="mt-4 md:mt-0 hidden md:block">
                <i class="fas fa-chalkboard-teacher text-6xl text-white opacity-80"></i>
            </div>
        </div>

        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-800 border-b-2 border-indigo-200 inline-block pb-1">Quick Actions</h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            
            <!-- Mark Attendance -->
            <div class="card bg-white shadow-lg p-6 flex flex-col items-center text-center border-t-4 border-blue-500">
                <div class="card-icon-container bg-blue-50 text-blue-600">
                    <i class="fas fa-user-check"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800 mb-2">Mark Attendance</h3>
                <p class="text-sm text-gray-500 mb-4 flex-grow">Record daily attendance for your assigned classes easily.</p>
                <a href="../attendance/attendance.php" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition font-medium">Go to Attendance</a>
            </div>

            <!-- View Attendance -->
            <div class="card bg-white shadow-lg p-6 flex flex-col items-center text-center border-t-4 border-indigo-500">
                <div class="card-icon-container bg-indigo-50 text-indigo-600">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800 mb-2">View Reports</h3>
                <p class="text-sm text-gray-500 mb-4 flex-grow">Review detailed attendance reports and student statistics.</p>
                <a href="../attendance/view.php" class="w-full bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition font-medium">View Reports</a>
            </div>

            <!-- Manage Exams -->
            <div class="card bg-white shadow-lg p-6 flex flex-col items-center text-center border-t-4 border-purple-500">
                <div class="card-icon-container bg-purple-50 text-purple-600">
                    <i class="fas fa-file-alt"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800 mb-2">Exam Records</h3>
                <p class="text-sm text-gray-500 mb-4 flex-grow">Manage examination records and setup assessments.</p>
                <a href="../exams/exams.php" class="w-full bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition font-medium">Manage Exams</a>
            </div>

            <!-- Enter Grades -->
            <div class="card bg-white shadow-lg p-6 flex flex-col items-center text-center border-t-4 border-green-500">
                <div class="card-icon-container bg-green-50 text-green-600">
                    <i class="fas fa-marker"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800 mb-2">Enter Grades</h3>
                <p class="text-sm text-gray-500 mb-4 flex-grow">Input marks and evaluate student performance efficiently.</p>
                <a href="../exams/enter_grades.php" class="w-full bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition font-medium">Grade Students</a>
            </div>

            <!-- Exam Results -->
            <div class="card bg-white shadow-lg p-6 flex flex-col items-center text-center border-t-4 border-yellow-500">
                <div class="card-icon-container bg-yellow-50 text-yellow-600">
                    <i class="fas fa-chart-pie"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800 mb-2">View Results</h3>
                <p class="text-sm text-gray-500 mb-4 flex-grow">Analyze overall class performance and generate report cards.</p>
                <a href="../exams/results.php" class="w-full bg-yellow-500 text-white px-4 py-2 rounded-lg hover:bg-yellow-600 transition font-medium">View Results</a>
            </div>

            <!-- Grade Scales -->
            <div class="card bg-white shadow-lg p-6 flex flex-col items-center text-center border-t-4 border-red-500">
                <div class="card-icon-container bg-red-50 text-red-600">
                    <i class="fas fa-balance-scale"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800 mb-2">Grade Scales</h3>
                <p class="text-sm text-gray-500 mb-4 flex-grow">Configure grading scales and academic benchmarks.</p>
                <a href="../exams/grade_scales.php" class="w-full bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition font-medium">Setup Scales</a>
            </div>

            <!-- Students -->
          <!-- Students -->
<div class="card bg-white shadow-lg p-6 flex flex-col items-center text-center border-t-4 border-pink-500">
    <div class="card-icon-container bg-pink-50 text-pink-600">
        <i class="fas fa-user-graduate"></i>
    </div>
    <h3 class="text-lg font-bold text-gray-800 mb-2">My Students</h3>
    <p class="text-sm text-gray-500 mb-4 flex-grow">
        Browse student profiles and contact information.
    </p>

    <a href="../read.php" 
       class="w-full bg-white text-pink-600 px-4 py-2 rounded-lg border border-pink-500 hover:bg-pink-500 hover:text-white transition font-medium shadow-sm hover:shadow-md">
       View Students
    </a>
</div>

            <!-- Profile Settings -->
            <div class="card bg-white shadow-lg p-6 flex flex-col items-center text-center border-t-4 border-gray-700">
                <div class="card-icon-container bg-gray-100 text-gray-700">
                    <i class="fas fa-user-cog"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800 mb-2">Profile Settings</h3>
                <p class="text-sm text-gray-500 mb-4 flex-grow">Update your personal information and account preferences.</p>
                <a href="teacher_profile.php" class="w-full bg-gray-700 text-white px-4 py-2 rounded-lg hover:bg-gray-800 transition font-medium">Edit Profile</a>
            </div>

        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>