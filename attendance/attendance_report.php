<?php
error_reporting(0);
ini_set('display_errors', 0);

session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] !== 'Admin' && strtolower($_SESSION['user_role']) !== 'teacher')) {
    header("Location: ../auth/login.php?error=Unauthorized access");
    exit();
}

include '../config/connection.php';
include 'attendance_header.php';

// --- Configuration ---
$total_possible_lectures = 32; // Assume 32 lectures per semester
$low_attendance_threshold = 75; // Percentage threshold for alarm

if (isset($_GET['student_id']) && isset($_GET['subject_id'])) {
    $student_id = $_GET['student_id'];
    $subject_id = $_GET['subject_id'];

    // Fetch student and subject details
    $student_stmt = $conn->prepare("SELECT name, roll_no FROM students WHERE id = ?");
    $student_stmt->bind_param("i", $student_id);
    $student_stmt->execute();
    $student_result = $student_stmt->get_result();
    $student = $student_result->fetch_assoc();
    $student_stmt->close();

    $subject_stmt = $conn->prepare("SELECT course_name FROM courses WHERE id = ?");
    $subject_stmt->bind_param("i", $subject_id);
    $subject_stmt->execute();
    $subject_result = $subject_stmt->get_result();
    $subject = $subject_result->fetch_assoc();
    $subject_stmt->close();

    // Fetch all attendance records for the student and subject WITH IDs
    $attendance_stmt = $conn->prepare("SELECT id, date, status, note FROM attendance 
                                      WHERE student_id = ? 
                                      AND subject = ? 
                                      ORDER BY date");
    $attendance_stmt->bind_param("is", $student_id, $subject['course_name']);
    $attendance_stmt->execute();
    $attendance_result = $attendance_stmt->get_result();

    $attendance_data = [];
    while ($row = $attendance_result->fetch_assoc()) {
        $attendance_data[] = $row;
    }
    $attendance_stmt->close();

    // --- Function to calculate attendance statistics ---
    function calculateAttendance($data) {
        $daily = [];
        $monthly = [];
        $yearly = [];
        $present_count = 0;
        $absent_count = 0;
        $consecutive_absent_count = 0;
        $previous_status = null;
        $consecutive_absence_notification = false;

        foreach ($data as $record) {
            $date = new DateTime($record['date']);
            $year = $date->format('Y');
            $month = $date->format('Y-m');
            $day = $date->format('Y-m-d');
            $status = $record['status'];

            if ($status === 'Present') {
                $present_count++;
                if ($previous_status === 'Absent') {
                    $consecutive_absent_count = 0;
                }
            } elseif ($status === 'Absent') {
                $absent_count++;
                if ($previous_status === 'Absent') {
                    $consecutive_absent_count++;
                    if ($consecutive_absent_count >= 2) {
                        $consecutive_absence_notification = true;
                    }
                } else {
                    $consecutive_absent_count = 1;
                }
            }
            $previous_status = $status;

            // Daily
            if (!isset($daily[$day])) {
                $daily[$day] = ['Present' => 0, 'Absent' => 0];
            }
            $daily[$day][$status]++;

            // Monthly
            if (!isset($monthly[$month])) {
                $monthly[$month] = ['Present' => 0, 'Absent' => 0, 'total_days' => 0];
            }
            $monthly[$month][$status]++;
            $monthly[$month]['total_days']++;

            // Yearly
            if (!isset($yearly[$year])) {
                $yearly[$year] = ['Present' => 0, 'Absent' => 0, 'total_days' => 0];
            }
            $yearly[$year][$status]++;
            $yearly[$year]['total_days']++;
        }

        $total_lectures_taken = count($data);
        $attendance_percentage = ($total_lectures_taken > 0) ? round(($present_count / $total_lectures_taken) * 100, 2) : 0;

        return [
            'daily' => $daily,
            'monthly' => $monthly,
            'yearly' => $yearly,
            'present_count' => $present_count,
            'absent_count' => $absent_count,
            'total_lectures_taken' => $total_lectures_taken,
            'attendance_percentage' => $attendance_percentage,
            'consecutive_absence_notification' => $consecutive_absence_notification,
        ];
    }

    $attendance_report = calculateAttendance($attendance_data);
} else {
    echo "<div class='container'><p class='alert alert-danger'>Invalid student or subject ID.</p></div>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Report</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 25px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        h2, h3, h4 {
            color: #2c3e50;
            margin-bottom: 20px;
        }
        .report-section {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            background-color: #f9f9f9;
        }
        .report-section h4 {
            color: #3498db;
            margin-top: 0;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }
        .chart-container {
            width: 100%;
            height: 400px;
            margin-top: 20px;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #3498db;
            text-decoration: none;
            font-weight: 500;
        }
        .back-link:hover {
            text-decoration: underline;
            color: #2980b9;
        }
        .attendance-summary {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            background-color: #e9ecef;
        }
        .summary-item {
            margin-bottom: 10px;
            font-size: 16px;
        }
        .summary-value {
            font-weight: 600;
        }
        .low-attendance-alarm {
            color: #e74c3c;
            font-weight: bold;
            background-color: #fde8e8;
            padding: 10px;
            border-radius: 5px;
            margin-top: 15px;
        }
        .consecutive-absence-notification {
            color: #e67e22;
            font-weight: bold;
            background-color: #fef5e8;
            padding: 10px;
            border-radius: 5px;
            margin-top: 15px;
        }
        .badge-present {
            background-color: #2ecc71;
            color: white;
        }
        .badge-absent {
            background-color: #e74c3c;
            color: white;
        }
        .badge-note {
            background-color: #3498db;
            color: white;
        }
        .print-btn {
            margin-bottom: 20px;
        }
        .note-btn {
            position: relative;
        }
        .has-note:after {
            content: '';
            position: absolute;
            top: -5px;
            right: -5px;
            width: 8px;
            height: 8px;
            background-color: #3498db;
            border-radius: 50%;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Attendance Report</h2>
        <button onclick="window.print()" class="btn btn-primary print-btn">
            <i class="fas fa-print"></i> Print Report
        </button>
    </div>
    
    <?php if (isset($student) && isset($subject)): ?>
        <div class="student-info mb-4">
            <h3 class="mb-3">Student Information</h3>
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($student['name']); ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Roll No:</strong> <?php echo htmlspecialchars($student['roll_no']); ?></p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <p><strong>Subject:</strong> <?php echo htmlspecialchars($subject['course_name']); ?></p>
                </div>
            </div>
        </div>

        <div class="attendance-summary">
            <h4>Attendance Summary</h4>
            <div class="row">
                <div class="col-md-3">
                    <p class="summary-item">Total Lectures: <span class="summary-value"><?php echo $total_possible_lectures; ?></span></p>
                </div>
                <div class="col-md-3">
                    <p class="summary-item">Lectures Taken: <span class="summary-value"><?php echo $attendance_report['total_lectures_taken']; ?></span></p>
                </div>
                <div class="col-md-3">
                    <p class="summary-item">Present: <span class="summary-value"><?php echo $attendance_report['present_count']; ?></span></p>
                </div>
                <div class="col-md-3">
                    <p class="summary-item">Absent: <span class="summary-value"><?php echo $attendance_report['absent_count']; ?></span></p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <p class="summary-item">Attendance Percentage: 
                        <span class="summary-value <?php echo ($attendance_report['attendance_percentage'] < $low_attendance_threshold) ? 'text-danger' : 'text-success'; ?>">
                            <?php echo $attendance_report['attendance_percentage']; ?>%
                        </span>
                    </p>
                </div>
            </div>

            <?php if ($attendance_report['attendance_percentage'] < $low_attendance_threshold && $total_possible_lectures > 0): ?>
                <?php
                $remaining_lectures = $total_possible_lectures - $attendance_report['total_lectures_taken'];
                $needed_present = ceil(($low_attendance_threshold / 100) * $total_possible_lectures - $attendance_report['present_count']);
                if ($needed_present > $remaining_lectures) :
                ?>
                    <div class="low-attendance-alarm">
                        <i class="fas fa-exclamation-triangle"></i> Warning: Attendance is below <?php echo $low_attendance_threshold; ?>%. Even if present in all remaining lectures, <?php echo $low_attendance_threshold; ?>% might not be achievable.
                    </div>
                <?php elseif ($attendance_report['total_lectures_taken'] > 0) : ?>
                    <div class="low-attendance-alarm">
                        <i class="fas fa-exclamation-triangle"></i> Warning: Attendance is below <?php echo $low_attendance_threshold; ?>%. Needs at least <?php echo $needed_present; ?> present lectures out of the remaining <?php echo $remaining_lectures; ?> to reach <?php echo $low_attendance_threshold; ?>%.
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($attendance_report['consecutive_absence_notification']): ?>
                <div class="consecutive-absence-notification">
                    <i class="fas fa-bell"></i> Alert: Student has been absent for 3 or more consecutive lectures.
                </div>
            <?php endif; ?>
        </div>

        <div class="report-section">
            <h4>Daily Attendance Breakdown</h4>
            <?php if (!empty($attendance_data)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($attendance_data as $record): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($record['date']); ?></td>
                                    <td>
                                        <span class="badge <?php echo $record['status'] === 'Present' ? 'badge-present' : 'badge-absent'; ?>">
                                            <?php echo htmlspecialchars($record['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary note-btn <?php echo !empty($record['note']) ? 'has-note' : ''; ?>" 
                                                data-id="<?php echo $record['id']; ?>">
                                            <i class="fas fa-edit"></i> <?php echo empty($record['note']) ? 'Add Note' : 'View/Edit Note'; ?>
                                        </button>
                                        <?php if(!empty($record['note'])): ?>
                                            <span class="badge badge-note ml-2">Has Note</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Note Modal -->
                <div class="modal fade" id="noteModal" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Attendance Note</h5>
                                <button type="button" class="close" data-dismiss="modal">
                                    <span>&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <textarea class="form-control" id="absenceNote" rows="5" placeholder="Enter note about this attendance record..."></textarea>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary" id="saveNoteBtn">
                                    <i class="fas fa-save"></i> Save Note
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="chart-container">
                    <canvas id="dailyAttendanceChart"></canvas>
                </div>
                <script>
                    // Daily chart script
                    const dailyCtx = document.getElementById('dailyAttendanceChart').getContext('2d');
                    const dailyLabels = <?php echo json_encode(array_keys($attendance_report['daily'])); ?>;
                    const dailyPresentData = <?php echo json_encode(array_column(array_values($attendance_report['daily']), 'Present')); ?>;
                    const dailyAbsentData = <?php echo json_encode(array_column(array_values($attendance_report['daily']), 'Absent')); ?>;

                    new Chart(dailyCtx, {
                        type: 'bar',
                        data: {
                            labels: dailyLabels,
                            datasets: [
                                {
                                    label: 'Present',
                                    data: dailyPresentData,
                                    backgroundColor: 'rgba(46, 204, 113, 0.7)',
                                    borderColor: 'rgba(46, 204, 113, 1)',
                                    borderWidth: 1
                                },
                                {
                                    label: 'Absent',
                                    data: dailyAbsentData,
                                    backgroundColor: 'rgba(231, 76, 60, 0.7)',
                                    borderColor: 'rgba(231, 76, 60, 1)',
                                    borderWidth: 1
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    title: {
                                        display: true,
                                        text: 'Number of Days'
                                    }
                                },
                                x: {
                                    title: {
                                        display: true,
                                        text: 'Date'
                                    }
                                }
                            }
                        }
                    });

                    // Note functionality
                    $(document).ready(function() {
                        // Handle note button clicks
                        $('.note-btn').click(function() {
                            const attendanceId = $(this).data('id');
                            
                            // Fetch existing note if any
                            $.get('get_note.php', { id: attendanceId }, function(response) {
                                $('#absenceNote').val(response.note || '');
                                $('#noteModal').data('id', attendanceId);
                                $('#noteModal').modal('show');
                            }, 'json').fail(function() {
                                alert('Failed to load note. Please try again.');
                            });
                        });

                        // Handle save button
                        $('#saveNoteBtn').click(function() {
                            const note = $('#absenceNote').val();
                            const attendanceId = $('#noteModal').data('id');
                            
                            $.post('save_note.php', { 
                                id: attendanceId, 
                                note: note 
                            }, function(response) {
                                if(response.success) {
                                    $('#noteModal').modal('hide');
                                    location.reload(); // Refresh to show updated note status
                                } else {
                                    alert('Failed to save note. Please try again.');
                                }
                            }, 'json').fail(function() {
                                alert('Failed to save note. Please try again.');
                            });
                        });
                    });
                </script>
            <?php else: ?>
                <div class="alert alert-info">No attendance records found.</div>
            <?php endif; ?>
        </div>

        <a href="view.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Student Attendance
        </a>
    <?php endif; ?>
</div>

<!-- Include Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
