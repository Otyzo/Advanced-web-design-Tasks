<?php
// config.php
// Database connection settings for the Employee Records Management System.
// Update these values if your MySQL/XAMPP setup differs.

session_start();

$db_host     = "localhost";
$db_username = "root";
$db_password = "";
$db_name     = "employee_records_db";

$conn = mysqli_connect($db_host, $db_username, $db_password, $db_name);

if (!$conn) {
    die("Connection Failed: " . mysqli_connect_error());
}
?>
