<?php
/**
 * ShopMart - Database Connection
 * Week 5: Database Components — CRUD Operations
 *
 * Same mysqli connection pattern as Week 4.
 */

$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'shopmart';

$conn = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}
