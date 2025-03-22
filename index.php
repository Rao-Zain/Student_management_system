
<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}
?>
<?php
include 'config/connection.php';
include "includes/header.php";


$total_students = $conn->query("SELECT COUNT(*) as count FROM students")->fetch_assoc()['count'];

$male_students = $conn->query("SELECT COUNT(*) as count FROM students WHERE gender = 'Male'")->fetch_assoc()['count'];

$female_students = $conn->query("SELECT COUNT(*) as count FROM students WHERE gender = 'Female'")->fetch_assoc()['count'];

$course_data = $conn->query("SELECT course, COUNT(*) as count FROM students GROUP BY course");
$courses = [];
$course_counts = [];
while ($row = $course_data->fetch_assoc()) {
    $courses[] = $row['course'];
    $course_counts[] = (int)$row['count'];
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Management System - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-gray-100">
<div class="container">
    <div class="card shadow mb-4">
        <div class="card-body text-center buttons-div" >
            <!-- <h1 class="card-title">Student Management System Dashboard</h1> -->
           <strong> <p class="mb-4">Welcome to the Student Management System. You can manage student information from here.</p></strong>
            <!-- <div class="flex buttons space-x-4 mb-6 mt-5">
        <a href="read.php" class="bg-blue-500 text-white px-4 py-2 rounded ml-6">View All Students</a>
        <a href="create.php" class="bg-green-500 text-white px-4 py-2 rounded ml-4">Add New Student</a> -->
    </div>
        </div>
    </div>
</div>
<div class="container mx-auto p-6">
    
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
        <div class="bg-white shadow-lg rounded-lg p-6">
            <h2 class="text-xl font-bold mb-4">Total Students</h2>
            <p class="text-3xl"><?php echo $total_students; ?></p>
        </div>

        <div class="bg-white shadow-lg rounded-lg p-6">
            <h2 class="text-xl font-bold mb-4">Male Students</h2>
            <p class="text-3xl"><?php echo $male_students; ?></p>
        </div>

        <div class="bg-white shadow-lg rounded-lg p-6">
            <h2 class="text-xl font-bold mb-4">Female Students</h2>
            <p class="text-3xl"><?php echo $female_students; ?></p>
        </div>

        <div class="card bg-white rounded-2xl shadow-lg p-6 text-center transition-transform transform-gpu hover:scale-105 hover:shadow-2xl hover:bg-blue-10 transition-colors duration-300">
            <h2 class="text-xl font-bold mb-2">Teacher's Dashboard</h2>
            <p class="text-gray-600 mb-4">Easily Manage Teachers Activities.</p>
              <a href="teachers/teacher_dashboard.php" class="bg-blue-500 text-white px-4 py-2 rounded-full hover:bg-blue-600 transition duration-300">Go</a>
        </div>
        <div class="card bg-white rounded-2xl shadow-lg p-6 text-center transition-transform transform-gpu hover:scale-105 hover:shadow-2xl hover:bg-blue-10 transition-colors duration-300">
            <h2 class="text-xl font-bold mb-2">Assign a Subject</h2>
            <p class="text-gray-600 mb-4">Easily Manage Teachers Activities.</p>
              <a href="teachers/assign_subjects.php" class="bg-blue-500 text-white px-4 py-2 rounded-full hover:bg-blue-600 transition duration-300">Go</a>
        </div>
        <div class="card bg-white rounded-2xl shadow-lg p-6 text-center transition-transform transform-gpu hover:scale-105 hover:shadow-2xl hover:bg-blue-10 transition-colors duration-300">
            <h2 class="text-xl font-bold mb-2">Mark Attendance</h2>
            <p class="text-gray-600 mb-4">Easily Manage Teachers Activities.</p>
              <a href="attendance/attendance.php" class="bg-blue-500 text-white px-4 py-2 rounded-full hover:bg-blue-600 transition duration-300">Go</a>
        </div>
        <div class="card bg-white rounded-2xl shadow-lg p-6 text-center transition-transform transform-gpu hover:scale-105 hover:shadow-2xl hover:bg-blue-10 transition-colors duration-300">
            <h2 class="text-xl font-bold mb-2">View Attendance</h2>
            <p class="text-gray-600 mb-4">Easily Manage Teachers Activities.</p>
              <a href="attendance/view.php" class="bg-blue-500 text-white px-4 py-2 rounded-full hover:bg-blue-600 transition duration-300">Go</a>
        </div>

       
    <?php    
      //session_start();
if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'admin') {
    
    echo '

     <div class="card bg-white rounded-2xl shadow-lg p-6 text-center transition-transform transform-gpu hover:scale-105 hover:shadow-2xl hover:bg-blue-10 transition-colors duration-300">
            <h2 class="text-xl font-bold mb-2">Manage Programs</h2>
            <p class="text-gray-600 mb-4">Easily Manage Teachers Activities.</p>
              <a href="programms/manage_programs.php" class="bg-blue-500 text-white px-4 py-2 rounded-full hover:bg-blue-600 transition duration-300">Go</a>
        </div>
     <div class="card bg-white rounded-2xl shadow-lg p-6 text-center transition-transform transform-gpu hover:scale-105 hover:shadow-2xl hover:bg-blue-10 transition-colors duration-300">
            <h2 class="text-xl font-bold mb-2">Manage Courses</h2>
            <p class="text-gray-600 mb-4">Easily Manage Teachers Activities.</p>
              <a href="programms/manage_courses.php" class="bg-blue-500 text-white px-4 py-2 rounded-full hover:bg-blue-600 transition duration-300">Go</a>
        </div>
     <div class="card bg-white rounded-2xl shadow-lg p-6 text-center transition-transform transform-gpu hover:scale-105 hover:shadow-2xl hover:bg-blue-10 transition-colors duration-300">
            <h2 class="text-xl font-bold mb-2">Manage Users</h2>
            <p class="text-gray-600 mb-4">Easily Manage Users Activities.</p>
              <a href="manage_users.php" class="bg-blue-500 text-white px-4 py-2 rounded-full hover:bg-blue-600 transition duration-300">Go</a>
        </div>';
}
    ?>
 </div>

    <!-- Action Buttons -->
    <!-- <div class="flex space-x-4 mb-6">
        <a href="read.php" class="bg-blue-500 text-white px-4 py-2 rounded">View All Students</a>
        <a href="create.php" class="bg-green-500 text-white px-4 py-2 rounded">Add New Student</a>
    </div> -->

    <div class="bg-white shadow-lg rounded-lg p-6">
        <h2 class="text-xl font-bold mb-4">Students Per Course</h2>
        <canvas id="courseChart"></canvas>
    </div>
</div>

<script>
    const ctx = document.getElementById('courseChart').getContext('2d');
    const courseChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($courses); ?>,
            datasets: [{
                label: 'Number of Students',
                data: <?php echo json_encode($course_counts); ?>,
                backgroundColor: 'rgba(75, 192, 192, 0.5)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    
</script>

</body>
</html>