<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}
include 'config/connection.php';
include "includes/header.php";

// Get all programs
$programsResult = $conn->query("SELECT * FROM programs");
$programs = [];
while ($row = $programsResult->fetch_assoc()) {
    $programs[] = $row;
}

// Get all courses with their program associations
$coursesQuery = "SELECT c.id, c.course_name, c.program_id, p.program_name 
                    FROM courses c 
                    JOIN programs p ON c.program_id = p.id 
                    ORDER BY p.program_name, c.course_name";
$coursesResult = $conn->query($coursesQuery);
$coursesByProgram = [];

while ($row = $coursesResult->fetch_assoc()) {
    $programId = $row['program_id'];
    if (!isset($coursesByProgram[$programId])) {
        $coursesByProgram[$programId] = [
            'name' => $row['program_name'],
            'courses' => []
        ];
    }
    $coursesByProgram[$programId]['courses'][] = [
        'id' => $row['id'],
        'name' => $row['course_name']
    ];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Student | Student Management System</title>
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
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h2 class="text-center mb-0">Add New Student</h2>
        </div>
        <div class="card-body">
            <form action="store.php" method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6">
                        <h4 class="mb-3"><i class="fas fa-user"></i> Personal Information</h4>
                        <div class="mb-3">
                            <label for="name" class="form-label">Name:</label>
                            <input type="text" id="name" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="f_name" class="form-label">Father's Name:</label>
                            <input type="text" id="f_name" name="f_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Gender:</label>
                            <div class="form-check">
                                <input type="radio" id="male" name="gender" value="Male" class="form-check-input" required>
                                <label for="male" class="form-check-label">Male</label>
                            </div>
                            <div class="form-check">
                                <input type="radio" id="female" name="gender" value="Female" class="form-check-input">
                                <label for="female" class="form-check-label">Female</label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email:</label>
                            <input type="email" id="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone_no" class="form-label">Phone:</label>
                            <input type="number" id="phone_no" name="phone_no" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Address:</label>
                            <textarea id="address" name="address" class="form-control" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h4 class="mb-3"><i class="fas fa-graduation-cap"></i> Academic Information</h4>
                        <div class="mb-3">
                            <label for="roll_no" class="form-label">Roll No:</label>
                            <input type="text" id="roll_no" name="roll_no" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="marks" class="form-label">Marks:</label>
                            <input type="number" id="marks" name="marks" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="last_qualification" class="form-label">Last Qualification:</label>
                            <select id="last_qualification" name="last_qualification" class="form-select" required>
                                <option value="">Select Qualification</option>
                                <option value="Matric">Matric</option>
                                <option value="Intermediate">Intermediate</option>
                                <option value="Bachelor">Bachelor</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="result_card" class="form-label">Result Card:</label>
                            <input type="file" id="result_card" name="result_card" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password:</label>
                            <input type="password" id="password" name="password" class="form-control" required>
                        </div>
                    </div>
                </div>



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
                                   value="<?= $course['id'] ?>">
                            <label class="form-check-label" for="course_<?= $course['id'] ?>"><?= htmlspecialchars($course['name']) ?></label>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php
// ... (Rest of your HTML and JavaScript) ...
?>
                <div class="d-flex justify-content-between mt-4">
                    <a href="read.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Student
                    </a>
                    </button>
                    
                </div>
            </form>
        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<!-- <script>
    $(document).ready(function() {
        // Handle program selection
        $('input[name="program"]').change(function() {
            var selectedProgram = $(this).val();
            $('.course-options').hide();
            $('#program_' + selectedProgram).show();
        });
    });
</script> -->
</body>
</html>