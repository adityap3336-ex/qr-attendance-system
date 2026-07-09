<?php
session_start();
include('includes/db.php');

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $role = $_POST['role'];
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    if($role == 'student'){
        $query = "SELECT * FROM students WHERE roll_number='$username' OR email='$username'";
        $result = mysqli_query($conn, $query);
        $student = mysqli_fetch_assoc($result);

        if($student && password_verify($password, $student['password'])){
    $_SESSION['student_id'] = $student['id'];
    $_SESSION['student_name'] = $student['name'];
    $_SESSION['role'] = 'student';
    
    // Check if there's a pending scan URL
    if(isset($_SESSION['redirect_after_login'])){
        $redirect = $_SESSION['redirect_after_login'];
        unset($_SESSION['redirect_after_login']);
        header('Location: ' . $redirect);
        exit();
    }
    
    header('Location: student/dashboard.php');
    exit();
}

    } else if($role == 'teacher'){
        $query = "SELECT * FROM teachers WHERE email='$username'";
        $result = mysqli_query($conn, $query);
        $teacher = mysqli_fetch_assoc($result);

        if($teacher && password_verify($password, $teacher['password'])){
            $_SESSION['teacher_id'] = $teacher['id'];
            $_SESSION['teacher_name'] = $teacher['name'];
            $_SESSION['role'] = 'teacher';
            header('Location: teacher/dashboard.php');
            exit();
        } else {
            header('Location: index.php?error=1');
            exit();
        }

    } else if($role == 'admin'){
        $query = "SELECT * FROM admins WHERE email='$username'";
        $result = mysqli_query($conn, $query);
        $admin = mysqli_fetch_assoc($result);

        if($admin && password_verify($password, $admin['password'])){
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            $_SESSION['role'] = 'admin';
            header('Location: admin/dashboard.php');
            exit();
        } else {
            header('Location: index.php?error=1');
            exit();
        }
    }

} else {
    header('Location: index.php');
    exit();
}
?>