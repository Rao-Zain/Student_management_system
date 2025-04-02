<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

require_once 'config/connection.php';
require_once "includes/header.php";
// require_once "includes/functions.php";

// Verify user has permission to edit (if you have permission system)
if (function_exists('checkPermission')) {
    checkPermission('edit_student');
}

// Sanitize the ID input
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    $_SESSION['error_message'] = 'Invalid student ID.';
    header("Location: read.php");
    exit();
}

// Prepared statement to prevent SQL injection
$stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error_message'] = 'Student not found.';
    header("Location: read.php");
    exit();
}

$student = $result->fetch_assoc();

// Fetch all programs
$programsQuery = "SELECT * FROM programs ORDER BY program_name";
$programsResult = $conn->query($programsQuery);

// Fetch all courses with their programs
$coursesQuery = "SELECT c.*, p.program_name 
                FROM courses c 
                JOIN programs p ON c.program_id = p.id 
                ORDER BY p.program_name, c.course_name";
$coursesResult = $conn->query($coursesQuery);

// Get student's current courses
$studentCoursesQuery = "SELECT course_id FROM student_courses WHERE student_id = ?";
$stmt = $conn->prepare($studentCoursesQuery);
if (!$stmt) {
    // Output the MySQL error message
    echo "Error preparing statement: " . $conn->error;
    exit(); // Stop execution to prevent further errors
}
$stmt->bind_param("i", $id);
$stmt->execute();
$studentCoursesResult = $stmt->get_result();

$studentCourses = [];
while ($row = $studentCoursesResult->fetch_assoc()) {
    $studentCourses[] = $row['course_id'];
}

// Process form submission
if (isset($_POST['update'])) {
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Validate and sanitize inputs
        $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
        $father_name = filter_var($_POST['father_name'], FILTER_SANITIZE_STRING);
        $marks = filter_var($_POST['marks'], FILTER_SANITIZE_STRING);
        $roll_no = filter_var($_POST['roll_no'], FILTER_SANITIZE_STRING);
        $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : $student['password'];
        $last_qualification = filter_var($_POST['last_qualification'], FILTER_SANITIZE_STRING);
        $gender = filter_var($_POST['gender'], FILTER_SANITIZE_STRING);
        $address = filter_var($_POST['address'], FILTER_SANITIZE_STRING);
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $phone = filter_var($_POST['phone'], FILTER_SANITIZE_STRING);
        
        // Initialize result_card with current value
        $result_card = $student['result_card'];
        
        // Handle file upload if a new file is provided
        if (!empty($_FILES['result_card']['name'])) {
            $uploadsDir = 'uploads/';
            
            // Create directory if it doesn't exist
            if (!is_dir($uploadsDir)) {
                mkdir($uploadsDir, 0777, true);
            }
            
            // Generate unique filename to prevent overwriting
            $fileExtension = pathinfo($_FILES['result_card']['name'], PATHINFO_EXTENSION);
            $uniqueFilename = 'result_' . time() . '_' . uniqid() . '.' . $fileExtension;
            $targetFilePath = $uploadsDir . $uniqueFilename;
            
            // Check file type
            $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf'];
            if (in_array(strtolower($fileExtension), $allowedTypes)) {
                if (move_uploaded_file($_FILES['result_card']['tmp_name'], $targetFilePath)) {
                    // Delete old file if it exists and is different
                    if (!empty($student['result_card']) && file_exists($uploadsDir . $student['result_card']) && $student['result_card'] != $uniqueFilename) {
                        unlink($uploadsDir . $student['result_card']);
                    }
                    $result_card = $uniqueFilename;
                } else {
                    throw new Exception('Failed to upload file. Please try again.');
                }
            } else {
                throw new Exception('Invalid file type. Only JPG, JPEG, PNG, and PDF files are allowed.');
            }
        }
        
        // Use prepared statement for update to prevent SQL injection
        $updateStmt = $conn->prepare("UPDATE students SET 
                                name=?, 
                                father_name=?, 
                                marks=?, 
                                roll_no=?, 
                                password=?, 
                                last_qualification=?, 
                                gender=?, 
                                result_card=?, 
                                address=?, 
                                email=?, 
                                phone=?
                                -- updated_at=NOW()
                               WHERE id=?");
                               
        $updateStmt->bind_param("sssssssssssi", 
            $name, 
            $father_name, 
            $marks, 
            $roll_no, 
            $password, 
            $last_qualification, 
            $gender, 
            $result_card, 
            $address, 
            $email, 
            $phone,
            $id);
        
        if (!$updateStmt->execute()) {
            throw new Exception('Error updating student basic information: ' . $conn->error);
        }
        
        // Update student courses
        // First, delete all existing course associations
        $deleteCoursesStmt = $conn->prepare("DELETE FROM student_courses WHERE student_id = ?");
        $deleteCoursesStmt->bind_param("i", $id);
        
        if (!$deleteCoursesStmt->execute()) {
            throw new Exception('Error removing existing course enrollments: ' . $conn->error);
        }
        
        // Then, insert the new course selections
        if (isset($_POST['courses']) && !empty($_POST['courses'])) {
            $insertCourseStmt = $conn->prepare("INSERT INTO student_courses (student_id, course_id) VALUES (?, ?)");
            
            foreach ($_POST['courses'] as $courseId) {
                $insertCourseStmt->bind_param("ii", $id, $courseId);
                
                if (!$insertCourseStmt->execute()) {
                    throw new Exception('Error enrolling in course: ' . $conn->error);
                }
            }
        }
        
        // If everything succeeded, commit the transaction
        $conn->commit();
        
        // Log the action if you have logging functionality
        if (function_exists('logActivity')) {
            logActivity($_SESSION['user_id'], 'Updated student record', 'students', $id);
        }
        
        $_SESSION['success_message'] = 'Student updated successfully!';
        header("Location: read.php");
        exit();
        
    } catch (Exception $e) {
        // If anything fails, roll back the transaction
        $conn->rollback();
        $_SESSION['error_message'] = $e->getMessage();
    }
}

// Group courses by program for display
$coursesByProgram = [];
$coursesResult->data_seek(0); // Reset pointer
while ($course = $coursesResult->fetch_assoc()) {
    if (!isset($coursesByProgram[$course['program_id']])) {
        $coursesByProgram[$course['program_id']] = [
            'name' => $course['program_name'],
            'courses' => []
        ];
    }
    $coursesByProgram[$course['program_id']]['courses'][] = [
        'id' => $course['id'],
        'name' => $course['course_name']
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student | Student Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
        .card-header {
            background-color: #f8f9fa;
        }
        .program-section {
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
        }
        .program-title {
            font-weight: bold;
            margin-bottom: 10px;
            border-bottom: 1px solid #eee;
            padding-bottom: 8px;
        }
        .course-options {
            display: flex;
            flex-wrap: wrap;
        }
        .course-option {
            width: 50%;
            padding: 5px 0;
        }
    </style>
</head>
<body>
<div class="container mt-4">
    <!-- Success/Error Messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
            <?= $_SESSION['success_message']; ?>
            <?php unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger">
            <?= $_SESSION['error_message']; ?>
            <?php unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>

    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h2 class="text-center mb-0">Edit Student</h2>
        </div>
        <div class="card-body">
            <form action="" method="POST" enctype="multipart/form-data">
                <!-- Personal Information Section -->
                <div class="row">
                    <div class="col-md-6">
                        <h4 class="mb-3"><i class="fas fa-user"></i> Personal Information</h4>
                        <div class="mb-3">
                            <label for="name" class="form-label">Name:</label>
                            <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($student['name']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="father_name" class="form-label">Father's Name:</label>
                            <input type="text" id="father_name" name="father_name" class="form-control" value="<?= htmlspecialchars($student['father_name']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Gender:</label>
                            <div class="form-check">
                                <input type="radio" id="male" name="gender" value="Male" class="form-check-input" <?= ($student['gender'] == 'Male') ? 'checked' : '' ?>>
                                <label for="male" class="form-check-label">Male</label>
                            </div>
                            <div class="form-check">
                                <input type="radio" id="female" name="gender" value="Female" class="form-check-input" <?= ($student['gender'] == 'Female') ? 'checked' : '' ?>>
                                <label for="female" class="form-check-label">Female</label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email:</label>
                            <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($student['email']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone:</label>
                            <input type="text" id="phone" name="phone" class="form-control" value="<?= htmlspecialchars($student['phone']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Address:</label>
                            <textarea id="address" name="address" class="form-control" rows="3"><?= htmlspecialchars($student['address']) ?></textarea>
                        </div>
                    </div>
                    
                    <!-- Academic Information -->
                    <div class="col-md-6">
                        <h4 class="mb-3"><i class="fas fa-graduation-cap"></i> Academic Information</h4>
                        <div class="mb-3">
                            <label for="roll_no" class="form-label">Roll No:</label>
                            <input type="text" id="roll_no" name="roll_no" class="form-control" value="<?= htmlspecialchars($student['roll_no']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="marks" class="form-label">Marks:</label>
                            <input type="text" id="marks" name="marks" class="form-control" value="<?= htmlspecialchars($student['marks']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="last_qualification" class="form-label">Last Qualification:</label>
                            <select id="last_qualification" name="last_qualification" class="form-select">
                                <?php foreach(['Matric', 'Intermediate', 'Bachelor', 'Master'] as $qualification): ?>
                                    <option value="<?= $qualification ?>" <?= ($student['last_qualification'] == $qualification) ? 'selected' : '' ?>><?= $qualification ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="result_card" class="form-label">Result Card:</label>
                            <input type="file" id="result_card" name="result_card" class="form-control">
                            <?php if (!empty($student['result_card'])): ?>
                                <div class="mt-2">
                                    <small class="text-muted">Current file: <?= htmlspecialchars($student['result_card']) ?></small>
                                    <img src="uploads/<?= htmlspecialchars($student['result_card']) ?>" 
                                         class="mt-2 img-thumbnail" 
                                         style="max-width: 150px;"
                                         alt="Result Card">
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password: <small class="text-muted">(Leave blank to keep current password)</small></label>
                            <input type="password" id="password" name="password" class="form-control">
                        </div>
                    </div>
                </div>
                
                <!-- Course Selection Section -->
                <div class="mt-4">
                    <h4 class="mb-3"><i class="fas fa-book"></i> Program & Course Selection</h4>
                    <p class="text-muted">Select the courses this student is enrolled in:</p>
                    
                    <?php foreach ($coursesByProgram as $programId => $program): ?>
                        <div class="program-section">
                            <div class="program-title">
                                <i class="fas fa-bookmark"></i> <?= htmlspecialchars($program['name']) ?>
                            </div>
                            <div class="course-options">
                                <?php foreach ($program['courses'] as $course): ?>
                                    <div class="course-option">
                                        <div class="form-check">
                                            <input type="checkbox" 
                                                   class="form-check-input" 
                                                   id="course_<?= $course['id'] ?>" 
                                                   name="courses[]" 
                                                   value="<?= $course['id'] ?>" 
                                                   <?= in_array($course['id'], $studentCourses) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="course_<?= $course['id'] ?>"><?= htmlspecialchars($course['name']) ?></label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="d-flex justify-content-between mt-4">
                    <a href="read.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                    <button type="submit" name="update" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Student
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script>
// You can add custom JavaScript here if needed
document.addEventListener('DOMContentLoaded', function() {
    // For example, you might want to add client-side validation or dynamic form behavior
});
</script>
</body>
</html>