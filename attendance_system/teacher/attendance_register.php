<?php
session_start();
include('../includes/db.php');

if(!isset($_SESSION['teacher_id'])){
    header('Location: ../index.php');
    exit();
}

$teacher_id = $_SESSION['teacher_id'];
$teacher_name = $_SESSION['teacher_name'];

// Get filter values
$filter_subject = isset($_GET['subject']) ? mysqli_real_escape_string($conn, $_GET['subject']) : '';
$filter_from = isset($_GET['from']) ? $_GET['from'] : date('Y-m-01'); // First day of current month
$filter_to = isset($_GET['to']) ? $_GET['to'] : date('Y-m-d'); // Today

// Build session filter query
$session_filter = "WHERE teacher_id = $teacher_id 
                   AND DATE(created_at) BETWEEN '$filter_from' AND '$filter_to'";
if($filter_subject != ''){
    $session_filter .= " AND subject = '$filter_subject'";
}

// Get all sessions for this teacher in date range
$sessions_query = mysqli_query($conn, 
"SELECT * FROM sessions $session_filter ORDER BY created_at ASC");
$sessions = [];
while($row = mysqli_fetch_assoc($sessions_query)){
    $sessions[] = $row;
}

// Get all students
$students_query = mysqli_query($conn, "SELECT * FROM students ORDER BY name ASC");
$students = [];
while($row = mysqli_fetch_assoc($students_query)){
    $students[] = $row;
}

// Get all attendance records for these sessions
$attendance_map = []; // [student_id][session_id] = true/false
if(count($sessions) > 0){
    $session_ids = implode(',', array_column($sessions, 'id'));
    $att_query = mysqli_query($conn, 
    "SELECT student_id, session_id FROM attendance WHERE session_id IN ($session_ids)");
    while($row = mysqli_fetch_assoc($att_query)){
        $attendance_map[$row['student_id']][$row['session_id']] = true;
    }
}

// Get unique subjects for filter dropdown
$subjects_query = mysqli_query($conn, 
"SELECT DISTINCT subject FROM sessions WHERE teacher_id=$teacher_id ORDER BY subject ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Register</title>
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

        .filter-form {
            display: flex;
            gap: 15px;
            align-items: flex-end;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .filter-group label {
            font-size: 13px;
            font-weight: 600;
            color: #555;
        }

        .filter-group input,
        .filter-group select {
            padding: 9px 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            outline: none;
        }

        .filter-group input:focus,
        .filter-group select:focus {
            border-color: #1a73e8;
        }

        .btn {
            background: #1a73e8;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            font-weight: 600;
        }

        .btn:hover { background: #0d47a1; }

        .btn-reset {
            background: #f0f2f5;
            color: #333;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
        }

        /* Register Table */
        .table-wrapper {
            overflow-x: auto;
        }

        .register-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }

        .register-table th {
            background: #1a73e8;
            color: white;
            padding: 10px 12px;
            font-size: 13px;
            text-align: center;
            white-space: nowrap;
        }

        .register-table th.student-col {
            text-align: left;
            min-width: 150px;
        }

        .register-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #eee;
            text-align: center;
            font-size: 13px;
        }

        .register-table td.student-name {
            text-align: left;
            font-weight: 500;
            color: #333;
            white-space: nowrap;
        }

        .register-table tr:hover { background: #f9f9f9; }

        .present {
            color: #2e7d32;
            font-size: 18px;
        }

        .absent {
            color: #c62828;
            font-size: 18px;
        }

        .percentage {
            font-weight: 700;
            font-size: 14px;
            padding: 4px 10px;
            border-radius: 20px;
        }

        .pct-high {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .pct-mid {
            background: #fff8e1;
            color: #f57f17;
        }

        .pct-low {
            background: #ffebee;
            color: #c62828;
        }

        .session-date {
            font-size: 11px;
            color: #ccc;
            display: block;
        }

        .summary-cards {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
        }

        .summary-card {
            flex: 1;
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            border-top: 4px solid #1a73e8;
        }

        .summary-card h2 {
            font-size: 30px;
            color: #1a73e8;
        }

        .summary-card p {
            color: #666;
            font-size: 13px;
            margin-top: 5px;
        }

        .no-data {
            text-align: center;
            color: #999;
            padding: 40px;
            font-size: 15px;
        }

        .legend {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
            font-size: 13px;
            color: #555;
        }

        .legend span { display: flex; align-items: center; gap: 5px; }
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

    <!-- Filter Card -->
    <div class="card">
        <h3>🔍 Filter Attendance Register</h3>
        <form method="GET" class="filter-form">
            <div class="filter-group">
                <label>Subject</label>
                <select name="subject">
                    <option value="">All Subjects</option>
                    <?php while($sub = mysqli_fetch_assoc($subjects_query)): ?>
                    <option value="<?php echo $sub['subject']; ?>"
                        <?php echo ($filter_subject == $sub['subject']) ? 'selected' : ''; ?>>
                        <?php echo $sub['subject']; ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="filter-group">
                <label>From Date</label>
                <input type="date" name="from" value="<?php echo $filter_from; ?>">
            </div>
            <div class="filter-group">
                <label>To Date</label>
                <input type="date" name="to" value="<?php echo $filter_to; ?>">
            </div>
            <button type="submit" class="btn">🔍 Apply Filter</button>
            <a href="attendance_register.php" class="btn-reset">↺ Reset</a>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="summary-cards">
        <div class="summary-card">
            <h2><?php echo count($students); ?></h2>
            <p>Total Students</p>
        </div>
        <div class="summary-card">
            <h2><?php echo count($sessions); ?></h2>
            <p>Total Sessions</p>
        </div>
        <div class="summary-card">
            <h2>
                <?php
                $total_present = 0;
                foreach($attendance_map as $s_att){
                    $total_present += count($s_att);
                }
                echo $total_present;
                ?>
            </h2>
            <p>Total Present Entries</p>
        </div>
        <div class="summary-card">
            <h2>
                <?php
                $total_possible = count($students) * count($sessions);
                echo $total_possible > 0 ? round(($total_present / $total_possible) * 100) . '%' : '0%';
                ?>
            </h2>
            <p>Overall Attendance</p>
        </div>
    </div>

    <!-- Register Table -->
    <div class="card">
        <h3>📋 Attendance Register</h3>

        <?php if(count($sessions) == 0 || count($students) == 0): ?>
            <p class="no-data">No data found for selected filters. Try changing the date range or subject.</p>
        <?php else: ?>

        <div class="legend">
            <span>✅ Present</span>
            <span>❌ Absent</span>
        </div>

        <div class="table-wrapper">
            <table class="register-table">
                <thead>
                    <tr>
                        <th class="student-col">#&nbsp;&nbsp;Student</th>
                        <th>Roll No</th>
                        <?php foreach($sessions as $session): ?>
                        <th>
                            <?php echo htmlspecialchars($session['subject']); ?>
                            <span class="session-date">
                                <?php echo date('d M', strtotime($session['created_at'])); ?>
                            </span>
                        </th>
                        <?php endforeach; ?>
                        <th>Present</th>
                        <th>Absent</th>
                        <th>Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1; foreach($students as $student): ?>
                    <?php
                        $present_count = 0;
                        foreach($sessions as $session){
                            if(isset($attendance_map[$student['id']][$session['id']])){
                                $present_count++;
                            }
                        }
                        $absent_count = count($sessions) - $present_count;
                        $pct = count($sessions) > 0 ? round(($present_count / count($sessions)) * 100) : 0;
                        $pct_class = $pct >= 75 ? 'pct-high' : ($pct >= 50 ? 'pct-mid' : 'pct-low');
                    ?>
                    <tr>
                        <td class="student-name">
                            <?php echo $i++; ?>&nbsp;&nbsp;👤 <?php echo $student['name']; ?>
                        </td>
                        <td><?php echo $student['roll_number']; ?></td>

                        <?php foreach($sessions as $session): ?>
                        <td>
                            <?php if(isset($attendance_map[$student['id']][$session['id']])): ?>
                                <span class="present">✅</span>
                            <?php else: ?>
                                <span class="absent">❌</span>
                            <?php endif; ?>
                        </td>
                        <?php endforeach; ?>

                        <td><strong style="color:#2e7d32;"><?php echo $present_count; ?></strong></td>
                        <td><strong style="color:#c62828;"><?php echo $absent_count; ?></strong></td>
                        <td>
                            <span class="percentage <?php echo $pct_class; ?>">
                                <?php echo $pct; ?>%
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

</div>

</body>
</html>