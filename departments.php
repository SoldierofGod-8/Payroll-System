<?php
$page_title = 'Departments';
$active = 'departments';

require 'config/db.php';
require 'includes/auth.php';
require 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name = trim($_POST['dept_name'] ?? '');
        if ($name === '') {
            flash_set('error', 'Department name is required.');
        } else {
            try {
                $stmt = $conn->prepare("INSERT INTO departments (dept_name) VALUES (?)");
                $stmt->bind_param('s', $name);
                $stmt->execute();
                flash_set('success', 'Department added successfully.');
            } catch (mysqli_sql_exception $ex) {
                flash_set('error', 'Department already exists.');
            }
        }
    } elseif ($action === 'rename') {
        $dept_id = (int)($_POST['dept_id'] ?? 0);
        $name = trim($_POST['dept_name'] ?? '');
        if ($dept_id > 0 && $name !== '') {
            try {
                $stmt = $conn->prepare("UPDATE departments SET dept_name = ? WHERE dept_id = ?");
                $stmt->bind_param('si', $name, $dept_id);
                $stmt->execute();
                flash_set('success', 'Department updated successfully.');
            } catch (mysqli_sql_exception $ex) {
                flash_set('error', 'Department name already exists.');
            }
        }
    } elseif ($action === 'delete') {
        $dept_id = (int)($_POST['dept_id'] ?? 0);
        if ($dept_id > 0) {
            $stmt = $conn->prepare("DELETE FROM departments WHERE dept_id = ?");
            $stmt->bind_param('i', $dept_id);
            $stmt->execute();
            flash_set('success', 'Department deleted successfully.');
        }
    }
    header('Location: departments.php');
    exit;
}

$rows = $conn->query("SELECT d.*, (SELECT COUNT(*) FROM employees e WHERE e.department_id = d.dept_id) AS staff_count FROM departments d ORDER BY d.dept_name");
?>

<h1 class="page-title">Department Management</h1>

<div class="card form-card">
    <h3>Add New Department</h3>
    <form method="post" action="departments.php" class="inline-form">
        <input type="hidden" name="action" value="add">
        <input type="text" name="dept_name" placeholder="Department name" required>
        <button type="submit" class="btn btn-primary">Add Department</button>
    </form>
</div>

<div class="card">
    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Department</th>
                <th class="align-center">Staff Count</th>
                <th class="no-print">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($rows->num_rows === 0) : ?>
                <tr><td colspan="4" class="empty">No departments yet.</td></tr>
            <?php else : ?>
                <?php $i = 1; while ($row = $rows->fetch_assoc()) : ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= e($row['dept_name']) ?></td>
                        <td class="align-center"><?= (int)$row['staff_count'] ?></td>
                        <td class="no-print actions">
                            <form method="post" action="departments.php" class="inline-form">
                                <input type="hidden" name="action" value="rename">
                                <input type="hidden" name="dept_id" value="<?= (int)$row['dept_id'] ?>">
                                <input type="text" name="dept_name" value="<?= e($row['dept_name']) ?>" required>
                                <button type="submit" class="btn btn-small">Save</button>
                            </form>
                            <form method="post" action="departments.php" data-confirm="Delete department <?= e($row['dept_name']) ?>?">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="dept_id" value="<?= (int)$row['dept_id'] ?>">
                                <button type="submit" class="btn btn-small btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require 'includes/footer.php'; ?>