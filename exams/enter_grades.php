<?php
require_once '../config/connection.php';
require_once 'grade_functions.php';

$exam_id = $_GET['exam_id'] ?? 0;
$exam = $conn->query("SELECT e.*, et.name as exam_type, et.max_marks, c.course_name
                     FROM exams e
                     JOIN exam_types et ON e.exam_type_id = et.exam_type_id
                     JOIN courses c ON e.subject_id = c.id
                     WHERE e.exam_id = $exam_id")->fetch_assoc();

if (!$exam) {
    $_SESSION['error'] = "Invalid exam ID";
    header("Location: exams.php");
    exit;
}

// Handle grade submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_grades'])) {
    foreach ($_POST['marks'] as $student_id => $marks) {
        $marks = (float)$marks;
        
        // Validate marks don't exceed max marks for this exam type
        if ($marks > $exam['max_marks']) {
            $_SESSION['error'] = "Marks cannot exceed maximum marks for this exam type!";
            header("Location: enter_grades.php?exam_id=$exam_id");
            exit;
        }
        
        $stmt = $conn->prepare("INSERT INTO student_grades
                              (student_id, exam_subject_id, marks_obtained, recorded_by)
                              VALUES (?, ?, ?, ?)
                              ON DUPLICATE KEY UPDATE
                              marks_obtained = VALUES(marks_obtained),
                              recorded_by = VALUES(recorded_by)");
        $stmt->bind_param("iidi",
            $student_id,
            $exam_id,
            $marks,
            $_SESSION['user_id']
        );
        $stmt->execute();
        
        // Update performance summary for this student and course
        update_student_performance($student_id, $exam['subject_id']);
    }
    
    $_SESSION['message'] = "Grades saved successfully!";
    header("Location: enter_grades.php?exam_id=$exam_id");
    exit;
}

// Get students enrolled in this subject
$students = $conn->query("SELECT s.id, s.name, s.roll_no, sg.marks_obtained
                               FROM students s
                               JOIN student_courses sc ON s.id = sc.student_id
                               LEFT JOIN student_grades sg ON sg.student_id = s.id AND sg.exam_subject_id = $exam_id
                               WHERE sc.course_id = {$exam['subject_id']}
                               ORDER BY s.roll_no");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Enter Grades - <?= htmlspecialchars($exam['name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../attendance/attendance_header.php'; ?>    
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Enter Grades: <?= htmlspecialchars($exam['name']) ?></h2>
            <a href="exams.php" class="btn btn-secondary">Back to Exams</a>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <?= $exam['exam_type'] ?> - Max Marks: <?= $exam['max_marks'] ?>
            </div>
            <div class="card-body">
                <p><strong>Subject:</strong> <?= htmlspecialchars($exam['course_name']) ?></p>
                <p><strong>Date:</strong> <?= date('d M Y', strtotime($exam['exam_date'])) ?></p>
            </div>
        </div>
        
        <form method="POST">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Roll No</th>
                            <th>Student Name</th>
                            <th>Marks Obtained (Max: <?= $exam['max_marks'] ?>)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($student = $students->fetch_assoc()): ?>
                        <tr>
                            <td><?= $student['roll_no'] ?></td>
                            <td><?= htmlspecialchars($student['name']) ?></td>
                            <td>
                                <input type="number" step="0.01" 
                                       name="marks[<?= $student['id'] ?>]" 
                                       value="<?= $student['marks_obtained'] ?? '' ?>" 
                                       class="form-control" required
                                       min="0" max="<?= $exam['max_marks'] ?>">
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="text-center mt-3">
                <button type="submit" name="save_grades" class="btn btn-primary btn-lg">Save Grades</button>
            </div>
        </form>
    </div>
</body>
</html>