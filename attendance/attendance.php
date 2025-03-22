<?php
session_start();
include '../config/connection.php';

if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'Teacher' && $_SESSION['user_role'] !== 'admin')) {
    header('Location: ../auth/login.php');
    exit();
}

include '../config/connection.php';

// Fetch students with names and roll numbers
$students = $conn->query("SELECT id, name, roll_no FROM students");

// Fetch subjects from the courses table
$subjects = $conn->query("SELECT id, course_name FROM courses");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'];
    $subject = $_POST['subject'];

    foreach ($_POST['attendance'] as $student_id => $status) {
        $sql = "INSERT INTO attendance (student_id, subject, date, status) 
                VALUES ('$student_id', '$subject', '$date', '$status')";
        $conn->query($sql);
    }

    echo "Attendance marked successfully!";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Mark Attendance</title>
    <link rel="stylesheet" href="attendance_style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
</head>
<body>
<div class="header">
    <h1>Student Management System</h1>
    <div class="nav-links">
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="../index.php">Dashboard</a>
            <a href="../read.php">All Students</a>
            <a href="../create.php">Add New Student</a>
            <a href="view.php">View Attendance</a>
            <a href="../auth/logout.php">Logout (<?php echo isset($_SESSION['username']) ? $_SESSION['username'] : 'User'; ?>)</a>
        <?php else: ?>
            <a href="../auth/login.php">Login</a>
            <a href="../auth/register.php">Register</a>
        <?php endif; ?>
    </div>
</div>
<div class="container">
    <h2>Mark Attendance</h2>
    <form method="POST">
        <label>Date:</label>
        <input type="date" name="date" required><br><br>

        <label>Subject:</label>
        <select name="subject" required>
            <?php while ($row = $subjects->fetch_assoc()) { ?>
                <option value="<?php echo $row['course_name']; ?>"><?php echo $row['course_name']; ?></option>
            <?php } ?>
        </select><br><br>

        <table border="1">
            <tr>
                <th>Student ID</th>
                <th>Roll Number</th>
                <th>Student Name</th>
                <th>Status</th>
            </tr>
            <?php while ($row = $students->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['roll_no']; ?></td>
                    <td><?php echo $row['name']; ?></td>
                    <td>
                        <select name="attendance[<?php echo $row['id']; ?>]">
                            <option value="Present">Present</option>
                            <option value="Absent">Absent</option>
                            <option value="Late">Late</option>
                        </select>
                    </td>
                </tr>
            <?php } ?>
        </table>

        <button type="submit">Mark Attendance</button>
    </form>
</div>
</body>
</html>