<?php
$page_title = 'Payroll Processing';
$active = 'payroll';

require 'config/db.php';
require 'includes/auth.php';
require 'includes/header.php';

$month = (int)date('n');
$year = (int)date('Y');
if (isset($_GET['month'], $_GET['year'])) {
    $month = (int)$_GET['month'];
    $year = (int)$_GET['year'];
}
if ($month < 1 || $month > 12) {
    $month = (int)date('n');
}
if ($year < 2000 || $year > 2100) {
    $year = (int)date('Y');
}

$processed = 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_payroll'])) {
    $month = (int)($_POST['month'] ?? $month);
    $year = (int)($_POST['year'] ?? $year);

    $stmt = $conn->prepare("SELECT e.employee_id, e.basic_salary,
        COALESCE(a.housing, 0) + COALESCE(a.transport, 0) + COALESCE(a.medical, 0) + COALESCE(a.utility, 0) + COALESCE(a.other, 0) AS total_allowance,
        COALESCE(d.tax, 0) + COALESCE(d.pension, 0) + COALESCE(d.loan, 0) + COALESCE(d.cooperative, 0) + COALESCE(d.other, 0) AS total_deduction
        FROM employees e
        LEFT JOIN allowances a ON a.employee_id = e.employee_id
        LEFT JOIN deductions d ON d.employee_id = e.employee_id
        ORDER BY e.full_name");
    $stmt->execute();
    $result = $stmt->get_result();

    $upsert = $conn->prepare("INSERT INTO payroll (employee_id, payroll_month, payroll_year, basic_salary, total_allowance, gross_salary, total_deduction, net_salary)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE basic_salary = VALUES(basic_salary), total_allowance = VALUES(total_allowance), gross_salary = VALUES(gross_salary), total_deduction = VALUES(total_deduction), net_salary = VALUES(net_salary)");

    while ($row = $result->fetch_assoc()) {
        $gross = $row['basic_salary'] + $row['total_allowance'];
        $net = $gross - $row['total_deduction'];
        $upsert->bind_param('iisddddd', $row['employee_id'], $month, $year, $row['basic_salary'], $row['total_allowance'], $gross, $row['total_deduction'], $net);
        $upsert->execute();
        $processed++;
    }
    flash_set('success', 'Payroll processed for ' . $processed . ' employee(s) in ' . month_name($month) . ' ' . $year . '.');
    header('Location: payroll.php?month=' . $month . '&year=' . $year);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_payroll_id'])) {
    $delete_id = (int)$_POST['delete_payroll_id'];
    $stmt = $conn->prepare("DELETE FROM payroll WHERE payroll_id = ?");
    $stmt->bind_param('i', $delete_id);
    $stmt->execute();
    flash_set('success', 'Payroll record deleted.');
    header('Location: payroll.php?month=' . $month . '&year=' . $year);
    exit;
}

$stmt = $conn->prepare("SELECT p.*, e.staff_id, e.full_name, d.dept_name
    FROM payroll p
    JOIN employees e ON p.employee_id = e.employee_id
    LEFT JOIN departments d ON e.department_id = d.dept_id
    WHERE p.payroll_month = ? AND p.payroll_year = ?
    ORDER BY e.full_name");
$stmt->bind_param('ii', $month, $year);
$stmt->execute();
$records = $stmt->get_result();

$totalGross = 0;
$totalDed = 0;
$totalNet = 0;
$recordList = [];
while ($r = $records->fetch_assoc()) {
    $recordList[] = $r;
    $totalGross += $r['gross_salary'];
    $totalDed += $r['total_deduction'];
    $totalNet += $r['net_salary'];
}
?>

<h1 class="page-title">Payroll Processing</h1>

<div class="card form-card">
    <h3>Run Payroll for a Period</h3>
    <form method="post" action="payroll.php" class="inline-form"
          data-confirm="Run payroll for <?= month_name($month) ?> <?= $year ?>? Existing payroll for this period will be recalculated.">
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
        <button type="submit" name="run_payroll" class="btn btn-primary">Run Payroll</button>
    </form>
    <p class="hint">
        Gross Salary = Basic Salary + Total Allowances &middot; Net Salary = Gross Salary &minus; Total Deductions
    </p>
</div>

<div class="card">
    <div class="card-header">
        <h3>Payroll Records &mdash; <?= month_name($month) ?> <?= $year ?></h3>
        <a class="btn btn-small" href="reports.php?report=monthly&month=<?= $month ?>&year=<?= $year ?>">View Monthly Report</a>
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Staff ID</th>
                <th>Employee</th>
                <th>Department</th>
                <th class="align-right">Basic Salary</th>
                <th class="align-right">Allowances</th>
                <th class="align-right">Gross Salary</th>
                <th class="align-right">Deductions</th>
                <th class="align-right">Net Salary</th>
                <th class="no-print">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($recordList) === 0) : ?>
                <tr><td colspan="9" class="empty">No payroll records for <?= month_name($month) ?> <?= $year ?>. Run payroll to generate records.</td></tr>
            <?php else : ?>
                <?php foreach ($recordList as $r) : ?>
                    <tr>
                        <td><?= e($r['staff_id']) ?></td>
                        <td><?= e($r['full_name']) ?></td>
                        <td><?= e($r['dept_name'] ?? '-') ?></td>
                        <td class="align-right"><?= money($r['basic_salary']) ?></td>
                        <td class="align-right"><?= money($r['total_allowance']) ?></td>
                        <td class="align-right"><?= money($r['gross_salary']) ?></td>
                        <td class="align-right"><?= money($r['total_deduction']) ?></td>
                        <td class="align-right"><strong><?= money($r['net_salary']) ?></strong></td>
                        <td class="no-print actions">
                            <a class="btn btn-small" target="_blank" href="payslip.php?payroll_id=<?= (int)$r['payroll_id'] ?>">Payslip</a>
                            <form method="post" action="payroll.php?month=<?= $month ?>&year=<?= $year ?>"
                                  data-confirm="Delete this payroll record?">
                                <input type="hidden" name="delete_payroll_id" value="<?= (int)$r['payroll_id'] ?>">
                                <button type="submit" class="btn btn-small btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
        <?php if (count($recordList) > 0) : ?>
            <tfoot>
                <tr class="total-row">
                    <td colspan="3">Total (<?= count($recordList) ?> staff)</td>
                    <td class="align-right"></td>
                    <td class="align-right"></td>
                    <td class="align-right"><?= money($totalGross) ?></td>
                    <td class="align-right"><?= money($totalDed) ?></td>
                    <td class="align-right"><?= money($totalNet) ?></td>
                    <td></td>
                </tr>
            </tfoot>
        <?php endif; ?>
    </table>
</div>

<?php require 'includes/footer.php'; ?>