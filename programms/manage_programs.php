<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
    header('Location: /student_management_system/auth/login.php');
    exit();
}

include '../config/connection.php';

if (isset($_POST['add_program'])) {
    $program_name = $_POST['program_name'];

    $stmt = $conn->prepare("INSERT INTO programs (program_name) VALUES (?)");
    $stmt->bind_param("s", $program_name);
    $stmt->execute();

    header("Location: manage_programs.php");
    exit();
}

$programs = $conn->query("SELECT * FROM programs");


// Delete Program
if (isset($_GET['delete_programs'])) {
    $id = intval($_GET['delete_programs']);

    try {
        mysqli_begin_transaction($conn);

        // Delete related courses in courses table FIRST
        $deleteCoursesStmt = $conn->prepare("DELETE FROM courses WHERE program_id = ?");
        $deleteCoursesStmt->bind_param("i", $id);
        $deleteCoursesStmt->execute();

        $stmt = $conn->prepare("DELETE FROM programs WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $reorderQuery = "SET @count = 0; UPDATE programs SET id = @count := @count + 1; ALTER TABLE programs AUTO_INCREMENT = 1;";

            if (mysqli_multi_query($conn, $reorderQuery)) {
                do {
                    if ($result = mysqli_store_result($conn)) {
                        mysqli_free_result($result);
                    }
                } while (mysqli_more_results($conn) && mysqli_next_result($conn));

                mysqli_commit($conn);
                header("Location: manage_programs.php");
                exit();
            } else {
                throw new Exception("Failed to reorder IDs: " . mysqli_error($conn));
            }
        } else {
            throw new Exception("Failed to delete program: " . mysqli_error($conn));
        }
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "Error: " . $e->getMessage();
    } finally {
        // Remove $stmt->close() and $conn->close() from here
        // The connection will be closed at the end of the script
    }
}

$programs = $conn->query("SELECT * FROM programs");

// ... (your HTML and other code) ...

// Close the connection at the end of the script
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Programs</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="header_style.css" rel="stylesheet">
</head>
<body class="bg-gray-100">

<div class="header">
    <h1>Student Management System</h1>
    <div class="nav-links">
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="../index.php">Dashboard</a>
            <a href="../read.php">All Students</a>
            <a href="../create.php">Add New Student</a>
            <a href="manage_courses.php">Add New Course</a>
            <a href="../auth/logout.php">Logout (<?php echo isset($_SESSION['username']) ? $_SESSION['username'] : 'User'; ?>)</a>
        <?php else: ?>
            <a href="../auth/login.php">Login</a>
            <a href="../auth/register.php">Register</a>
        <?php endif; ?>
    </div>
</div>

<div class="container mx-auto p-8">
    <div class="bg-white shadow-md rounded p-6 mb-6">
        <h2 class="text-2xl font-bold mb-4">Add New Program</h2>
        <form method="POST" action="">
            <div class="mb-4">
                <input type="text" name="program_name" placeholder="Program Name" required class="w-full p-2 border border-gray-300 rounded">
            </div>

            <button type="submit" name="add_program" class="bg-blue-500 text-white px-4 py-2 rounded">Add Program</button>
        </form>
    </div>

    <div class="bg-white shadow-md rounded p-6">
        <h3 class="text-2xl font-bold mb-4">All Programs</h3>
        <table class="w-full text-left border-collapse">
            <thead>
                <tr>
                    <th class="border-b p-3">ID</th>
                    <th class="border-b p-3">Program Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $programs->fetch_assoc()): ?>
                    <tr>
                        <td class="border-b p-3"><?= $row['id'] ?></td>
                        <td class="border-b p-3"><?= $row['program_name'] ?></td>
                        <td>
                            <a href="edit_programs.php?id=<?= $row['id'] ?>" class="bg-green-500 text-white px-2 py-1 rounded">Edit</a>
                            <a href="manage_programs.php?delete_programs=<?= $row['id'] ?>" class="bg-red-500 text-white px-2 py-1 rounded">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>
