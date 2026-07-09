<?php
session_start();
include('../includes/db.php');

if(!isset($_SESSION['teacher_id'])){
    header('Location: ../index.php');
    exit();
}

$teacher_id = $_SESSION['teacher_id'];
$teacher_name = $_SESSION['teacher_name'];

// Get all sessions by this teacher with attendance count
$sessions = mysqli_query($conn, 
"SELECT s.*, COUNT(a.id) as total_present 
FROM sessions s 
LEFT JOIN attendance a ON s.id = a.session_id 
WHERE s.teacher_id = $teacher_id 
GROUP BY s.id 
ORDER BY s.created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Attendance</title>
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

        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            margin-bottom: 25px;
        }

        .card h3 {
            color: #1a73e8;
            margin-bottom: 20px;
            font-size: 18px;
        }

        table { width: 100%; border-collapse: collapse; }

        table th {
            background: #1a73e8;
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

        .badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-green {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .badge-red {
            background: #ffebee;
            color: #c62828;
        }

        .view-btn {
            background: #1a73e8;
            color: white;
            padding: 6px 14px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
        }

        .view-btn:hover { background: #0d47a1; }

        .no-data {
            text-align: center;
            color: #999;
            padding: 30px;
            font-size: 15px;
        }

        /* Detail table */
        .detail-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .detail-card h3 {
            color: #1a73e8;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="navbar">
    <h2>📋 Attendance System</h2>
    <div>
        <span>👨‍🏫 <?php echo $teacher_name; ?></span>
        <a href="dashboard.php">Dashboard</a>
        <a href="../logout.php">Logout</a>
    </div>
</div>

<div class="container">

    <div class="card">
        <h3>📊 All Sessions & Attendance</h3>

        <?php if(mysqli_num_rows($sessions) > 0): ?>
        <table>
            <tr>
                <th>#</th>
                <th>Subject</th>
                <th>Created At</th>
                <th>Expires At</th>
                <th>Status</th>
                <th>Students Present</th>
                <th>Action</th>
            </tr>
            <?php $i=1; while($row = mysqli_fetch_assoc($sessions)): ?>
            <tr>
                <td><?php echo $i++; ?></td>
                <td><?php echo $row['subject']; ?></td>
                <td><?php echo date('d M Y h:i A', strtotime($row['created_at'])); ?></td>
                <td><?php echo date('d M Y h:i A', strtotime($row['expires_at'])); ?></td>
                <td>
                    <?php if(strtotime($row['expires_at']) > time()): ?>
                        <span class="badge badge-green">🟢 Active</span>
                    <?php else: ?>
                        <span class="badge badge-red">🔴 Expired</span>
                    <?php endif; ?>
                </td>
                <td style="text-align:center;">
                    <strong><?php echo $row['total_present']; ?></strong> students
                </td>
                <td>
                    <a href="view_attendance.php?session_id=<?php echo $row['id']; ?>" class="view-btn">
                        👁 View
                    </a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
        <?php else: ?>
        <p class="no-data">No sessions found. Generate a QR code to start taking attendance!</p>
        <?php endif; ?>
    </div>

    <?php
    // If a specific session is selected, show students who attended
    if(isset($_GET['session_id'])){
        $session_id = intval($_GET['session_id']);
        
        // Get session info
        $session_info = mysqli_fetch_assoc(mysqli_query($conn, 
        "SELECT * FROM sessions WHERE id=$session_id AND teacher_id=$teacher_id"));
        
        if($session_info){
            // Get students who attended this session
            $attendees = mysqli_query($conn,
            "SELECT st.name, st.roll_number, st.email, a.marked_at 
            FROM attendance a 
            JOIN students st ON a.student_id = st.id 
            WHERE a.session_id = $session_id 
            ORDER BY a.marked_at ASC");
            ?>

            <div class="detail-card">
                <h3>👥 Students Present — <?php echo $session_info['subject']; ?> 
                (<?php echo date('d M Y', strtotime($session_info['created_at'])); ?>)</h3>

                <?php if(mysqli_num_rows($attendees) > 0): ?>
                <table>
                    <tr>
                        <th>#</th>
                        <th>Student Name</th>
                        <th>Roll Number</th>
                        <th>Email</th>
                        <th>Marked At</th>
                    </tr>
                    <?php $j=1; while($att = mysqli_fetch_assoc($attendees)): ?>
                    <tr>
                        <td><?php echo $j++; ?></td>
                        <td><?php echo $att['name']; ?></td>
                        <td><?php echo $att['roll_number']; ?></td>
                        <td><?php echo $att['email']; ?></td>
                        <td><?php echo date('h:i A', strtotime($att['marked_at'])); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </table>
                <?php else: ?>
                <p class="no-data">No students have marked attendance for this session yet.</p>
                <?php endif; ?>
            </div>

        <?php } 
    } ?>

</div>

</body>
</html>