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
    course_code VARCHAR(50) NOT NULL;
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

$parents = "CREATE TABLE parents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    FOREIGN KEY (student_id) REFERENCES students(id)
)";

// if($conn->query($parents)=== TRUE){
//     echo "Table Created Successfully";
// }
// else{
//     echo "Error: ".$conn ->error;	
// }


$exam = "CREATE TABLE IF NOT EXISTS exams (
    exam_id INT AUTO_INCREMENT PRIMARY KEY,
    exam_type_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    exam_date DATE NOT NULL,
    subject_id INT NOT NULL,
    description TEXT,
    FOREIGN KEY (exam_type_id) REFERENCES exam_types(exam_type_id),
    FOREIGN KEY (subject_id) REFERENCES courses(id)
)";

// if($conn->query($exam)=== TRUE){
//     echo "Table Created Successfully";
// }
// else{
//     echo "Error: ".$conn ->error;	
// };

$exam_subjects= "CREATE TABLE exam_subjects (
    exam_subject_id INT AUTO_INCREMENT PRIMARY KEY,
    exam_id INT NOT NULL,
    course_id INT NOT NULL,
    max_marks DECIMAL(5,2) NOT NULL,
    passing_marks DECIMAL(5,2) NOT NULL,
    FOREIGN KEY (exam_id) REFERENCES exams(exam_id),
    FOREIGN KEY (course_id) REFERENCES courses(id),
    UNIQUE KEY (exam_id, course_id)
)";

// if($conn->query($exam_subjects)=== TRUE){
//     echo "Table Created Successfully";
// }
// else{
//     echo "Error: ".$conn ->error;	
// };

$student_grades = "CREATE TABLE IF NOT EXISTS student_grades (
    grade_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    exam_id INT NOT NULL,
    marks_obtained DECIMAL(5,2) NOT NULL,
    recorded_by INT,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (exam_id) REFERENCES exams(exam_id),
    FOREIGN KEY (recorded_by) REFERENCES users(id)
)";


// if($conn->query($student_grades)=== TRUE){
//     echo "Table Created Successfully";
// }
// else{
//     echo "Error: ".$conn ->error;	
// };


$grade_scales = "CREATE TABLE grade_scale (
    scale_id INT AUTO_INCREMENT PRIMARY KEY,
    min_percentage DECIMAL(5,2) NOT NULL,
    max_percentage DECIMAL(5,2) NOT NULL,
    letter_grade VARCHAR(2) NOT NULL,
    grade_points DECIMAL(3,2) NOT NULL,
    description VARCHAR(100),
    UNIQUE KEY (min_percentage, max_percentage)
);";


// if($conn->query($grade_scales)=== TRUE){
//     echo "Table Created Successfully";
// }
// else{
//     echo "Error: ".$conn ->error;	
// };

$student_performance= "CREATE TABLE IF NOT EXISTS student_performance (
    performance_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    semester VARCHAR(20),
    midterm_marks DECIMAL(5,2),
    final_marks DECIMAL(5,2),
    sessional_marks DECIMAL(5,2),
    total_marks DECIMAL(5,2),
    percentage DECIMAL(5,2),
    final_grade VARCHAR(2),
    gpa DECIMAL(3,2),
    status ENUM('Pass', 'Fail') NOT NULL,
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (course_id) REFERENCES courses(id),
    UNIQUE KEY (student_id, course_id, semester)
)";

// if($conn->query($student_performance)=== TRUE){
//     echo "Table Created Successfully";
// }
// else{
//     echo "Error: ".$conn ->error;	
// };

$exam_types = "CREATE TABLE exam_types (
    exam_type_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    max_marks DECIMAL(5,2) NOT NULL,
    description TEXT
)";


// if($conn->query($exam_types)=== TRUE){
//     echo "Table Created Successfully";
// }
// else{
//     echo "Error: ".$conn ->error;	
// };

$exam_type= "INSERT INTO exam_types (name, max_marks, description) VALUES 
('Midterm', 30, 'Midterm examination'),
('Final', 50, 'Final examination'),
('Attendance', 5, 'Class attendance marks'),
('Quiz', 5, 'Quiz performance marks'),
('Presentation', 5, 'Presentation marks'),
('Assignment', 5, 'Assignment marks')";


// if($conn->query($exam_type)=== TRUE){
//     echo "Table Created Successfully";
// }
// else{
//     echo "Error: ".$conn ->error;	
// };
?>