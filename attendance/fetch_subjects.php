<?php
include '../config/connection.php';

if (isset($_GET['program_id'])) {
    $programId = $_GET['program_id'];

    // Get both course ID and name
    $sql = "SELECT c.id, c.course_name FROM courses c WHERE c.program_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $programId);
    $stmt->execute();
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