<?php
/**
 * ShopMart - Auth Guard
 * Week 5: Database Components — CRUD Operations
 *
 * Reuses the basic session check from Week 4's login system.
 * Any logged-in user can manage products this week; role-based
 * restriction (admin-only) is added properly in Week 7.
 */

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login_redirect.php');
    exit;
}
