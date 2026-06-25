<?php
/**
 * Student Portal - Protected Dashboard
 * BIT3208 Week 7: User Authentication and Session Management
 *
 * Restricts access to logged-in users only, per the brief:
 * if no session exists, redirect back to login.
 */

session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Student Portal</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; margin: 0; }
        nav { background: #2c3e50; color: #fff; padding: 16px 30px; display: flex;
              justify-content: space-between; align-items: center; }
        nav a { color: #fff; background: #e74c3c; padding: 8px 14px; border-radius: 4px;
                text-decoration: none; font-size: 0.85rem; }
        main { padding: 40px; max-width: 600px; margin: 0 auto; }
        .card { background: #fff; padding: 24px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
        h1 { color: #2c3e50; margin-top: 0; }
    </style>
</head>
<body>
    <nav>
        <span>Student Portal</span>
        <a href="logout.php">Logout</a>
    </nav>
    <main>
        <div class="card">
            <h1>Welcome <?= htmlspecialchars($_SESSION['user']) ?></h1>
            <p>You are logged in as <strong><?= htmlspecialchars($_SESSION['email']) ?></strong>.</p>
            <p>This is your protected student dashboard — only visible to authenticated users.
               If you try to open this page without logging in, you will be redirected to the login screen.</p>
        </div>
    </main>
</body>
</html>
