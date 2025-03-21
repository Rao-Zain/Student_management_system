<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

include 'config/connection.php';
include "includes/header.php";

// Fetch programs and courses from the database
$programsResult = $conn->query("SELECT * FROM programs");
$coursesResult = $conn->query("SELECT * FROM courses");

$id = $_GET['id'];
$sql = "SELECT * FROM students WHERE id = $id";
$result = $conn->query($sql);

if($result->num_rows > 0) {
    $row = $result->fetch_assoc();
} else {
    die("Student not found.");
}

if(isset($_POST['update'])){
    $name = $_POST['name'];
    $father_name = $_POST['father_name'];
    $marks = $_POST['marks'];
    $roll_no = $_POST['roll_no'];
    $password = $_POST['password'];
    $last_qualification = $_POST['last_qualification'];
    $programmes = isset($_POST['programme']) ? implode(", ", $_POST['programme']) : '';
    $courses = isset($_POST['course']) ? implode(", ", $_POST['course']) : '';
    $gender = $_POST['gender'];
    $address = $_POST['address'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    if($_FILES['result_card']['name']) {
        $uploadsDir = 'uploads/';
    
        // Check if the uploads directory exists, if not, create it
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0777, true);
        }
    
        $result_card = $uploadsDir . basename($_FILES['result_card']['name']);
    
        if (move_uploaded_file($_FILES['result_card']['tmp_name'], $result_card)) {
            echo "File uploaded successfully.";
        } else {
            echo "Failed to upload file.";
        }
    }
    

    $update = "UPDATE students SET 
                name='$name', 
                father_name='$father_name', 
                marks='$marks', 
                roll_no='$roll_no', 
                password='$password', 
                last_qualification='$last_qualification', 
                programme='$programmes', 
                course='$courses', 
                gender='$gender', 
                result_card='$result_card', 
                address='$address', 
                email='$email', 
                phone='$phone' 
               WHERE id=$id";

    if($conn->query($update) === TRUE) {
        header("Location: read.php");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Student</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center mb-4">Edit Student</h2>
    <form action="" method="POST" enctype="multipart/form-data">

        <!-- Name, Father's Name, Marks, Roll No -->
        <div class="mb-3"><label>Name:</label><input type="text" name="name" class="form-control" value="<?= $row['name'] ?>" required></div>
        <div class="mb-3"><label>Father's Name:</label><input type="text" name="father_name" class="form-control" value="<?= $row['father_name'] ?>" required></div>
        <div class="mb-3"><label>Marks:</label><input type="text" name="marks" class="form-control" value="<?= $row['marks'] ?>" required></div>
        <div class="mb-3"><label>Roll No:</label><input type="text" name="roll_no" class="form-control" value="<?= $row['roll_no'] ?>" required></div>

        <!-- Program Selection -->
        <div class="mb-3">
            <label>Programme:</label><br>
            <?php while($program = $programsResult->fetch_assoc()): ?>
                <label><input type="checkbox" name="programme[]" value="<?= $program['program_name'] ?>" 
                    <?= in_array($program['program_name'], explode(", ", $row['programme'])) ? 'checked' : '' ?>> 
                    <?= $program['program_name'] ?>
                </label><br>
            <?php endwhile; ?>
        </div>

        <!-- Course Selection -->
        <div class="mb-3">
            <label>Course:</label><br>
            <?php while($course = $coursesResult->fetch_assoc()): ?>
                <label><input type="checkbox" name="course[]" value="<?= $course['course_name'] ?>" 
                    <?= in_array($course['course_name'], explode(", ", $row['course'])) ? 'checked' : '' ?>> 
                    <?= $course['course_name'] ?>
                </label><br>
            <?php endwhile; ?>
        </div>

        <!-- Last Qualification -->
        <div class="mb-3">
            <label>Last Qualification:</label>
            <select name="last_qualification" class="form-control">
                <?php foreach(['Matric', 'Intermediate', 'Bachelor', 'Master'] as $qualification): ?>
                    <option value="<?= $qualification ?>" <?= ($row['last_qualification'] == $qualification) ? 'selected' : '' ?>><?= $qualification ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Other Fields -->
        <div class="mb-3"><label>Email:</label><input type="text" name="email" class="form-control" value="<?= $row['email'] ?>" required></div>
        <div class="mb-3"><label>Phone:</label><input type="text" name="phone" class="form-control" value="<?= $row['phone'] ?>" required></div>
        <div class="mb-3"><label>Gender:</label> 
            <input type="radio" name="gender" value="Male" <?= ($row['gender'] == 'Male') ? 'checked' : '' ?>> Male
            <input type="radio" name="gender" value="Female" <?= ($row['gender'] == 'Female') ? 'checked' : '' ?>> Female
        </div>
        <div class="mb-3"><label>Result Card:</label><input type="file" name="result_card" class="form-control"></div>
        <div class="mb-3"><label>Address:</label><textarea name="address" class="form-control"><?= $row['address'] ?></textarea></div>

        <button type="submit" name="update" class="btn btn-primary">Update Student</button>
    </form>
</div>
</body>
</html>
