<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] !== 'Admin' && strtolower($_SESSION['user_role']) !== 'teacher')) {
    header("Location: ../auth/login.php?error=Unauthorized access");
    exit();
}
require_once '../config/connection.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_scale'])) {
        // Add new grade scale
        $min = $_POST['min_percentage'];
        $max = $_POST['max_percentage'];
        $letter = $_POST['letter_grade'];
        $points = $_POST['grade_points'];
        $description = $_POST['description'];
        
        $stmt = $conn->prepare("INSERT INTO grade_scale 
                              (min_percentage, max_percentage, letter_grade, grade_points, description) 
                              VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ddsss", $min, $max, $letter, $points, $description);
        $stmt->execute();
        
        $_SESSION['message'] = "Grade scale added successfully!";
        header("Location: grade_scales.php");
        exit;
    }
    elseif (isset($_POST['update_scale'])) {
        // Update existing grade scale
        $id = $_POST['scale_id'];
        $min = $_POST['min_percentage'];
        $max = $_POST['max_percentage'];
        $letter = $_POST['letter_grade'];
        $points = $_POST['grade_points'];
        $description = $_POST['description'];
        
        $stmt = $conn->prepare("UPDATE grade_scale SET 
                              min_percentage = ?,
                              max_percentage = ?,
                              letter_grade = ?,
                              grade_points = ?,
                              description = ?
                              WHERE scale_id = ?");
        $stmt->bind_param("ddsssi", $min, $max, $letter, $points, $description, $id);
        $stmt->execute();
        
        $_SESSION['message'] = "Grade scale updated successfully!";
        header("Location: grade_scales.php");
        exit;
    }
}

// Handle delete request
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Check if scale is being used before deleting
    $check = $conn->query("SELECT COUNT(*) FROM student_grades WHERE grade = 
                          (SELECT letter_grade FROM grade_scale WHERE scale_id = $id)")->fetch_row()[0];
    
    if ($check > 0) {
        $_SESSION['error'] = "Cannot delete - this grade scale is being used by student records!";
    } else {
        $stmt = $conn->prepare("DELETE FROM grade_scale WHERE scale_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        $_SESSION['message'] = "Grade scale deleted successfully!";
    }
    
    header("Location: grade_scales.php");
    exit;
}

// Fetch scale for editing
$edit_scale = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $edit_scale = $conn->query("SELECT * FROM grade_scale WHERE scale_id = $id")->fetch_assoc();
}

// Fetch all grade scales
$scales = $conn->query("SELECT * FROM grade_scale ORDER BY min_percentage DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Grade Scale Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .edit-form {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include '../attendance/attendance_header.php'; ?>
    
    <div class="container mt-4">
        <!-- Display messages -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <h2>Grade Scale Management</h2>
        
        <!-- Edit Form (shown when editing) -->
        <?php if ($edit_scale): ?>
        <div class="edit-form">
            <h4>Edit Grade Scale</h4>
            <form method="POST">
                <input type="hidden" name="scale_id" value="<?= $edit_scale['scale_id'] ?>">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Min Percentage</label>
                        <input type="number" step="0.01" name="min_percentage" 
                               class="form-control" value="<?= $edit_scale['min_percentage'] ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Max Percentage</label>
                        <input type="number" step="0.01" name="max_percentage" 
                               class="form-control" value="<?= $edit_scale['max_percentage'] ?>" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Letter Grade</label>
                        <input type="text" name="letter_grade" 
                               class="form-control" value="<?= $edit_scale['letter_grade'] ?>" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Grade Points</label>
                        <input type="number" step="0.01" name="grade_points" 
                               class="form-control" value="<?= $edit_scale['grade_points'] ?>" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Description</label>
                        <input type="text" name="description" 
                               class="form-control" value="<?= htmlspecialchars($edit_scale['description']) ?>">
                    </div>
                    <div class="col-12">
                        <button type="submit" name="update_scale" class="btn btn-primary">Update Scale</button>
                        <a href="grade_scale.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
        <?php endif; ?>
        
        <!-- Add Grade Scale Form -->
        <div class="card mb-4">
            <div class="card-header"><?= $edit_scale ? 'Add New Grade Scale' : 'Add Grade Scale' ?></div>
            <div class="card-body">
                <form method="POST">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Min Percentage</label>
                            <input type="number" step="0.01" name="min_percentage" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Max Percentage</label>
                            <input type="number" step="0.01" name="max_percentage" class="form-control" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Letter Grade</label>
                            <input type="text" name="letter_grade" class="form-control" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Grade Points</label>
                            <input type="number" step="0.01" name="grade_points" class="form-control" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Description</label>
                            <input type="text" name="description" class="form-control">
                        </div>
                        <div class="col-12">
                            <button type="submit" name="add_scale" class="btn btn-primary">Add Scale</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Grade Scales List -->
        <div class="card">
            <div class="card-header">Current Grade Scales</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Min %</th>
                                <th>Max %</th>
                                <th>Letter Grade</th>
                                <th>Grade Points</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($scale = $scales->fetch_assoc()): ?>
                            <tr>
                                <td><?= $scale['min_percentage'] ?></td>
                                <td><?= $scale['max_percentage'] ?></td>
                                <td><?= $scale['letter_grade'] ?></td>
                                <td><?= $scale['grade_points'] ?></td>
                                <td><?= htmlspecialchars($scale['description']) ?></td>
                                <td>
                                    <a href="grade_scales.php?edit=<?= $scale['scale_id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="grade_scales.php?delete=<?= $scale['scale_id'] ?>" 
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Are you sure you want to delete this grade scale?')">Delete</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php include '../includes/footer.php'; ?>
</body>
</html>
