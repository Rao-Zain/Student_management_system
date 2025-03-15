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
    $programme = implode(", ", $_POST['programme']);
    $gender = $_POST['gender'];
    $address = $_POST['address'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $course = $_POST['course'];
   

    if($_FILES['result_card']['name']) {
        $result_card = 'uploads/' . $_FILES['result_card']['name'];
        move_uploaded_file($_FILES['result_card']['tmp_name'], $result_card);
    } else {
        $result_card = $row['result_card']; // Keep the existing file if not changed
    }

    $update = "UPDATE students SET name='$name', marks='$marks', roll_no='$roll_no', password='$password', 
               last_qualification='$last_qualification', programme='$programme', gender='$gender', 
               result_card='$result_card', address='$address', father_name='$father_name', email='$email', phone='$phone', 
               course='$course' WHERE id=$id";

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center mb-4">Edit Student</h2>
    <form action="" method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label>Name:</label>
            <input type="text" name="name" class="form-control" value="<?= $row['name'] ?>" required>
        </div>
        <div class="mb-3">
            <label>Father's Name:</label>
            <input type="text" name="father_name" class="form-control" value="<?= $row['father_name'] ?>" required>
        </div>
        <div class="mb-3">
            <label>Marks:</label>
            <input type="text" name="marks" class="form-control" value="<?= $row['marks'] ?>" required>
        </div>
        <div class="mb-3">
            <label>Roll No:</label>
            <input type="text" name="roll_no" class="form-control" value="<?= $row['roll_no'] ?>" required>
        </div>
        <div class="mb-3">
            <label>Password:</label>
            <input type="password" name="password" class="form-control" value="<?= $row['password'] ?>" required>
        </div>
        <div class="mb-3">
            <label>Last Qualification:</label>
            <select name="last_qualification"  class="form-control">
    <option  class="form-control" value="Matric" <?php if($row['last_qualification'] == 'Matric') echo 'selected'; ?>>Matric</option>
    <option  class="form-control" value="Intermediate" <?php if($row['last_qualification'] == 'Intermediate') echo 'selected'; ?>>Intermediate</option>
    <option  class="form-control" value="Bachelor" <?php if($row['last_qualification'] == 'Bachelor') echo 'selected'; ?>>Bachelor</option>
    <option  class="form-control" value="Master" <?php if($row['last_qualification'] == 'Master') echo 'selected'; ?>>Master</option>
</select>
 </div>
        <div class="mb-3">
        <?php
    // Fetch the existing programme value from the database
    $programmeArray = explode(", ", $row['programme']); // Convert the string to an array
?>

<label>Programme:</label><br>

<label><input  type="checkbox" name="programme[]" value="BS(IT)"<?php if(in_array('BS(IT)', $programmeArray)) echo 'checked'; ?>> BS(IT)</label> <br>
 <label><input type="checkbox" name="programme[]" value="BS(PA)" <?php if(in_array('BS(PA)', $programmeArray)) echo 'checked'; ?>> BS(PA)</label><br>
<label><input type="checkbox" name="programme[]" value="BS(Eng)" <?php if(in_array('BS(Eng)', $programmeArray)) echo 'checked'; ?> > BS(Eng)</label><br>
<label><input type="checkbox" name="programme[]" value="BS(Soc)"  <?php if(in_array('BS(Soc)', $programmeArray)) echo 'checked'; ?> > BS(Soc)</label><br>

        <div class="form-group mt-3">
            <label>Email:</label>
            <input type="text" name="email" class="form-control" value="<?= $row['email'] ?>" required>
        </div>
        <div class="form-group mt-3">
            <label>Phone NO:</label>
            <input type="text" name="phone" class="form-control" value="<?= $row['phone'] ?>" required>
        </div>
        <div class="mb-3 mt-3">
            <label>Gender:</label> <br>
            <input type="radio" name="gender" value="Male" <?php if($row['gender'] == 'Male') echo 'checked'; ?>> Male
<input type="radio" name="gender" value="Female" <?php if($row['gender'] == 'Female') echo 'checked'; ?>> Female

        </div>
        <div class="form-group">
            <label>Course:</label>
            <input type="text" name="course" class="form-control" value="<?= $row['course'] ?>" required>
        </div>
        <div class="mb-3">
            <label>Result Card:</label>
            <input type="file" name="result_card" class="form-control">
            <p>Current File: <?= $row['result_card'] ?></p>
        </div>
        <div class="mb-3">
            <label>Address:</label>
            <textarea name="address" class="form-control" required><?= $row['address'] ?></textarea>
        </div>
        <button type="submit" name="update" class="btn btn-primary">Update Student</button>
    </form>
</div>
</body>
</html>
