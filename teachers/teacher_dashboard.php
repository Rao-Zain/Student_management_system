<?php
session_start();
if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'Teacher' && $_SESSION['user_role'] !== 'admin')) {

    header("Location: auth/login.php");
    exit();
}
include '../config/connection.php';
$teacher_id = $_SESSION['user_id'];

// Fetch assigned subjects for this teacher
// Fetch assigned subjects for this teacher
if ($_SESSION['user_role'] === 'Teacher') {
    $stmt = $conn->prepare("SELECT courses.course_name 
                            FROM teacher_subjects 
                            JOIN courses ON teacher_subjects.course_id = courses.id 
                            WHERE teacher_subjects.teacher_id = ?");
    $stmt->bind_param("i", $teacher_id);
} else if ($_SESSION['user_role'] === 'admin') {
    $stmt = $conn->prepare("SELECT users.username, courses.course_name 
                            FROM teacher_subjects 
                            JOIN courses ON teacher_subjects.course_id = courses.id 
                            JOIN users ON teacher_subjects.teacher_id = users.id");
}

if ($stmt->execute()) {
    $result = $stmt->get_result();
    $subjects = $result->fetch_all(MYSQLI_ASSOC);
} else {
    echo "Error executing query: " . $stmt->error;
}


if (isset($_SESSION['success_message'])) {
    echo '<p class="text-green-500 mb-3">' . $_SESSION['success_message'] . '</p>';
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    echo '<p class="text-red-500 mb-3">' . $_SESSION['error_message'] . '</p>';
    unset($_SESSION['error_message']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0f0f0;
            font-family: Arial, sans-serif;
        }
        .card {
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="bg-green-600 text-white p-4 flex justify-between items-center">
        <h1 class="text-2xl font-bold">Teacher Dashboard</h1>
        <div>
            <a href="../index.php" class="mr-4 text-white hover:text-gray-200">Admin Panel</a>
            <a href="assign_subjects.php" class="mr-4 text-white hover:text-gray-200">Assign Subjects</a>
            <a href="read.php" class="mr-4 text-white hover:text-gray-200">All Students</a>
            <a href="../attendance/attendance.php" class="mr-4 text-white hover:text-gray-200">Mark Attendance</a>
            <a href="../attendance/view.php" class="mr-4 text-white hover:text-gray-200">View Attendance Reports</a>
            <a href="../auth/logout.php" class="text-white hover:text-gray-200">Logout (<?php echo $_SESSION['username']; ?>)</a>
        </div>
    </div>

    <!-- Dashboard Content -->
    <div class="card bg-white rounded-2xl shadow-lg p-6 text-center mt-3 ml-7 container">
            <h1 class="text-xl font-bold m  b-2">Teacher Dashboard</h1>
            <h2 class="text-gray-600 mb-4">Assigned Subjects:</h2>
            <?php if (count($subjects) > 0): ?>
    <ul>
        <?php foreach ($subjects as $subject): ?>
            <li class="mb-2 bg-gray-200 p-2 rounded flex justify-between items-center">
                <?php echo $subject['course_name']; ?>
                <?php if ($_SESSION['user_role'] === 'admin'): ?>
                    <form action="remove_subject.php" method="POST" class="inline-block ml-4">
                    <input type="hidden" name="teacher_id" value="<?php echo $subject['teacher_id']; ?>">

                        <input type="hidden" name="course_name" value="<?php echo $subject['course_name']; ?>">
                        <button type="submit" class="bg-red-500 text-white px-2 py-1 rounded">Remove</button>
                    </form>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p class="bg-blue-500 text-white px-4 py-2 rounded-full hover:bg-blue-600 transition">No subjects assigned yet.</p>
<?php endif; ?>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-5">
            <!-- Card 1 -->
           
            <div class="card bg-white rounded-2xl shadow-lg p-6 text-center">
                <h2 class="text-xl font-bold mb-2">Mark Attendance</h2>
                <p class="text-gray-600 mb-4">Easily mark student attendance for your subjects.</p>
                <a href="../attendance/attendance.php" class="bg-blue-500 text-white px-4 py-2 rounded-full hover:bg-blue-600 transition">Go</a>
            </div>

            <!-- Card 2 -->
            <div class="card bg-white rounded-2xl shadow-lg p-6 text-center">
                <h2 class="text-xl font-bold mb-2">View Reports</h2>
                <p class="text-gray-600 mb-4">Check detailed attendance reports of your students.</p>
                <a href="../attendance/view.php" class="bg-blue-500 text-white px-4 py-2 rounded-full hover:bg-blue-600 transition">Go</a>
            </div>

            <!-- Card 3 -->
            <div class="card bg-white rounded-2xl shadow-lg p-6 text-center">
                <h2 class="text-xl font-bold mb-2">Profile Settings</h2>
                <p class="text-gray-600 mb-4">Update your profile and settings here.</p>
                <a href="teacher_profile.php" class="bg-blue-500 text-white px-4 py-2 rounded-full hover:bg-blue-600 transition">Go</a>
            </div>
        </div>
    </div>
</body>
</html>
