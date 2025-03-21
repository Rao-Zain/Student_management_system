<?php
session_start();
include '../config/connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

// Add Course
if (isset($_POST['add_course'])) {
    $course_name = $_POST['course_name'];
    $program_id = $_POST['program_id'];

    $stmt = $conn->prepare("INSERT INTO courses (course_name, program_id) VALUES (?, ?)");
    $stmt->bind_param("si", $course_name, $program_id);
    $stmt->execute();
    header("Location: manage_courses.php");
    exit();
}

// Edit Course
if (isset($_POST['edit_course'])) {
    $id = intval($_POST['course_id']);
    $course_name = $_POST['course_name'];
    $program_id = $_POST['program_id'];

    $stmt = $conn->prepare("UPDATE courses SET course_name = ?, program_id = ? WHERE id = ?");
    $stmt->bind_param("sii", $course_name, $program_id, $id);
    $stmt->execute();
    header("Location: manage_courses.php");
    exit();
}

// Delete Course
if (isset($_GET['delete_course'])) {
    $id = intval($_GET['delete_course']);

    try {
        mysqli_begin_transaction($conn);

        $stmt = $conn->prepare("DELETE FROM courses WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $reorderQuery = "SET @count = 0; UPDATE courses SET id = @count := @count + 1; ALTER TABLE courses AUTO_INCREMENT = 1;";

            if (mysqli_multi_query($conn, $reorderQuery)) {
                do {
                    if ($result = mysqli_store_result($conn)) {
                        mysqli_free_result($result);
                    }
                } while (mysqli_more_results($conn) && mysqli_next_result($conn));

                mysqli_commit($conn);

                header("Location: manage_courses.php");
                exit();
            } else {
                throw new Exception("Failed to reorder IDs: " . mysqli_error($conn));
            }
        } else {
            throw new Exception("Failed to delete course: " . mysqli_error($conn));
        }
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "Error: " . $e->getMessage();
    } finally {
        $stmt->close();
        $conn->close();
    }
}

$courses = $conn->query("SELECT courses.*, programs.program_name FROM courses JOIN programs ON courses.program_id = programs.id");
$programs = $conn->query("SELECT * FROM programs");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Courses</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="header_style.css" rel="stylesheet">
</head>
<body class="bg-gray-100">

<div class="header">
    <h1>Student Management System</h1>
    <div class="nav-links">
        <a href="../index.php">Dashboard</a>
        <a href="../read.php">All Students</a>
        <a href="../create.php">Add New Student</a>
        <a href="../auth/logout.php">Logout</a>
    </div>
</div>

<div class="container mx-auto p-8">
    <div class="bg-white shadow-md rounded p-6 mb-6">
        <h2 class="text-2xl font-bold mb-4">Add New Course</h2>
        <form method="POST" action="">
            <input type="text" name="course_name" placeholder="Course Name" required class="w-full p-2 mb-4 border rounded">
            <select name="program_id" required class="w-full p-2 mb-4 border rounded">
                <option value="">Select Program</option>
                <?php while ($program = $programs->fetch_assoc()): ?>
                    <option value="<?= $program['id'] ?>"><?= $program['program_name'] ?></option>
                <?php endwhile; ?>
            </select>
            <button type="submit" name="add_course" class="bg-blue-500 text-white px-4 py-2 rounded">Add Course</button>
        </form>
    </div>

    <div class="bg-white shadow-md rounded p-6">
        <h3 class="text-2xl font-bold mb-4">All Courses</h3>
        <table class="w-full text-left border-collapse">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Course Name</th>
                    <th>Program</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $courses->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['course_name'] ?></td>
                        <td><?= $row['program_name'] ?></td>
                        <td>
                            <a href="edit_course.php?id=<?= $row['id'] ?>" class="bg-green-500 text-white px-2 py-1 rounded">Edit</a>
                            <a href="manage_courses.php?delete_course=<?= $row['id'] ?>" class="bg-red-500 text-white px-2 py-1 rounded">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
