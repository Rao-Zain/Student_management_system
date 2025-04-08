<?php
require_once '../config/connection.php';
require_once 'grade_functions.php';

session_start();

// Initialize variables
$error = '';
$student = null;
$performance_data = [];
$roll_number = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $roll_number = trim($_POST['roll_number'] ?? '');
    
    if (empty($roll_number)) {
        $error = "Please enter your roll number";
    } else {
        // Get student by roll number
        $stmt = $conn->prepare("SELECT * FROM students WHERE roll_no = ?");
        $stmt->bind_param("s", $roll_number);
        $stmt->execute();
        $student = $stmt->get_result()->fetch_assoc();
        
        if (!$student) {
            $error = "No student found with this roll number";
        } else {
            $student_id = $student['id'];
            
            // Get all courses the student is enrolled in
            $courses = $conn->query("SELECT c.* 
                                   FROM courses c
                                   JOIN student_courses sc ON c.id = sc.course_id
                                   WHERE sc.student_id = $student_id
                                   ORDER BY c.course_name");

            // Get current date
            $current_date = date('Y-m-d');

            // Get performance data for each course
            while ($course = $courses->fetch_assoc()) {
                $performance = $conn->query("SELECT * FROM student_performance 
                                            WHERE student_id = $student_id AND course_id = {$course['id']}
                                            ORDER BY semester DESC")->fetch_all(MYSQLI_ASSOC);
                
                // Get all exams for this course
                $exams = $conn->query("SELECT e.*, et.name as exam_type 
                                     FROM exams e
                                     JOIN exam_types et ON e.exam_type_id = et.exam_type_id
                                     WHERE e.subject_id = {$course['id']}
                                     ORDER BY e.exam_date ASC")->fetch_all(MYSQLI_ASSOC);
                
                // Check if all exams have been completed
                $all_exams_completed = true;
                foreach ($exams as $exam) {
                    if ($exam['exam_date'] > $current_date) {
                        $all_exams_completed = false;
                        break;
                    }
                }
                
                $performance_data[$course['id']] = [
                    'course' => $course,
                    'performance' => $performance,
                    'exams' => $exams,
                    'all_exams_completed' => $all_exams_completed
                ];
            }
        }
    }
}

// Get detailed marks for each component
function get_component_marks($student_id, $course_id, $component) {
    global $conn;
    return $conn->query("SELECT sg.marks_obtained, e.name as exam_name, e.exam_date
                        FROM student_grades sg
                        JOIN exams e ON sg.exam_subject_id = e.exam_id
                        JOIN exam_types et ON e.exam_type_id = et.exam_type_id
                        WHERE sg.student_id = $student_id
                        AND e.subject_id = $course_id
                        AND et.name = '$component'")->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Result Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color:rgb(96 124 245);
            --secondary-color:rgb(108 103 207);
            --accent-color: #4cc9f0;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --success-color: #4ade80;
            --warning-color: #facc15;
            --danger-color: #f87171;
            --border-radius: 12px;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f0f2f5;
            color: var(--dark-color);
            line-height: 1.6;
        }
        
        .result-portal {
            max-width: 1100px;
            margin: 40px auto;
            padding: 0;
            background: transparent;
        }
        
        .search-box {
            background: linear-gradient(145deg, var(--primary-color), var(--secondary-color));
            padding: 40px;
            border-radius: var(--border-radius);
            color: white;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(67, 97, 238, 0.2);
        }
        
        .search-box h2 {
            font-weight: 700;
            margin-bottom: 15px;
        }
        
        .input-group-text {
            background-color: white;
            border: none;
            color: var(--primary-color);
        }
        
        .form-control {
            border: none;
            padding: 12px 20px;
            border-radius: var(--border-radius) 0 0 var(--border-radius) !important;
        }
        
        .form-control:focus {
            box-shadow: none;
            border-color: var(--accent-color);
        }
        
        .btn-search {
            background-color: white;
            color: var(--primary-color);
            font-weight: 600;
            border: none;
            border-radius: 0 var(--border-radius) var(--border-radius) 0 !important;
            padding: 12px 25px;
        }
        
        .btn-search:hover {
            background-color: var(--light-color);
            color: var(--secondary-color);
        }
        
        .student-info {
            background-color: white;
            padding: 25px;
            border-radius: var(--border-radius);
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            border-left: 5px solid var(--primary-color);
        }
        
        .student-info h4 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .student-info i {
            color: var(--primary-color);
            margin-right: 10px;
        }
        
        .report-card {
            background-color: white;
            padding: 30px;
            margin-bottom: 30px;
            border-radius: var(--border-radius);
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
        }
        
        .report-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .course-performance h3 {
            font-weight: 600;
            color: var(--dark-color);
            padding-bottom: 15px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .badge {
            font-weight: 500;
            padding: 8px 12px;
            border-radius: 50px;
        }
        
        .bg-primary {
            background-color: var(--primary-color) !important;
        }
        
        .bg-success {
            background-color: var(--success-color) !important;
        }
        
        .bg-danger {
            background-color: var(--danger-color) !important;
        }
        
        .bg-warning {
            background-color: var(--warning-color) !important;
            color: var(--dark-color);
        }
        
        .pending-exam {
            background-color: #fffbeb;
            padding: 20px;
            border-radius: var(--border-radius);
            margin: 20px 0;
            border-left: 5px solid var(--warning-color);
        }
        
        .component-card {
            border: none;
            margin-bottom: 20px;
            border-radius: var(--border-radius);
            background-color: #f8f9fa;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        
        .component-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .component-card .card-body {
            padding: 25px;
        }
        
        .component-card h5 {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 15px;
        }
        
        .component-card i {
            color: var(--primary-color);
            margin-right: 10px;
        }
        
        .component-card .display-6 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .course-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .midterm-card {
            border-top: 5px solid #4361ee;
        }
        
        .final-card {
            border-top: 5px solid #3a0ca3;
        }
        
        .sessional-card {
            border-top: 5px solid #4cc9f0;
        }
        
        .sessional-component {
            background-color: white;
            border-radius: var(--border-radius);
            padding: 20px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .sessional-component:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .sessional-component h6 {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 15px;
        }
        
        .sessional-component i {
            color: var(--primary-color);
            margin-right: 5px;
            font-size: 1.1rem;
        }
        
        .sessional-component .fs-3 {
            font-weight: 700;
            color: var(--primary-color);
            margin: 0;
        }
        
        .attendance-component {
            border-top: 3px solid #4361ee;
        }
        
        .quiz-component {
            border-top: 3px solid #3a0ca3;
        }
        
        .presentation-component {
            border-top: 3px solid #4cc9f0;
        }
        
        .assignment-component {
            border-top: 3px solid #7209b7;
        }
        
        .final-result {
            background: linear-gradient(145deg, #f8f9fa, #ffffff);
            border-radius: var(--border-radius);
            padding: 30px;
            margin-top: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .final-result h4 {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 25px;
            text-align: center;
        }
        
        .final-result h5 {
            font-weight: 500;
            color: #6c757d;
            margin-bottom: 10px;
        }
        
        .final-result .display-5 {
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .section-title {
            position: relative;
            margin-bottom: 30px;
            margin-top: 30px;
            font-weight: 600;
            color: var(--dark-color);
            display: inline-block;
            padding-bottom: 8px;
        }
        
        .section-title:after {
            content: '';
            position: absolute;
            width: 50%;
            height: 3px;
            background: var(--primary-color);
            bottom: 0;
            left: 0;
        }
        
        .action-buttons {
            margin-top: 40px;
        }
        
        .btn-lg {
            padding: 12px 25px;
            border-radius: var(--border-radius);
            font-weight: 500;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }
        
        .btn-secondary:hover {
            background-color: #5c636a;
            border-color: #5c636a;
        }
        
        @media print {
            .no-print { display: none; }
            body { background-color: white; }
            .report-card { 
                box-shadow: none; 
                margin-bottom: 50px;
                break-inside: avoid;
            }
            .search-box { display: none; }
            .component-card, .sessional-component {
                box-shadow: none;
                transform: none !important;
            }
            .final-result {
                box-shadow: none;
            }
        }
        
        @media (max-width: 768px) {
            .search-box {
                padding: 30px 15px;
            }
            .report-card {
                padding: 20px 15px;
            }
            .component-card .display-6 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>

<?php include '../attendance/attendance_header.php'; ?>
<div class="container">
        <div class="result-portal">
            <div class="search-box text-center no-print">
                <h2><i class="fas fa-graduation-cap"></i> Student Result Portal</h2>
                <p class="lead">Enter your roll number to view your academic performance</p>
                
                <form method="POST" class="mt-4">
                    <div class="row justify-content-center">
                        <div class="col-md-6">
                            <div class="input-group mb-3">
                                <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                <input type="text" class="form-control form-control-lg" 
                                       name="roll_number" placeholder="Enter Roll Number" 
                                       value="<?= htmlspecialchars($roll_number) ?>" required>
                                <button class="btn btn-search btn-lg" type="submit">View Results</button>
                            </div>
                            <?php if ($error): ?>
                                <div class="alert alert-danger"><?= $error ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>
            
            <?php if ($student): ?>
                <div class="student-info">
                    <div class="row">
                        <div class="col-md-4">
                            <h4><i class="fas fa-user-graduate"></i> <?= htmlspecialchars($student['name']) ?></h4>
                        </div>
                        <div class="col-md-4">
                            <h4><i class="fas fa-id-card"></i> Roll No: <?= htmlspecialchars($student['roll_no']) ?></h4>
                        </div>
                        <div class="col-md-4">
                            <h4><i class="fas fa-calendar-alt"></i> Date: <?= date('F j, Y') ?></h4>
                        </div>
                    </div>
                </div>
                
                <?php if (empty($performance_data)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> No course records found for this student.
                    </div>
                <?php else: ?>
                    <?php foreach ($performance_data as $course_id => $data): 
                        $course = $data['course'];
                        $performance = $data['performance'][0] ?? null;
                        $all_exams_completed = $data['all_exams_completed'];
                        
                        if (!$performance) continue;
                        
                        // Get component marks
                        $midterm = get_component_marks($student['id'], $course_id, 'Midterm');
                        $final = get_component_marks($student['id'], $course_id, 'Final');
                        $attendance = get_component_marks($student['id'], $course_id, 'Attendance');
                        $quiz = get_component_marks($student['id'], $course_id, 'Quiz');
                        $presentation = get_component_marks($student['id'], $course_id, 'Presentation');
                        $assignment = get_component_marks($student['id'], $course_id, 'Assignment');
                    ?>
                    <div class="report-card">
                        <div class="course-performance">
                            <div class="course-header">
                                <h3>
                                    <i class="fas fa-book"></i> <?= htmlspecialchars($course['course_name']) ?>
                                </h3>
                                <span class="badge bg-primary">
                                    <?= $course['course_code'] ?>
                                </span>
                            </div>
                            
                            <?php if (!$all_exams_completed): ?>
                            <div class="pending-exam">
                                <i class="fas fa-exclamation-triangle me-2"></i> 
                                <strong>Note:</strong> Some exams for this course are still pending. Final grade will be available after all exams are completed.
                            </div>
                            <?php endif; ?>
                            
                            <!-- Component Marks -->
                            <h4 class="section-title">Exam Results</h4>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="card component-card midterm-card h-100">
                                        <div class="card-body">
                                            <h5><i class="fas fa-file-alt"></i> Midterm Exam</h5>
                                            <p class="display-6"><?= $midterm['marks_obtained'] ?? 0 ?> / 30</p>
                                            <small class="text-muted">
                                                <?= $midterm['exam_name'] ?? 'N/A' ?>
                                                <?php if (isset($midterm['exam_date'])): ?>
                                                    <br><i class="far fa-calendar-alt me-1"></i> <?= date('M j, Y', strtotime($midterm['exam_date'])) ?>
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="card component-card final-card h-100">
                                        <div class="card-body">
                                            <h5><i class="fas fa-file-alt"></i> Final Exam</h5>
                                            <p class="display-6"><?= $final['marks_obtained'] ?? 0 ?> / 50</p>
                                            <small class="text-muted">
                                                <?= $final['exam_name'] ?? 'N/A' ?>
                                                <?php if (isset($final['exam_date'])): ?>
                                                    <br><i class="far fa-calendar-alt me-1"></i> <?= date('M j, Y', strtotime($final['exam_date'])) ?>
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="card component-card sessional-card h-100">
                                        <div class="card-body">
                                            <h5><i class="fas fa-tasks"></i> Sessional</h5>
                                            <p class="display-6"><?= $performance['sessional_marks'] ?? 0 ?> / 20</p>
                                            <small class="text-muted">Continuous Assessment</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Sessional Breakdown -->
                            <h4 class="section-title">Sessional Components</h4>
                            <div class="row">
                                <div class="col-md-3 mb-4">
                                    <div class="sessional-component attendance-component">
                                        <h6><i class="fas fa-user-check"></i> Attendance</h6>
                                        <p class="fs-3"><?= $attendance['marks_obtained'] ?? 0 ?> / 5</p>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-4">
                                    <div class="sessional-component quiz-component">
                                        <h6><i class="fas fa-question-circle"></i> Quiz</h6>
                                        <p class="fs-3"><?= $quiz['marks_obtained'] ?? 0 ?> / 5</p>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-4">
                                    <div class="sessional-component presentation-component">
                                        <h6><i class="fas fa-chalkboard-teacher"></i> Presentation</h6>
                                        <p class="fs-3"><?= $presentation['marks_obtained'] ?? 0 ?> / 5</p>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-4">
                                    <div class="sessional-component assignment-component">
                                        <h6><i class="fas fa-file-upload"></i> Assignment</h6>
                                        <p class="fs-3"><?= $assignment['marks_obtained'] ?? 0 ?> / 5</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Final Results - Only show if all exams are completed -->
                            <?php if ($all_exams_completed): ?>
                            <div class="final-result">
                                <h4>Final Result</h4>
                                <div class="row text-center">
                                    <div class="col-md-4">
                                        <h5>Total Marks</h5>
                                        <p class="display-5"><?= $performance['total_marks'] ?> / 100</p>
                                    </div>
                                    <div class="col-md-4">
                                        <h5>Percentage</h5>
                                        <p class="display-5"><?= $performance['percentage'] ?>%</p>
                                    </div>
                                    <div class="col-md-4">
                                        <h5>Final Grade</h5>
                                        <p class="display-5">
                                            <?= $performance['final_grade'] ?>
                                            <span class="badge bg-<?= $performance['status'] === 'Pass' ? 'success' : 'danger' ?> ms-2">
                                                <?= $performance['status'] ?>
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <div class="no-print text-center action-buttons">
                        <button onclick="window.print()" class="btn btn-primary btn-lg me-3">
                            <i class="fas fa-print me-2"></i> Print Results
                        </button>
                        <a href="<?= $_SERVER['PHP_SELF'] ?>" class="btn btn-secondary btn-lg">
                            <i class="fas fa-redo me-2"></i> Search Again
                        </a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>