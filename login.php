<?php
session_start();

if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

require 'config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please enter your username and password.';
    } else {
        try {
            $stmt = $conn->prepare("SELECT user_id, username, password, user_type FROM users WHERE username = ?");
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['username'];
                $_SESSION['user_type'] = $user['user_type'];
                header('Location: dashboard.php');
                exit;
            }
            $error = 'Invalid username or password.';
        } catch (Exception $ex) {
            $error = 'Login failed. Please check your database connection.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | <?= e(SYS_NAME) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-body">
    <main class="login-card">
        <div class="login-brand">
            <div class="login-logo">&#8358;</div>
            <h1>Staff Payroll System</h1>
            <p><?= e(ORG_NAME) ?></p>
        </div>

        <?php if ($error) : ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" action="login.php" autocomplete="off">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" placeholder="Enter username" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter password" required>

            <button type="submit" class="btn btn-primary btn-block">Login</button>
        </form>

        <p class="hint">Default administrator account: <strong>admin</strong> / <strong>admin123</strong></p>
    </main>
</body>
</html>