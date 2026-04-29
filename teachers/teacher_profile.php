<?php
session_start();
include '../config/connection.php';
include 'header.php';

// Check if the user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Teacher' && $_SESSION['user_role'] !== 'Admin') {
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card-shadow {
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }
        .info-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-3xl mx-auto">
            <!-- Profile Card -->
            <div class="bg-white rounded-xl card-shadow overflow-hidden">
                <!-- Profile Header -->
                <div class="gradient-bg p-8 text-center">
                    <div class="w-32 h-32 mx-auto bg-white rounded-full flex items-center justify-center mb-4 shadow-lg">
                        <i class="fas fa-user-tie text-5xl text-purple-600"></i>
                    </div>
                    <h2 class="text-white text-2xl font-bold"><?= htmlspecialchars($user['username']) ?></h2>
                    <p class="text-purple-100 mt-1">Teacher</p>
                    <div class="mt-4 inline-flex items-center bg-white/20 px-4 py-1 rounded-full">
                        <i class="fas fa-check-circle text-white mr-2"></i>
                        <span class="text-white text-sm">Verified Account</span>
                    </div>
                </div>

                <!-- Profile Info -->
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-user-circle mr-2 text-purple-600"></i>
                        Account Information
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Username Card -->
                        <div class="info-card bg-gradient-to-br from-purple-50 to-indigo-50 p-4 rounded-lg border border-purple-100">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-user text-purple-600 text-xl"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm text-gray-500">Username</p>
                                    <p class="text-lg font-semibold text-gray-800"><?= htmlspecialchars($user['username']) ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Email Card -->
                        <div class="info-card bg-gradient-to-br from-blue-50 to-cyan-50 p-4 rounded-lg border border-blue-100">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-envelope text-blue-600 text-xl"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm text-gray-500">Email Address</p>
                                    <p class="text-lg font-semibold text-gray-800"><?= htmlspecialchars($user['email']) ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Role Card -->
                        <div class="info-card bg-gradient-to-br from-green-50 to-teal-50 p-4 rounded-lg border border-green-100">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-chalkboard-teacher text-green-600 text-xl"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm text-gray-500">Role</p>
                                    <p class="text-lg font-semibold text-gray-800">Teacher</p>
                                </div>
                            </div>
                        </div>

                        <!-- Member Since Card -->
                        <div class="info-card bg-gradient-to-br from-orange-50 to-yellow-50 p-4 rounded-lg border border-orange-100">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-calendar-alt text-orange-600 text-xl"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm text-gray-500">Member Since</p>
                                    <p class="text-lg font-semibold text-gray-800">N/A</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-gray-50 p-6 border-t border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-bolt mr-2 text-purple-600"></i>
                        Quick Actions
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <a href="edit_teacher_profile.php" 
                            class="flex items-center justify-center bg-gradient-to-r from-purple-600 to-indigo-600 text-white py-3 px-4 rounded-lg font-semibold hover:from-purple-700 hover:to-indigo-700 transition transform hover:scale-[1.02] shadow-md">
                            <i class="fas fa-edit mr-2"></i>
                            Edit Profile
                        </a>
                        <a href="teacher_dashboard.php" 
                            class="flex items-center justify-center bg-white text-gray-700 py-3 px-4 rounded-lg font-semibold border-2 border-gray-200 hover:border-purple-400 hover:text-purple-600 transition">
                            <i class="fas fa-home mr-2"></i>
                            Dashboard
                        </a>
                    </div>
                </div>
            </div>

            <!-- Back Link -->
            <div class="text-center mt-6">
                <a href="teacher_dashboard.php" class="text-purple-600 hover:text-purple-800 transition inline-flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Dashboard
                </a>
            </div>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>

