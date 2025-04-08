<?php
require_once '../config/connection.php';
require_once 'grade_functions.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['create_exam'])) {
        $exam_type_id = $_POST['exam_type_id'];
        $name = $_POST['name'];
        $exam_date = $_POST['exam_date'];
        $subject_id = $_POST['subject_id'];
        $description = $_POST['description'];
        
        $stmt = $conn->prepare("INSERT INTO exams 
                              (exam_type_id, name, exam_date, subject_id, description)
                              VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $exam_type_id, $name, $exam_date, $subject_id, $description);
        $stmt->execute();
        
        $_SESSION['message'] = "Exam created successfully!";
        header("Location: exams.php");
        exit;
    }
}

// Get all exams with their types
$exams = $conn->query("SELECT e.*, et.name as exam_type, et.max_marks, c.course_name
                      FROM exams e
                      JOIN exam_types et ON e.exam_type_id = et.exam_type_id
                      JOIN courses c ON e.subject_id = c.id
                      ORDER BY e.exam_date DESC");

// Get all exam types for dropdown
$exam_types = $conn->query("SELECT * FROM exam_types");

// Get all courses for dropdown
$courses = $conn->query("SELECT * FROM courses");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Exam Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../attendance/attendance_header.php'; ?>    
    <div class="container mt-4">
        <h2>Exam Management</h2>
        
        <!-- Create Exam Form -->
        <div class="card mb-4">
            <div class="card-header">Create New Exam</div>
            <div class="card-body">
                <form method="POST">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Exam Type</label>
                            <select name="exam_type_id" class="form-select" required>
                                <?php while ($type = $exam_types->fetch_assoc()): ?>
                                <option value="<?= $type['exam_type_id'] ?>">
                                    <?= $type['name'] ?> (Max: <?= $type['max_marks'] ?>)
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Exam Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Exam Date</label>
                            <input type="date" name="exam_date" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Subject</label>
                            <select name="subject_id" class="form-select" required>
                                <?php while ($course = $courses->fetch_assoc()): ?>
                                <option value="<?= $course['id'] ?>"><?= $course['course_name'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Description</label>
                            <input type="text" name="description" class="form-control">
                        </div>
                        <div class="col-12">
                            <button type="submit" name="create_exam" class="btn btn-primary">Create Exam</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Exams List -->
        <div class="card">
            <div class="card-header">All Exams</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Type</th>
                                <th>Name</th>
                                <th>Subject</th>
                                <th>Date</th>
                                <th>Max Marks</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($exam = $exams->fetch_assoc()): ?>
                            <tr>
                                <td><?= $exam['exam_id'] ?></td>
                                <td><?= $exam['exam_type'] ?></td>
                                <td><?= htmlspecialchars($exam['name']) ?></td>
                                <td><?= htmlspecialchars($exam['course_name']) ?></td>
                                <td><?= date('d M Y', strtotime($exam['exam_date'])) ?></td>
                                <td><?= $exam['max_marks'] ?></td>
                                <td>
                                    <a href="enter_grades.php?exam_id=<?= $exam['exam_id'] ?>" 
                                       class="btn btn-sm btn-success">Enter Grades</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>