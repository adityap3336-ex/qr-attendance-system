<?php
session_start();
include('../includes/db.php');

// Check if student is logged in
if(!isset($_SESSION['student_id'])){
    // Save the scan URL so we can redirect back after login
    $_SESSION['redirect_after_login'] = "student/scan.php?token=" . $_GET['token'];
    header('Location: ../index.php');
    exit();
}

$student_id = $_SESSION['student_id'];
$student_name = $_SESSION['student_name'];
$message = '';
$status = '';

if(isset($_GET['token'])){
    $token = mysqli_real_escape_string($conn, $_GET['token']);
    
    // Find the session with this token
    $query = "SELECT * FROM sessions WHERE qr_code='$token'";
    $result = mysqli_query($conn, $query);
    $session = mysqli_fetch_assoc($result);
    
    if($session){
        // Check if QR code is expired
        if(strtotime($session['expires_at']) < time()){
            $message = "❌ This QR Code has expired! Ask your teacher to generate a new one.";
            $status = "error";
        } else {
            // Check if student already marked attendance for this session
            $check = "SELECT * FROM attendance WHERE student_id=$student_id AND session_id=" . $session['id'];
            $already = mysqli_fetch_assoc(mysqli_query($conn, $check));
            
            if($already){
                $message = "⚠️ You have already marked attendance for this session!";
                $status = "warning";
            } else {
                // Mark attendance
                $mark = "INSERT INTO attendance (student_id, session_id) VALUES ($student_id, " . $session['id'] . ")";
                if(mysqli_query($conn, $mark)){
                    $message = "✅ Attendance marked successfully for " . $session['subject'] . "!";
                    $status = "success";
                } else {
                    $message = "❌ Something went wrong. Please try again.";
                    $status = "error";
                }
            }
        }
    } else {
        $message = "❌ Invalid QR Code! Please scan a valid code.";
        $status = "error";
    }
} else {
    $message = "❌ No token found! Please scan a valid QR code.";
    $status = "error";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mark Attendance</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #1a73e8, #0d47a1);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            width: 420px;
            text-align: center;
        }

        .icon {
            font-size: 70px;
            margin-bottom: 20px;
        }

        .card h2 {
            color: #333;
            margin-bottom: 15px;
            font-size: 22px;
        }

        .message {
            padding: 15px 20px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            margin-bottom: 25px;
        }

        .success {
            background: #e8f5e9;
            color: #2e7d32;
            border: 2px solid #a5d6a7;
        }

        .error {
            background: #ffebee;
            color: #c62828;
            border: 2px solid #ef9a9a;
        }

        .warning {
            background: #fff8e1;
            color: #f57f17;
            border: 2px solid #ffe082;
        }

        .student-info {
            background: #f0f2f5;
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-size: 14px;
            color: #555;
        }

        .btn {
            display: inline-block;
            background: #1a73e8;
            color: white;
            padding: 12px 30px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 15px;
            font-weight: 600;
            transition: background 0.3s;
        }

        .btn:hover { background: #0d47a1; }

        .time {
            color: #999;
            font-size: 13px;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="card">
    <?php if($status == 'success'): ?>
        <div class="icon">🎉</div>
        <h2>Attendance Marked!</h2>
    <?php elseif($status == 'warning'): ?>
        <div class="icon">⚠️</div>
        <h2>Already Marked!</h2>
    <?php else: ?>
        <div class="icon">❌</div>
        <h2>Error!</h2>
    <?php endif; ?>

    <div class="message <?php echo $status; ?>">
        <?php echo $message; ?>
    </div>

    <div class="student-info">
        👤 Logged in as: <strong><?php echo $student_name; ?></strong>
    </div>

    <a href="dashboard.php" class="btn">Go to My Dashboard</a>

    <p class="time">📅 <?php echo date('d M Y, h:i A'); ?></p>
</div>

</body>
</html>