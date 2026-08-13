<?php
$page_title = 'Dashboard';
$active = 'dashboard';

require 'config/db.php';
require 'includes/auth.php';
require 'includes/header.php';

$month = (int)date('n');
$year = (int)date('Y');
if (isset($_GET['month'], $_GET['year'])) {
    $month = (int)$_GET['month'];
    $year = (int)$_GET['year'];
}

$totalEmployees = (int)$conn->query("SELECT COUNT(*) AS c FROM employees")->fetch_assoc()['c'];
$totalDepartments = (int)$conn->query("SELECT COUNT(*) AS c FROM departments")->fetch_assoc()['c'];

$stmt = $conn->prepare("SELECT COALESCE(SUM(gross_salary),0) AS gross, COALESCE(SUM(total_deduction),0) AS ded, COALESCE(SUM(net_salary),0) AS net, COUNT(*) AS c FROM payroll WHERE payroll_month = ? AND payroll_year = ?");
$stmt->bind_param('ii', $month, $year);
$stmt->execute();
$summary = $stmt->get_result()->fetch_assoc();

$recentEmployees = $conn->query("SELECT e.*, d.dept_name FROM employees e LEFT JOIN departments d ON e.department_id = d.dept_id ORDER BY e.created_at DESC LIMIT 5");

$recentPayroll = $conn->prepare("SELECT p.*, e.full_name, e.staff_id FROM payroll p JOIN employees e ON p.employee_id = e.employee_id WHERE p.payroll_month = ? AND p.payroll_year = ? ORDER BY p.net_salary DESC LIMIT 5");
$recentPayroll->bind_param('ii', $month, $year);
$recentPayroll->execute();
$payrollRows = $recentPayroll->get_result();
?>

<h1 class="page-title">Dashboard</h1>

<?php if ($totalEmployees === 0) : ?>
    <div class="card welcome-card">
        <h3>Welcome to the Staff Payroll System</h3>
        <p>Follow these steps to get started, or read the <a href="guide.php">User Guide</a> for full instructions:</p>
        <ol class="guide-list">
            <li>Add departments on the <a href="departments.php">Departments</a> page.</li>
            <li>Register your staff on the <a href="employees.php">Employees</a> page.</li>
            <li>Set allowances and deductions for each employee.</li>
            <li>Run payroll from the <a href="payroll.php">Payroll</a> page.</li>
            <li>Print payslips and view reports.</li>
        </ol>
    </div>
<?php endif; ?>

<div class="period-form">
    <form method="get" action="dashboard.php" class="inline-form">
        <label>Period:
            <select name="month">
                <?php for ($m = 1; $m <= 12; $m++) : ?>
                    <option value="<?= $m ?>" <?= $m === $month ? 'selected' : '' ?>><?= month_name($m) ?></option>
                <?php endfor; ?>
            </select>
            <select name="year">
                <?php for ($y = $year - 1; $y <= $year + 1; $y++) : ?>
                    <option value="<?= $y ?>" <?= $y === $year ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
        </label>
        <button type="submit" class="btn btn-small">View</button>
    </form>
</div>

<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-value"><?= $totalEmployees ?></div>
        <div class="stat-label">Total Employees</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $totalDepartments ?></div>
        <div class="stat-label">Departments</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= money($summary['gross']) ?></div>
        <div class="stat-label">Gross Payroll (<?= month_name($month) ?>)</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= money($summary['net']) ?></div>
        <div class="stat-label">Net Payroll (<?= month_name($month) ?>)</div>
    </div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-header">
            <h3>Salary Summary &mdash; <?= month_name($month) ?> <?= $year ?></h3>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="align-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Total Gross Salary</td>
                    <td class="align-right"><?= money($summary['gross']) ?></td>
                </tr>
                <tr>
                    <td>Total Deductions</td>
                    <td class="align-right"><?= money($summary['ded']) ?></td>
                </tr>
                <tr class="total-row">
                    <td>Net Payroll (<?= $summary['c'] ?> staff)</td>
                    <td class="align-right"><?= money($summary['net']) ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Recently Registered Employees</h3>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Staff ID</th>
                    <th>Name</th>
                    <th>Department</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($recentEmployees->num_rows === 0) : ?>
                    <tr><td colspan="3" class="empty">No employees registered yet.</td></tr>
                <?php else : ?>
                    <?php while ($row = $recentEmployees->fetch_assoc()) : ?>
                        <tr>
                            <td><?= e($row['staff_id']) ?></td>
                            <td><?= e($row['full_name']) ?></td>
                            <td><?= e($row['dept_name'] ?? '-') ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Top Staff by Net Salary &mdash; <?= month_name($month) ?> <?= $year ?></h3>
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Staff ID</th>
                <th>Employee</th>
                <th class="align-right">Gross Salary</th>
                <th class="align-right">Deductions</th>
                <th class="align-right">Net Salary</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($payrollRows->num_rows === 0) : ?>
                <tr><td colspan="5" class="empty">No payroll records for this period yet. Run payroll from the <a href="payroll.php">Payroll</a> page.</td></tr>
            <?php else : ?>
                <?php while ($row = $payrollRows->fetch_assoc()) : ?>
                    <tr>
                        <td><?= e($row['staff_id']) ?></td>
                        <td><?= e($row['full_name']) ?></td>
                        <td class="align-right"><?= money($row['gross_salary']) ?></td>
                        <td class="align-right"><?= money($row['total_deduction']) ?></td>
                        <td class="align-right"><?= money($row['net_salary']) ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require 'includes/footer.php'; ?>