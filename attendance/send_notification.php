<?php
require '../config/connection.php';
// Add to send_notification.php
$logMessage = date('Y-m-d H:i:s') . " - Started notification process\n";
file_put_contents(__DIR__.'/notification_logs.log', $logMessage, FILE_APPEND);

// After each notification send
$logMessage = date('Y-m-d H:i:s') . " - Sent to {$parent['email']}\n";
file_put_contents(__DIR__.'/notification_logs.log', $logMessage, FILE_APPEND);
// Function to check consecutive absences
function checkConsecutiveAbsences($conn) {
    // Get all students with 3+ consecutive absences
    $query = "SELECT a.student_id, s.name as student_name, 
              GROUP_CONCAT(a.date ORDER BY a.date SEPARATOR ', ') as absent_dates,
              p.name as parent_name, p.email, p.phone
              FROM attendance a
              JOIN students s ON a.student_id = s.id
              JOIN parents p ON s.id = p.student_id
              WHERE a.status = 'Absent'
              GROUP BY a.student_id
              HAVING COUNT(*) >= 3
              AND DATEDIFF(MAX(a.date), MIN(a.date)) = 2"; // 3 consecutive days
    
    $result = $conn->query($query);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Function to send email
function sendEmailNotification($to, $studentName, $absentDates) {
    $subject = "Attendance Alert: $studentName has been absent for 3 consecutive days";
    
    $message = "
    <html>
    <head>
        <title>Attendance Alert</title>
    </head>
    <body>
        <h2>Attendance Alert</h2>
        <p>Dear Parent,</p>
        <p>Your child <strong>$studentName</strong> has been absent for 3 consecutive days on:</p>
        <ul>
            <li>" . str_replace(", ", "</li><li>", $absentDates) . "</li>
        </ul>
        <p>Please contact the school if there are any concerns.</p>
        <p>Best regards,<br>School Administration</p>
        <?php include '../includes/footer.php'; ?>
</body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: attendance@yourschool.edu" . "\r\n";
    
    return mail($to, $subject, $message, $headers);
}

// Function to send SMS (using Twilio API example)
function sendSMSNotification($phone, $studentName, $absentDates) {
    $account_sid = 'YOUR_TWILIO_SID';
    $auth_token = 'YOUR_TWILIO_TOKEN';
    $twilio_number = 'YOUR_TWILIO_PHONE';
    
    $client = new Twilio\Rest\Client($account_sid, $auth_token);
    
    $message = "ATTN: $studentName has been absent for 3 consecutive days ($absentDates). Please contact the school.";
    
    try {
        $client->messages->create(
            $phone,
            [
                'from' => $twilio_number,
                'body' => $message
            ]
        );
        return true;
    } catch (Exception $e) {
        error_log("SMS failed: " . $e->getMessage());
        return false;
    }
}

// Main execution
$absentStudents = checkConsecutiveAbsences($conn);

foreach ($absentStudents as $student) {
    // Send email
    sendEmailNotification($student['email'], $student['student_name'], $student['absent_dates']);
    
    // Send SMS
    sendSMSNotification($student['phone'], $student['student_name'], $student['absent_dates']);
    
    // Mark as notified to avoid duplicate alerts
    $conn->query("UPDATE attendance SET notified = 1 WHERE student_id = {$student['student_id']}");
}

echo "Notifications sent successfully!";
?>
