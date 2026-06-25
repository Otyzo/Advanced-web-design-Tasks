<?php
// delete_employee.php - Delete operation
require_once 'config.php';
require_once 'includes/auth.php';

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    $stmt = $conn->prepare("DELETE FROM employees WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

header("Location: index.php?status=deleted");
exit();
?>
