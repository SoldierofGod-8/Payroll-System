<?php
$page_title = 'User Management';
$active = 'users';

require 'config/db.php';
require 'includes/auth.php';
require_admin();
require 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $user_type = $_POST['user_type'] ?? 'User';

        if ($username === '' || $password === '') {
            flash_set('error', 'Username and password are required.');
        } elseif (strlen($password) < 4) {
            flash_set('error', 'Password must be at least 4 characters.');
        } else {
            try {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (username, password, user_type) VALUES (?, ?, ?)");
                $stmt->bind_param('sss', $username, $hash, $user_type);
                $stmt->execute();
                flash_set('success', 'User account "' . $username . '" created successfully.');
            } catch (mysqli_sql_exception $ex) {
                flash_set('error', 'Username already exists.');
            }
        }
    } elseif ($action === 'delete') {
        $user_id = (int)($_POST['user_id'] ?? 0);
        if ($user_id === (int)$_SESSION['user_id']) {
            flash_set('error', 'You cannot delete your own account.');
        } elseif ($user_id > 0) {
            $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            flash_set('success', 'User account deleted successfully.');
        }
    } elseif ($action === 'reset') {
        $user_id = (int)($_POST['user_id'] ?? 0);
        $password = $_POST['password'] ?? '';
        if ($user_id > 0 && $password !== '') {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
            $stmt->bind_param('si', $hash, $user_id);
            $stmt->execute();
            flash_set('success', 'Password reset successfully.');
        } else {
            flash_set('error', 'Enter a new password for the user.');
        }
    }
    header('Location: users.php');
    exit;
}

$rows = $conn->query("SELECT user_id, username, user_type, created_at FROM users ORDER BY username");
?>

<h1 class="page-title">User Management</h1>

<div class="card form-card">
    <h3>Add New User</h3>
    <form method="post" action="users.php" class="inline-form">
        <input type="hidden" name="action" value="add">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <select name="user_type">
            <option value="User">User</option>
            <option value="Admin">Admin</option>
        </select>
        <button type="submit" class="btn btn-primary">Add User</button>
    </form>
</div>

<div class="card">
    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Username</th>
                <th>User Type</th>
                <th>Created</th>
                <th class="no-print">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($rows->num_rows === 0) : ?>
                <tr><td colspan="5" class="empty">No users found.</td></tr>
            <?php else : ?>
                <?php $i = 1; while ($row = $rows->fetch_assoc()) : ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= e($row['username']) ?><?= (int)$row['user_id'] === (int)$_SESSION['user_id'] ? ' <small>(you)</small>' : '' ?></td>
                        <td><span class="badge <?= $row['user_type'] === 'Admin' ? 'badge-admin' : 'badge-user' ?>"><?= e($row['user_type']) ?></span></td>
                        <td><?= e(date('d M Y', strtotime($row['created_at']))) ?></td>
                        <td class="no-print actions">
                            <form method="post" action="users.php" class="inline-form">
                                <input type="hidden" name="action" value="reset">
                                <input type="hidden" name="user_id" value="<?= (int)$row['user_id'] ?>">
                                <input type="password" name="password" placeholder="New password" required>
                                <button type="submit" class="btn btn-small">Reset Password</button>
                            </form>
                            <form method="post" action="users.php" data-confirm="Delete user <?= e($row['username']) ?>?">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="user_id" value="<?= (int)$row['user_id'] ?>">
                                <button type="submit" class="btn btn-small btn-danger" <?= (int)$row['user_id'] === (int)$_SESSION['user_id'] ? 'disabled' : '' ?>>Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require 'includes/footer.php'; ?>