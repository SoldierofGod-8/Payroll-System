<?php
$page_title = 'Employee Registration';
$active = 'employees';

require 'config/db.php';
require 'includes/auth.php';
require 'includes/header.php';

$employee_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$is_edit = $employee_id > 0;

$emp = [
    'staff_id' => '',
    'full_name' => '',
    'gender' => 'Male',
    'department_id' => '',
    'position' => '',
    'phone' => '',
    'basic_salary' => '',
];

if ($is_edit) {
    $stmt = $conn->prepare("SELECT * FROM employees WHERE employee_id = ?");
    $stmt->bind_param('i', $employee_id);
    $stmt->execute();
    $found = $stmt->get_result()->fetch_assoc();
    if (!$found) {
        flash_set('error', 'Employee record not found.');
        header('Location: employees.php');
        exit;
    }
    $emp = $found;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $staff_id = trim($_POST['staff_id'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $gender = $_POST['gender'] ?? 'Male';
    $department_id = (int)($_POST['department_id'] ?? 0);
    $position = trim($_POST['position'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $basic_salary = (float)($_POST['basic_salary'] ?? 0);
    $employee_id = (int)($_POST['employee_id'] ?? 0);

    if ($staff_id === '' || $full_name === '' || $position === '') {
        flash_set('error', 'Staff ID, full name and position are required.');
    } elseif ($basic_salary < 0) {
        flash_set('error', 'Basic salary cannot be negative.');
    } else {
        $exists = false;
        $stmt = $conn->prepare("SELECT COUNT(*) AS c FROM employees WHERE staff_id = ? AND employee_id <> ?");
        $stmt->bind_param('si', $staff_id, $employee_id);
        $stmt->execute();
        $exists = (int)$stmt->get_result()->fetch_assoc()['c'] > 0;

        if ($exists) {
            flash_set('error', 'Staff ID already exists. Please use a unique staff ID.');
        } elseif ($employee_id > 0) {
            $stmt = $conn->prepare("UPDATE employees SET staff_id = ?, full_name = ?, gender = ?, department_id = ?, position = ?, phone = ?, basic_salary = ? WHERE employee_id = ?");
            $dept = $department_id > 0 ? $department_id : null;
            $stmt->bind_param('ssssisdi', $staff_id, $full_name, $gender, $dept, $position, $phone, $basic_salary, $employee_id);
            $stmt->execute();
            flash_set('success', 'Employee record updated successfully.');
            header('Location: employees.php');
            exit;
        } else {
            $stmt = $conn->prepare("INSERT INTO employees (staff_id, full_name, gender, department_id, position, phone, basic_salary) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $dept = $department_id > 0 ? $department_id : null;
            $stmt->bind_param('ssssiss', $staff_id, $full_name, $gender, $dept, $position, $phone, $basic_salary);
            $stmt->execute();
            flash_set('success', 'Employee registered successfully.');
            header('Location: employees.php');
            exit;
        }
    }
}

$depts = $conn->query("SELECT dept_id, dept_name FROM departments ORDER BY dept_name");
?>

<h1 class="page-title"><?= $is_edit ? 'Edit Employee' : 'Register New Employee' ?></h1>

<div class="card form-card">
    <form method="post" action="employee_form.php" autocomplete="off">
        <?php if ($is_edit) : ?>
            <input type="hidden" name="employee_id" value="<?= (int)$emp['employee_id'] ?>">
        <?php endif; ?>

        <div class="form-grid">
            <div>
                <label>Staff ID</label>
                <input type="text" name="staff_id" value="<?= e($emp['staff_id']) ?>" required>
            </div>
            <div>
                <label>Full Name</label>
                <input type="text" name="full_name" value="<?= e($emp['full_name']) ?>" required>
            </div>
            <div>
                <label>Gender</label>
                <select name="gender">
                    <option value="Male" <?= $emp['gender'] === 'Male' ? 'selected' : '' ?>>Male</option>
                    <option value="Female" <?= $emp['gender'] === 'Female' ? 'selected' : '' ?>>Female</option>
                </select>
            </div>
            <div>
                <label>Department</label>
                <select name="department_id">
                    <option value="">-- Select Department --</option>
                    <?php while ($d = $depts->fetch_assoc()) : ?>
                        <option value="<?= (int)$d['dept_id'] ?>" <?= (int)$emp['department_id'] === (int)$d['dept_id'] ? 'selected' : '' ?>>
                            <?= e($d['dept_name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div>
                <label>Position</label>
                <input type="text" name="position" value="<?= e($emp['position']) ?>" required>
            </div>
            <div>
                <label>Phone Number</label>
                <input type="text" name="phone" value="<?= e($emp['phone']) ?>">
            </div>
            <div>
                <label>Basic Salary</label>
                <input type="number" step="0.01" min="0" name="basic_salary" value="<?= e($emp['basic_salary']) ?>" required>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><?= $is_edit ? 'Update' : 'Save' ?></button>
            <a href="employees.php" class="btn">Cancel</a>
        </div>
    </form>
</div>

<?php require 'includes/footer.php'; ?>