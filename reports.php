<?php
$page_title = 'Reports';
$active = 'reports';

require 'config/db.php';
require 'includes/auth.php';
require 'includes/header.php';

$report = $_GET['report'] ?? 'monthly';
$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('n');
$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
if ($month < 1 || $month > 12) {
    $month = (int)date('n');
}

$selectedEmployee = isset($_GET['employee_id']) ? (int)$_GET['employee_id'] : 0;
?>

<h1 class="page-title">Payroll Reports</h1>

<div class="card form-card">
    <form method="get" action="reports.php" class="inline-form">
        <label>Report:
            <select name="report">
                <option value="monthly" <?= $report === 'monthly' ? 'selected' : '' ?>>Monthly Payroll Report</option>
                <option value="staff" <?= $report === 'staff' ? 'selected' : '' ?>>Staff Payroll Report</option>
                <option value="department" <?= $report === 'department' ? 'selected' : '' ?>>Department Salary Summary</option>
                <option value="employee_list" <?= $report === 'employee_list' ? 'selected' : '' ?>>Employee List</option>
            </select>
        </label>
        <?php if ($report !== 'employee_list') : ?>
            <label>Month:
                <select name="month">
                    <?php for ($m = 1; $m <= 12; $m++) : ?>
                        <option value="<?= $m ?>" <?= $m === $month ? 'selected' : '' ?>><?= month_name($m) ?></option>
                    <?php endfor; ?>
                </select>
            </label>
            <label>Year:
                <select name="year">
                    <?php for ($y = date('Y') - 1; $y <= date('Y') + 2; $y++) : ?>
                        <option value="<?= $y ?>" <?= $y === $year ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </label>
        <?php endif; ?>
        <?php if ($report === 'staff') : ?>
            <label>Employee:
                <select name="employee_id">
                    <option value="">-- All Employees --</option>
                    <?php
                    $emps = $conn->query("SELECT employee_id, staff_id, full_name FROM employees ORDER BY full_name");
                    while ($emp = $emps->fetch_assoc()) : ?>
                        <option value="<?= (int)$emp['employee_id'] ?>" <?= $selectedEmployee === (int)$emp['employee_id'] ? 'selected' : '' ?>>
                            <?= e($emp['full_name']) ?> (<?= e($emp['staff_id']) ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </label>
        <?php endif; ?>
        <button type="submit" class="btn btn-primary">Generate Report</button>
    </form>
</div>

<div class="toolbar no-print">
    <button onclick="window.print()" class="btn">Print Report</button>
</div>

<?php if ($report === 'monthly') : ?>
    <div class="card report-card">
        <div class="report-header">
            <h2><?= e(ORG_NAME) ?></h2>
            <h3>Monthly Payroll Report &mdash; <?= month_name($month) ?> <?= $year ?></h3>
        </div>
        <?php
        $stmt = $conn->prepare("SELECT p.*, e.staff_id, e.full_name, d.dept_name
            FROM payroll p JOIN employees e ON p.employee_id = e.employee_id
            LEFT JOIN departments d ON e.department_id = d.dept_id
            WHERE p.payroll_month = ? AND p.payroll_year = ?
            ORDER BY d.dept_name, e.full_name");
        $stmt->bind_param('ii', $month, $year);
        $stmt->execute();
        $rows = $stmt->get_result();
        $totG = 0; $totD = 0; $totN = 0;
        $list = [];
        while ($r = $rows->fetch_assoc()) {
            $list[] = $r;
            $totG += $r['gross_salary'];
            $totD += $r['total_deduction'];
            $totN += $r['net_salary'];
        }
        ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Staff ID</th>
                    <th>Employee</th>
                    <th>Department</th>
                    <th class="align-right">Basic</th>
                    <th class="align-right">Allowance</th>
                    <th class="align-right">Gross</th>
                    <th class="align-right">Deductions</th>
                    <th class="align-right">Net Salary</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($list) === 0) : ?>
                    <tr><td colspan="9" class="empty">No payroll records for this period.</td></tr>
                <?php else : ?>
                    <?php $i = 1; foreach ($list as $r) : ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= e($r['staff_id']) ?></td>
                            <td><?= e($r['full_name']) ?></td>
                            <td><?= e($r['dept_name'] ?? '-') ?></td>
                            <td class="align-right"><?= money($r['basic_salary']) ?></td>
                            <td class="align-right"><?= money($r['total_allowance']) ?></td>
                            <td class="align-right"><?= money($r['gross_salary']) ?></td>
                            <td class="align-right"><?= money($r['total_deduction']) ?></td>
                            <td class="align-right"><?= money($r['net_salary']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <?php if (count($list) > 0) : ?>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="4">TOTAL (<?= count($list) ?> staff)</td>
                        <td class="align-right"></td>
                        <td class="align-right"></td>
                        <td class="align-right"><?= money($totG) ?></td>
                        <td class="align-right"><?= money($totD) ?></td>
                        <td class="align-right"><?= money($totN) ?></td>
                    </tr>
                </tfoot>
            <?php endif; ?>
        </table>
    </div>

<?php elseif ($report === 'staff') : ?>
    <div class="card report-card">
        <div class="report-header">
            <h2><?= e(ORG_NAME) ?></h2>
            <h3>Staff Payroll Report<?= $selectedEmployee ? ' &mdash; Selected Employee' : ' &mdash; All Staff' ?></h3>
        </div>
        <?php
        if ($selectedEmployee > 0) {
            $stmt = $conn->prepare("SELECT p.*, e.staff_id, e.full_name, d.dept_name
                FROM payroll p JOIN employees e ON p.employee_id = e.employee_id
                LEFT JOIN departments d ON e.department_id = d.dept_id
                WHERE p.employee_id = ? AND p.payroll_month = ? AND p.payroll_year = ?
                ORDER BY p.payroll_year, p.payroll_month");
            $stmt->bind_param('iii', $selectedEmployee, $month, $year);
        } else {
            $stmt = $conn->prepare("SELECT p.*, e.staff_id, e.full_name, d.dept_name
                FROM payroll p JOIN employees e ON p.employee_id = e.employee_id
                LEFT JOIN departments d ON e.department_id = d.dept_id
                WHERE p.payroll_month = ? AND p.payroll_year = ?
                ORDER BY e.full_name");
            $stmt->bind_param('ii', $month, $year);
        }
        $stmt->execute();
        $rows = $stmt->get_result();
        ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Staff ID</th>
                    <th>Employee</th>
                    <th>Department</th>
                    <th class="align-right">Gross Salary</th>
                    <th class="align-right">Deductions</th>
                    <th class="align-right">Net Salary</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($rows->num_rows === 0) : ?>
                    <tr><td colspan="6" class="empty">No payroll records found.</td></tr>
                <?php else : ?>
                    <?php while ($r = $rows->fetch_assoc()) : ?>
                        <tr>
                            <td><?= e($r['staff_id']) ?></td>
                            <td><?= e($r['full_name']) ?></td>
                            <td><?= e($r['dept_name'] ?? '-') ?></td>
                            <td class="align-right"><?= money($r['gross_salary']) ?></td>
                            <td class="align-right"><?= money($r['total_deduction']) ?></td>
                            <td class="align-right"><?= money($r['net_salary']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

<?php elseif ($report === 'department') : ?>
    <div class="card report-card">
        <div class="report-header">
            <h2><?= e(ORG_NAME) ?></h2>
            <h3>Department Salary Summary &mdash; <?= month_name($month) ?> <?= $year ?></h3>
        </div>
        <?php
        $stmt = $conn->prepare("SELECT d.dept_name, COUNT(e.employee_id) AS staff_count,
            COALESCE(SUM(p.gross_salary),0) AS gross, COALESCE(SUM(p.total_deduction),0) AS ded, COALESCE(SUM(p.net_salary),0) AS net
            FROM departments d
            LEFT JOIN employees e ON e.department_id = d.dept_id
            LEFT JOIN payroll p ON p.employee_id = e.employee_id AND p.payroll_month = ? AND p.payroll_year = ?
            GROUP BY d.dept_id ORDER BY d.dept_name");
        $stmt->bind_param('ii', $month, $year);
        $stmt->execute();
        $rows = $stmt->get_result();
        $totG = 0; $totD = 0; $totN = 0; $totC = 0;
        $list = [];
        while ($r = $rows->fetch_assoc()) {
            $list[] = $r;
            $totG += $r['gross'];
            $totD += $r['ded'];
            $totN += $r['net'];
            $totC += $r['staff_count'];
        }
        ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Department</th>
                    <th class="align-center">Staff</th>
                    <th class="align-right">Total Gross</th>
                    <th class="align-right">Total Deductions</th>
                    <th class="align-right">Total Net</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($list) === 0) : ?>
                    <tr><td colspan="5" class="empty">No departments available.</td></tr>
                <?php else : ?>
                    <?php foreach ($list as $r) : ?>
                        <tr>
                            <td><?= e($r['dept_name']) ?></td>
                            <td class="align-center"><?= (int)$r['staff_count'] ?></td>
                            <td class="align-right"><?= money($r['gross']) ?></td>
                            <td class="align-right"><?= money($r['ded']) ?></td>
                            <td class="align-right"><?= money($r['net']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <?php if (count($list) > 0) : ?>
                <tfoot>
                    <tr class="total-row">
                        <td>TOTAL</td>
                        <td class="align-center"><?= $totC ?></td>
                        <td class="align-right"><?= money($totG) ?></td>
                        <td class="align-right"><?= money($totD) ?></td>
                        <td class="align-right"><?= money($totN) ?></td>
                    </tr>
                </tfoot>
            <?php endif; ?>
        </table>
    </div>

<?php else : ?>
    <div class="card report-card">
        <?php
        $empTotal = (int)$conn->query("SELECT COUNT(*) AS c FROM employees")->fetch_assoc()['c'];
        $rows = $conn->query("SELECT e.*, d.dept_name FROM employees e LEFT JOIN departments d ON e.department_id = d.dept_id ORDER BY e.full_name");
        ?>
        <div class="report-header">
            <h2><?= e(ORG_NAME) ?></h2>
            <h3>Employee List (<?= $empTotal ?> staff)</h3>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Staff ID</th>
                    <th>Full Name</th>
                    <th>Gender</th>
                    <th>Department</th>
                    <th>Position</th>
                    <th>Phone</th>
                    <th class="align-right">Basic Salary</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($rows->num_rows === 0) : ?>
                    <tr><td colspan="7" class="empty">No employees registered.</td></tr>
                <?php else : ?>
                    <?php while ($r = $rows->fetch_assoc()) : ?>
                        <tr>
                            <td><?= e($r['staff_id']) ?></td>
                            <td><?= e($r['full_name']) ?></td>
                            <td><?= e($r['gender']) ?></td>
                            <td><?= e($r['dept_name'] ?? '-') ?></td>
                            <td><?= e($r['position']) ?></td>
                            <td><?= e($r['phone'] ?? '-') ?></td>
                            <td class="align-right"><?= money($r['basic_salary']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require 'includes/footer.php'; ?>