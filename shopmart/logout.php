<?php
/**
 * ShopMart - Logout (Basic)
 * Week 4: clears the session.
 */

session_start();
session_unset();
session_destroy();

header('Location: login.php');
exit;
