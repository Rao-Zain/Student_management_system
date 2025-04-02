<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

include 'config/connection.php';
include "includes/header.php";

// Query to get all students with their programs and courses
$sql = "SELECT s.id, s.name, s.father_name, s.roll_no, s.last_qualification, 
        s.marks, s.email, s.phone, s.address, s.gender, s.result_card
        FROM students s
        ORDER BY s.id";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Students</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
        .card-header {
            background-color: #f8f9fa;
        }
        .program-badge {
            background-color: #6c757d;
            color: white;
            margin-right: 5px;
            margin-bottom: 5px;
            font-size: 0.85rem;
        }
        .course-badge {
            background-color: #17a2b8;
            color: white;
            margin-right: 5px;
            margin-bottom: 5px;
            font-size: 0.8rem;
        }
        .result-card-img {
            max-width: 100px;
            max-height: 100px;
        }
    </style>
</head>
<body>

<div class="container-fluid mt-4">
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

    <div class="row mb-3">
        <div class="col-md-6">
            <h2>All Students</h2>
        </div>
        <div class="col-md-6 text-right">
            <a href="create.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add New Student</a>
        </div>
    </div>

    <?php if ($result && $result->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Father's Name</th>
                        <th>Roll No</th>
                        <th>Qualification</th>
                        <th>Marks</th>
                        <th>Programs & Courses</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Address</th>
                        <th>Gender</th>
                        <th>Result Card</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): 
                        // Get programs and courses for this student
                        $programsQuery = "SELECT DISTINCT p.id, p.program_name
                                        FROM programs p
                                        JOIN courses c ON p.id = c.program_id
                                        JOIN student_courses sc ON c.id = sc.course_id
                                        WHERE sc.student_id = {$row['id']}
                                        ORDER BY p.program_name";
                        $programsResult = $conn->query($programsQuery);
                        
                        // Get courses for this student
                        $coursesQuery = "SELECT c.id, c.course_name, p.program_name, p.id as program_id
                                        FROM courses c
                                        JOIN programs p ON c.program_id = p.id
                                        JOIN student_courses sc ON c.id = sc.course_id
                                        WHERE sc.student_id = {$row['id']}
                                        ORDER BY p.program_name, c.course_name";
                        $coursesResult = $conn->query($coursesQuery);
                        
                        // Group courses by program
                        $programCourses = [];
                        if ($coursesResult && $coursesResult->num_rows > 0) {
                            while ($course = $coursesResult->fetch_assoc()) {
                                if (!isset($programCourses[$course['program_id']])) {
                                    $programCourses[$course['program_id']] = [
                                        'name' => $course['program_name'],
                                        'courses' => []
                                    ];
                                }
                                $programCourses[$course['program_id']]['courses'][] = $course['course_name'];
                            }
                        }
                    ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['name'] ?></td>
                        <td><?= $row['father_name'] ?></td>
                        <td><?= $row['roll_no'] ?></td>
                        <td><?= $row['last_qualification'] ?></td>
                        <td><?= $row['marks'] ?></td>
                        <td>
                            <?php if (!empty($programCourses)): ?>
                                <?php foreach ($programCourses as $program): ?>
                                    <div class="mb-2">
                                        <span class="badge program-badge"><?= $program['name'] ?></span>
                                        <div class="ml-2">
                                            <?php foreach ($program['courses'] as $course): ?>
                                                <span class="badge course-badge"><?= $course ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span class="text-muted">No programs/courses</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $row['email'] ?></td>
                        <td><?= $row['phone'] ?></td>
                        <td><?= substr($row['address'], 0, 30) . (strlen($row['address']) > 30 ? '...' : '') ?></td>
                        <td><?= $row['gender'] ?></td>
                        <td>
                            <?php 
                            $resultCardPath = 'uploads/' . $row['result_card'];
                            if (!empty($row['result_card']) && file_exists($resultCardPath)): 
                            ?>
                                <a href="<?= $resultCardPath ?>" target="_blank">
                                    <img src="<?= $resultCardPath ?>" class="result-card-img" alt="Result Card">
                                </a>
                            <?php else: ?>
                                <span class="text-muted">No image</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="update.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary mb-1">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger mb-1" 
                               onclick="return confirm('Are you sure you want to delete this student?')">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                            <!-- <a href="student_detail.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i> View
                            </a> -->
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> No Records Found
        </div>
    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>