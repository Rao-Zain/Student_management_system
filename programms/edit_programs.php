<?php
session_start();
include '../config/connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
    header('Location: ../auth/login.php');
    exit();
}

// Check if the edit_course ID is provided
if (isset($_GET['id'])) {
    $course_id = intval($_GET['id']);

    // Fetch the course data
    $stmt = $conn->prepare("SELECT * FROM programs WHERE id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $course = $result->fetch_assoc();
    } else {
        die("Course not found. Please check the URL.");
    }

    $stmt->close();
} else {
    die("Invalid request. No course ID provided.");
}

// Update the course
if (isset($_POST['update_programs'])) {
    $program_name = $_POST['course_name'];  // Change to 'course_name' to match your input name

    $stmt = $conn->prepare("UPDATE programs SET program_name = ? WHERE id = ?");
    $stmt->bind_param("si", $program_name, $course_id); // Use the existing $course_id for update
    $stmt->execute();

    header("Location: manage_programs.php");
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Course</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="header_style.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
<div class="container mx-auto p-8">
    <div class="bg-white shadow-md rounded p-6">
        <h2 class="text-2xl font-bold mb-4">Edit Course</h2>
        <form method="POST">
            <div class="mb-4">
                <input type="text" name="course_name" value="<?= htmlspecialchars($course['program_name']) ?>" required class="w-full p-2 border border-gray-300 rounded">
            </div>
            
            <button type="submit" name="update_programs" class="bg-blue-500 text-white px-4 py-2 rounded">Update Program</button>
        </form>
    </div>
</div>
</body>
</html>
