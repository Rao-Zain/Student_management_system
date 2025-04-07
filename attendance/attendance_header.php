<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .dark-mode {
            background-color: #1a202c;
            color: #e2e8f0;
        }

        .header {
            background: linear-gradient(135deg, #4a90e2, #63a4ff);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        .dark-mode .header {
            background: linear-gradient(135deg, #2d3748, #4a5568);
        }

        .header h1 {
            color: white;
            font-size: 1.8rem;
            margin: 0;
        }

        .nav-links {
            display: flex;
            align-items: center;
        }

        /* Hide menu button on large screens */
        .menu-toggle {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
        }

        /* Mobile Navigation */
        @media (max-width: 768px) {
            .menu-toggle {
                display: block;
            }

            .nav-links {
                display: none;
                flex-direction: column;
                position: absolute;
                top: 4.5rem;
                left: 0;
                width: 100%;
                background: linear-gradient(135deg, #4a90e2, #63a4ff);
                border-top: 1px solid rgba(255, 255, 255, 0.2);
                padding: 1rem 0;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
                z-index: 1000;
                visibility: hidden;
        opacity: 0;
        transform: translateY(-10px);
        transition: opacity 0.3s ease, transform 0.3s ease, visibility 0.3s;
            }

            .nav-links.show {
                visibility: visible;
                opacity: 1;
                transform: translateY(0);
            }
        }

        .dark-mode .nav-links {
            background: linear-gradient(135deg, #2d3748, #4a5568);
        }

        .toggle-button {
            margin-top: 0.5rem;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            display: flex;
            align-items: center;
            transition: background-color 0.3s ease;
        }
        .nav-links a:hover {
            background-color: rgba(255, 255, 255, 0.14);
            text-decoration: none;
            color: white;
        }
        .student{
            font-size: 1.5rem;
            color: white;
            text-decoration: none;
        }
        .student:hover{
            color:rgb(228, 230, 235);
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="header">
    <a href="index.php" class="student"> Student Management System</a>
        <button class="menu-toggle" id="menuToggle"><i class="fas fa-bars"></i></button>
        <div class="nav-links" id="navLinks">
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if (isset($_SESSION['user_role'])): ?>
                    <?php if ($_SESSION['user_role'] === 'admin'): ?>
                        
                        <a href="../index.php"><i class="fas fa-tachometer-alt py-2 "></i> Dashboard</a>
                        
                    <?php elseif ($_SESSION['user_role'] === 'Teacher'): ?>
                        <a href="teachers/teacher_dashboard.php"><i class="fas fa-chalkboard-teacher"></i> Teacher Dashboard</a>
                    <?php endif; ?>
                    <a href="../read.php"><i class="fas fa-list-ul "></i> All Students</a>
                    <a href="../create.php"><i class="fas fa-plus-circle "></i> Add Student</a>
                    <a href="attendance.php"><i class="fa fa-user-check "></i> Mark Attendance</a>
                    <a href="view.php"><i class="fas fa-clock "></i> View Attendance</a>
                    <a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout (<?php echo $_SESSION['username']; ?>)</a>
                <?php else: ?>
                    <a href="../auth/login.php"><i class="fas fa-sign-in-alt "></i> Login</a>
                    <a href="../auth/register.php"><i class="fas fa-user-plus "></i> Register</a>
                <?php endif; ?>
            <?php else: ?>
                <a href="../auth/login.php"><i class="fas fa-sign-in-alt "></i> Login</a>
                <a href="../auth/register.php"><i class="fas fa-user-plus "></i> Register</a>
            <?php endif; ?>
            <!-- <button id="darkModeToggle" class="toggle-button">Toggle Dark Mode</button> -->
        </div>
    </div>

    <script>
        const darkModeToggle = document.getElementById('darkModeToggle');
        const body = document.body;
        const menuToggle = document.getElementById('menuToggle');
        const navLinks = document.getElementById('navLinks');

        // Dark Mode Toggle
        darkModeToggle.addEventListener('click', () => {
            body.classList.toggle('dark-mode');
            if (body.classList.contains('dark-mode')) {
                localStorage.setItem('darkMode', 'enabled');
            } else {
                localStorage.setItem('darkMode', 'disabled');
            }
        });

        if (localStorage.getItem('darkMode') === 'enabled') {
            body.classList.add('dark-mode');
        }

        const menuToggle = document.getElementById('menuToggle');
const navLinks = document.getElementById('navLinks');

menuToggle.addEventListener('click', (event) => {
    event.stopPropagation(); // Prevent closing when clicking on the button
    navLinks.classList.toggle('show');
});

// Hide menu when clicking outside
document.addEventListener('click', (event) => {
    if (!navLinks.contains(event.target) && event.target !== menuToggle) {
        navLinks.classList.remove('show');
    }
});

    </script>
</body>
</html>
