<?php
session_start();
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'payroll_db';
$status = '';
$error = '';
$installed = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = trim($_POST['host'] ?? 'localhost');
    $user = trim($_POST['user'] ?? 'root');
    $pass = $_POST['pass'] ?? '';
    $dbname = trim($_POST['dbname'] ?? 'payroll_db');

    try {
        $conn = new mysqli($host, $user, $pass);
        $conn->set_charset('utf8mb4');

        $conn->query("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $conn->select_db($dbname);

        $conn->query("CREATE TABLE IF NOT EXISTS users (
            user_id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            user_type ENUM('Admin','User') NOT NULL DEFAULT 'User',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB");

        $conn->query("CREATE TABLE IF NOT EXISTS departments (
            dept_id INT AUTO_INCREMENT PRIMARY KEY,
            dept_name VARCHAR(50) NOT NULL UNIQUE
        ) ENGINE=InnoDB");

        $conn->query("CREATE TABLE IF NOT EXISTS employees (
            employee_id INT AUTO_INCREMENT PRIMARY KEY,
            staff_id VARCHAR(20) NOT NULL UNIQUE,
            full_name VARCHAR(100) NOT NULL,
            gender ENUM('Male','Female') NOT NULL,
            department_id INT NULL,
            position VARCHAR(50) NOT NULL,
            phone VARCHAR(20) NULL,
            basic_salary DECIMAL(12,2) NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_emp_dept FOREIGN KEY (department_id) REFERENCES departments(dept_id) ON DELETE SET NULL
        ) ENGINE=InnoDB");

        $conn->query("CREATE TABLE IF NOT EXISTS allowances (
            allowance_id INT AUTO_INCREMENT PRIMARY KEY,
            employee_id INT NOT NULL UNIQUE,
            housing DECIMAL(12,2) NOT NULL DEFAULT 0,
            transport DECIMAL(12,2) NOT NULL DEFAULT 0,
            medical DECIMAL(12,2) NOT NULL DEFAULT 0,
            utility DECIMAL(12,2) NOT NULL DEFAULT 0,
            other DECIMAL(12,2) NOT NULL DEFAULT 0,
            CONSTRAINT fk_allow_emp FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE
        ) ENGINE=InnoDB");

        $conn->query("CREATE TABLE IF NOT EXISTS deductions (
            deduction_id INT AUTO_INCREMENT PRIMARY KEY,
            employee_id INT NOT NULL UNIQUE,
            tax DECIMAL(12,2) NOT NULL DEFAULT 0,
            pension DECIMAL(12,2) NOT NULL DEFAULT 0,
            loan DECIMAL(12,2) NOT NULL DEFAULT 0,
            cooperative DECIMAL(12,2) NOT NULL DEFAULT 0,
            other DECIMAL(12,2) NOT NULL DEFAULT 0,
            CONSTRAINT fk_ded_emp FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE
        ) ENGINE=InnoDB");

        $conn->query("CREATE TABLE IF NOT EXISTS payroll (
            payroll_id INT AUTO_INCREMENT PRIMARY KEY,
            employee_id INT NOT NULL,
            payroll_month TINYINT NOT NULL,
            payroll_year SMALLINT NOT NULL,
            basic_salary DECIMAL(12,2) NOT NULL DEFAULT 0,
            total_allowance DECIMAL(12,2) NOT NULL DEFAULT 0,
            gross_salary DECIMAL(12,2) NOT NULL DEFAULT 0,
            total_deduction DECIMAL(12,2) NOT NULL DEFAULT 0,
            net_salary DECIMAL(12,2) NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_emp_period (employee_id, payroll_month, payroll_year),
            CONSTRAINT fk_pay_emp FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE
        ) ENGINE=InnoDB");

        $count = $conn->query("SELECT COUNT(*) AS c FROM departments")->fetch_assoc()['c'];
        if ((int)$count === 0) {
            $conn->query("INSERT INTO departments (dept_name) VALUES
                ('Administration'), ('Finance'), ('Human Resources'), ('Information Technology'), ('Operations')");
        }

        $res = $conn->query("SELECT COUNT(*) AS c FROM users WHERE username = 'admin'")->fetch_assoc()['c'];
        if ((int)$res === 0) {
            $stmt = $conn->prepare("INSERT INTO users (username, password, user_type) VALUES ('admin', ?, 'Admin')");
            $hash = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt->bind_param('s', $hash);
            $stmt->execute();
        }

        $installed = true;
    } catch (Exception $ex) {
        $error = $ex->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install | Staff Payroll System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-body">
    <main class="login-card">
        <div class="login-brand">
            <h1>Staff Payroll System</h1>
            <p>Installation Wizard</p>
        </div>

        <?php if ($status) : ?><div class="alert alert-success"><?= htmlspecialchars($status) ?></div><?php endif; ?>
        <?php if ($error) : ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <?php if ($installed) : ?>
            <div class="alert alert-success">Database installed successfully. Default login: <strong>admin</strong> / <strong>admin123</strong>.</div>
            <a class="btn btn-primary btn-block" href="login.php">Go to Login</a>
        <?php else : ?>
            <form method="post" action="install.php" autocomplete="off">
                <label>Host</label>
                <input type="text" name="host" value="<?= htmlspecialchars($host) ?>" required>

                <label>Database User</label>
                <input type="text" name="user" value="<?= htmlspecialchars($user) ?>" required>

                <label>Database Password</label>
                <input type="password" name="pass" value="<?= htmlspecialchars($pass) ?>">

                <label>Database Name</label>
                <input type="text" name="dbname" value="<?= htmlspecialchars($dbname) ?>" required>

                <button type="submit" class="btn btn-primary btn-block">Install System</button>
            </form>
            <p class="hint">This installs the database tables and creates the default admin account (admin / admin123).</p>
        <?php endif; ?>
    </main>
</body>
</html>