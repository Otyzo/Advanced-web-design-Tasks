<?php
// db_connect.php
// Shared connection used by Class Activities 1, 2, and 3.
// Matches the studentdb example from the Week 6 lecture material.

$conn = mysqli_connect("localhost", "root", "", "studentdb");

if (!$conn) {
    die("Connection Failed: " . mysqli_connect_error());
}
?>
