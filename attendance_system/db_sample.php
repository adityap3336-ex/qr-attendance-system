<?php
$host = "your_database_host";
$user = "your_database_username";
$password = "your_database_password";
$database = "your_database_name";

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>