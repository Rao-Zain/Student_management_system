<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

include 'config/connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Extract form data
    $name = $_POST['name'];
    $f_name = $_POST['f_name'];
    $marks = $_POST['marks'];
    $roll_no = $_POST['roll_no'];
    $password = $_POST['password'];
    $last_qualification = $_POST['last_qualification'];
    $email = $_POST['email'];
    $phone_no = $_POST['phone_no'];
    $gender = $_POST['gender'];
    $address = $_POST['address'];
    
    // Selected programs and courses
    $selectedPrograms = isset($_POST['programs']) ? $_POST['programs'] : [];
    $selectedCourses = isset($_POST['courses']) ? $_POST['courses'] : [];
    
    // Process file upload
    $result_card = $_FILES['result_card']['name'];
    $target_dir = "uploads/";
    
    // Create uploads directory if it doesn't exist
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    // Generate unique filename to prevent overwriting
    $filename = time() . '_' . basename($_FILES["result_card"]["name"]);
    $target_file = $target_dir . $filename;
    
    if (move_uploaded_file($_FILES["result_card"]["tmp_name"], $target_file)) {
        // Insert student record
        $sql = "INSERT INTO students (name, father_name, marks, roll_no, password, last_qualification, email, phone, gender, address, result_card) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssissssssss", $name, $f_name, $marks, $roll_no, $password, $last_qualification, $email, $phone_no, $gender, $address, $filename);
        
        if ($stmt->execute()) {
            $student_id = $conn->insert_id;
            
            // Insert student program relationships (if needed)
            foreach ($selectedPrograms as $programId) {
                // If you have a student_programs table, you can use it here
                // Otherwise, this information is implicit through course enrollment
                
                // Optional: If you have a student_programs table
                // $sql = "INSERT INTO student_programs (student_id, program_id) VALUES (?, ?)";
                // $stmt = $conn->prepare($sql);
                // $stmt->bind_param("ii", $student_id, $programId);
                // $stmt->execute();
            }
            
            // Insert student course relationships
            if (!empty($selectedCourses)) {
                $success = true;
                
                foreach ($selectedCourses as $courseId) {
                    // Get program ID for this course to ensure consistency
                    $courseQuery = "SELECT program_id FROM courses WHERE id = ?";
                    $stmt = $conn->prepare($courseQuery);
                    $stmt->bind_param("i", $courseId);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $courseInfo = $result->fetch_assoc();
                    
                    if ($courseInfo) {
                        // Insert into student_courses table
                        $enrollQuery = "INSERT INTO student_courses (student_id, course_id) VALUES (?, ?)";
                        $stmt = $conn->prepare($enrollQuery);
                        $stmt->bind_param("ii", $student_id, $courseId);
                        
                        if (!$stmt->execute()) {
                            $success = false;
                            error_log("Failed to enroll student $student_id in course $courseId: " . $conn->error);
                        }
                    }
                }
                
                if ($success) {
                    $_SESSION['success_message'] = "Student added successfully with all course enrollments.";
                } else {
                    $_SESSION['error_message'] = "Student added but some course enrollments failed.";
                }
            } else {
                $_SESSION['warning_message'] = "Student added but no courses were selected.";
            }
            
            header("Location: read.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Error adding student: " . $conn->error;
            header("Location: create.php");
            exit();
        }
    } else {
        $_SESSION['error_message'] = "Error uploading file.";
        header("Location: create.php");
        exit();
    }
    
    $conn->close();
}
?>