<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

include 'config/connection.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Fetch student data
        $stmt = $conn->prepare("SELECT result_card FROM students WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row) {
            // Delete the result card file if it exists
            if (!empty($row['result_card']) && file_exists($row['result_card'])) {
                unlink($row['result_card']);
            }
            
            // Delete related attendance records first
            $stmt = $conn->prepare("DELETE FROM attendance WHERE student_id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            
            // Delete student record
            $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $conn->commit();
                $_SESSION['success'] = "Student deleted successfully.";
            } else {
                throw new Exception("Error deleting student.");
            }
        } else {
            throw new Exception("Student not found.");
        }
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = $e->getMessage();
    }
} else {
    $_SESSION['error'] = "Invalid request.";
}
  // Reorder IDs to remove gaps
  $reorderQuery = "SET @count = 0; 
  UPDATE students SET id = @count := @count + 1;
  ALTER TABLE students AUTO_INCREMENT = 1;";

if (!mysqli_multi_query($conn, $reorderQuery)) {
throw new Exception("Failed to reorder IDs: " . mysqli_error($conn));
}
header("Location: read.php");
exit();
?>