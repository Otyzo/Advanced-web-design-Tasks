<?php
/**
 * Student Portal - Database Connection
 * BIT3208 Week 7: User Authentication and Session Management
 */

$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "studentportal"
);

if (!$conn) {
    die("Connection Failed: " . mysqli_connect_error());
}
