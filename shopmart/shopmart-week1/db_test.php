<?php
/**
 * ShopMart - Week 1: Database Connection Test
 * Confirms MySQL is running and reachable from PHP via mysqli.
 * (No tables needed yet — just proving the connection works.)
 */

$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';

$conn = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS);

if ($conn) {
    echo "<h2 style='color:green;font-family:sans-serif;'>✅ Connected to MySQL successfully.</h2>";
    echo "<p style='font-family:sans-serif;'>MySQL server version: " . mysqli_get_server_info($conn) . "</p>";
    mysqli_close($conn);
} else {
    echo "<h2 style='color:red;font-family:sans-serif;'>❌ Connection failed: " . mysqli_connect_error() . "</h2>";
}
