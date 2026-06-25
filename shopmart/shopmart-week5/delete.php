<?php
/**
 * ShopMart - Delete Product (DELETE)
 * Week 5: Database Components — CRUD Operations
 */

require_once 'auth_check.php';
require_once 'db_connect.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id > 0) {
    $stmt = mysqli_prepare($conn, "DELETE FROM products WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

header('Location: index.php?deleted=1');
exit;
