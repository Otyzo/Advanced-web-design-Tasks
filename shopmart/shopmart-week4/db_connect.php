<?php
/**
 * ShopMart - Database Connection
 * Week 4: Server-side Components (PHP) — Basic Login System
 *
 * Uses mysqli (the simpler, more commonly taught Week-4-level API).
 * Week 7 later upgrades the project to PDO with more advanced security.
 */

$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'shopmart';

$conn = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}
