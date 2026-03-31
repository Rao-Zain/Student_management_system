<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../config/connection.php';

// Test connection
if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

if (isset($_GET['program_id'])) {
    $programId = $_GET['program_id'];

    // Get both course ID and name
    $sql = "SELECT c.id, c.course_name FROM courses c WHERE c.program_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param("i", $programId);
    if (!$stmt->execute()) {
        echo json_encode(['error' => 'Execute failed: ' . $stmt->error]);
        exit;
    }
    $result = $stmt->get_result();

    $subjects = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $subjects[] = [
                'id' => $row['id'],
                'course_name' => $row['course_name']
            ];
        }
        echo json_encode($subjects);
    } else {
        echo json_encode(['error' => 'No subjects found for this program.']);
    }
} else {
    echo json_encode(['error' => 'Program ID not provided.']);
}
?>