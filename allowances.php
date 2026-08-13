<?php
$page_title = 'Allowances';
$active = 'allowances';

require 'config/db.php';
require 'includes/auth.php';
require 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_id = (int)($_POST['employee_id'] ?? 0);
    $housing = (float)($_POST['housing'] ?? 0);
    $transport = (float)($_POST['transport'] ?? 0);
    $medical = (float)($_POST['medical'] ?? 0);
    $utility = (float)($_POST['utility'] ?? 0);
    $other = (float)($_POST['other'] ?? 0);

    if ($employee_id > 0) {
        $stmt = $conn->prepare("INSERT INTO allowances (employee_id, housing, transport, medical, utility, other)
            VALUES (?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE housing = VALUES(housing), transport = VALUES(transport), medical = VALUES(medical), utility = VALUES(utility), other = VALUES(other)");
        $stmt->bind_param('iddddd', $employee_id, $housing, $transport, $medical, $utility, $other);
        $stmt->execute();
        flash_set('success', 'Allowance record saved successfully.');
    }
    header('Location: allowances.php');
    exit;
}

$rows = $conn->query("SELECT e.employee_id, e.staff_id, e.full_name,
    COALESCE(a.housing, 0) AS housing, COALESCE(a.transport, 0) AS transport,
    COALESCE(a.medical, 0) AS medical, COALESCE(a.utility, 0) AS utility, COALESCE(a.other, 0) AS other
    FROM employees e LEFT JOIN allowances a ON a.employee_id = e.employee_id
    ORDER BY e.full_name");
?>

<h1 class="page-title">Allowance Management</h1>

<div class="card">
    <table class="data-table">
        <thead>
            <tr>
                <th>Staff</th>
                <th>Housing</th>
                <th>Transport</th>
                <th>Medical</th>
                <th>Utility</th>
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
                    <?php $fid = 'allow-form-' . $row['employee_id']; ?>
                    <?php $total = $row['housing'] + $row['transport'] + $row['medical'] + $row['utility'] + $row['other']; ?>
                    <tr>
                        <td>
                            <strong><?= e($row['full_name']) ?></strong><br>
                            <small><?= e($row['staff_id']) ?></small>
                        </td>
                        <td><input type="number" step="0.01" min="0" name="housing" form="<?= $fid ?>" value="<?= e($row['housing']) ?>"></td>
                        <td><input type="number" step="0.01" min="0" name="transport" form="<?= $fid ?>" value="<?= e($row['transport']) ?>"></td>
                        <td><input type="number" step="0.01" min="0" name="medical" form="<?= $fid ?>" value="<?= e($row['medical']) ?>"></td>
                        <td><input type="number" step="0.01" min="0" name="utility" form="<?= $fid ?>" value="<?= e($row['utility']) ?>"></td>
                        <td><input type="number" step="0.01" min="0" name="other" form="<?= $fid ?>" value="<?= e($row['other']) ?>"></td>
                        <td class="align-right"><strong><?= money($total) ?></strong></td>
                        <td class="no-print"><button type="submit" class="btn btn-small btn-primary" form="<?= $fid ?>">Save</button></td>
                    </tr>
                    <form id="<?= $fid ?>" method="post" action="allowances.php" hidden>
                        <input type="hidden" name="employee_id" value="<?= (int)$row['employee_id'] ?>">
                    </form>
                <?php endwhile; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require 'includes/footer.php'; ?>