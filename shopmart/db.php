<?php
// ============================================================
// db.php — Database Connection
// BIT3208 Capstone Project — ShopMart
// ============================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // Change if your MySQL user differs
define('DB_PASS', '');           // Change to your MySQL password
define('DB_NAME', 'shopmart');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die('<div style="font-family:sans-serif;padding:20px;background:#fee2e2;color:#991b1b;border-radius:8px;margin:20px;">
        <strong>Database Connection Failed:</strong> ' . htmlspecialchars($conn->connect_error) . '
        <br><small>Check your credentials in db.php and ensure MySQL is running.</small>
    </div>');
}

$conn->set_charset('utf8mb4');
