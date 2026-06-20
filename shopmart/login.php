<?php
/**
 * ShopMart - Login (Basic)
 * Week 4: Server-side Components (PHP) — connects to MySQL, starts a session.
 *
 * Kept intentionally simple for this week:
 *  - mysqli with prepared statements (prevents SQL injection)
 *  - password_verify() against the bcrypt hash
 *  - plain session_start() / $_SESSION on success
 *
 * NOT yet included (added later in Week 7): CSRF tokens, login
 * attempt lockout, role-based access control, session timeout.
 */

session_start();
require_once 'db_connect.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $query = "SELECT id, username, password_hash FROM users WHERE username = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 's', $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if ($user && password_verify($password, $user['password_hash'])) {
        // Success: store basic info in the session
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];

        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid username or password.';
    }

    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - ShopMart (Week 4)</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white shadow-md rounded-lg p-8 w-full max-w-md">
        <h1 class="text-2xl font-bold mb-2 text-gray-800">Log In</h1>
        <p class="text-sm text-gray-500 mb-6">Week 4 — basic PHP + MySQL login with session</p>

        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <label class="block mb-2 text-sm font-medium text-gray-700">Username</label>
            <input type="text" name="username" class="w-full border rounded px-3 py-2 mb-4" required>

            <label class="block mb-2 text-sm font-medium text-gray-700">Password</label>
            <input type="password" name="password" class="w-full border rounded px-3 py-2 mb-6" required>

            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">
                Log In
            </button>
        </form>

        <p class="mt-4 text-sm text-gray-600">
            Don't have an account? <a href="register.php" class="text-blue-600">Register</a>
        </p>
    </div>
</body>
</html>
