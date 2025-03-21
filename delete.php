<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}
?>
<?php
include 'config/connection.php'; // Your database connection file
include "includes/header.php";

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    try {
        // Start transaction
        mysqli_begin_transaction($conn);
        
        // Get the image filename before deletion
        $query = "SELECT result_card FROM students WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . mysqli_error($conn));
        }
        
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            $imageFile = $row['result_card'];
            
            // Delete the record from database
            $deleteQuery = "DELETE FROM students WHERE id = ?";
            $deleteStmt = mysqli_prepare($conn, $deleteQuery);
            
            if (!$deleteStmt) {
                throw new Exception("Prepare delete failed: " . mysqli_error($conn));
            }
            
            mysqli_stmt_bind_param($deleteStmt, "i", $id);
            $deleteSuccess = mysqli_stmt_execute($deleteStmt);
            
            if ($deleteSuccess) {
                // Delete the image file if it exists
                if (!empty($imageFile)) {
                    $filePath = "uploads/" . $imageFile;
                    if (file_exists($filePath)) {
                        if (!unlink($filePath)) {
                            throw new Exception("Failed to delete image file");
                        }
                    }
                }

                // Reorder IDs to remove gaps
                $reorderQuery = "SET @count = 0; 
                                UPDATE students SET id = @count := @count + 1;
                                ALTER TABLE students AUTO_INCREMENT = 1;";
                
                if (!mysqli_multi_query($conn, $reorderQuery)) {
                    throw new Exception("Failed to reorder IDs: " . mysqli_error($conn));
                }

                // Commit transaction
                mysqli_commit($conn);
                
                // Redirect on success
                header("Location: read.php");
                exit();
            } else {
                throw new Exception("Delete failed: " . mysqli_error($conn));
            }
        } else {
            throw new Exception("Record not found");
        }
        
    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($conn);
        echo "Error: " . $e->getMessage();
    } finally {
        // Clean up
        if (isset($stmt)) {
            mysqli_stmt_close($stmt);
        }
        if (isset($deleteStmt)) {
            mysqli_stmt_close($deleteStmt);
        }
        mysqli_close($conn);
    }
} else {
    echo "Invalid Request!";
}
?>
