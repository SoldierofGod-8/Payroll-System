<?php
if (!isset($page_title)) {
    $page_title = 'Dashboard';
}
if (!isset($active)) {
    $active = '';
}
$display_name = !empty($_SESSION['full_name']) ? $_SESSION['full_name'] : $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title) ?> | <?= e(SYS_NAME) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header class="topbar no-print">
    <div class="topbar-inner">
        <div class="brand"><?= e(SYS_NAME) ?></div>
        <div class="user-box">
            <span class="user-name"><?= e($display_name) ?> (<?= e($_SESSION['user_type']) ?>)</span>
            <a href="logout.php" class="btn btn-small btn-danger">Logout</a>
        </div>
    </div>
</header>

<nav class="navbar no-print">
    <a href="dashboard.php" class="<?= $active === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
    <a href="employees.php" class="<?= $active === 'employees' ? 'active' : '' ?>">Employees</a>
    <a href="departments.php" class="<?= $active === 'departments' ? 'active' : '' ?>">Departments</a>
    <a href="allowances.php" class="<?= $active === 'allowances' ? 'active' : '' ?>">Allowances</a>
    <a href="deductions.php" class="<?= $active === 'deductions' ? 'active' : '' ?>">Deductions</a>
    <a href="payroll.php" class="<?= $active === 'payroll' ? 'active' : '' ?>">Payroll</a>
    <a href="reports.php" class="<?= $active === 'reports' ? 'active' : '' ?>">Reports</a>
    <a href="guide.php" class="<?= $active === 'guide' ? 'active' : '' ?>">Guide</a>
    <?php if (is_admin()) : ?>
        <a href="users.php" class="<?= $active === 'users' ? 'active' : '' ?>">Users</a>
        <a href="backup.php" class="<?= $active === 'backup' ? 'active' : '' ?>">Backup</a>
    <?php endif; ?>
</nav>

<main class="container">
<?php flash_show(); ?>