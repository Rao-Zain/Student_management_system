<?php
require_once '../config/connection.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Fetch all teachers
$teacher_query = "SELECT id, username FROM users WHERE role = 'Teacher'";
$teacher_result = $conn->query($teacher_query);
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

    $stmt = $conn->prepare("INSERT INTO teacher_subjects (teacher_id, course_id) VALUES (?, ?)");
    
    if ($stmt) {
        $stmt->bind_param("ii", $teacher_id, $course_id);

        if ($stmt->execute()) {
            $success_message = "Subject assigned successfully!";
        } else {
            $error_message = "Error assigning subject: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $error_message = "Error preparing statement: " . $conn->error;
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
<body>
    <div class="container mx-auto mt-5 p-5 bg-white rounded shadow-lg">
        <h1 class="text-2xl font-bold mb-5">Assign Subjects to Teachers</h1>
        
        <?php if (isset($success_message)): ?>
            <p class="text-green-500 mb-3"><?php echo $success_message; ?></p>
        <?php elseif (isset($error_message)): ?>
            <p class="text-red-500 mb-3"><?php echo $error_message; ?></p>
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

            <div>
                <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded">Assign Subject</button>
            </div>
            <div>
                <a href="teacher_dashboard.php" class="bg-blue-500 text-white py-2 px-4  rounded">Go To Dashboard</a>
            </div>
        </form>
    </div>
</body>
</html>
