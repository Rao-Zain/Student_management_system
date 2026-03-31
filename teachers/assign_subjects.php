<?php
require_once '../config/connection.php';

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
    header("Location: ../auth/login.php");
    exit();
}
include 'header.php';

// Fetch all teachers from the 'users' table, filtering by role
$query = "SELECT id, username FROM users WHERE role = 'Teacher'";
$teacher_result = $conn->query($query);
$teachers = [];
while ($row = $teacher_result->fetch_assoc()) {
    $teachers[] = $row;
}

// Fetch all courses
$course_query = "SELECT id, course_name FROM courses";
$course_result = $conn->query($course_query);
$courses = [];
while ($row = $course_result->fetch_assoc()) {
    $courses[] = $row;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teacher_id = $_POST['teacher_id'];
    $course_id = $_POST['course_id'];

    if ($teacher_id === "select" || $course_id === "select") {
        $error_message = "Please select a valid teacher and course.";
    } else {
        // Check if the subject is already assigned to the teacher
        $check_query = "SELECT * FROM teacher_subjects WHERE teacher_id = ? AND course_id = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("ii", $teacher_id, $course_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows > 0) {
            $error_message = "This subject is already assigned to the selected teacher.";
        } else {
            // Assign the subject to the teacher
            $stmt = $conn->prepare("INSERT INTO teacher_subjects (teacher_id, course_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $teacher_id, $course_id);
            if ($stmt->execute()) {
                $success_message = "Subject assigned successfully!";
            } else {
                $error_message = "Error assigning subject: " . $stmt->error;
            }
            $stmt->close();
        }
        $check_stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Subjects to Teachers</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-md mx-auto bg-white rounded-xl shadow-md overflow-hidden md:max-w-2xl p-6">
        <h1 class="text-2xl font-bold mb-5 text-center">Assign Subjects to Teachers</h1>

        <?php if (isset($success_message)): ?>
            <p class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert"><?php echo $success_message; ?></p>
        <?php elseif (isset($error_message)): ?>
            <p class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert"><?php echo $error_message; ?></p>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-4">
            <div>
                <label for="teacher_id" class="block mb-1 font-medium">Select Teacher:</label>
                <select name="teacher_id" id="teacher_id" class="w-full p-2 border rounded">
                    <option value="select">Select a Teacher</option>
                    <?php foreach ($teachers as $teacher): ?>
                        <option value="<?php echo $teacher['id']; ?>"><?php echo $teacher['username']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="course_id" class="block mb-1 font-medium">Select Course:</label>
                <select name="course_id" id="course_id" class="w-full p-2 border rounded">
                    <option value="select">Select a Course</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?php echo $course['id']; ?>"><?php echo $course['course_name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex flex-col space-y-2">
                <button type="submit" class="bg-green-500 text-white py-2 px-4 rounded">Assign Subject</button>
                <a href="view_teacher.php" class="bg-yellow-500 text-white py-2 px-4 rounded">View Teachers</a>
                <a href="teacher_dashboard.php" class="bg-blue-500 text-white py-2 px-4 rounded">To Dashboard</a>
            </div>
        </form>
    </div>
</body>
</html>