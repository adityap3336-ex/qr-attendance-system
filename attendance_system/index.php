<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Management System</title>
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

        .login-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            width: 400px;
            text-align: center;
        }

        h1 {
            color: #1a73e8;
            margin-bottom: 5px;
            font-size: 24px;
        }

        p {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }

        .role-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
        }

        .role-btn {
            flex: 1;
            padding: 12px;
            border: 2px solid #1a73e8;
            background: white;
            color: #1a73e8;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .role-btn.active {
            background: #1a73e8;
            color: white;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        label {
            display: block;
            color: #333;
            margin-bottom: 6px;
            font-size: 14px;
            font-weight: 500;
        }

        input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: border 0.3s;
            outline: none;
        }

        input:focus {
            border-color: #1a73e8;
        }

        .login-btn {
            width: 100%;
            padding: 13px;
            background: #1a73e8;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }

        .login-btn:hover {
            background: #0d47a1;
        }

        .error {
            color: red;
            font-size: 13px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<div class="login-container">
    <h1>📋 Attendance System</h1>
    <p>QR Code Based Attendance Management</p>

    <div class="role-buttons">
<button class="role-btn active" onclick="setRole('student')">🎓 Student</button>
<button class="role-btn" onclick="setRole('teacher')">👨‍🏫 Teacher</button>
<button class="role-btn" onclick="setRole('admin')">🔐 Admin</button>
    </div>

    <?php if(isset($_GET['error'])): ?>
        <div class="error">❌ Invalid credentials. Please try again.</div>
    <?php endif; ?>

    <form action="login_process.php" method="POST">
        <input type="hidden" name="role" id="roleInput" value="student">

        <div class="form-group">
            <label>Roll Number / Email</label>
            <input type="text" name="username" placeholder="Enter your roll number or email" required>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="Enter your password" required>
        </div>

        <button type="submit" class="login-btn">Login →</button>
    </form>
</div>

<script>
    function setRole(role) {
        document.getElementById('roleInput').value = role;
        document.querySelectorAll('.role-btn').forEach(btn => btn.classList.remove('active'));
        event.target.classList.add('active');
    }
</script>

</body>
</html>