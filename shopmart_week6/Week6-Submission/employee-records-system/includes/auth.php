<?php
// includes/auth.php
// Include this on every page that requires a logged-in user.

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
