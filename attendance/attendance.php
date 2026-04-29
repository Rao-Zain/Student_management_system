<?php
session_start();
include '../config/connection.php';

if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'Teacher' && $_SESSION['user_role'] !== 'Admin')) {
    header('Location: ../auth/login.php');
    exit();
}

include 'attendance_header.php';

// Fetch all programs
$programsQuery = "SELECT id, program_name FROM programs";
$programs = $conn->query($programsQuery);

$selectedProgram = null;
$selectedSubject = null;
$students = null;

// Handle program selection
if (isset($_POST['program_id'])) {
    $selectedProgram = $_POST['program_id'];
    
    // Get subjects for the selected program
    $subjectsQuery = "SELECT id, course_name FROM courses WHERE program_id = $selectedProgram";
    $subjects = $conn->query($subjectsQuery);
}

// Handle subject selection
if (isset($_POST['subject_id'])) {
    $selectedSubject = $_POST['subject_id'];
    
    // Get students enrolled in the selected subject through student_courses
    $studentsQuery = "SELECT DISTINCT s.id, s.name, s.roll_no 
                        FROM students s 
                        INNER JOIN student_courses sc ON s.id = sc.student_id
                        WHERE sc.course_id = $selectedSubject
                        ORDER BY s.roll_no";
    $students = $conn->query($studentsQuery);
}

// Handle attendance submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_attendance'])) {
    $date = $_POST['date'];
    $subject = $_POST['subject_name'];

    // Check if attendance already exists for this date and subject
    $checkQuery = "SELECT COUNT(*) as count FROM attendance 
                    WHERE subject = '$subject' AND date = '$date'";
    $checkResult = $conn->query($checkQuery);
    $checkData = $checkResult->fetch_assoc();
    
    if ($checkData['count'] > 0) {
        $message = "Attendance for this subject and date already exists!";
        $messageType = "danger";
    } else {
        // Insert attendance records
        $success = true;
        foreach ($_POST['attendance'] as $student_id => $status) {
            $sql = "INSERT INTO attendance (student_id, subject, date, status)
                    VALUES ('$student_id', '$subject', '$date', '$status')";
            if (!$conn->query($sql)) {
                $success = false;
                break;
            }
        }
        
        if ($success) {
            $message = "Attendance marked successfully!";
            $messageType = "success";
        } else {
            $message = "Error marking attendance: " . $conn->error;
            $messageType = "danger";
        }
    }
}

// Handle student details request
if (isset($_POST['student_id'])) {
    $studentId = $_POST['student_id'];
    
    // Fetch student details
    $studentDetailsQuery = "SELECT * FROM students WHERE id = $studentId";
    $studentDetailsResult = $conn->query($studentDetailsQuery);
    $studentDetails = $studentDetailsResult->fetch_assoc();
    
    // Fetch attendance history
    $attendanceHistoryQuery = "SELECT * FROM attendance WHERE student_id = $studentId ORDER BY date DESC LIMIT 5";
    $attendanceHistoryResult = $conn->query($attendanceHistoryQuery);
    
    // Build HTML for modal content
    $html = "<div class='student-details'>";
    $html .= "<p><strong>Student ID:</strong> " . $studentDetails['id'] . "</p>";
    $html .= "<p><strong>Name:</strong> " . $studentDetails['name'] . "</p>";
    $html .= "<p><strong>Roll Number:</strong> " . $studentDetails['roll_no'] . "</p>";
    $html .= "<p><strong>Attendance History:</strong></p>";
    $html .= "<ul>";
    while ($attendanceRow = $attendanceHistoryResult->fetch_assoc()) {
        $html .= "<li>" . $attendanceRow['date'] . ": " . $attendanceRow['status'] . "</li>";
    }
    $html .= "</ul>";
    $html .= "</div>";
    
    echo $html;
    exit(); // Stop further execution
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Mark Attendance</title>
    <link rel="stylesheet" href="attendance_style.css">
   
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<div class="container mt-4">
    <h2 class="mb-4">Mark Attendance</h2>
    
    <?php if (isset($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?>" role="alert">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            Step 1: Select Program
        </div>
        <div class="card-body">
            <form method="POST" id="programForm">
                <div class="form-group">
                    <label for="program">Program:</label>
                    <select name="program_id" id="program" class="form-control" required onchange="this.form.submit()">
                        <option value="">-- Select Program --</option>
                        <?php while ($row = $programs->fetch_assoc()): ?>
                            <option value="<?php echo $row['id']; ?>" <?php if(isset($_POST['program_id']) && $_POST['program_id'] == $row['id']) echo 'selected'; ?>>
                                <?php echo $row['program_name']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </form>
        </div>
    </div>
    
    <?php if ($selectedProgram): ?>
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            Step 2: Select Subject
        </div>
        <div class="card-body">
            <form method="POST" id="subjectForm">
                <input type="hidden" name="program_id" value="<?php echo $selectedProgram; ?>">
                <div class="form-group">
                    <label for="subject">Subject:</label>
                    <select name="subject_id" id="subject" class="form-control" required onchange="this.form.submit()">
                        <option value="">-- Select Subject --</option>
                        <?php 
                        // Fetch subjects for the selected program
                        $subjectsQuery = "SELECT id, course_name FROM courses WHERE program_id = $selectedProgram";
                        $subjects = $conn->query($subjectsQuery);
                        
                        while ($row = $subjects->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $row['id']; ?>" <?php if(isset($_POST['subject_id']) && $_POST['subject_id'] == $row['id']) echo 'selected'; ?>>
                                <?php echo $row['course_name']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if ($selectedSubject && $students && $students->num_rows > 0): 
        // Get the subject name
        $subjectQuery = "SELECT course_name FROM courses WHERE id = $selectedSubject";
        $subjectResult = $conn->query($subjectQuery);
        $subjectName = $subjectResult->fetch_assoc()['course_name'];
    ?>
    <div class="card">
        <div class="card-header bg-primary text-white">
            Step 3: Mark Attendance for <?php echo $subjectName; ?>
        </div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="program_id" value="<?php echo $selectedProgram; ?>">
                <input type="hidden" name="subject_id" value="<?php echo $selectedSubject; ?>">
                <input type="hidden" name="subject_name" value="<?php echo $subjectName; ?>">
                <input type="hidden" name="mark_attendance" value="1">
                
                <div class="form-group">
                    <label for="date">Date:</label>
                    <input type="date" name="date" id="date" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="thead-dark">
                            <tr>
                                <th>Roll Number</th>
                                <th>Student Name</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $students->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['roll_no']; ?></td>
                                    <td><?php echo $row['name']; ?></td>
                                    <td>
                                        <select name="attendance[<?php echo $row['id']; ?>]" class="form-control">
                                            <option value="Present">Present</option>
                                            <option value="Absent">Absent</option>
                                            <option value="Late">Late</option>
                                        </select>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info" onclick="viewStudentDetails(<?php echo $row['id']; ?>)">View Details</button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="form-group">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="markAllPresent">
                        <label class="form-check-label" for="markAllPresent">
                            Mark all students as present
                        </label>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-success">Mark Attendance</button>
            </form>
        </div>
    </div>
    <?php elseif ($selectedSubject): ?>
        <div class="alert alert-warning">
            No students found enrolled in this course. Please check enrollments or select a different course.
        </div>
    <?php endif; ?>
</div>

<div class="modal fade" id="studentDetailsModal" tabindex="-1" role="dialog" aria-labelledby="studentDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="studentDetailsModalLabel">Student Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="studentDetailsContent">
                Loading...
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Set date to current date by default
    document.addEventListener('DOMContentLoaded', function() {
        const today = new Date().toISOString().split('T')[0];
        if (document.getElementById('date')) {
            document.getElementById('date').value = today;
        }
        
        // Handle "Mark all as present" checkbox
        const markAllCheckbox = document.getElementById('markAllPresent');
        if (markAllCheckbox) {
            markAllCheckbox.addEventListener('change', function() {
                const attendanceSelects = document.querySelectorAll('select[name^="attendance"]');
                attendanceSelects.forEach(function(select) {
                    select.value = markAllCheckbox.checked ? 'Present' : select.value;
                });
            });
        }
    });
    
    // Function to view student details
    function viewStudentDetails(studentId) {
        const modal = $('#studentDetailsModal');
        modal.find('#studentDetailsContent').html('Loading student information...');
        modal.modal('show');
        
        $.ajax({
            url: 'attendance.php', // Send request to the same file
            type: 'POST',
            data: { student_id: studentId },
            success: function(response) {
                modal.find('#studentDetailsContent').html(response);
            },
            error: function() {
                modal.find('#studentDetailsContent').html('Error loading student details');
            }
        });
    }
</script>
    <?php include '../includes/footer.php'; ?>
</body>
</html>
