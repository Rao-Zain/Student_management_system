<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}
?>
<?php
include 'config/connection.php';
include "includes/header.php";

$total_students = $conn->query("SELECT COUNT(*) as count FROM students")->fetch_assoc()['count'];
$male_students = $conn->query("SELECT COUNT(*) as count FROM students WHERE gender = 'Male'")->fetch_assoc()['count'];
$female_students = $conn->query("SELECT COUNT(*) as count FROM students WHERE gender = 'Female'")->fetch_assoc()['count'];

$course_data = $conn->query("SELECT sc.course_id, c.course_name, COUNT(*) as count 
                                FROM student_courses sc
                                JOIN courses c ON sc.course_id = c.id
                                GROUP BY sc.course_id");

$courses = [];
$course_counts = [];
while ($row = $course_data->fetch_assoc()) {
    $courses[] = $row['course_name'];
    $course_counts[] = (int)$row['count'];
}

$conn->close();
?>

<!-- Add Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    body {
        background-color: #f3f4f6;
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }
    .main-content {
        flex: 1;
    }
    .card-hover {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border-radius: 1rem;
    }
    .card-hover:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
    .icon-box {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 1rem;
    }
    .welcome-hero {
        background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
        border-radius: 1rem;
        color: white;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }
</style>

<div class="main-content container mx-auto px-4 py-8">
    
    <!-- Welcome Banner -->
    <div class="welcome-hero flex flex-col md:flex-row items-center justify-between p-8 mb-8">
        <div>
            <h1 class="text-4xl font-extrabold mb-2 text-white drop-shadow-md">Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?>! ✨</h1>
            <p class="text-blue-100 opacity-95 text-lg">Your complete overview of the Student Management System.</p>
        </div>
        <div class="mt-4 md:mt-0 hidden md:block opacity-90">
            <i class="fas fa-school text-7xl text-white"></i>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800 border-b-2 border-blue-200 inline-block pb-1">System Overview</h2>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <!-- Total Students -->
        <div class="bg-white rounded-2xl shadow-lg p-6 flex items-center card-hover border-l-4 border-blue-500">
            <div class="rounded-full bg-blue-100 p-4 mr-4">
                <i class="fas fa-users text-3xl text-blue-600"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-semibold uppercase tracking-wider">Total Students</p>
                <p class="text-4xl font-bold text-gray-800"><?php echo $total_students; ?></p>
            </div>
        </div>
        <!-- Male Students -->
        <div class="bg-white rounded-2xl shadow-lg p-6 flex items-center card-hover border-l-4 border-indigo-500">
            <div class="rounded-full bg-indigo-100 p-4 mr-4">
                <i class="fas fa-male text-3xl text-indigo-600 px-1"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-semibold uppercase tracking-wider">Male Students</p>
                <p class="text-4xl font-bold text-gray-800"><?php echo $male_students; ?></p>
            </div>
        </div>
        <!-- Female Students -->
        <div class="bg-white rounded-2xl shadow-lg p-6 flex items-center card-hover border-l-4 border-pink-500">
            <div class="rounded-full bg-pink-100 p-4 mr-4">
                <i class="fas fa-female text-3xl text-pink-600 px-1"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-semibold uppercase tracking-wider">Female Students</p>
                <p class="text-4xl font-bold text-gray-800"><?php echo $female_students; ?></p>
            </div>
        </div>
    </div>

    <!-- Admin Actions -->
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800 border-b-2 border-purple-200 inline-block pb-1">Administrative Actions</h2>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        
        <?php if (isset($_SESSION['user_id']) && strtolower($_SESSION['user_role']) === 'admin'): ?>
            <!-- Manage Teachers -->
            <div class="bg-white card-hover shadow-lg p-6 flex flex-col items-center text-center border-t-4 border-purple-500">
                <div class="icon-box bg-purple-50 text-purple-600">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800 mb-2">Teachers Directory</h3>
                <p class="text-sm text-gray-500 mb-4 flex-grow">Manage and view all registered teachers.</p>
                <a href="teachers/view_teacher.php" class="w-full bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition font-medium">Manage Teachers</a>
            </div>

            <!-- Add Teacher -->
            <div class="bg-white card-hover shadow-lg p-6 flex flex-col items-center text-center border-t-4 border-indigo-500">
                <div class="icon-box bg-indigo-50 text-indigo-600">
                    <i class="fas fa-user-plus"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800 mb-2">Add Teacher</h3>
                <p class="text-sm text-gray-500 mb-4 flex-grow">Register a new teacher into the system.</p>
                <a href="teachers/add_teacher.php" class="w-full bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition font-medium">Add New</a>
            </div>

            <!-- Manage Programs -->
            <div class="bg-white card-hover shadow-lg p-6 flex flex-col items-center text-center border-t-4 border-blue-500">
                <div class="icon-box bg-blue-50 text-blue-600">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800 mb-2">Academic Programs</h3>
                <p class="text-sm text-gray-500 mb-4 flex-grow">Configure degrees and study programs.</p>
                <a href="programms/manage_programs.php" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition font-medium">Manage Programs</a>
            </div>

            <!-- Manage Courses -->
            <div class="bg-pink-50 card-hover shadow-md p-6 flex flex-col items-center text-center border-t-4 border-pink-500">
                <div class="icon-box bg-white shadow-sm text-pink-600">
                    <i class="fas fa-book-open"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800 mb-2">Courses Setup</h3>
                <p class="text-sm text-gray-600 mb-4 flex-grow">Manage subjects and course details.</p>
                <a href="programms/manage_courses.php" class="w-full bg-pink-500 text-white px-4 py-2 rounded-lg hover:bg-pink-600 transition font-medium shadow-md hover:shadow-lg">Manage Courses</a>
            </div>

            <!-- Manage Users -->
            <div class="bg-red-50 card-hover shadow-md p-6 flex flex-col items-center text-center border-t-4 border-red-500">
                <div class="icon-box bg-white shadow-sm text-red-600">
                    <i class="fas fa-users-cog"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800 mb-2">User Roles</h3>
                <p class="text-sm text-gray-600 mb-4 flex-grow">Control system access and user permissions.</p>
                <a href="manage_users.php" class="w-full bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition font-medium shadow-md hover:shadow-lg">Manage Users</a>
            </div>
            <!-- Assign Subjects -->
            <div class="bg-yellow-50 card-hover shadow-md p-6 flex flex-col items-center text-center border-t-4 border-yellow-500">
                <div class="icon-box bg-white shadow-sm text-yellow-600">
                    <i class="fas fa-tasks"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800 mb-2">Assign Subjects</h3>
                <p class="text-sm text-gray-600 mb-4 flex-grow">Allocate courses to specific teachers.</p>
                <a href="teachers/assign_subjects.php" class="w-full bg-yellow-500 text-white px-4 py-2 rounded-lg hover:bg-yellow-600 transition font-medium shadow-md hover:shadow-lg">Assign Now</a>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['user_id']) && (strtolower($_SESSION['user_role']) === 'admin' || strtolower($_SESSION['user_role']) === 'teacher')): ?>
            <!-- Attendance Logs -->
            <div class="bg-white card-hover shadow-lg p-6 flex flex-col items-center text-center border-t-4 border-green-500">
                <div class="icon-box bg-green-50 text-green-600">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800 mb-2">Global Attendance</h3>
                <p class="text-sm text-gray-500 mb-4 flex-grow">View system-wide attendance logs.</p>
                <a href="attendance/view.php" class="w-full bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition font-medium">View Logs</a>
            </div>
            
            <!-- Teacher's View -->
            <div class="bg-white card-hover shadow-lg p-6 flex flex-col items-center text-center border-t-4 border-gray-700">
                <div class="icon-box bg-gray-100 text-gray-700">
                    <i class="fas fa-chalkboard"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800 mb-2">Teacher's View</h3>
                <p class="text-sm text-gray-500 mb-4 flex-grow">Switch to the teacher perspective dashboard.</p>
                <a href="teachers/teacher_dashboard.php" class="w-full bg-gray-700 text-white px-4 py-2 rounded-lg hover:bg-gray-800 transition font-medium">Enter Dashboard</a>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['user_id']) && strtolower($_SESSION['user_role']) === 'student'): ?>
            <!-- View Results -->
            <div class="bg-blue-50 card-hover shadow-md p-6 flex flex-col items-center text-center border-t-4 border-blue-500">
                <div class="icon-box bg-white shadow-sm text-blue-600">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800 mb-2">My Results</h3>
                <p class="text-sm text-gray-600 mb-4 flex-grow">View your academic performance and grades.</p>
                <a href="exams/results.php" class="w-full bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition font-medium shadow-md hover:shadow-lg">View Results</a>
            </div>
        <?php endif; ?>

    </div>

    <!-- Chart Section -->
    <div class="bg-white shadow-xl rounded-2xl p-8 mb-6 border border-gray-100">
        <div class="flex items-center mb-6">
            <i class="fas fa-chart-bar text-2xl text-blue-500 mr-3"></i>
            <h2 class="text-2xl font-bold text-gray-800">Enrollment Analytics</h2>
        </div>
        <div class="w-full" style="max-height: 400px;">
            <canvas id="courseChart"></canvas>
        </div>
    </div>
</div>

<script>
    const ctx = document.getElementById('courseChart').getContext('2d');
    
    // Create a gradient for the bars
    let gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(59, 130, 246, 0.8)'); // blue-500
    gradient.addColorStop(1, 'rgba(139, 92, 246, 0.8)'); // purple-500

    const courseChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($courses); ?>,
            datasets: [{
                label: 'Enrolled Students',
                data: <?php echo json_encode($course_counts); ?>,
                backgroundColor: gradient,
                borderColor: 'transparent',
                borderRadius: 8,
                borderWidth: 0,
                barPercentage: 0.6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false // Hide legend for cleaner look
                },
                tooltip: {
                    backgroundColor: 'rgba(17, 24, 39, 0.9)',
                    titleFont: { size: 14, family: "'Segoe UI', sans-serif" },
                    bodyFont: { size: 14, family: "'Segoe UI', sans-serif" },
                    padding: 12,
                    cornerRadius: 8,
                    displayColors: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(243, 244, 246, 1)',
                        drawBorder: false,
                    },
                    ticks: {
                        font: { family: "'Segoe UI', sans-serif", size: 12 },
                        color: '#6B7280',
                        stepSize: 1
                    }
                },
                x: {
                    grid: {
                        display: false,
                        drawBorder: false,
                    },
                    ticks: {
                        font: { family: "'Segoe UI', sans-serif", size: 12 },
                        color: '#4B5563'
                    }
                }
            }
        }
    });
</script>

<?php include 'includes/footer.php'; ?>
</body>
</html>