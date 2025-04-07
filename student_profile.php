<?php
session_start();
include 'config/connection.php';
include "includes/header.php";
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

$id = $_GET['id'] ?? $_SESSION['user_id'];
if (!$id) {
    die("Error: Student ID not found.");
}

$sql = "SELECT students.*, programs.program_name 
                FROM students 
                LEFT JOIN student_courses ON students.id = student_courses.student_id
                LEFT JOIN courses ON student_courses.course_id = courses.id
                LEFT JOIN programs ON courses.program_id = programs.id
                WHERE students.id = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("SQL Error: " . $conn->error);
}

$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $student = $result->fetch_assoc();
} else {
    die("Error: Student not found.");
}

$course_sql = "SELECT courses.course_name FROM courses
                    INNER JOIN student_courses ON courses.id = student_courses.course_id
                    WHERE student_courses.student_id = ?";

$course_stmt = $conn->prepare($course_sql);
if (!$course_stmt) {
    die("SQL Error in course query: " . $conn->error);
}

$course_stmt->bind_param("i", $id);
$course_stmt->execute();
$course_result = $course_stmt->get_result();
$courses = [];

while ($row = $course_result->fetch_assoc()) {
    $courses[] = $row['course_name'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #f0f8ff, #e6e6fa);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .dark-mode {
            background-color: #1a202c;
            color: #e2e8f0;
        }
        .profile-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.08);
            background: white;
            transition: transform 0.3s ease;
        }
        .dark-mode .profile-container {
            background-color: #2d3748;
        }
        .profile-container:hover {
            transform: translateY(-19px);
        }
        .profile-container img {
            border: 5px solid #a8dadc;
            max-width: 150px;
            margin-bottom: 25px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        .profile-container img:hover {
            transform: scale(1.05);
        }
        .profile-container p {
            font-size: 1.1rem;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            color: #333;
        }
        .dark-mode .profile-container p {
            color: #e2e8f0;
        }
        .profile-container p strong {
            margin-right: 15px;
            min-width: 160px;
            color: #2a6f97;
        }
        .dark-mode .profile-container p strong {
            color: #90cdf4;
        }
        .profile-container p i {
            margin-right: 12px;
            color: #457b9d;
            font-size: 1.2rem;
        }
        .dark-mode .profile-container p i {
            color: #63b3ed;
        }
        .profile-container h2 {
            color: #1d3557;
            margin-bottom: 30px;
            text-align: center;
            font-weight: 600;
        }
        .dark-mode .profile-container h2 {
            color: #90cdf4;
        }
        .btn-edit {
            background-color: #a8dadc;
            color: #1d3557;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
            margin-top: 30px;
            font-weight: 500;
            transition: background-color 0.3s ease;
        }
        .btn-edit:hover {
            background-color: #457b9d;
            color: white;
        }
        .profile-container p, .profile-container h2, .btn-edit {
            opacity: 0;
            animation: fadeIn 0.8s ease-out forwards;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .profile-container p:nth-child(n+4), .btn-edit {
            animation-delay: 0.2s;
        }
        .img-center {
            display: flex;
            justify-content: center;
            align-items: center;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="d-flex justify-content-end mb-4">
            <button id="darkModeToggle" class="btn btn-secondary">Toggle Dark Mode</button>
        </div>
        <div class="profile-container">
            <h2><i class="fas fa-user-graduate"></i> Student Profile</h2>
            <div class="text-center img-center">
                <img src="uploads/<?php echo htmlspecialchars($student['result_card']); ?>" alt="Result Card" class="img-fluid">
            </div>
            <p><i class="fas fa-user"></i> <strong>Name:</strong> <?php echo htmlspecialchars($student['name']); ?></p>
            <p><i class="fas fa-id-card"></i> <strong>Roll No:</strong> <?php echo htmlspecialchars($student['roll_no']); ?></p>
            <p><i class="fas fa-graduation-cap"></i> <strong>Marks:</strong> <?php echo htmlspecialchars($student['marks']); ?></p>
            <p><i class="fas fa-certificate"></i> <strong>Last Qualification:</strong> <?php echo htmlspecialchars($student['last_qualification']); ?></p>
            <p><i class="fas fa-venus-mars"></i> <strong>Gender:</strong> <?php echo htmlspecialchars($student['gender']); ?></p>
            <p><i class="fas fa-map-marker-alt"></i> <strong>Address:</strong> <?php echo htmlspecialchars($student['address']); ?></p>
            <p><i class="fas fa-book"></i> <strong>Program:</strong> <?php echo htmlspecialchars($student['program_name'] ?? 'Not Assigned'); ?></p>
            <p><i class="fas fa-list-ul"></i> <strong>Courses:</strong> <?php echo !empty($courses) ? implode(", ", $courses) : "No courses assigned"; ?></p>

            <div class="text-center">
                <a href="update.php?id=<?php echo $student['id']; ?>" class="btn-edit">
                    <i class="fas fa-edit"></i> Edit Profile
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const darkModeToggle = document.getElementById('darkModeToggle');
        const body = document.body;

        darkModeToggle.addEventListener('click', () => {
            body.classList.toggle('dark-mode');
            if (body.classList.contains('dark-mode')) {
                localStorage.setItem('darkMode', 'enabled');
            } else {
                localStorage.setItem('darkMode', 'disabled');
            }
        });

        if (localStorage.getItem('darkMode') === 'enabled') {
            body.classList.add('dark-mode');
        }
    </script>
</body>
</html>