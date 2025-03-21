<?php
error_reporting(0); // Turn off all error reporting
ini_set('display_errors', 0); // Don't display errors on the page
?>

<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

include '../config/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = $_POST['subject'];
    $date = $_POST['date'];

    $sql = "SELECT * FROM attendance WHERE subject = '$subject' AND date = '$date'";
    $attendance = $conn->query($sql);

    if (!$attendance) {
        die("Query Failed: " . $conn->error);  // This will show the exact error
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Attendance</title>
    <link rel="stylesheet" href="attendance_style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
</head>
<body>

<div class="header">
        <h1>Student Management System</h1>
        <div class="nav-links">
        <?php if (isset($_SESSION['user_id'])): ?>
    <a href="../index.php">Dashboard</a>
    <a href="../read.php">All Students</a>
    <a href="../create.php">Add New Student</a>
    <a href="attendance.php">Mark Attendance</a>
    <a href="../auth/logout.php">Logout (<?php echo isset($_SESSION['username']) ? $_SESSION['username'] : 'User'; ?>)</a>
<?php else: ?>
    <a href="../auth/login.php">Login</a>
    <a href="../auth/register.php">Register</a>
<?php endif; ?>

        </div>
    </div>

    <div class="container">
        <h2>View Attendance</h2>
    <form method="POST">
        <label>Subject:</label>
        <input type="text" name="subject" required><br><br>

        <label>Date:</label>
        <input type="date" name="date" required><br><br>

        <button type="submit">View Attendance</button>
        <h2>
</h2>
    </form>

    <?php if (isset($attendance)) { ?>
    <table border="1">
        <tr>
            <th>Student ID</th>
            <th>Subject</th>
            <th>Date</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $attendance->fetch_assoc()) { ?>
        <tr>
            <td><?php echo $row['student_id']; ?></td>
            <td><?php echo $row['subject']; ?></td>
            <td><?php echo $row['date']; ?></td>
            <td><?php echo $row['status']; ?></td>
            <td>        <a href="attendance_report.php?student_id=<?php echo $row['student_id']; ?>" class="btn btn-primary">View Report</a>
            </td>
        </tr>
        <?php } ?>
    </table>
    <?php } ?>
    </div>
</body>
</html>
