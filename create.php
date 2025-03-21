<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}
?>
<?php
include 'config/connection.php';
include "includes/header.php";

$programsResult = $conn->query("SELECT * FROM programs");
$coursesResult = $conn->query("SELECT * FROM courses");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Student</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>

<div class="container mt-5">
    <h2>Add New Student</h2>
    <form action="store.php" method="POST" enctype="multipart/form-data">

        <div class="form-group">
            <label>Name:</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Father Name:</label>
            <input type="text" name="f_name" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Marks:</label>
            <input type="number" name="marks" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Roll No:</label>
            <input type="text" name="roll_no" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Password:</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Last Qualification:</label>
            <select name="last_qualification" class="form-control" required>
                <option value="">Select Qualification</option>
                <option value="Matric">Matric</option>
                <option value="Intermediate">Intermediate</option>
                <option value="Bachelor">Bachelor</option>
            </select>
        </div>

        <div class="form-group">
            <label>Select Programs:</label><br>
            <?php while ($program = $programsResult->fetch_assoc()) { ?>
                <label><input type="checkbox" name="programs[]" value="<?php echo $program['program_name']; ?>"> <?php echo $program['program_name']; ?></label><br>
            <?php } ?>
        </div>
        <div class="form-group">
            <label>Email:</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Phone No:</label>
            <input type="number" name="phone_no" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Select Courses:</label><br>
            <?php while ($course = $coursesResult->fetch_assoc()) { ?>
                <label><input type="checkbox" name="courses[]" value="<?php echo $course['course_name']; ?>"> <?php echo $course['course_name']; ?></label><br>
            <?php } ?>
        </div>

        <div class="form-group">
            <label>Gender:</label><br>
            <label><input type="radio" name="gender" value="Male"> Male</label>
            <label><input type="radio" name="gender" value="Female"> Female</label>
        </div>

        <div class="form-group">
            <label>Upload Picture (Result Card):</label>
            <input type="file" name="result_card" class="form-control-file" required>
        </div>

        <div class="form-group">
            <label>Address:</label>
            <textarea name="address" class="form-control" rows="3" required></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Add Student</button>
    </form>
</div>

</body>
</html>
