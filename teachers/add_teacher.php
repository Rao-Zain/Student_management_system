<?php
session_start();
require_once '../config/connection.php';

// Check if the user is logged in as an admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    header('Location: ../index.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $subjects = $_POST['subjects'];

    $stmt = $conn->prepare("INSERT INTO teachers (name, email, password, subjects) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $password, $subjects);

    if ($stmt->execute()) {
        echo "Teacher added successfully!";
    } else {
        echo "Error: " . $conn->error;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Teacher</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <h2>Add Teacher</h2>
    <form method="POST">
        <label>Name:</label><br>
        <input type="text" name="name" required><br>

        <label>Email:</label><br>
        <input type="email" name="email" required><br>

        <label>Password:</label><br>
        <input type="password" name="password" required><br>

        <label>Subjects (Comma-separated):</label><br>
        <input type="text" name="subjects" placeholder="e.g., Math, Science" required><br>

        <input type="submit" value="Add Teacher">
    </form>
</body>
</html>
