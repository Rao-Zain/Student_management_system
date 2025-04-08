<?php
error_reporting(0);
ini_set('display_errors', 0);

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

include '../config/connection.php';
include 'attendance_header.php';
$programs = $conn->query("SELECT id, program_name FROM programs");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $program_id = $_POST['program'];
    $subject_id = $_POST['subject']; // Changed variable name for clarity
    $dateFilter = $_POST['date_filter'];
    $nameFilter = $_POST['name_filter'];
    $rollFilter = $_POST['roll_filter'];

    $sql = "SELECT a.*, s.name, s.roll_no
            FROM attendance a
            JOIN students s ON a.student_id = s.id
            JOIN student_courses sc ON s.id = sc.student_id
            JOIN courses c ON sc.course_id = c.id
            WHERE c.program_id = '$program_id'
            AND c.id = '$subject_id'
            AND a.subject = (SELECT course_name FROM courses WHERE id = '$subject_id')"; // Ensure subject matches the selected course

    if (!empty($dateFilter)) {
        $sql .= " AND a.date = '$dateFilter'";
    }

    if (!empty($nameFilter)) {
        $sql .= " AND s.name LIKE '%$nameFilter%'";
    }

    if (!empty($rollFilter)) {
        $sql .= " AND s.roll_no LIKE '%$rollFilter%'";
    }

    $attendance = $conn->query($sql);

    if (!$attendance) {
        die("Query Failed: " . $conn->error);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Attendance</title>
    <link rel="stylesheet" href="attendance_style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <style>
        /* General Styles */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f9f9f9;
        }

        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
        }
        h2 {
            margin-bottom: 20px;
            font-size: 1.8rem;
            color: #333;
        }
        form {
            margin-bottom: 30px;
        }
        label {
            font-weight: bold;
            margin-right: 10px;
            display: inline-block;
            width: 150px;
        }
        .filter-input {
            width: 200px;
            padding: 8px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            transition: border-color 0.3s ease;
        }
        .filter-input:focus {
            border-color: #6a11cb;
            outline: none;
        }
        select.filter-input {
            appearance: none;
            -webkit-appearance: none;
            background: url('data:image/svg+xml;charset=UTF-8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"><path fill="%236a11cb" d="M7.25 11.438L3.75 7.938l1.438-1.438L8 9.562l2.813-2.813 1.438 1.438z"/></svg>') no-repeat right 10px center;
            background-size: 12px;
        }
        button[type="submit"] {
            background-color: #6a11cb;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button[type="submit"]:hover {
            background-color:rgb(75, 13, 168);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table th, table td {
            padding: 12px;
            text-align: left;
            border: 1px solid #dee2e6;
        }
        table th {
            background-color: #5a0ecb;
            font-size: 1rem;
            font-weight: bold;
        }
        table td {
            font-size: 0.9rem;
        }
        .btnn-primary {
            background-color: #5a0ecb;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            color: white;
            text-decoration: none;
        }
        .btnn-primary:hover {
            background-color:rgb(57, 7, 133);
            color: #ffff
        }
    </style>
    <script>
        function fetchSubjects(programId) {
            if (!programId) return;

            fetch('fetch_subjects.php?program_id=' + programId)
                .then(response => response.json())
                .then(data => {
                    const subjectSelect = document.getElementById('subject-select');
                    subjectSelect.innerHTML = '<option value="">Select Subject</option>';

                    if (data.error) {
                        alert(data.error);
                        return;
                    }

                    data.forEach(subject => {
                        const option = document.createElement('option');
                        option.value = subject.id; // Store course ID as value
                        option.textContent = subject.course_name;
                        subjectSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error("Error fetching subjects:", error);
                    alert("Failed to load subjects.");
                });
        }
    </script>
</head>
<body>

<div class="container">
    <h2>View Attendance</h2>
    <form method="POST">
        <div class="row">
            <div class="col-md-6">
                <div style="margin-bottom: 15px;">
                    <label>Program:</label>
                    <select name="program" class="filter-input" onchange="fetchSubjects(this.value)">
                        <option value="">Select Program</option>
                        <?php while ($row = $programs->fetch_assoc()) { ?>
                            <option value="<?php echo $row['id']; ?>"><?php echo $row['program_name']; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div style="margin-bottom: 15px;">
                    <label>Subject:</label>
                    <select class="filter-input" name="subject" id="subject-select" required>
                        <option value="">Select Subject</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div style="margin-bottom: 15px;">
                    <label>Date Filter:</label>
                    <input type="date" name="date_filter" class="filter-input">
                </div>
                <div style="margin-bottom: 15px;">
                    <label>Name Filter:</label>
                    <input type="text" name="name_filter" placeholder="Filter by Name" class="filter-input">
                </div>
                <div style="margin-bottom: 15px;">
                    <label>Roll Number Filter:</label>
                    <input type="text" name="roll_filter" placeholder="Filter by Roll Number" class="filter-input">
                </div>
            </div>
        </div>
        <button type="submit">View Attendance</button>
    </form>

    <?php if (isset($attendance) && $attendance->num_rows > 0) { ?>
        <table border="1">
            <tr>
                <th>Student ID</th>
                <th>Roll Number</th>
                <th>Student Name</th>
                <th>Subject</th>
                <th>Date</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            <?php while ($row = $attendance->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $row['student_id']; ?></td>
                    <td><?php echo $row['roll_no']; ?></td>
                    <td><?php echo $row['name']; ?></td>
                    <td><?php echo $row['subject']; ?></td>
                    <td><?php echo $row['date']; ?></td>
                    <td><?php echo $row['status']; ?></td>
                    <td>
                        <a href="attendance_report.php?student_id=<?php echo $row['student_id']; ?>&subject_id=<?php echo $subject_id; ?>" class="btn btnn-primary">View Report</a>
                    </td>
                </tr>
            <?php } ?>
        </table>
    <?php } elseif (isset($attendance) && $attendance->num_rows === 0) { ?>
        <p>No attendance records found for the selected criteria.</p>
    <?php } ?>
</div>

</body>
</html>