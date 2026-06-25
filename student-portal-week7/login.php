<?php
/**
 * Student Portal - Login
 * BIT3208 Week 7: User Authentication and Session Management
 *
 * Follows the brief's workflow: check database records, verify password
 * with password_verify(), create a session, then redirect to dashboard.
 */

session_start();
include("connection.php");

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {

    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = mysqli_query($conn, $sql);
    $user = mysqli_fetch_assoc($result);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user['fullname'];
        $_SESSION['email'] = $user['email'];
        header("Location: dashboard.php");
        exit;
    } else {
        $message = "Invalid Login";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Student Portal</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; display: flex;
               align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .card { background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                width: 320px; }
        h2 { margin-top: 0; color: #2c3e50; }
        input { width: 100%; padding: 10px; margin-bottom: 12px; border: 1px solid #ccc;
                border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #2c7be5; color: #fff; border: none;
                 border-radius: 4px; cursor: pointer; font-weight: bold; }
        button:hover { background: #1a5fc1; }
        .message { margin-bottom: 14px; padding: 10px; border-radius: 4px; font-size: 0.9rem;
                    background: #fdecea; color: #b3261e; border: 1px solid #f5c2bd; }
        a { color: #2c7be5; text-decoration: none; font-size: 0.85rem; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Student Login</h2>

        <?php if ($message): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>

        <p style="margin-top:12px; text-align:center;">
            Don't have an account? <a href="register.php">Register here</a>
        </p>
    </div>
</body>
</html>
