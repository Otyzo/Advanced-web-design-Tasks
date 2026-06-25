<?php
/**
 * Student Portal - Registration
 * BIT3208 Week 7: User Authentication and Session Management
 *
 * Follows the brief: full name, email, password (hashed with password_hash),
 * basic input validation, and a user-friendly success/error message.
 */

include("connection.php");

$message = "";

if (isset($_POST['email'])) {

    $name     = trim($_POST['fullname']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    // --- Input validation ---
    if (empty($name) || empty($email) || empty($password)) {
        $message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters.";
    } else {

        // --- Check for existing email ---
        $check = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
        if (mysqli_num_rows($check) > 0) {
            $message = "An account with that email already exists.";
        } else {

            // --- Hash password before storing (never store plain text) ---
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO users (fullname, email, password)
                    VALUES ('$name', '$email', '$hashedPassword')";

            if (mysqli_query($conn, $sql)) {
                $message = "Registration Successful. You can now log in.";
            } else {
                $message = "Error: " . mysqli_error($conn);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Student Portal</title>
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
        .message { margin-bottom: 14px; padding: 10px; border-radius: 4px; font-size: 0.9rem; }
        .success { background: #e6f4ea; color: #1e7e34; border: 1px solid #b6e0c1; }
        .error { background: #fdecea; color: #b3261e; border: 1px solid #f5c2bd; }
        a { color: #2c7be5; text-decoration: none; font-size: 0.85rem; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Student Registration</h2>

        <?php if ($message): ?>
            <div class="message <?= strpos($message, 'Successful') !== false ? 'success' : 'error' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="register.php">
            <input type="text" name="fullname" placeholder="Full Name" value="<?= htmlspecialchars($_POST['fullname'] ?? '') ?>" required>
            <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Register</button>
        </form>

        <p style="margin-top:12px; text-align:center;">
            Already have an account? <a href="login.php">Login here</a>
        </p>
    </div>
</body>
</html>
