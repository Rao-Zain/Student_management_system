<?php
include 'config/connection.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Management System - Dashboard</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }
        .container {
            margin-top: 50px;
        }
        .card {
            border-radius: 10px;
        }
        .card-title {
            font-size: 24px;
        }
        .btn {
            border-radius: 20px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="card shadow mb-4">
        <div class="card-body text-center">
            <h2 class="card-title">Student Management System Dashboard</h2>
            <p>Welcome to the Student Management System. You can manage student information from here.</p>
            <a href="read.php" class="btn btn-primary btn-lg mt-3">View All Students</a>
            <a href="create.php" class="btn btn-success btn-lg mt-3">Add New Student</a>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
