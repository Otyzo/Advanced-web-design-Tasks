<?php
/**
 * ShopMart - Registration (Basic)
 * Week 4: Server-side Components (PHP) — connects to MySQL via mysqli
 */

require_once 'db_connect.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    // --- Basic validation ---
    if ($username === '' || $email === '' || $password === '') {
        $errors[] = 'All fields are required.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }

    // --- Check if username/email already exists ---
    if (empty($errors)) {
        $checkQuery = "SELECT id FROM users WHERE username = ? OR email = ?";
        $stmt = mysqli_prepare($conn, $checkQuery);
        mysqli_stmt_bind_param($stmt, 'ss', $username, $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $errors[] = 'Username or email already exists.';
        }
        mysqli_stmt_close($stmt);
    }

    // --- Insert new user ---
    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $insertQuery = "INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insertQuery);
        mysqli_stmt_bind_param($stmt, 'sss', $username, $email, $hashedPassword);

        if (mysqli_stmt_execute($stmt)) {
            $success = true;
        } else {
            $errors[] = 'Registration failed: ' . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - ShopMart (Week 4)</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white shadow-md rounded-lg p-8 w-full max-w-md">
        <h1 class="text-2xl font-bold mb-2 text-gray-800">Create an Account</h1>
        <p class="text-sm text-gray-500 mb-6">Week 4 — basic PHP + MySQL registration</p>

        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-300 text-green-700 px-4 py-3 rounded mb-4">
                Account created! You can now <a href="login.php" class="underline font-medium">log in</a>.
            </div>
        <?php endif; ?>

        <?php if ($errors): ?>
            <div class="bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc list-inside">
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="register.php">
            <label class="block mb-2 text-sm font-medium text-gray-700">Username</label>
            <input type="text" name="username" class="w-full border rounded px-3 py-2 mb-4" required>

            <label class="block mb-2 text-sm font-medium text-gray-700">Email</label>
            <input type="email" name="email" class="w-full border rounded px-3 py-2 mb-4" required>

            <label class="block mb-2 text-sm font-medium text-gray-700">Password</label>
            <input type="password" name="password" class="w-full border rounded px-3 py-2 mb-6" required>

            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">
                Register
            </button>
        </form>

        <p class="mt-4 text-sm text-gray-600">
            Already have an account? <a href="login.php" class="text-blue-600">Log in</a>
        </p>
    </div>
</body>
</html>
