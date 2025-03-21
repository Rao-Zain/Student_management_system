<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}
?>
<?php
include 'config/connection.php';
include "includes/header.php";

$sql = "SELECT * FROM students";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Students</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5" style="overflow-x: auto;">
    <h2 class="text-center mb-4">All Students</h2>
    <a href="create.php" class="btn btn-primary mb-3">Add New Student</a>
    <a href="index.php" class="btn btn-success mb-3">List All Student</a>
    <table class="table table-bordered table-striped">
        <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Father's Name</th>
            <th>Roll No</th>
            <th>Qualification</th>
            <th>Marks</th>
            <th>Programme</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Address</th>
            <th>Gender</th>
            <th>Course</th>
            <th>Result Card</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>" . $row['id'] . "</td>
                <td>" . $row['name'] . "</td>
                <td>" . $row['father_name'] . "</td>
                <td>" . $row['roll_no'] . "</td>
                <td>" . $row['last_qualification'] . "</td>
                <td>" . $row['marks'] . "</td>
                <td>" . $row['programme'] . "</td>
                <td>" . $row['email'] . "</td>
                <td>" . $row['phone'] . "</td>
                <td>" . $row['address'] . "</td>
                <td>" . $row['gender'] . "</td>
                <td>" . $row['course'] . "</td>
                <td>";
                if (!empty($row['result_card']) && file_exists($row['result_card'])) {
                    echo "<img src='" . $row['result_card'] . "' alt='Result Card' width='80' height='80'>";
                } else {
                    echo "<img src='uploads/no_image.jpeg' alt='No Image' width='80' height='80'>";
                }
                echo "</td>
                <td>
                    <a class='btn btn-success btn-sm' href='update.php?id=" . $row['id'] . "'>Edit</a>
                    <a class='btn btn-danger btn-sm' href='delete.php?id=" . $row['id'] . "'>Delete</a>
                </td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='14' class='text-center'>No Records Found</td></tr>";
}
?>

        </tbody>
    </table>
</div>
</body>
</html>
