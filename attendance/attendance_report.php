<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

include '../config/connection.php';
include 'attendance_header.php';
if (isset($_GET['student_id'])) {
    $student_id = $_GET['student_id'];

    $studentQuery = "SELECT * FROM students WHERE id = '$student_id'";
    $studentResult = $conn->query($studentQuery);

    if ($studentResult && $studentResult->num_rows > 0) {
        $student = $studentResult->fetch_assoc();
    } else {
        echo "<div class='container mt-5'><div class='alert alert-danger'>Student not found!</div></div>";
        exit();
    }

    $subjectsQuery = "SELECT DISTINCT subject FROM attendance WHERE student_id = '$student_id'";
    $subjectsResult = $conn->query($subjectsQuery);
    $subjects = [];

    if ($subjectsResult && $subjectsResult->num_rows > 0) {
        while ($row = $subjectsResult->fetch_assoc()) {
            $subjects[] = $row['subject'];
        }
    }

    $allAttendanceQuery = "SELECT * FROM attendance WHERE student_id = '$student_id' ORDER BY date DESC";
    $allAttendanceResult = $conn->query($allAttendanceQuery);
    $allAttendance = [];

    if ($allAttendanceResult && $allAttendanceResult->num_rows > 0) {
        while ($row = $allAttendanceResult->fetch_assoc()) {
            $allAttendance[] = $row;
        }
    }
} else {
    echo "<div class='container mt-5'><div class='alert alert-danger'>No student selected!</div></div>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Report | Student Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .low-attendance { color: #dc3545; font-weight: bold; }
        .good-attendance { color: #28a745; font-weight: bold; }
        .report-header { background: linear-gradient(135deg, #6a11cb, #2575fc); color: white; padding: 30px 20px; border-radius: 8px 8px 0 0; }
        .report-header h2 { margin-bottom: 5px; }
        .report-header p { margin-bottom: 0; }
        .card { border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); }
        .card-body { padding: 25px; }
        .card-footer { background-color: #f8f9fa; border-top: 1px solid #dee2e6; padding: 20px; text-align: center; }
        .btn-secondary { background-color: #6c757d; border-color: #6c757d; }
        .btn-secondary:hover { background-color: #5a6268; border-color: #5a6268; }
        .subject-section { margin-bottom: 20px; border: 1px solid #dee2e6; border-radius: 5px; padding: 15px; }
        .subject-title { font-weight: bold; margin-bottom: 10px; }
        .attendance-table { width: 100%; }
        .attendance-table th, .attendance-table td { padding: 8px; text-align: left; border-bottom: 1px solid #dee2e6; }
        .attendance-table thead th { background-color: #e9ecef; }
        .attendance-table tbody tr:nth-child(even) { background-color: #f8f9fa; }
    </style>
</head>
<body>
<div class="container mt-4">
    <div class="card">
        <div class="report-header">
            <h2 class="text-center"><i class="fas fa-chart-line me-2"></i> Attendance Report for <?php echo htmlspecialchars($student['name']); ?></h2>
            <p class="text-center"><i class="fas fa-id-card me-2"></i> <strong>Roll No:</strong> <?php echo htmlspecialchars($student['roll_no']); ?></p>
        </div>
        <div class="card-body">
            <?php if (!empty($subjects)): ?>
                <?php foreach ($subjects as $subject): ?>
                    <div class="subject-section">
                        <h4 class="subject-title"><?php echo htmlspecialchars($subject); ?></h4>
                        <table class="attendance-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Percentage</th>
                                    <th>Quality</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $subjectAttendance = array_filter($allAttendance, function($record) use ($subject) {
                                    return $record['subject'] == $subject;
                                });

                                $totalLectures = count($subjectAttendance);
                                $presentLectures = count(array_filter($subjectAttendance, function($r) { return $r['status'] == 'Present' || $r['status'] == 'Late'; }));
                                $percentage = ($totalLectures > 0) ? ($presentLectures / 32) * 100 : 0;
                                $quality = ($percentage >= 75) ? 'Good' : 'Low';
                                $qualityClass = ($percentage >= 75) ? 'good-attendance' : 'low-attendance';

                                foreach ($subjectAttendance as $record):
                                ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($record['date']); ?></td>
                                        <td><?php echo htmlspecialchars($record['status']); ?></td>
                                        <td><?php echo round($percentage, 2); ?>%</td>
                                        <td class="<?php echo $qualityClass; ?>"><?php echo htmlspecialchars($quality); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-warning">No attendance records found for this student.</div>
            <?php endif; ?>
        </div>
        <div class="card-footer">
            <a href="view.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i> Back to Page
            </a>
        </div>
    </div>
</div>
</body>
</html>