<?php
session_start();
include('../includes/db.php');

if(!isset($_SESSION['admin_id'])){
    header('Location: ../index.php');
    exit();
}

$admin_name = $_SESSION['admin_name'];
$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $roll = mysqli_real_escape_string($conn, $_POST['roll_number']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if roll number already exists
    $check = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT id FROM students WHERE roll_number='$roll' OR email='$email'"));

    if($check){
        $error = "Roll number or email already exists!";
    } else {
        $query = "INSERT INTO students (name, roll_number, email, phone, password) 
                  VALUES ('$name', '$roll', '$email', '$phone', '$password')";
        if(mysqli_query($conn, $query)){
            $success = "Student added successfully!";
        } else {
            $error = "Something went wrong. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Student</title>
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

        .container {
            padding: 30px;
            max-width: 600px;
            margin: 0 auto;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .card h3 {
            color: #6a1b9a;
            margin-bottom: 25px;
            font-size: 18px;
        }

        .form-group { margin-bottom: 20px; }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #333;
            font-size: 14px;
        }

        input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            outline: none;
        }

        input:focus { border-color: #6a1b9a; }

        .btn {
            background: #6a1b9a;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            font-weight: 600;
            width: 100%;
        }

        .btn:hover { background: #4a148c; }

        .error {
            background: #ffebee;
            color: #c62828;
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .success {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #6a1b9a;
            text-decoration: none;
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
        <h3>➕ Add New Student</h3>

        <?php if($error): ?>
        <div class="error">❌ <?php echo $error; ?></div>
        <?php endif; ?>

        <?php if($success): ?>
        <div class="success">✅ <?php echo $success; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" placeholder="e.g. Rahul Patil" required>
            </div>
            <div class="form-group">
                <label>Roll Number</label>
                <input type="text" name="roll_number" placeholder="e.g. CS502" required>
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="e.g. rahul@student.com" required>
            </div>
            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" name="phone" placeholder="e.g. 9876543210" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Set a password for student" required>
            </div>
            <button type="submit" class="btn">➕ Add Student</button>
        </form>
        <a href="students.php" class="back-link">← Back to Students List</a>
    </div>
</div>

</body>
</html>