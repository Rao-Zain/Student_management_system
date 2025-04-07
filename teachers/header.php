<!-- Header -->
<div class="header">
    <h1>Teacher Dashboard</h1>
    <button class="menu-toggle" id="menuToggle"><i class="fas fa-bars"></i></button>
    <div class="nav-links" id="navLinks">
        <a href="../index.php"><i class="fas fa-cogs"></i> Admin Panel</a>
        <a href="teacher_dashboard.php"><i class="fas fa-chalkboard-teacher"></i> Teacher Dashboard</a>
        <a href="../read.php"><i class="fas fa-list-ul"></i> List Student</a>
        <a href="../programms/manage_programs.php"><i class="fas fa-tasks"></i> Manage Program</a>
        <a href="../attendance/view.php"><i class="fas fa-calendar-check"></i> View Attendance Reports</a>
        <a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout (<?php echo $_SESSION['username']; ?>)</a>
        <button id="darkModeToggle" class="toggle-button">Toggle Dark Mode</button>
    </div>
</div>

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

    .nav-links a {
        color: white;
        text-decoration: none;
        margin-right: 15px;
        transition: color 0.3s ease;
    }

    .nav-links a:hover {
        color: #d1d5db;
    }

    .menu-toggle {
        display: none;
        background: none;
        border: none;
        color: white;
        font-size: 1.5rem;
        cursor: pointer;
    }

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
            opacity: 0;
            transform: translateY(-10px);
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        .nav-links.show {
            display: flex;
            opacity: 1;
            transform: translateY(0);
        }
    }

    .dark-mode .nav-links {
        background: linear-gradient(135deg, #2d3748, #4a5568);
    }
</style>

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

    // Mobile Menu Toggle
    menuToggle.addEventListener('click', (event) => {
        event.stopPropagation();
        navLinks.classList.toggle('show');
    });

    // Hide menu when clicking outside
    document.addEventListener('click', (event) => {
        if (!navLinks.contains(event.target) && event.target !== menuToggle) {
            navLinks.classList.remove('show');
        }
    });
</script>
