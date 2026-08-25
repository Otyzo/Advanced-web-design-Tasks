<?php
// ============================================================
// logout.php — Session destroy + Remember-Me cookie cleanup
// (Week 9 — session management & cookies)
// ============================================================
session_start();
require_once 'db.php';

// Invalidate the remember-me token both client-side and in the DB
if (!empty($_SESSION['user_id'])) {
    $stmt = $conn->prepare("UPDATE users SET remember_token = NULL, remember_expires = NULL WHERE id = ?");
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $stmt->close();
}
if (isset($_COOKIE['remember_me'])) {
    setcookie('remember_me', '', time() - 3600, '/');
}

session_unset();
session_destroy();
header('Location: login.php');
exit;
