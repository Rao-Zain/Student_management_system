<?php
session_start();
include '../config/connection.php';

if (isset($_POST['email'])) {
    $email = trim($_POST['email']);
    
    // Check if the user exists
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) { // Checking if the statement is prepared correctly
        die("SQL Error: " . $conn->error);
    }
    
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $token = bin2hex(random_bytes(32)); // Generating a unique token
        
        // Save the token in the database
        $sql = "UPDATE users SET reset_token = ? WHERE email = ?";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) { // Checking if the statement is prepared correctly
            die("SQL Error: " . $conn->error);
        }
        
        $stmt->bind_param("ss", $token, $email);
        $stmt->execute();

        // Create a reset link
        $resetLink = "http://localhost/student_management_system/auth/reset_password.php?token=" . $token;

        // Show the link for testing (in real case, send via email)
        echo "Password reset link: <a href='" . $resetLink . "'>Click here to reset your password</a>";
    } else {
        echo "No user found with that email.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <title>Forgot Password</title>
    <style>
        body {
            background: #1f293a;
            color: #fff;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        form {
            background: #2c4766;
            padding: 30px;
            border-radius: 10px;
            width: 300px;
        }
        input {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: none;
            border-radius: 5px;
        }
        button {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            background-color: #0ef;
            color: #1f293a;
            font-weight: bold;
        }
    </style>
</head>
<body>

<form action="" method="POST">
    <h2>Forgot Password</h2>
    <?php if (isset($error)) echo "<p style='color: red;'>$error</p>"; ?>
    <?php if (isset($_SESSION['success'])): ?>
        <p style='color: green;'><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
    <?php endif; ?>
    <input type="email" name="email" placeholder="Enter your email" required>
    <button type="submit">Send Reset Link</button>
</form>

    <?php include '../includes/footer.php'; ?>
</body>
</html>

