<?php
$page_title = 'User Guide';
$active = 'guide';

require 'config/db.php';
require 'includes/auth.php';
require 'includes/header.php';
?>

<h1 class="page-title">User Guide</h1>

<div class="card">
    <div class="card-header"><h3>Getting Started</h3></div>
    <ol class="guide-list">
        <li><strong>Login</strong> &mdash; Enter your username and password on the login page. Only authorised users can access the system.</li>
        <li><strong>Add Departments</strong> &mdash; Visit <a href="departments.php">Departments</a> to create departments such as Finance or Human Resources before registering staff.</li>
        <li><strong>Register Employees</strong> &mdash; Use <a href="employees.php">Employees</a> to add staff records (Staff ID, name, gender, department, position, phone, basic salary).</li>
        <li><strong>Set Allowances</strong> &mdash; Go to <a href="allowances.php">Allowances</a> to enter housing, transport, medical, utility and other allowances for each employee.</li>
        <li><strong>Set Deductions</strong> &mdash; Go to <a href="deductions.php">Deductions</a> to enter tax, pension, loan, cooperative and other deductions for each employee.</li>
        <li><strong>Run Payroll</strong> &mdash; On <a href="payroll.php">Payroll</a>, choose a month and year, then click <em>Run Payroll</em>. The system calculates gross and net salary for every employee automatically.</li>
        <li><strong>Print Payslips</strong> &mdash; From the payroll table, click <em>Payslip</em> to view or print an employee's payslip for the period.</li>
        <li><strong>View Reports</strong> &mdash; Use <a href="reports.php">Reports</a> for the monthly payroll report, staff payroll history, department salary summary and employee list.</li>
        <li><strong>Backup Data</strong> &mdash; Administrators can download a database backup from the <a href="backup.php">Backup</a> page.</li>
    </ol>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-header"><h3>Navigation Map</h3></div>
        <table class="data-table">
            <thead>
                <tr><th>Menu</th><th>What it does</th></tr>
            </thead>
            <tbody>
                <tr><td><strong>Dashboard</strong></td><td>System overview, employee count and monthly salary summary.</td></tr>
                <tr><td><strong>Employees</strong></td><td>Add, edit, search and delete employee records.</td></tr>
                <tr><td><strong>Departments</strong></td><td>Add, rename and delete departments.</td></tr>
                <tr><td><strong>Allowances</strong></td><td>Set housing, transport, medical, utility and other allowances per staff.</td></tr>
                <tr><td><strong>Deductions</strong></td><td>Set tax, pension, loan, cooperative and other deductions per staff.</td></tr>
                <tr><td><strong>Payroll</strong></td><td>Compute gross and net salary for any month, view records, generate payslips.</td></tr>
                <tr><td><strong>Reports</strong></td><td>Monthly payroll report, staff payroll report, department summary and employee list.</td></tr>
                <tr><td><strong>Users</strong> (Admin)</td><td>Create and manage system login accounts.</td></tr>
                <tr><td><strong>Backup</strong> (Admin)</td><td>Download a database backup file.</td></tr>
                <tr><td><strong>Logout</strong></td><td>End your session securely.</td></tr>
            </tbody>
        </table>
    </div>

    <div class="card">
        <div class="card-header"><h3>How Salary is Calculated</h3></div>
        <table class="data-table">
            <tbody>
                <tr>
                    <td>Gross Salary</td>
                    <td>= Basic Salary + Total Allowances</td>
                </tr>
                <tr>
                    <td>Total Deductions</td>
                    <td>= Tax + Pension + Loan + Cooperative + Other</td>
                </tr>
                <tr class="total-row">
                    <td><strong>Net Salary</strong></td>
                    <td><strong>= Gross Salary &minus; Total Deductions</strong></td>
                </tr>
            </tbody>
        </table>
        <h3 style="margin-top:1.5rem;">Workflow</h3>
        <p class="hint">Register staff &rarr; set allowances &rarr; set deductions &rarr; run monthly payroll &rarr; print payslips &rarr; generate reports.</p>
        <h3 style="margin-top:1.5rem;">Tips</h3>
        <ul class="guide-list">
            <li>Regularly backup the database using the <a href="backup.php">Backup</a> page.</li>
            <li>Running payroll again for the same month recalculates and updates that period's records.</li>
            <li>Use the <a href="login.php">login</a> page with your administrator credentials to manage users.</li>
        </ul>
    </div>
</div>

<?php require 'includes/footer.php'; ?>