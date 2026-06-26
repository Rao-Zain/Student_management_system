<?php
require_once '../config/connection.php';
require_once 'grade_functions.php';

session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] !== 'Admin' && strtolower($_SESSION['user_role']) !== 'teacher')) {
    header("Location: ../auth/login.php?error=Unauthorized access");
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['create_exam'])) {
        $exam_type_id = $_POST['exam_type_id'];
        $name = $_POST['name'];
        $exam_date = $_POST['exam_date'];
        $subject_id = $_POST['subject_id'];
        $duration = $_POST['duration'] ?? 60;
        $instructions = $_POST['instructions'] ?? '';
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        $stmt = $conn->prepare("INSERT INTO exams 
                              (exam_type_id, name, exam_date, subject_id, description, duration_minutes, is_active)
                              VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssii", $exam_type_id, $name, $exam_date, $subject_id, $instructions, $duration, $is_active);
        $stmt->execute();
        
        $_SESSION['message'] = "Exam created successfully!";
        header("Location: exams.php");
        exit;
    }
    
    // Delete exam
    if (isset($_POST['delete_exam'])) {
        $exam_id = $_POST['exam_id'];
        $conn->query("DELETE FROM exams WHERE exam_id = $exam_id");
        $_SESSION['message'] = "Exam deleted successfully!";
        header("Location: exams.php");
        exit;
    }
}

// Get filter parameters
$filter_type = $_GET['filter_type'] ?? '';
$filter_status = $_GET['filter_status'] ?? '';
$search = $_GET['search'] ?? '';

// Build query with filters
$where_clauses = [];
if ($filter_type) {
    $where_clauses[] = "et.name = '" . $conn->real_escape_string($filter_type) . "'";
}
if ($search) {
    $where_clauses[] = "(e.name LIKE '%$search%' OR c.course_name LIKE '%$search%')";
}

$where_sql = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";

// Get all exams with their types
$exams_query = "SELECT DISTINCT e.exam_id, e.name, e.exam_date, e.description, e.duration_minutes, e.is_active, et.name as exam_type, et.max_marks, c.course_name,
                (SELECT COUNT(*) FROM student_grades WHERE exam_id = e.exam_id) as grades_entered
                FROM exams e
                JOIN exam_types et ON e.exam_type_id = et.exam_type_id
                JOIN courses c ON e.subject_id = c.id
                $where_sql
                ORDER BY e.exam_date DESC";
$exams = $conn->query($exams_query);

// Get statistics
$total_exams = $conn->query("SELECT COUNT(*) as count FROM exams")->fetch_assoc()['count'];
$upcoming_exams = $conn->query("SELECT COUNT(*) as count FROM exams WHERE exam_date >= CURDATE()")->fetch_assoc()['count'];
$completed_exams = $conn->query("SELECT COUNT(*) as count FROM exams WHERE exam_date < CURDATE()")->fetch_assoc()['count'];

// Get all exam types for dropdown
$exam_types = $conn->query("SELECT DISTINCT name FROM exam_types ORDER BY name");

// Get all courses for dropdown
$courses = $conn->query("SELECT * FROM courses");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Management - LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        .gradient-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .stat-card {
            border: none;
            border-radius: 12px;
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .exam-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            transition: all 0.3s;
        }
        .exam-card:hover {
            box-shadow: 0 5px 20px rgba(0,0,0,0.12);
        }
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-upcoming {
            background-color: #e3f2fd;
            color: #1976d2;
        }
        .status-completed {
            background-color: #e8f5e9;
            color: #388e3c;
        }
        .status-today {
            background-color: #fff3e0;
            color: #f57c00;
        }
        .action-btn {
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 13px;
        }
        .search-box {
            border-radius: 25px;
            border: 2px solid #e0e0e0;
            padding-left: 20px;
        }
        .search-box:focus {
            border-color: #667eea;
            box-shadow: none;
        }
        .filter-btn {
            border-radius: 20px;
        }
        .modal-header {
            border-radius: 15px 15px 0 0;
        }
    </style>
</head>
<body>
<?php include '../attendance/attendance_header.php'; ?>

<!-- Statistics Cards -->
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card stat-card bg-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-file-alt text-primary fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-0">Total Exams</p>
                            <h3 class="mb-0 fw-bold"><?= $total_exams ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card bg-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-clock text-warning fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-0">Upcoming</p>
                            <h3 class="mb-0 fw-bold"><?= $upcoming_exams ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card bg-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-check-circle text-success fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-0">Completed</p>
                            <h3 class="mb-0 fw-bold"><?= $completed_exams ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter Bar -->
    <div class="card exam-card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control search-box" 
                               placeholder="Search exams..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="filter_type" class="form-select filter-btn">
                        <option value="">All Exam Types</option>
                        <?php while ($type = $exam_types->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($type['name']) ?>" 
                            <?= $filter_type == $type['name'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($type['name']) ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="filter_status" class="form-select filter-btn">
                        <option value="">All Status</option>
                        <option value="upcoming" <?= $filter_status == 'upcoming' ? 'selected' : '' ?>>Upcoming</option>
                        <option value="completed" <?= $filter_status == 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="today" <?= $filter_status == 'today' ? 'selected' : '' ?>>Today</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100 filter-btn">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Action Bar -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0 fw-bold"><i class="fas fa-list-alt me-2"></i>All Exams</h5>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createExamModal">
            <i class="fas fa-plus-circle me-1"></i> Create New Exam
        </button>
    </div>

    <!-- Success Message -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-1"></i> <?= $_SESSION['message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <!-- Exams Table -->
    <div class="card exam-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Exam Name</th>
                            <th>Type</th>
                            <th>Subject</th>
                            <th>Date & Time</th>
                            <th>Duration</th>
                            <th>Marks</th>
                            <th>Status</th>
                            <th>Progress</th>
                            <th class="pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($exams->num_rows > 0): ?>
                            <?php while ($exam = $exams->fetch_assoc()): 
                                $exam_date = strtotime($exam['exam_date']);
                                $today = strtotime(date('Y-m-d'));
                                $is_past = $exam_date < $today;
                                $is_today = $exam_date == $today;
                            ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-semibold"><?= htmlspecialchars($exam['name']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($exam['description'] ?? 'No description') ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-info bg-opacity-10 text-info">
                                        <?= $exam['exam_type'] ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($exam['course_name']) ?></td>
                                <td>
                                    <div><?= date('d M Y', $exam_date) ?></div>
                                    <small class="text-muted"><?= date('h:i A', $exam_date) ?></small>
                                </td>
                                <td><?= $exam['duration_minutes'] ?? 60 ?> min</td>
                                <td><?= $exam['max_marks'] ?></td>
                                <td>
                                    <?php if ($is_today): ?>
                                        <span class="status-badge status-today">
                                            <i class="fas fa-bolt me-1"></i>Today
                                        </span>
                                    <?php elseif ($is_past): ?>
                                        <span class="status-badge status-completed">
                                            <i class="fas fa-check me-1"></i>Completed
                                        </span>
                                    <?php else: ?>
                                        <span class="status-badge status-upcoming">
                                            <i class="fas fa-clock me-1"></i>Upcoming
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="progress" style="height: 6px; width: 80px;">
                                        <div class="progress-bar bg-success" style="width: <?= min(100, $exam['grades_entered'] * 10) ?>%"></div>
                                    </div>
                                    <small class="text-muted"><?= $exam['grades_entered'] ?>/10 entered</small>
                                </td>
                                <td class="pe-4">
                                    <div class="btn-group">
                                        <a href="enter_grades.php?exam_id=<?= $exam['exam_id'] ?>" 
                                           class="btn btn-sm btn-success action-btn" title="Enter Grades">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="results.php?exam_id=<?= $exam['exam_id'] ?>" 
                                           class="btn btn-sm btn-info action-btn" title="View Results">
                                            <i class="fas fa-chart-bar"></i>
                                        </a>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this exam?')">
                                            <input type="hidden" name="exam_id" value="<?= $exam['exam_id'] ?>">
                                            <button type="submit" name="delete_exam" class="btn btn-sm btn-danger action-btn" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <i class="fas fa-folder-open text-muted fs-1 d-block mb-2"></i>
                                    <p class="text-muted mb-0">No exams found</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Create Exam Modal -->
<div class="modal fade" id="createExamModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header gradient-header text-white">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Create New Exam</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Exam Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required 
                                   placeholder="e.g., Midterm Examination">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Exam Type <span class="text-danger">*</span></label>
                            <select name="exam_type_id" class="form-select" required>
                                <?php 
                                $exam_types = $conn->query("SELECT MIN(exam_type_id) AS exam_type_id, name, max_marks FROM exam_types GROUP BY name, max_marks ORDER BY name");
                                while ($type = $exam_types->fetch_assoc()): ?>
                                <option value="<?= $type['exam_type_id'] ?>">
                                    <?= htmlspecialchars($type['name']) ?> (Max: <?= htmlspecialchars($type['max_marks']) ?> marks)
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Subject <span class="text-danger">*</span></label>
                            <select name="subject_id" class="form-select" required>
                                <?php 
                                $courses = $conn->query("SELECT * FROM courses");
                                while ($course = $courses->fetch_assoc()): ?>
                                <option value="<?= $course['id'] ?>"><?= $course['course_name'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Exam Date & Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="exam_date" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Duration (minutes)</label>
                            <input type="number" name="duration" class="form-control" value="60" min="15" max="180">
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" name="is_active" id="isActive" checked>
                                <label class="form-check-label" for="isActive">Active (Visible to Students)</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Instructions</label>
                            <textarea name="instructions" class="form-control" rows="3" 
                                      placeholder="Enter exam instructions for students..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="create_exam" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Create Exam
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php include '../includes/footer.php'; ?>
</body>
</html>
