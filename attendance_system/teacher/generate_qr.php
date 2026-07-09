<?php
session_start();
include('../includes/db.php');

if(!isset($_SESSION['teacher_id'])){
    header('Location: ../index.php');
    exit();
}

$teacher_id = $_SESSION['teacher_id'];
$teacher_name = $_SESSION['teacher_name'];
$qr_generated = false;
$qr_url = '';
$session_id = '';
$scan_url = '';

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $duration = $_POST['duration'];
    
    $token = uniqid($teacher_id, true);
    $expires_at = date('Y-m-d H:i:s', strtotime("+$duration minutes"));
    
    $query = "INSERT INTO sessions (teacher_id, subject, qr_code, expires_at) 
              VALUES ('$teacher_id', '$subject', '$token', '$expires_at')";
    
    if(mysqli_query($conn, $query)){
        $session_id = mysqli_insert_id($conn);
        $scan_url = "https://attendancemanager.great-site.net/student/scan.php?token=$token";
        $qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($scan_url);
        
        $_SESSION['last_qr'] = [
            'qr_url' => $qr_url,
            'session_id' => $session_id,
            'scan_url' => $scan_url,
            'subject' => $subject,
            'duration' => $duration,
            'expires_at' => $expires_at
        ];
        
        $qr_generated = true;
    }

} else {
    if(isset($_SESSION['last_qr'])){
        $last = $_SESSION['last_qr'];
        if(strtotime($last['expires_at']) > time()){
            $qr_url = $last['qr_url'];
            $session_id = $last['session_id'];
            $scan_url = $last['scan_url'];
            $qr_generated = true;
        } else {
            unset($_SESSION['last_qr']);
        }
    }
}

$recent = mysqli_query($conn, 
"SELECT * FROM sessions WHERE teacher_id=$teacher_id 
ORDER BY created_at DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate QR Code</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
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

        .card h3 { color: #1a73e8; margin-bottom: 20px; font-size: 18px; }

        .form-group { margin-bottom: 20px; }

        label { display: block; margin-bottom: 6px; font-weight: 500; color: #333; }

        input, select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            outline: none;
        }

        input:focus, select:focus { border-color: #1a73e8; }

        .btn {
            background: #1a73e8;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            font-weight: 600;
        }

        .btn:hover { background: #0d47a1; }

        .qr-box { text-align: center; padding: 20px; }
        .qr-box img { border: 3px solid #1a73e8; border-radius: 10px; padding: 10px; margin: 15px 0; }
        .qr-box h3 { color: #1a73e8; margin-bottom: 10px; }
        .qr-box p { color: #666; font-size: 14px; margin-bottom: 5px; }

        .token-box {
            background: #f0f2f5;
            padding: 10px 15px;
            border-radius: 6px;
            font-family: monospace;
            font-size: 13px;
            color: #333;
            margin: 10px 0;
            word-break: break-all;
        }

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

        .layout { display: flex; gap: 25px; }
        .layout .left { flex: 1; }
        .layout .right { width: 350px; }

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
    <h2>📋 Attendance System</h2>
    <div>
        <span>👨‍🏫 <?php echo $teacher_name; ?></span>
        <a href="dashboard.php">Dashboard</a>
        <a href="../logout.php">Logout</a>
    </div>
</div>

<div class="container">
    <div class="layout">

        <div class="left">
            <div class="card">
                <h3>📱 Generate New QR Code</h3>
                <form method="POST">
                    <div class="form-group">
                        <label>Subject Name</label>
                        <input type="text" name="subject" placeholder="e.g. Computer Networks" required>
                    </div>
                    <div class="form-group">
                        <label>QR Code Valid For</label>
                        <select name="duration">
                            <option value="5">5 minutes</option>
                            <option value="10" selected>10 minutes</option>
                            <option value="15">15 minutes</option>
                            <option value="30">30 minutes</option>
                            <option value="60">1 hour</option>
                        </select>
                    </div>
                    <button type="submit" class="btn">🔲 Generate QR Code</button>
                </form>
            </div>

            <div class="card">
                <h3>🕐 Recent Sessions</h3>
                <table>
                    <tr>
                        <th>Subject</th>
                        <th>Created At</th>
                        <th>Status</th>
                    </tr>
                    <?php while($row = mysqli_fetch_assoc($recent)): ?>
                    <tr>
                        <td><?php echo $row['subject']; ?></td>
                        <td><?php echo date('d M Y h:i A', strtotime($row['created_at'])); ?></td>
                        <td>
                            <?php if(strtotime($row['expires_at']) > time()): ?>
                                <span class="badge-active">🟢 Active</span>
                            <?php else: ?>
                                <span class="badge-expired">🔴 Expired</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </table>
            </div>
        </div>

        <div class="right">
            <?php if($qr_generated): ?>
            <div class="card">
                <div class="qr-box">
                    <h3>✅ QR Code Ready!</h3>
                    <p>Ask students to scan this code</p>
                    <img src="<?php echo $qr_url; ?>" width="250" height="250" alt="QR Code">
                    <p><strong>Session ID:</strong> #<?php echo $session_id; ?></p>
                    <div class="token-box"><?php echo $scan_url; ?></div>
                </div>
            </div>
            <?php else: ?>
            <div class="card">
                <div class="qr-box">
                    <p style="font-size:60px;">🔲</p>
                    <h3>No QR Code Yet</h3>
                    <p>Fill the form and click Generate to create a QR code for your class</p>
                </div>
            </div>
            <?php endif; ?>
        </div>

    </div>
</div>

</body>
</html>