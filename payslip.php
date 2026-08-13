<?php
$page_title = 'Payslip';
$active = 'payroll';

require 'config/db.php';
require 'includes/auth.php';

$payroll_id = (int)($_GET['payroll_id'] ?? 0);
if ($payroll_id <= 0) {
    flash_set('error', 'Invalid payslip request.');
    header('Location: payroll.php');
    exit;
}

$stmt = $conn->prepare("SELECT p.*, e.staff_id, e.full_name, e.position, e.gender,
    d.dept_name, COALESCE(a.housing,0) AS housing, COALESCE(a.transport,0) AS transport,
    COALESCE(a.medical,0) AS medical, COALESCE(a.utility,0) AS utility, COALESCE(a.other,0) AS other_allowance,
    COALESCE(dd.tax,0) AS tax, COALESCE(dd.pension,0) AS pension, COALESCE(dd.loan,0) AS loan,
    COALESCE(dd.cooperative,0) AS cooperative, COALESCE(dd.other,0) AS other_deduction
    FROM payroll p
    JOIN employees e ON p.employee_id = e.employee_id
    LEFT JOIN departments d ON e.department_id = d.dept_id
    LEFT JOIN allowances a ON a.employee_id = e.employee_id
    LEFT JOIN deductions dd ON dd.employee_id = e.employee_id
    WHERE p.payroll_id = ?");
$stmt->bind_param('i', $payroll_id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row) {
    flash_set('error', 'Payslip not found.');
    header('Location: payroll.php');
    exit;
}

$totalAllowance = $row['housing'] + $row['transport'] + $row['medical'] + $row['utility'] + $row['other_allowance'];
$totalDeduction = $row['tax'] + $row['pension'] + $row['loan'] + $row['cooperative'] + $row['other_deduction'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip | <?= e(SYS_NAME) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="print-actions no-print">
    <a href="payroll.php" class="btn">&larr; Back to Payroll</a>
    <button onclick="window.print()" class="btn btn-primary">Print Payslip</button>
</div>

<div class="payslip">
    <div class="payslip-header">
        <div class="payslip-logo">&#8358;</div>
        <div>
            <h2><?= e(ORG_NAME) ?></h2>
            <p>Staff Payroll System &middot; Employee Payslip</p>
        </div>
        <div class="payslip-period">
            <strong>Pay Period</strong><br>
            <?= month_name($row['payroll_month']) ?> <?= (int)$row['payroll_year'] ?>
        </div>
    </div>

    <div class="payslip-employee">
        <table class="data-table">
            <tbody>
                <tr>
                    <td><strong>Employee Name:</strong> <?= e($row['full_name']) ?></td>
                    <td><strong>Staff ID:</strong> <?= e($row['staff_id']) ?></td>
                </tr>
                <tr>
                    <td><strong>Department:</strong> <?= e($row['dept_name'] ?? '-') ?></td>
                    <td><strong>Position:</strong> <?= e($row['position']) ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="payslip-sections">
        <div class="payslip-col">
            <h3>Earnings</h3>
            <table class="data-table">
                <tbody>
                    <tr><td>Basic Salary</td><td class="align-right"><?= money($row['basic_salary']) ?></td></tr>
                    <tr><td>Housing Allowance</td><td class="align-right"><?= money($row['housing']) ?></td></tr>
                    <tr><td>Transport Allowance</td><td class="align-right"><?= money($row['transport']) ?></td></tr>
                    <tr><td>Medical Allowance</td><td class="align-right"><?= money($row['medical']) ?></td></tr>
                    <tr><td>Utility Allowance</td><td class="align-right"><?= money($row['utility']) ?></td></tr>
                    <tr><td>Other Allowance</td><td class="align-right"><?= money($row['other_allowance']) ?></td></tr>
                    <tr class="total-row"><td><strong>Total Allowance</strong></td><td class="align-right"><strong><?= money($totalAllowance) ?></strong></td></tr>
                    <tr class="total-row"><td><strong>Gross Salary</strong></td><td class="align-right"><strong><?= money($row['gross_salary']) ?></strong></td></tr>
                </tbody>
            </table>
        </div>
        <div class="payslip-col">
            <h3>Deductions</h3>
            <table class="data-table">
                <tbody>
                    <tr><td>Tax</td><td class="align-right"><?= money($row['tax']) ?></td></tr>
                    <tr><td>Pension</td><td class="align-right"><?= money($row['pension']) ?></td></tr>
                    <tr><td>Loan Repayment</td><td class="align-right"><?= money($row['loan']) ?></td></tr>
                    <tr><td>Cooperative Contribution</td><td class="align-right"><?= money($row['cooperative']) ?></td></tr>
                    <tr><td>Other Deduction</td><td class="align-right"><?= money($row['other_deduction']) ?></td></tr>
                    <tr class="total-row"><td><strong>Total Deductions</strong></td><td class="align-right"><strong><?= money($totalDeduction) ?></strong></td></tr>
                    <tr class="total-row"><td><strong>Net Salary</strong></td><td class="align-right"><strong><?= money($row['net_salary']) ?></strong></td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="payslip-signatures">
        <div>
            <p>_________________________________</p>
            <p>Prepared By (Payroll Officer)</p>
        </div>
        <div>
            <p>_________________________________</p>
            <p>Approved By (Management)</p>
        </div>
        <div class="payslip-net">
            <span>NET PAY</span>
            <strong><?= money($row['net_salary']) ?></strong>
        </div>
    </div>

    <p class="payslip-note">This is a computer-generated payslip and does not require a physical signature.</p>
</div>
</body>
</html>