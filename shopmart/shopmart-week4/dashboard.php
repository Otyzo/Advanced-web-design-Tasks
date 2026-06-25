<?php
/**
 * ShopMart - Dashboard (Basic)
 * Week 4: simple session check — no roles yet (added in Week 7).
 */

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - ShopMart (Week 4)</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-blue-600 text-white px-6 py-4 flex justify-between items-center">
        <span class="font-bold">ShopMart</span>
        <a href="logout.php" class="bg-white text-blue-600 px-3 py-1 rounded text-sm">Logout</a>
    </nav>
    <main class="p-8">
        <h1 class="text-2xl font-bold mb-2">
            Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!
        </h1>
        <p class="text-gray-600">
            You are logged in. This confirms the session was created successfully after
            your credentials were verified against the MySQL database.
        </p>
    </main>
</body>
</html>
