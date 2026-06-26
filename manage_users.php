<?php
session_start();
include 'config/connection.php';
include "includes/header.php";

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
    header('Location: /student_management_system/auth/login.php?error=Unauthorized access');
    exit();
}

// Fetch all users
$stmt = $conn->prepare("SELECT id, username, email, role FROM users");
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
</head>
<body >
    <div class="container mx-auto mt-10">
        <!-- Success/Error Messages -->
        <?php if (isset($_GET['success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                <?= htmlspecialchars($_GET['success']) ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                <?= htmlspecialchars($_GET['error']) ?>
            </div>
        <?php endif; ?>

        <h1 class="text-3xl font-bold mb-6">Manage Users</h1>
        <div class="overflow-x-auto">
            <table class="w-full table-auto shadow-md rounded-lg">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-center text-gray-700 font-semibold uppercase">ID</th>
                        <th class="px-4 py-3 text-center text-gray-700 font-semibold uppercase">Username</th>
                        <th class="px-4 py-3 text-center text-gray-700 font-semibold uppercase">Email</th>
                        <th class="px-4 py-3 text-center text-gray-700 font-semibold uppercase">Role</th>
                        <th class="px-4 py-3 text-center text-gray-700 font-semibold uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $result->fetch_assoc()): ?>
                        <tr class="border-b bg-white hover:bg-gray-50">
                            <td class="px-4 py-4 text-center text-gray-800"><?= htmlspecialchars($user['id']) ?></td>
                            <td class="px-4 py-4 text-center text-gray-800"><?= htmlspecialchars($user['username']) ?></td>
                            <td class="px-4 py-4 text-center text-gray-800"><?= htmlspecialchars($user['email']) ?></td>
                            <td class="px-4 py-4 text-center">
                                <?php if (strtolower($user['role']) === 'admin'): ?>
                                    <span class="px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800">Admin</span>
                                <?php elseif (strtolower($user['role']) === 'teacher'): ?>
                                    <span class="px-3 py-1 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-800">Teacher</span>
                                <?php else: ?>
                                    <span class="px-3 py-1 rounded-full text-sm font-semibold bg-gray-100 text-gray-800">Student</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-4 text-center flex justify-center items-center space-x-2">
                                <?php if (strtolower($user['role']) === 'admin'): ?>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <form action="update_user_role.php" method="POST" class="inline-block">
                                            <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['id']) ?>">
                                            <button type="submit" name="make_teacher" class="bg-green-700 text-white py-1 px-3 rounded hover:bg-green-800 transition text-xs font-bold shadow-sm">Make Teacher</button>
                                        </form>
                                        <form action="update_user_role.php" method="POST" class="inline-block">
                                            <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['id']) ?>">
                                            <button type="submit" name="make_student" class="bg-gray-500 text-white py-1 px-3 rounded hover:bg-gray-600 transition text-xs font-bold shadow-sm">Make Student</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-sm text-gray-400 italic">Current User</span>
                                    <?php endif; ?>
                                <?php elseif (strtolower($user['role']) === 'teacher'): ?>
                                    <form action="update_user_role.php" method="POST" class="inline-block">
                                        <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['id']) ?>">
                                        <button type="submit" name="make_admin" class="bg-red-700 text-white py-1 px-3 rounded hover:bg-blue-600 transition text-xs font-bold shadow-sm">Make Admin</button>
                                    </form>
                                    <form action="update_user_role.php" method="POST" class="inline-block">
                                        <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['id']) ?>">
                                        <button type="submit" name="make_student" class="bg-gray-500 text-white py-1 px-3 rounded hover:bg-gray-600 transition text-xs font-bold shadow-sm">Make Student</button>
                                    </form>
                                <?php else: ?>
                                    <form action="update_user_role.php" method="POST" class="inline-block">
                                        <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['id']) ?>">
                                        <button type="submit" name="make_admin" class="bg-red-700 text-white py-1 px-3 rounded hover:bg-blue-600 transition text-xs font-bold shadow-sm">Make Admin</button>
                                    </form>
                                    <form action="update_user_role.php" method="POST" class="inline-block">
                                        <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['id']) ?>">
                                        <button type="submit" name="make_teacher" class="bg-green-700 text-white py-1 px-3 rounded hover:bg-blue-800 transition text-xs font-bold shadow-sm">Make Teacher</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>
</body>
</html>
