<?php
session_start();
include 'config/connection.php';
include "includes/header.php";

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php?error=Unauthorized access');
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
        <h1 class="text-3xl font-bold mb-6">Manage Users</h1>
        <div class="overflow-x-auto">
            <table class="w-full table-auto shadow-md rounded-lg">
                <thead>
                    <tr class="">
                        <th class="px-4 py-2">ID</th>
                        <th class="px-4 py-2">Username</th>
                        <th class="px-4 py-2">Email</th>
                        <th class="px-4 py-2">Role</th>
                        <th class="px-4 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $result->fetch_assoc()): ?>
                        <tr class="border-b">
                            <td class="px-4 py-2 text-center"><?= $user['id'] ?></td>
                            <td class="px-4 py-2 text-center"><?= $user['username'] ?></td>
                            <td class="px-4 py-2 text-center"><?= $user['email'] ?></td>
                            <td class="px-4 py-2 text-center"><?= $user['role'] ?></td>
                            <td class="px-4 py-2 text-center">
                                <?php if ($user['role'] === 'admin'): ?>
                                    <span class="text-green-600 font-semibold">Admin</span>
                                <?php elseif ($user['role'] === 'Teacher'): ?>
                                    <span class="text-yellow-600 font-semibold">Teacher</span>
                                <?php else: ?>
                                    <form action="update_user_role.php" method="POST" class="inline-block">
                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                        <button type="submit" name="make_admin" class="bg-blue-500 text-white py-1 px-3 rounded hover:bg-blue-600 transition">Make Admin</button>
                                    </form>
                                    <form action="update_user_role.php" method="POST" class="inline-block">
                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                        <button type="submit" name="make_teacher" class="bg-orange-500 text-white py-1 px-3 rounded hover:bg-orange-600 transition">Make Teacher</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
