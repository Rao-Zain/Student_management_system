<?php
require_once '../config/connection.php';

$query = "SELECT id, username, email, role FROM users WHERE role = 'Teacher'";
$result = $conn->query($query);

echo "<h2>Teachers in users table:</h2>";
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "ID: " . $row['id'] . " - Name: " . $row['username'] . " - Email: " . $row['email'] . " - Role: " . $row['role'] . "<br>";
    }
} else {
    echo "No teachers found in users table.";
}

// Also check if teachers table still exists
$result2 = $conn->query("SHOW TABLES LIKE 'teachers'");
if ($result2 && $result2->num_rows > 0) {
    echo "<h2>Teachers table still exists:</h2>";
    $query2 = "SELECT id, name, email FROM teachers";
    $result3 = $conn->query($query2);
    while ($row = $result3->fetch_assoc()) {
        echo "ID: " . $row['id'] . " - Name: " . $row['name'] . " - Email: " . $row['email'] . "<br>";
    }
} else {
    echo "<h2>Teachers table has been dropped.</h2>";
}

$conn->close();
?>