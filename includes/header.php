<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management System</title>
    <!-- Tailwind CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
        }
        .header {
            background-color: #4CAF50;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            color: white;
        }
        .nav-links a {
            color: white;
            text-decoration: none;
            margin: 0 10px;
            padding: 6px 12px;
            border-radius: 5px;
            transition: background-color 0.5s;
        }
        .nav-links a:hover {
            background-color: #3e8e41;
            text-decoration: none;
            color:rgb(223, 214, 214);
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Student Management System</h1>
        <div class="nav-links">
        <?php if (isset($_SESSION['user_id'])): ?>
    <a href="index.php">Dashboard</a>
    <a href="read.php">All Students</a>
    <a href="create.php">Add New Student</a>
    <a href="auth/logout.php">Logout (<?php echo isset($_SESSION['username']) ? $_SESSION['username'] : 'User'; ?>)</a>
<?php else: ?>
    <a href="auth/login.php">Login</a>
    <a href="auth/register.php">Register</a>
<?php endif; ?>

        </div>
    </div>
