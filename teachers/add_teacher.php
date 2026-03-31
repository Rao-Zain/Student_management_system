<?php
session_start();
require_once '../config/connection.php';
require_once 'header.php';

// Check if the user is logged in as an admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'Admin') {
    header('Location: ../index.php');
    exit();
}

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the data from the form
    $username = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = 'Teacher';

    // Insert the teacher into the users table
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $email, $password, $role);

    if ($stmt->execute()) {
        echo "<div class='bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative' role='alert'>
        <strong class='font-bold'>Success!</strong>
        <span class='block sm:inline'>Teacher added successfully!</span>
        </div>";
    } else {
        echo "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative' role='alert'>
        <strong class='font-bold'>Error!</strong>
        <span class='block sm:inline'>Error: " . $conn->error . "</span>
        </div>";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Teacher</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
</head>
<body class="bg-gray-100 ">

    <div class="max-w-md mx-auto bg-white rounded-xl shadow-md overflow-hidden md:max-w-2xl p-6 mt-8">
        <h2 class="text-2xl font-bold mb-4 text-center">Add Teacher</h2>

        <!-- Teacher Registration Form -->
        <form method="POST" class="space-y-4">
            
            <!-- Name Field -->
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Name:</label>
                <input type="text" name="name" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>

            <!-- Email Field -->
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Email:</label>
                <input type="email" name="email" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>

            <!-- Password Field -->
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Password:</label>
                <input type="password" name="password" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>

            <!-- Submit Button -->
            <div class="text-center">
                <input type="submit" value="Add Teacher" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
            </div>
        </form>
    </div>

</body>
</html>
