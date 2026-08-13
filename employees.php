<?php
$page_title = 'Employees';
$active = 'employees';

require 'config/db.php';
require 'includes/auth.php';
require 'includes/header.php';

$q = trim($_GET['q'] ?? '');
if ($q !== '') {
    $like = '%' . $q . '%';
    $stmt = $conn->prepare("SELECT e.*, d.dept_name FROM employees e LEFT JOIN departments d ON e.department_id = d.dept_id WHERE e.full_name LIKE ? OR e.staff_id LIKE ? OR e.position LIKE ? ORDER BY e.full_name");
    $stmt->bind_param('sss', $like, $like, $like);
    $stmt->execute();
    $rows = $stmt->get_result();
} else {
    $rows = $conn->query("SELECT e.*, d.dept_name FROM employees e LEFT JOIN departments d ON e.department_id = d.dept_id ORDER BY e.full_name");
}
?>

<h1 class="page-title">Employee Records</h1>

<div class="toolbar">
    <a href="employee_form.php" class="btn btn-primary">+ Add Employee</a>
    <form method="get" action="employees.php" class="inline-form">
        <input type="text" name="q" placeholder="Search name, staff ID or position..." value="<?= e($q) ?>">
        <button type="submit" class="btn btn-small">Search</button>
    </form>
</div>

<div class="card">
    <table class="data-table" id="employee-table">
        <thead>
            <tr>
                <th>Staff ID</th>
                <th>Full Name</th>
                <th>Gender</th>
                <th>Department</th>
                <th>Position</th>
                <th>Phone</th>
                <th class="align-right">Basic Salary</th>
                <th class="no-print">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($rows->num_rows === 0) : ?>
                <tr><td colspan="8" class="empty">No employee records found.</td></tr>
            <?php else : ?>
                <?php while ($row = $rows->fetch_assoc()) : ?>
                    <tr>
                        <td><?= e($row['staff_id']) ?></td>
                        <td><?= e($row['full_name']) ?></td>
                        <td><?= e($row['gender']) ?></td>
                        <td><?= e($row['dept_name'] ?? '-') ?></td>
                        <td><?= e($row['position']) ?></td>
                        <td><?= e($row['phone'] ?? '-') ?></td>
                        <td class="align-right"><?= money($row['basic_salary']) ?></td>
                        <td class="no-print actions">
                            <a class="btn btn-small" href="employee_form.php?id=<?= (int)$row['employee_id'] ?>">Edit</a>
                            <a class="btn btn-small" href="allowances.php?employee_id=<?= (int)$row['employee_id'] ?>">Allowances</a>
                            <a class="btn btn-small" href="deductions.php?employee_id=<?= (int)$row['employee_id'] ?>">Deductions</a>
                            <form method="post" action="employee_delete.php" data-confirm="Delete employee <?= e($row['full_name']) ?>? This will also remove their payroll records.">
                                <input type="hidden" name="employee_id" value="<?= (int)$row['employee_id'] ?>">
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