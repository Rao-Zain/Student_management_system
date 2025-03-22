<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

include '../config/connection.php';


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

    // Fetch unique subjects for the student
    $subjectsQuery = "SELECT DISTINCT subject FROM attendance WHERE student_id = '$student_id'";
    $subjectsResult = $conn->query($subjectsQuery);
    $subjects = [];

    if ($subjectsResult && $subjectsResult->num_rows > 0) {
        while ($row = $subjectsResult->fetch_assoc()) {
            $subjects[] = $row['subject'];
        }
    }

    // Fetch all attendance records for the student
    $allAttendanceQuery = "SELECT * FROM attendance WHERE student_id = '$student_id' ORDER BY date DESC";
    $allAttendanceResult = $conn->query($allAttendanceQuery);
    $allAttendance = [];

    if ($allAttendanceResult && $allAttendanceResult->num_rows > 0) {
        while ($row = $allAttendanceResult->fetch_assoc()) {
            $allAttendance[] = $row;
        }
    }
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
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .low-attendance { color: red; }
        .good-attendance { color: green; }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2>Attendance Report for <?php echo $student['name']; ?></h2>
    <p><strong>Roll No:</strong> <?php echo $student['roll_no']; ?></p>

    <?php if (!empty($subjects)): ?>
        <table class="table table-bordered table-striped mt-4">
            <thead>
                <tr>
                    <?php foreach ($subjects as $subject): ?>
                        <th colspan="4"><?php echo $subject; ?></th>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <?php foreach ($subjects as $subject): ?>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Percentage</th>
                        <th>Quality</th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php
                $maxLectures = 32;
                $attendanceBySubject = [];

                foreach ($allAttendance as $record) {
                    $attendanceBySubject[$record['subject']][] = $record;
                }

                $maxRows = 0;
                foreach ($attendanceBySubject as $subjectAttendance) {
                    $maxRows = max($maxRows, count($subjectAttendance));
                }

                for ($i = 0; $i < $maxRows; $i++) {
                    echo "<tr>";
                    foreach ($subjects as $subject) {
                        if (isset($attendanceBySubject[$subject][$i])) {
                            $record = $attendanceBySubject[$subject][$i];
                            $totalLectures = count($attendanceBySubject[$subject]);
                            $presentLectures = count(array_filter($attendanceBySubject[$subject], function($r) { return $r['status'] == 'Present' || $r['status'] == 'Late'; }));
                            $percentage = ($totalLectures > 0) ? ($presentLectures / $maxLectures) * 100 : 0;
                            $quality = ($percentage >= 75) ? 'Good' : 'Low';
                            $qualityClass = ($percentage >= 75) ? 'good-attendance' : 'low-attendance';

                            echo "<td>" . $record['date'] . "</td>";
                            echo "<td>" . $record['status'] . "</td>";
                            echo "<td>" . round($percentage, 2) . "%</td>";
                            echo "<td class='" . $qualityClass . "'>" . $quality . "</td>";
                        } else {
                            echo "<td colspan='4'></td>";
                        }
                    }
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-warning">No attendance records found for this student.</div>
    <?php endif; ?>

    <a href="../index.php" class="btn btn-primary mt-3">Back to Dashboard</a>
</div>
</body>
</html>