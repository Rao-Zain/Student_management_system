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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $marks = $_POST['marks'];
    $email = $_POST['email'];
    $father_name = $_POST['father_name'];
    $Phone = $_POST['phone'];
    $course = $_POST['course'];
    $roll_no = $_POST['roll_no'];
    $password = $_POST['password'];
    $last_qualification = $_POST['qualification'];
    $programme = implode(", ", $_POST['programme']);
    $gender = $_POST['gender'];
    $address = $_POST['address'];

    // Handle the file upload
    $result_card = $_FILES['result_card']['name'];
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($result_card);
    move_uploaded_file($_FILES['result_card']['tmp_name'], $target_file);

    $sql = "INSERT INTO students (name, marks, roll_no, password, last_qualification, programme, gender, result_card, address, email, phone, course, father_name)
            VALUES ('$name', '$marks', '$roll_no', '$password', '$last_qualification', '$programme', '$gender', '$result_card', '$address' , '$email', '$Phone', '$course', '$father_name')";

    if ($conn->query($sql) === TRUE) {
        echo "Student added successfully!";
        header("Location: read.php");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}

$conn->close();
?>
