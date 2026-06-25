<?php
/**
 * Student Portal - Logout
 * BIT3208 Week 7: User Authentication and Session Management
 */

session_start();
session_destroy();
header("Location: login.php");
exit();
