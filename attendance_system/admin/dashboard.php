<?php
session_start();
include('../includes/db.php');

if(!isset($_SESSION['admin_id'])){
    header('Location: ../index.php');
    exit();
}

$admin_name = $_SESSION['admin_name'];

// Get counts
$total_students = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM students"))['total'];
$total_teachers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM teachers"))['total'];
$total_sessions = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM sessions"))['total'];
$total_attendance = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM attendance"))['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body { background: #f0f2f5; }

        .navbar {
            background: #6a1b9a;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
        }

        .navbar h2 { font-size: 20px; }

        .navbar a {
            color: white;
            text-decoration: none;
            background: rgba(255,255,255,0.2);
            padding: 8px 15px;
            border-radius: 6px;
            font-size: 14px;
            margin-left: 10px;
        }

        .container { padding: 30px; }

        .welcome {
            background: white;
            padding: 20px 25px;
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            border-left: 5px solid #6a1b9a;
        }

        .welcome h3 { color: #6a1b9a; font-size: 22px; }
        .welcome p { color: #666; margin-top: 5px; }

        .stats {
            display: flex;
            gap: 20px;
            margin-bottom: 25px;
        }

        .stat-card {
            flex: 1;
            background: white;
            padding: 25px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            border-top: 4px solid #6a1b9a;
        }

        .stat-card h2 { font-size: 36px; color: #6a1b9a; }
        .stat-card p { color: #666; margin-top: 5px; font-size: 14px; }

        .actions {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .action-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            text-decoration: none;
            color: #333;
            transition: transform 0.2s;
        }

        .action-card:hover { transform: translateY(-3px); }
        .action-card .icon { font-size: 40px; margin-bottom: 10px; }
        .action-card h3 { color: #6a1b9a; margin-bottom: 8px; }
        .action-card p { color: #666; font-size: 13px; }
    </style>
</head>
<body>

<div class="navbar">
    <h2>🔐 Admin Panel — Attendance System</h2>
    <div>
        <span>👤 <?php echo $admin_name; ?></span>
        <a href="../logout.php">Logout</a>
    </div>
</div>

<div class="container">

    <div class="welcome">
        <h3>Welcome, <?php echo $admin_name; ?>! 🔐</h3>
        <p>Manage teachers, students, and monitor attendance from here.</p>
    </div>

    <div class="stats">
        <div class="stat-card">
            <h2><?php echo $total_students; ?></h2>
            <p>Total Students</p>
        </div>
        <div class="stat-card">
            <h2><?php echo $total_teachers; ?></h2>
            <p>Total Teachers</p>
        </div>
        <div class="stat-card">
            <h2><?php echo $total_sessions; ?></h2>
            <p>Total Sessions</p>
        </div>
        <div class="stat-card">
            <h2><?php echo $total_attendance; ?></h2>
            <p>Total Attendance Records</p>
        </div>
    </div>

    <div class="actions">
        <a href="teachers.php" class="action-card">
            <div class="icon">👨‍🏫</div>
            <h3>Manage Teachers</h3>
            <p>View, add, or remove teachers</p>
        </a>
        <a href="add_teacher.php" class="action-card">
            <div class="icon">➕</div>
            <h3>Add Teacher</h3>
            <p>Register a new teacher account</p>
        </a>
        <a href="students.php" class="action-card">
            <div class="icon">🎓</div>
            <h3>Manage Students</h3>
            <p>View, add, or remove students</p>
        </a>
        <a href="add_student.php" class="action-card">
            <div class="icon">➕</div>
            <h3>Add Student</h3>
            <p>Register a new student account</p>
        </a>
        <a href="reports.php" class="action-card">
            <div class="icon">📊</div>
            <h3>Attendance Reports</h3>
            <p>View complete attendance data</p>
        </a>
        <a href="../logout.php" class="action-card">
            <div class="icon">🚪</div>
            <h3>Logout</h3>
            <p>Sign out from admin panel</p>
        </a>
    </div>

</div>

</body>
</html>