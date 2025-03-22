<?php
require_once '../config/connection.php';

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}
include 'header.php';
// Fetch all teachers along with their assigned subjects and subject IDs
$query = "
    SELECT t.id, t.name AS teacher_name, c.course_name, c.id as course_id
    FROM teachers t
    LEFT JOIN teacher_subjects ts ON t.id = ts.teacher_id
    LEFT JOIN courses c ON ts.course_id = c.id
    UNION
    SELECT u.id, u.username AS teacher_name, c.course_name, c.id as course_id
    FROM users u
    LEFT JOIN teacher_subjects ts ON u.id = ts.teacher_id
    LEFT JOIN courses c ON ts.course_id = c.id
    WHERE u.role = 'Teacher';
";

$result = $conn->query($query);
$teachers = [];

while ($row = $result->fetch_assoc()) {
    $teacherId = $row['id'];
    $courseName = $row['course_name'] ?? 'None';
    $courseId = $row['course_id'];

    if (!isset($teachers[$teacherId])) {
        $teachers[$teacherId]['name'] = $row['teacher_name'];
        $teachers[$teacherId]['subjects'] = [];
    }

    // Check if the subject already exists for this teacher
    $subjectExists = false;
    foreach ($teachers[$teacherId]['subjects'] as $subject) {
        if ($subject['id'] == $courseId) {
            $subjectExists = true;
            break;
        }
    }

    // Add the subject only if it doesn't already exist
    if (!$subjectExists) {
        $teachers[$teacherId]['subjects'][] = [
            'name' => $courseName,
            'id' => $courseId,
        ];
    }
}

// Handle subject removal
if (isset($_GET['remove_teacher_id']) && isset($_GET['remove_course_id'])) {
    $removeTeacherId = $_GET['remove_teacher_id'];
    $removeCourseId = $_GET['remove_course_id'];

    $removeQuery = "DELETE FROM teacher_subjects WHERE teacher_id = ? AND course_id = ?";
    $stmt = $conn->prepare($removeQuery);
    $stmt->bind_param("ii", $removeTeacherId, $removeCourseId);

    if ($stmt->execute()) {
        // Removal successful, redirect to refresh the page
        header("Location: view_teacher.php");
        exit();
    } else {
        echo "Error removing subject: " . $stmt->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View All Teachers</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
</head>
<body>
    <div class="container mx-auto mt-5 p-5 bg-white rounded shadow-lg">
        <h1 class="text-2xl font-bold mb-5">All Teachers & Their Assigned Subjects</h1>

        <table class="table-auto w-full border-collapse border border-gray-300">
            <thead>
                <tr>
                    <th class="border border-gray-300 p-2">Teacher Name</th>
                    <th class="border border-gray-300 p-2">Assigned Subjects</th>
                    <th class="border border-gray-300 p-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($teachers as $teacherId => $teacher): ?>
                    <tr>
                        <td class="border border-gray-300 p-2"><?php echo $teacher['name']; ?></td>
                        <td class="border border-gray-300 p-2">
                            <?php foreach ($teacher['subjects'] as $subject): ?>
                                <div class="mb-2">
                                    <span><?php echo $subject['name']; ?></span>
                                </div>
                            <?php endforeach; ?>
                        </td>
                        <td class="border border-gray-300 p-2">
                            <?php foreach ($teacher['subjects'] as $subject): ?>
                                <div class="mb-2">
                                    <a href="?remove_teacher_id=<?php echo $teacherId; ?>&remove_course_id=<?php echo $subject['id']; ?>" class="bg-red-500 text-white py-1 px-2 rounded text-xs">Remove</a>
                                </div>
                            <?php endforeach; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="mt-4">
            <a href="assign_subjects.php" class="bg-yellow-500 text-white py-2 px-4 rounded">Assign a New Subject</a>
        </div>
        <div class="mt-4">
            <a href="teacher_dashboard.php" class="bg-blue-500 text-white py-2 px-4 rounded">Go To Dashboard</a>
        </div>
    </div>
</body>
</html>