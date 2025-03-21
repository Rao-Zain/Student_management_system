<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

include '../config/connection.php';
include '../includes/header.php';

// Check if student_id is passed in URL
if (isset($_GET['student_id'])) {
    $student_id = $_GET['student_id'];

    // Fetch student details
    $studentQuery = "SELECT * FROM students WHERE id = '$student_id'";
    $studentResult = $conn->query($studentQuery);

    if ($studentResult && $studentResult->num_rows > 0) {
        $student = $studentResult->fetch_assoc();
    } else {
        echo "Student not found!";
        exit();
    }

    // Fetch attendance records for the student
    $attendanceQuery = "SELECT * FROM attendance WHERE student_id = '$student_id' ORDER BY date DESC";
    $attendanceResult = $conn->query($attendanceQuery);
} else {
    echo "No student selected!";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Report</title>
    <link rel="stylesheet" href="../css/style.css"> <!-- Link to your styling file -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>


<div class="container mt-5">
    <h2>Attendance Report for <?php echo $student['name']; ?></h2>
    <p><strong>Roll No:</strong> <?php echo $student['roll_no']; ?></p>

    <?php if ($attendanceResult && $attendanceResult->num_rows > 0): ?>
        <table class="table table-bordered table-striped mt-4">
            <thead>
            <tr>
                <th>Date</th>
                <th>Subject</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody>
            <?php while ($row = $attendanceResult->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['date']; ?></td>
                    <td><?php echo $row['subject']; ?></td>
                    <td><?php echo $row['status']; ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-warning">No attendance records found for this student.</div>
    <?php endif; ?>

    <a href="../index.php" class="btn btn-primary mt-3">Back to Dashboard</a>
</div>
</body>
</html>
