<?php
/**
 * ShopMart - One-Time Password Fix
 *
 * The sample SQL files shipped with an incorrect placeholder password hash.
 * Run this once in your browser to fix the testuser account so
 * Test1234 actually works. Delete this file afterward.
 */

require_once 'db_connect.php';

$username = 'testuser';
$newPassword = 'Test1234';
$correctHash = password_hash($newPassword, PASSWORD_BCRYPT);

$stmt = mysqli_prepare($conn, "UPDATE users SET password_hash = ? WHERE username = ?");
mysqli_stmt_bind_param($stmt, 'ss', $correctHash, $username);

if (mysqli_stmt_execute($stmt)) {
    echo "<h2 style='color:green;'>Password fixed!</h2>";
    echo "<p>You can now log in with:</p>";
    echo "<p><strong>Username:</strong> testuser<br><strong>Password:</strong> Test1234</p>";
    echo "<p>New hash stored: " . htmlspecialchars($correctHash) . "</p>";
    echo "<p style='color:red;'>Please delete this file (fix_password.php) now that it has run.</p>";
} else {
    echo "<h2 style='color:red;'>Something went wrong:</h2>";
    echo mysqli_error($conn);
}

mysqli_stmt_close($stmt);
