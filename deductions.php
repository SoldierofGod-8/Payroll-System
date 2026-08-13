<?php
$page_title = 'Deductions';
$active = 'deductions';

require 'config/db.php';
require 'includes/auth.php';
require 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_id = (int)($_POST['employee_id'] ?? 0);
    $tax = (float)($_POST['tax'] ?? 0);
    $pension = (float)($_POST['pension'] ?? 0);
    $loan = (float)($_POST['loan'] ?? 0);
    $cooperative = (float)($_POST['cooperative'] ?? 0);
    $other = (float)($_POST['other'] ?? 0);

    if ($employee_id > 0) {
        $stmt = $conn->prepare("INSERT INTO deductions (employee_id, tax, pension, loan, cooperative, other)
            VALUES (?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE tax = VALUES(tax), pension = VALUES(pension), loan = VALUES(loan), cooperative = VALUES(cooperative), other = VALUES(other)");
        $stmt->bind_param('iddddd', $employee_id, $tax, $pension, $loan, $cooperative, $other);
        $stmt->execute();
        flash_set('success', 'Deduction record saved successfully.');
    }
    header('Location: deductions.php');
    exit;
}

$rows = $conn->query("SELECT e.employee_id, e.staff_id, e.full_name,
    COALESCE(d.tax, 0) AS tax, COALESCE(d.pension, 0) AS pension,
    COALESCE(d.loan, 0) AS loan, COALESCE(d.cooperative, 0) AS cooperative, COALESCE(d.other, 0) AS other
    FROM employees e LEFT JOIN deductions d ON d.employee_id = e.employee_id
    ORDER BY e.full_name");
?>

<h1 class="page-title">Deduction Management</h1>

<div class="card">
    <table class="data-table">
        <thead>
            <tr>
                <th>Staff</th>
                <th>Tax</th>
                <th>Pension</th>
                <th>Loan</th>
                <th>Cooperative</th>
                <th>Other</th>
                <th class="align-right">Total</th>
                <th class="no-print">Save</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($rows->num_rows === 0) : ?>
                <tr><td colspan="8" class="empty">No employees registered yet.</td></tr>
            <?php else : ?>
                <?php while ($row = $rows->fetch_assoc()) : ?>
                    <?php $fid = 'ded-form-' . $row['employee_id']; ?>
                    <?php $total = $row['tax'] + $row['pension'] + $row['loan'] + $row['cooperative'] + $row['other']; ?>
                    <tr>
                        <td>
                            <strong><?= e($row['full_name']) ?></strong><br>
                            <small><?= e($row['staff_id']) ?></small>
                        </td>
                        <td><input type="number" step="0.01" min="0" name="tax" form="<?= $fid ?>" value="<?= e($row['tax']) ?>"></td>
                        <td><input type="number" step="0.01" min="0" name="pension" form="<?= $fid ?>" value="<?= e($row['pension']) ?>"></td>
                        <td><input type="number" step="0.01" min="0" name="loan" form="<?= $fid ?>" value="<?= e($row['loan']) ?>"></td>
                        <td><input type="number" step="0.01" min="0" name="cooperative" form="<?= $fid ?>" value="<?= e($row['cooperative']) ?>"></td>
                        <td><input type="number" step="0.01" min="0" name="other" form="<?= $fid ?>" value="<?= e($row['other']) ?>"></td>
                        <td class="align-right"><strong><?= money($total) ?></strong></td>
                        <td class="no-print"><button type="submit" class="btn btn-small btn-primary" form="<?= $fid ?>">Save</button></td>
                    </tr>
                    <form id="<?= $fid ?>" method="post" action="deductions.php" hidden>
                        <input type="hidden" name="employee_id" value="<?= (int)$row['employee_id'] ?>">
                    </form>
                <?php endwhile; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require 'includes/footer.php'; ?>