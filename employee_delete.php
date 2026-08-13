<?php
require 'config/db.php';
require 'includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_id = (int)($_POST['employee_id'] ?? 0);
    if ($employee_id > 0) {
        $stmt = $conn->prepare("DELETE FROM employees WHERE employee_id = ?");
        $stmt->bind_param('i', $employee_id);
        $stmt->execute();
        flash_set('success', 'Employee record deleted successfully.');
    }
}
header('Location: employees.php');
exit;