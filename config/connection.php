<?php

$servername = "localhost";
$user = "root";
$password = "";
$dbname = "Student_Management_System";

// Step 1: Connect WITHOUT database
$conn = new mysqli($servername, $user, $password);

if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

// Step 2: Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";

if ($conn->query($sql) !== TRUE) {
    die("Error creating database: " . $conn->error);
}

// Step 3: Select the database
$conn->select_db($dbname);


//Infinity Free Credentials 


// $conn = new mysqli(
//     $servername,
//     $user,
//     $password,
//     $dbname
// );

// if ($conn->connect_error) {
//     die("Connection Failed: " . $conn->connect_error);
// }



// header("Location: read.php");
// echo "Connected Successfully";

// $create = "CREATE DATABASE Student_Management_System";
// if($conn->query($create)===TRUE){
//     Echo "Database Created Successfully";
// }
// else{
//     echo "Error: ".$conn ->error;
// }


$userTable = "CREATE TABLE IF NOT EXISTS users (
    id INT(6) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    reset_token VARCHAR(255),
    verification_token VARCHAR(255),
    is_verified TINYINT(1) DEFAULT 0,
    role ENUM('Admin', 'Student', 'Teacher') DEFAULT 'Student'
)";

if ($conn->query($userTable) !== TRUE) {
    die("Error creating users table: " . $conn->error);
}




$sql = " CREATE TABLE if not EXISTS students(
    id INT(6) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(244) NOT NULL,
    father_name VARCHAR(255),
    marks int(11) NOT NULL,
    roll_no VARCHAR(255) NOT NULL,
    password varchar(255) not null,
    last_qualification varchar(255) not null,
    gender varchar(255) not null,
    address varchar(255) not null,
    email VARCHAR(255),
    phone VARCHAR(50),
    result_card varchar(255) not null
)";

if($conn->query($sql) !== TRUE){
    die("Error creating students table: ".$conn ->error);
}

// Add missing columns to students table if they don't exist
$alter_students = "ALTER TABLE students 
    ADD COLUMN IF NOT EXISTS father_name VARCHAR(255),
    ADD COLUMN IF NOT EXISTS email VARCHAR(255),
    ADD COLUMN IF NOT EXISTS phone VARCHAR(50)";

if($conn->query($alter_students) !== TRUE){
    die("Error altering students table: ".$conn->error);
}

$program = "CREATE TABLE IF NOT EXISTS programs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    program_name VARCHAR(100) NOT NULL
)";
if($conn->query($program)=== TRUE){
    // Table created
} else {
    echo "Error: ".$conn->error;
}

$courses = "CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_name VARCHAR(100) NOT NULL,
    course_code VARCHAR(50) NOT NULL,
    program_id INT,
    FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE
)";
if($conn->query($courses)=== TRUE){
    
} else {
    echo "Error: ".$conn->error;
}

$attendance = "CREATE TABLE IF NOT EXISTS attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT,
    subject VARCHAR(255),
    date DATE,
    status VARCHAR(20)
)";

if($conn->query($attendance)=== TRUE){
    
}
else{
    echo "Error: ".$conn ->error;	
}

// Removed duplicate ALTER TABLE for 'subject' column in 'attendance'

// Migrate teachers to users if teachers table exists
$result = $conn->query("SHOW TABLES LIKE 'teachers'");
if ($result && $result->num_rows > 0) {
    // Insert teachers into users, avoiding duplicates by email
    $migrate = "INSERT INTO users (username, email, password, role) 
                SELECT name, email, password, 'Teacher' FROM teachers 
                WHERE email NOT IN (SELECT email FROM users)";
    $conn->query($migrate);

    // Update teacher_subjects to use the new user ids
    $update_ts = "UPDATE teacher_subjects 
                  SET teacher_id = (SELECT u.id FROM users u 
                                    INNER JOIN teachers t ON u.email = t.email 
                                    WHERE t.id = teacher_subjects.teacher_id)";
    $conn->query($update_ts);

    // Alter the foreign key to reference users instead of teachers
    $alter_fk = "ALTER TABLE teacher_subjects 
                 DROP FOREIGN KEY teacher_subjects_ibfk_1, 
                 ADD FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE";
    $conn->query($alter_fk);
}

// Drop the teachers table
$drop_teacher = "DROP TABLE IF EXISTS teachers";
$conn->query($drop_teacher);

// Create teacher_subjects if not exists (with correct FK)
$teacher_subject = "CREATE TABLE IF NOT EXISTS teacher_subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    course_id INT NOT NULL,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
)";
if($conn->query($teacher_subject)=== TRUE){
    
} else {
    echo "Error: ".$conn->error;
}

$student_courses = "CREATE TABLE IF NOT EXISTS student_courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
)";

if($conn->query($student_courses)=== TRUE){
    
}
else{
    echo "Error: ".$conn ->error;	
}

$parents = "CREATE TABLE IF NOT EXISTS parents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    FOREIGN KEY (student_id) REFERENCES students(id)
)";

if($conn->query($parents)=== TRUE){
    
}
else{
    echo "Error: ".$conn ->error;	
}


$exam = "CREATE TABLE IF NOT EXISTS exams (
    exam_id INT AUTO_INCREMENT PRIMARY KEY,
    exam_type_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    exam_date DATETIME NOT NULL,
    subject_id INT NOT NULL,
    description TEXT,
    duration_minutes INT DEFAULT 60,
    is_active TINYINT DEFAULT 1,
    FOREIGN KEY (exam_type_id) REFERENCES exam_types(exam_type_id),
    FOREIGN KEY (subject_id) REFERENCES courses(id)
)";

if($conn->query($exam)=== TRUE){
    
}
else{
    echo "Error: ".$conn ->error;	
};

$exam_subjects= "CREATE TABLE IF NOT EXISTS exam_subjects (
    exam_subject_id INT AUTO_INCREMENT PRIMARY KEY,
    exam_id INT NOT NULL,
    course_id INT NOT NULL,
    max_marks DECIMAL(5,2) NOT NULL,
    passing_marks DECIMAL(5,2) NOT NULL,
    FOREIGN KEY (exam_id) REFERENCES exams(exam_id),
    FOREIGN KEY (course_id) REFERENCES courses(id),
    UNIQUE KEY (exam_id, course_id)
)";

if($conn->query($exam_subjects)=== TRUE){
    
}
else{
    echo "Error: ".$conn ->error;	
};

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


if($conn->query($student_grades)=== TRUE){
    
}
else{
    echo "Error: ".$conn ->error;	
};


$grade_scales = "CREATE TABLE IF NOT EXISTS grade_scale (
    scale_id INT AUTO_INCREMENT PRIMARY KEY,
    min_percentage DECIMAL(5,2) NOT NULL,
    max_percentage DECIMAL(5,2) NOT NULL,
    letter_grade VARCHAR(2) NOT NULL,
    grade_points DECIMAL(3,2) NOT NULL,
    description VARCHAR(100),
    UNIQUE KEY (min_percentage, max_percentage)
)";
if($conn->query($grade_scales)=== TRUE){
    
} else {
    echo "Error: ".$conn->error;
}

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

if($conn->query($student_performance)=== TRUE){
    
}
else{
    echo "Error: ".$conn ->error;	
};

$exam_types = "CREATE TABLE IF NOT EXISTS exam_types (
    exam_type_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    max_marks DECIMAL(5,2) NOT NULL,
    description TEXT
)";


if($conn->query($exam_types)=== TRUE){
    
}
else{
    echo "Error: ".$conn ->error;	
};

$exam_type= "INSERT INTO exam_types (name, max_marks, description) VALUES 
('Midterm', 30, 'Midterm examination'),
('Final', 50, 'Final examination'),
('Attendance', 5, 'Class attendance marks'),
('Quiz', 5, 'Quiz performance marks'),
('Presentation', 5, 'Presentation marks'),
('Assignment', 5, 'Assignment marks')";


if($conn->query($exam_type)=== TRUE){
    
}
else{
    echo "Error: ".$conn ->error;	
};

// Add new columns to exams table if they don't exist (for existing databases)
// Check if columns exist first
$column_check = $conn->query("SHOW COLUMNS FROM exams LIKE 'duration_minutes'");
if ($column_check->num_rows == 0) {
    $conn->query("ALTER TABLE exams ADD COLUMN duration_minutes INT DEFAULT 60 AFTER description");
}
$column_check = $conn->query("SHOW COLUMNS FROM exams LIKE 'is_active'");
if ($column_check->num_rows == 0) {
    $conn->query("ALTER TABLE exams ADD COLUMN is_active TINYINT DEFAULT 1 AFTER duration_minutes");
}
?>
