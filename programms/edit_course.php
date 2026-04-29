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
    $stmt = $conn->prepare("SELECT * FROM courses WHERE id = ?");
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

// Fetch all programs
$programs = $conn->query("SELECT * FROM programs");

// Update the course
if (isset($_POST['update_course'])) {
    $course_name = $_POST['course_name'];
    $course_code = $_POST['course_code'];
    $program_id = $_POST['program_id'];

    $stmt = $conn->prepare("UPDATE courses SET course_name = ?, course_code = ?, program_id = ? WHERE id = ?");
    $stmt->bind_param("ssii", $course_name, $course_code, $program_id, $course_id);
    $stmt->execute();

    header("Location: manage_courses.php");
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
                <label class="block mb-1">Course Name</label>
                <input type="text" name="course_name" value="<?= $course['course_name'] ?>" required class="w-full p-2 border border-gray-300 rounded">
            </div>
            <div class="mb-4">
                <label class="block mb-1">Course Code</label>
                <input type="text" name="course_code" value="<?= $course['course_code'] ?>" required class="w-full p-2 border border-gray-300 rounded">
            </div>
            <div class="mb-4">
                <label class="block mb-1">Program</label>
                <select name="program_id" required class="w-full p-2 border border-gray-300 rounded">
                    <option value="">Select Program</option>
                    <?php while ($program = $programs->fetch_assoc()): ?>
                        <option value="<?= $program['id'] ?>" <?= $program['id'] == $course['program_id'] ? 'selected' : '' ?>><?= $program['program_name'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <button type="submit" name="update_course" class="bg-blue-500 text-white px-4 py-2 rounded">Update Course</button>
        </form>
    </div>
</div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>

