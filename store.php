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

// Fetch programs and courses from the database
$programsResult = $conn->query("SELECT * FROM programs");
$coursesResult = $conn->query("SELECT * FROM courses");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = $_POST['name'];
    $f_name = $_POST['f_name'];
    $marks = $_POST['marks'];
    $roll_no = $_POST['roll_no'];
    $email = $_POST['email'];
    $phone_no = $_POST['phone_no'];
    $password = $_POST['password'];
    $last_qualification = $_POST['last_qualification'];
    $programs = isset($_POST['programs']) ? implode(',', $_POST['programs']) : '';
    $courses = isset($_POST['courses']) ? implode(',', $_POST['courses']) : '';
    $gender = $_POST['gender'];
    $address = $_POST['address'];

    if($_FILES['result_card']['name']) {
        $result_card = 'uploads/' . $_FILES['result_card']['name'];
        move_uploaded_file($_FILES['result_card']['tmp_name'], $result_card);
    } else {
        $result_card = 'uploads/no_image.jpeg'; // Provide a default image if none is uploaded
    }
    
    

    $sql = "INSERT INTO students (name, father_name, marks, roll_no, email, phone,  password, last_qualification, programme, gender, result_card, address, course) 
            VALUES ('$name', '$f_name', '$marks', '$roll_no', '$email', '$phone_no', '$password', '$last_qualification', '$programs', '$gender', '$result_card', '$address', '$courses')";

    if ($conn->query($sql) === TRUE) {
        echo "Record added successfully!";
        header("Location: read .php");
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>
