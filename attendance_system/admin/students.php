<?php
session_start();
include('../includes/db.php');

if(!isset($_SESSION['admin_id'])){
    header('Location: ../index.php');
    exit();
}

$admin_name = $_SESSION['admin_name'];

// Delete student if requested
if(isset($_GET['delete'])){
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM attendance WHERE student_id=$id");
    mysqli_query($conn, "DELETE FROM students WHERE id=$id");
    header('Location: students.php?success=deleted');
    exit();
}

// Get all students
$students = mysqli_query($conn, 
"SELECT s.*, COUNT(a.id) as total_attendance 
FROM students s 
LEFT JOIN attendance a ON s.id = a.student_id 
GROUP BY s.id 
ORDER BY s.name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students</title>
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

        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .top-bar h3 { color: #6a1b9a; font-size: 18px; }

        .btn-add {
            background: #6a1b9a;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
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

        .delete-btn {
            background: #ffebee;
            color: #c62828;
            padding: 6px 14px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
        }

        .badge {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .success {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
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
    <div class="card">
        <div class="top-bar">
            <h3>🎓 All Students</h3>
            <a href="add_student.php" class="btn-add">➕ Add New Student</a>
        </div>

        <?php if(isset($_GET['success'])): ?>
        <div class="success">✅ Student deleted successfully!</div>
        <?php endif; ?>

        <table>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Roll Number</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Total Attendance</th>
                <th>Joined On</th>
                <th>Action</th>
            </tr>
            <?php $i=1; while($row = mysqli_fetch_assoc($students)): ?>
            <tr>
                <td><?php echo $i++; ?></td>
                <td>🎓 <?php echo $row['name']; ?></td>
                <td><?php echo $row['roll_number']; ?></td>
                <td><?php echo $row['email']; ?></td>
                <td><?php echo $row['phone']; ?></td>
                <td>
                    <span class="badge">
                        ✅ <?php echo $row['total_attendance']; ?> classes
                    </span>
                </td>
                <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                <td>
                    <a href="students.php?delete=<?php echo $row['id']; ?>"
                       class="delete-btn"
                       onclick="return confirm('Are you sure you want to delete this student?')">
                        🗑 Delete
                    </a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

</body>
</html>