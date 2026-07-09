<?php
session_start();
include('../includes/db.php');

// Check if teacher is logged in
if(!isset($_SESSION['teacher_id'])){
    header('Location: ../index.php');
    exit();
}

$teacher_name = $_SESSION['teacher_name'];
$teacher_id = $_SESSION['teacher_id'];

// Get total students count
$total_students = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM students"))['total'];

// Get total sessions by this teacher
$total_sessions = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM sessions WHERE teacher_id=$teacher_id"))['total'];

// Get today's sessions
$today_sessions = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM sessions WHERE teacher_id=$teacher_id AND DATE(created_at)=CURDATE()"))['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
        }

        body {
            background: #f0f2f5;
        }

        .navbar {
            background: #1a73e8;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
        }

        .navbar h2 {
            font-size: 20px;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            background: rgba(255,255,255,0.2);
            padding: 8px 15px;
            border-radius: 6px;
            font-size: 14px;
        }

        .navbar a:hover {
            background: rgba(255,255,255,0.3);
        }

        .container {
            padding: 30px;
        }

        .welcome {
            background: white;
            padding: 20px 25px;
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .welcome h3 {
            color: #1a73e8;
            font-size: 22px;
        }

        .welcome p {
            color: #666;
            margin-top: 5px;
        }

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
            border-top: 4px solid #1a73e8;
        }

        .stat-card h2 {
            font-size: 36px;
            color: #1a73e8;
        }

        .stat-card p {
            color: #666;
            margin-top: 5px;
            font-size: 14px;
        }

        .actions {
            display: flex;
            gap: 20px;
        }

        .action-card {
            flex: 1;
            background: white;
            padding: 25px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            text-decoration: none;
            color: #333;
            transition: transform 0.2s;
        }

        .action-card:hover {
            transform: translateY(-3px);
        }

        .action-card .icon {
            font-size: 40px;
            margin-bottom: 10px;
        }

        .action-card h3 {
            color: #1a73e8;
            margin-bottom: 8px;
        }

        .action-card p {
            color: #666;
            font-size: 13px;
        }
    </style>
</head>
<body>

<div class="navbar">
    <h2>📋 Attendance System</h2>
    <div style="display:flex; gap:10px; align-items:center;">
        <span>👨‍🏫 <?php echo $teacher_name; ?></span>
        <a href="../logout.php">Logout</a>
    </div>
</div>

<div class="container">

    <div class="welcome">
        <h3>Welcome back, <?php echo $teacher_name; ?>! 👋</h3>
        <p>Manage your classes and track student attendance from here.</p>
    </div>

    <div class="stats">
        <div class="stat-card">
            <h2><?php echo $total_students; ?></h2>
            <p>Total Students</p>
        </div>
        <div class="stat-card">
            <h2><?php echo $total_sessions; ?></h2>
            <p>Total Sessions</p>
        </div>
        <div class="stat-card">
            <h2><?php echo $today_sessions; ?></h2>
            <p>Today's Sessions</p>
        </div>
    </div>

    <div class="actions">
        <a href="generate_qr.php" class="action-card">
            <div class="icon">📱</div>
            <h3>Generate QR Code</h3>
            <p>Create a new QR code for today's class attendance</p>
        </a>
        <a href="view_attendance.php" class="action-card">
            <div class="icon">📊</div>
            <h3>View Attendance</h3>
            <p>Check attendance records for all your sessions</p>
        </a>
        <a href="students.php" class="action-card">
            <div class="icon">🎓</div>
            <h3>View Students</h3>
            <p>See list of all registered students</p>
        </a>
        <a href="attendance_register.php" class="action-card">
    <div class="icon">📋</div>
    <h3>Attendance Register</h3>
    <p>View full attendance register with present/absent for all students</p>
</a>
    </div>

</div>

</body>
</html>