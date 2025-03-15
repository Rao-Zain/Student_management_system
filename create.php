<!-- create.php -->
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
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = $_POST['name'];
    $marks = $_POST['marks'];
    $roll_no = $_POST['roll_no'];
    $password = $_POST['password'];
    $last_qualification = $_POST['last_qualification'];
    $programme = $_POST['programme'];
    $gender = $_POST['gender'];
    $address = $_POST['address'];
    
    // Handling Image Upload
    $result_card = $_FILES['result_card']['name'];
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["result_card"]["name"]);
    move_uploaded_file($_FILES["result_card"]["tmp_name"], $target_file);

    // Inserting Data into the Database
    $sql = "INSERT INTO students (name, marks, roll_no, password, last_qualification, programme, gender, result_card, address) 
            VALUES ('$name', '$marks', '$roll_no', '$password', '$last_qualification', '$programme', '$gender', '$target_file', '$address')";

    if ($conn->query($sql) === TRUE) {
        echo "Record added successfully!";
        header("Location: index.php"); // Redirecting to the main page
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
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
            <label>Father's Name:</label>
            <input type="text" name="father_name" class="form-control" required>
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
            <label for="qualification">Last Qualification:</label>
        <select name="qualification" class="form-control" required>
                        <option value="">Select Qualification</option>
                        <option value="Matric">Matric</option>
                        <option value="Intermediate">Intermediate</option>
                        <option value="Bachelor">Bachelor</option>
                    </select>
        </div>

        <!-- <div class="form-group"> -->
            
        <tr>
                <td class="form-group">Select Programs :</td>
                <td class="inline-group">
                    <label><input  type="checkbox" name="programme[]" value="BS(IT)"> BS(IT)</label>
                    <label><input type="checkbox" name="programme[]" value="BS(PA)"> BS(PA)</label>
                    <label><input type="checkbox" name="programme[]" value="BS(Eng)"> BS(Eng)</label>
                    <label><input type="checkbox" name="programme[]" value="BS(Soc)"> BS(Soc)</label>
                </td>
            </tr> 
         <!-- </div> -->
         <div class="form-group">
            <label>Email:</label>
            <input type="text" name="email" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Phone No:</label>
            <input type="text" name="phone" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Gender:</label>
           
                <td class="inline-group">
                    <label><input type="radio" name="gender" value="Male"> Male</label>
                    <label><input type="radio" name="gender" value="Female"> Female</label>
                </td>
        </div>
        <div class="form-group">
            <label>Course:</label>
            <input type="text" name="course" class="form-control" required>
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
