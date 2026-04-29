<?php
session_start();
include '../config/connection.php';
include 'header.php';

// Check if the user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Teacher' && $_SESSION['user_role'] !== 'Admin') {
    header('Location: login.php?error=Unauthorized access');
    exit();
}

$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($username) || empty($email)) {
        $error_message = 'Username and email are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Invalid email format.';
    } else {
        // Check if username or email already exists for other users
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
        $check_stmt->bind_param("ssi", $username, $email, $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $error_message = 'Username or email already exists.';
        } else {
            // Update basic info (without password)
            $update_stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
            $update_stmt->bind_param("ssi", $username, $email, $user_id);
            
            if ($update_stmt->execute()) {
                // Handle password change if provided
                if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
                    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
                        $error_message = 'All password fields are required to change password.';
                    } else {
                        // Verify current password
                        $pwd_stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
                        $pwd_stmt->bind_param("i", $user_id);
                        $pwd_stmt->execute();
                        $pwd_result = $pwd_stmt->get_result()->fetch_assoc();
                        
                        if (password_verify($current_password, $pwd_result['password'])) {
                            if ($new_password !== $confirm_password) {
                                $error_message = 'New passwords do not match.';
                            } elseif (strlen($new_password) < 6) {
                                $error_message = 'Password must be at least 6 characters.';
                            } else {
                                // Update password
                                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                                $pwd_update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                                $pwd_update->bind_param("si", $hashed_password, $user_id);
                                $pwd_update->execute();
                                $success_message = 'Profile and password updated successfully!';
                            }
                        } else {
                            $error_message = 'Current password is incorrect.';
                        }
                    }
                } else {
                    $success_message = 'Profile updated successfully!';
                }
            } else {
                $error_message = 'Failed to update profile.';
            }
        }
    }
}

// Get current user data
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
    <title>Edit Teacher Profile</title>
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
        .input-focus:focus {
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.3);
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto">
            <!-- Header -->
            <div class="flex items-center mb-6">
                <a href="teacher_profile.php" class="text-gray-600 hover:text-gray-800 transition">
                    <i class="fas fa-arrow-left text-xl"></i>
                </a>
                <h1 class="text-2xl font-bold ml-4 text-gray-800">Edit Profile</h1>
            </div>

            <!-- Success Message -->
            <?php if ($success_message): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle mr-2"></i>
                        <span><?= htmlspecialchars($success_message) ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Error Message -->
            <?php if ($error_message): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <span><?= htmlspecialchars($error_message) ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Profile Edit Form -->
            <div class="bg-white rounded-xl card-shadow overflow-hidden">
                <!-- Profile Header -->
                <div class="gradient-bg p-6 text-center">
                    <div class="w-24 h-24 mx-auto bg-white rounded-full flex items-center justify-center mb-3">
                        <i class="fas fa-user-tie text-4xl text-purple-600"></i>
                    </div>
                    <h2 class="text-white text-xl font-semibold"><?= htmlspecialchars($user['username']) ?></h2>
                    <p class="text-purple-100">Teacher</p>
                </div>

                <!-- Form -->
                <form method="POST" class="p-6">
                    <!-- Account Section -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-user-circle mr-2 text-purple-600"></i>
                            Account Information
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-user text-gray-400"></i>
                                    </div>
                                    <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" 
                                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 input-focus transition"
                                        required>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-envelope text-gray-400"></i>
                                    </div>
                                    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" 
                                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 input-focus transition"
                                        required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Password Section -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-lock mr-2 text-purple-600"></i>
                            Change Password
                            <span class="text-sm font-normal text-gray-500 ml-2">(Optional)</span>
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-lock text-gray-400"></i>
                                    </div>
                                    <input type="password" name="current_password" 
                                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 input-focus transition"
                                        placeholder="Enter current">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-key text-gray-400"></i>
                                    </div>
                                    <input type="password" name="new_password" 
                                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 input-focus transition"
                                        placeholder="Enter new">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-key text-gray-400"></i>
                                    </div>
                                    <input type="password" name="confirm_password" 
                                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 input-focus transition"
                                        placeholder="Confirm new">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-gray-200">
                        <button type="submit" 
                            class="flex-1 bg-gradient-to-r from-purple-600 to-indigo-600 text-white py-3 px-6 rounded-lg font-semibold hover:from-purple-700 hover:to-indigo-700 transition transform hover:scale-[1.02] shadow-lg">
                            <i class="fas fa-save mr-2"></i>
                            Save Changes
                        </button>
                        <a href="teacher_profile.php" 
                            class="flex-1 bg-gray-200 text-gray-700 py-3 px-6 rounded-lg font-semibold text-center hover:bg-gray-300 transition">
                            <i class="fas fa-times mr-2"></i>
                            Cancel
                        </a>
                    </div>
                </form>
            </div>

            <!-- Back to Dashboard -->
            <div class="text-center mt-6">
                <a href="teacher_dashboard.php" class="text-purple-600 hover:text-purple-800 transition">
                    <i class="fas fa-home mr-1"></i>
                    Back to Dashboard
                </a>
            </div>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>
