<?php
session_start();
include('../includes/db.php');

if(!isset($_SESSION['student_id'])){
    header('Location: ../index.php');
    exit();
}

$student_id = $_SESSION['student_id'];
$student_name = $_SESSION['student_name'];

// Get total attendance count
$total = mysqli_fetch_assoc(mysqli_query($conn, 
"SELECT COUNT(*) as total FROM attendance WHERE student_id=$student_id"))['total'];

// Get recent attendance
$recent = mysqli_query($conn, 
"SELECT a.marked_at, s.subject, s.created_at 
FROM attendance a 
JOIN sessions s ON a.session_id = s.id 
WHERE a.student_id=$student_id 
ORDER BY a.marked_at DESC LIMIT 10");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
        }

        body { background: #f0f2f5; }

        .navbar {
            background: #1a73e8;
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
        }

        .welcome h3 { color: #1a73e8; font-size: 22px; }
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
            border-top: 4px solid #1a73e8;
        }

        .stat-card h2 { font-size: 36px; color: #1a73e8; }
        .stat-card p { color: #666; margin-top: 5px; font-size: 14px; }

        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            margin-bottom: 25px;
        }

        .card h3 { color: #1a73e8; margin-bottom: 20px; }

        table { width: 100%; border-collapse: collapse; }

        table th {
            background: #1a73e8;
            color: white;
            padding: 12px;
            text-align: left;
            font-size: 14px;
        }

        table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
            color: #333;
        }

        table tr:hover { background: #f9f9f9; }

        .scan-btn {
            display: inline-block;
            background: #1a73e8;
            color: white;
            padding: 15px 30px;
            border-radius: 10px;
            text-decoration: none;
            font-size: 18px;
            font-weight: 600;
            margin-top: 10px;
        }

        .scan-btn:hover { background: #0d47a1; }

        .no-data { color: #999; text-align: center; padding: 20px; }
    </style>
</head>
<body>

<div class="navbar">
    <h2>📋 Attendance System</h2>
    <div>
        <span>🎓 <?php echo $student_name; ?></span>
        <a href="../logout.php">Logout</a>
    </div>
</div>

<div class="container">

    <div class="welcome">
        <h3>Welcome, <?php echo $student_name; ?>! 👋</h3>
        <p>Scan your teacher's QR code to mark your attendance.</p>
    </div>

    <div class="stats">
        <div class="stat-card">
            <h2><?php echo $total; ?></h2>
            <p>Classes Attended</p>
        </div>
    </div>

    <div class="card" style="text-align:center;">
        <h3>📱 Mark Your Attendance</h3>
        <p style="color:#666; margin-bottom:10px;">Ask your teacher for the QR code and scan it with your phone camera, or enter the session link directly.</p>
    </div>

    <div class="card">
        <h3>📊 My Attendance History</h3>
        <?php if(mysqli_num_rows($recent) > 0): ?>
        <table>
            <tr>
                <th>Subject</th>
                <th>Date</th>
                <th>Marked At</th>
            </tr>
            <?php while($row = mysqli_fetch_assoc($recent)): ?>
            <tr>
                <td><?php echo $row['subject']; ?></td>
                <td><?php echo date('d M Y', strtotime($row['marked_at'])); ?></td>
                <td><?php echo date('h:i A', strtotime($row['marked_at'])); ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
        <?php else: ?>
        <p class="no-data">No attendance records yet. Scan a QR code to mark attendance!</p>
        <?php endif; ?>
    </div>

</div>

</body>
</html>