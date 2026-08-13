<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

function is_admin()
{
    return ($_SESSION['user_type'] ?? '') === 'Admin';
}

function require_admin()
{
    if (!is_admin()) {
        flash_set('error', 'Access denied. Administrator privileges are required.');
        header('Location: dashboard.php');
        exit;
    }
}