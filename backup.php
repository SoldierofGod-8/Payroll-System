<?php
$page_title = 'Database Backup';
$active = 'backup';

require 'config/db.php';
require 'includes/auth.php';
require_admin();

if (isset($_GET['download'])) {
    $tables = ['users', 'departments', 'employees', 'allowances', 'deductions', 'payroll'];

    $output = "-- Staff Payroll System Database Backup\n";
    $output .= "-- Date: " . date('Y-m-d H:i:s') . "\n";
    $output .= "-- Database: " . DB_NAME . "\n\n";
    $output .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

    foreach (array_reverse($tables) as $table) {
        $output .= "DROP TABLE IF EXISTS `$table`;\n";
    }
    $output .= "\n";

    foreach ($tables as $table) {
        $result = $conn->query("SHOW CREATE TABLE `$table`")->fetch_assoc();
        $output .= $result['Create Table'] . ";\n\n";

        $data = $conn->query("SELECT * FROM `$table`");
        if ($data->num_rows > 0) {
            $cols = [];
            while ($col = $data->fetch_field()) {
                $cols[] = "`" . $col->name . "`";
            }
            $colList = implode(', ', $cols);
            while ($row = $data->fetch_assoc()) {
                $vals = [];
                foreach ($row as $val) {
                    $vals[] = $val === null ? 'NULL' : "'" . $conn->real_escape_string($val) . "'";
                }
                $output .= "INSERT INTO `$table` ($colList) VALUES (" . implode(', ', $vals) . ");\n";
            }
            $output .= "\n";
        }
    }

    $output .= "SET FOREIGN_KEY_CHECKS=1;\n";

    header('Content-Type: application/sql');
    header('Content-Disposition: attachment; filename="payroll_backup_' . date('Ymd_His') . '.sql"');
    header('Content-Length: ' . strlen($output));
    echo $output;
    exit;
}

require 'includes/header.php';
?>

<h1 class="page-title">Database Backup</h1>

<div class="card form-card">
    <h3>Download Database Backup</h3>
    <p>Click the button below to download a complete SQL backup of the payroll database. Store the file in a safe place (e.g. a flash drive or cloud storage).</p>
    <p class="hint">To restore: open phpMyAdmin, select the <?= e(DB_NAME) ?> database, import the downloaded .sql file, and run it.</p>
    <a href="backup.php?download=1" class="btn btn-primary">Download Backup</a>
</div>

<div class="card">
    <div class="card-header"><h3>Backup Tips</h3></div>
    <ul class="guide-list">
        <li>Perform a backup at least once every week.</li>
        <li>Always back up before running payroll or making bulk changes.</li>
        <li>Keep multiple backup files with different dates.</li>
        <li>Backups are stored on this computer until you download and move them to safe storage.</li>
    </ul>
</div>

<?php require 'includes/footer.php'; ?>