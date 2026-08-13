<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'payroll_db');
define('SYS_NAME', 'Staff Payroll System');
define('ORG_NAME', 'WAMISE TECH');
define('CURRENCY', '&#8358;');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    if (basename($_SERVER['SCRIPT_NAME']) !== 'install.php') {
        header('Location: install.php');
        exit;
    }
    throw $e;
}

function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function money($amount)
{
    return CURRENCY . number_format((float)$amount, 2);
}

function month_name($month)
{
    $names = ['', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    return $names[(int)$month] ?? $month;
}

function flash_set($type, $message)
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function flash_show()
{
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        $cls = $f['type'] === 'error' ? 'alert-error' : 'alert-success';
        echo '<div class="alert ' . $cls . '">' . e($f['message']) . '</div>';
        unset($_SESSION['flash']);
    }
}