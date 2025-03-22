<?php

$servername = "localhost";
$user = "root";
$password = "";
$dbname = "Student_Management_System";

$conn = new mysqli($servername, $user, $password, $dbname);

if($conn->connect_error){
    die("Connection Failed: ".$conn->connect_error);

}
// header("Location: read.php");
// echo "Connected Successfully";

// $create = "CREATE DATABASE Student_Management_System";
// if($conn->query($create)===TRUE){
//     Echo "Database Created Successfully";
// }
// else{
//     echo "Error: ".$conn ->error;
// }

$user = " CREATE TABLE IF NOT EXISTS users(
    id INT(6) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    reset_token VARCHAR(255),
    verification_token VARCHAR(255),
    is_verified  TINYINT(1) DEFAULT 0,
    role VARCHAR(20) DEFAULT 'student'
)";
$users = "ALTER TABLE users ADD COLUMN role ENUM('Admin', 'Student', 'Teacher') DEFAULT 'Student'";

// if($conn->query($user)=== TRUE){
//     echo "Table Created Successfully";
// }
// else{
//     echo "Error: ".$conn ->error;	
// }


$sql = " CREATE TABLE if not EXISTS students(
    id INT(6) AUTO_INCREMENT PRIMARY KEY,
     name VARCHAR(244) NOT NULL,
     marks int(11) NOT NULL,
     roll_no VARCHAR(255) NOT NULL,
     password varchar(255) not null,
     last_qualification varchar(255) not null,
     programme varchar(255) not null,
     gender varchar(255) not null,
     address varchar(255) not null,
        result_card varchar(255) not null
     )";

// if($conn->query($sql)=== TRUE){
//     echo "Table Created Successfully";
// }
// else{
//     echo "Error: ".$conn ->error;	
// }

$program = "CREATE TABLE programs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    program_name VARCHAR(100) NOT NULL,
    -- description TEXT
)";

// if($conn->query($program)=== TRUE){
//     echo "Table Created Successfully";
// }
// else{
//     echo "Error: ".$conn ->error;	
// }

$courses = "CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_name VARCHAR(100) NOT NULL,
    program_id INT,
    -- description TEXT,
    FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE
)";


// if($conn->query($courses)=== TRUE){
//     echo "Table Created Successfully";
// }
// else{
//     echo "Error: ".$conn ->error;	
// }

$attendance = "CREATE TABLE IF NOT EXISTS attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT,
    subject VARCHAR(255),
    date DATE,
    status VARCHAR(20)
)";

// if($conn->query($attendance)=== TRUE){
//     echo "Table Created Successfully";
// }
// else{
//     echo "Error: ".$conn ->error;	
// }

// $subject = "ALTER TABLE attendance ADD subject VARCHAR(255) NOT NULL";
// if($conn->query($subject)=== TRUE){
//     echo "Table Created Successfully";
// }
// else{
//     echo "Error: ".$conn ->error;	
// }
$teacher = "CREATE TABLE teachers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    subject VARCHAR(100) NOT NULL
)";
// if($conn->query($teacher)=== TRUE){
//     echo "Table Created Successfully";
// }
// else{
//     echo "Error: ".$conn ->error;	
// }
$teacher_sbuject = "CREATE TABLE IF NOT EXISTS `teacher_subjects` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `teacher_id` INT NOT NULL,
    `course_id ` INT NOT NULL,
    FOREIGN KEY (`teacher_id`) REFERENCES `teachers`(`id`),
    FOREIGN KEY (`subject_id`) REFERENCES `courses`(`id`)
)";
// if($conn->query($teacher_sbuject)=== TRUE){
//     echo "Table Created Successfully";
// }
// else{
//     echo "Error: ".$conn ->error;	
// }

$student_courses = "CREATE TABLE student_courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
)";

// if($conn->query($student_courses)=== TRUE){
//     echo "Table Created Successfully";
// }
// else{
//     echo "Error: ".$conn ->error;	
// }

?>