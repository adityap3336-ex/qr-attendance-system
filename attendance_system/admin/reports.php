<?php
session_start();
include('../includes/db.php');

if(!isset($_SESSION['admin_id'])){
    header('Location: ../index.php');
    exit();
}

$admin_name = $_SESSION['admin_name'];

// Get overall stats
$total_students = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM students"))['total'];
$total_teachers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM teachers"))['total'];
$total_sessions = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM sessions"))['total'];
$total_attendance = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM attendance"))['total'];

// Get all students with attendance percentage
$students = mysqli_query($conn,
"SELECT s.name, s.roll_number, s.email,
COUNT(a.id) as present,
(SELECT COUNT(*) FROM sessions) as total_sessions
FROM students s
LEFT JOIN attendance a ON s.id = a.student_id
GROUP BY s.id
ORDER BY s.name ASC");

// Get all sessions with attendance count
$sessions = mysqli_query($conn,
"SELECT s.*, t.name as teacher_name,
COUNT(a.id) as total_present
FROM sessions s
JOIN teachers t ON s.teacher_id = t.id
LEFT JOIN attendance a ON s.id = a.session_id
GROUP BY s.id
ORDER BY s.created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Reports</title>
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

        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            margin-bottom: 25px;
        }

        .card h3 {
            color: #6a1b9a;
            margin-bottom: 20px;
            font-size: 18px;
        }

        table { width: 100%; border-collapse: collapse; }

        table th {
            background: #6a1b9a;
            color: white;
            padding: 12px 15px;
            text-align: left;
            font-size: 14px;
        }

        table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
            color: #333;
        }

        table tr:hover { background: #f9f9f9; }

        .percentage {
            font-weight: 700;
            font-size: 14px;
            padding: 4px 10px;
            border-radius: 20px;
        }

        .pct-high { background: #e8f5e9; color: #2e7d32; }
        .pct-mid { background: #fff8e1; color: #f57f17; }
        .pct-low { background: #ffebee; color: #c62828; }

        .badge-active {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-expired {
            background: #ffebee;
            color: #c62828;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
    </style>
</head>
<body>

<div class="navbar">
    <h2>🔐 Admin Panel — Attendance System</h2>
    <div>
        <span>👤 <?php echo $admin_name; ?></span>
        <a href="dashboard.php">Dashboard</a>
        <a href="../logout.php">Logout</a>
    </div>
</div>

<div class="container">

    <!-- Summary Stats -->
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

    <!-- Student Attendance Report -->
    <div class="card">
        <h3>🎓 Student Attendance Report</h3>
        <table>
            <tr>
                <th>#</th>
                <th>Student Name</th>
                <th>Roll Number</th>
                <th>Email</th>
                <th>Classes Present</th>
                <th>Total Sessions</th>
                <th>Percentage</th>
            </tr>
            <?php $i=1; while($row = mysqli_fetch_assoc($students)): ?>
            <?php
                $pct = $row['total_sessions'] > 0 
                    ? round(($row['present'] / $row['total_sessions']) * 100) 
                    : 0;
                $pct_class = $pct >= 75 ? 'pct-high' : ($pct >= 50 ? 'pct-mid' : 'pct-low');
            ?>
            <tr>
                <td><?php echo $i++; ?></td>
                <td>🎓 <?php echo $row['name']; ?></td>
                <td><?php echo $row['roll_number']; ?></td>
                <td><?php echo $row['email']; ?></td>
                <td><?php echo $row['present']; ?></td>
                <td><?php echo $row['total_sessions']; ?></td>
                <td>
                    <span class="percentage <?php echo $pct_class; ?>">
                        <?php echo $pct; ?>%
                    </span>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <!-- Sessions Report -->
    <div class="card">
        <h3>📊 All Sessions Report</h3>
        <table>
            <tr>
                <th>#</th>
                <th>Subject</th>
                <th>Teacher</th>
                <th>Created At</th>
                <th>Expires At</th>
                <th>Status</th>
                <th>Students Present</th>
            </tr>
            <?php $i=1; while($row = mysqli_fetch_assoc($sessions)): ?>
            <tr>
                <td><?php echo $i++; ?></td>
                <td><?php echo $row['subject']; ?></td>
                <td>👨‍🏫 <?php echo $row['teacher_name']; ?></td>
                <td><?php echo date('d M Y h:i A', strtotime($row['created_at'])); ?></td>
                <td><?php echo date('d M Y h:i A', strtotime($row['expires_at'])); ?></td>
                <td>
                    <?php if(strtotime($row['expires_at']) > time()): ?>
                        <span class="badge-active">🟢 Active</span>
                    <?php else: ?>
                        <span class="badge-expired">🔴 Expired</span>
                    <?php endif; ?>
                </td>
                <td><strong><?php echo $row['total_present']; ?></strong> students</td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

</div>

</body>
</html>