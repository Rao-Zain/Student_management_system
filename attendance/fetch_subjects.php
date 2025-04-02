<?php
include '../config/connection.php';

if (isset($_GET['program_id'])) {
    $program_id = $_GET['program_id'];

    // Query to fetch subjects for the given program_id
    $query = "SELECT course_name FROM courses WHERE program_id = ?";
    $stmt = $conn->prepare($query);

    if ($stmt) {
        $stmt->bind_param("i", $program_id); // Bind the program_id as an integer
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $subjectArray = [];
            while ($row = $result->fetch_assoc()) {
                $subjectArray[] = $row;
            }
            echo json_encode($subjectArray); // Return JSON response
        } else {
            echo json_encode(['error' => 'No subjects found for this program']);
        }

        $stmt->close();
    } else {
        echo json_encode(['error' => 'Query preparation failed: ' . $conn->error]);
    }
} else {
    echo json_encode(['error' => 'program_id not set']);
}
?>