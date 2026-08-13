(function () {
    'use strict';

    var STORE_KEY = 'payroll_demo_data_v1';

    var seed = {
        departments: ['Administration', 'Finance', 'Human Resources', 'Information Technology', 'Operations'],
        employees: [
            {
                id: 1, staff_id: 'STF001', full_name: 'Adebayo Johnson', gender: 'Male',
                department: 'Administration', position: 'Administrative Manager', phone: '08012345678',
                basic_salary: 300000,
                allowances: { housing: 45000, transport: 20000, medical: 15000, utility: 10000, other: 5000 },
                deductions: { tax: 20000, pension: 18000, loan: 10000, cooperative: 5000, other: 2000 }
            },
            {
                id: 2, staff_id: 'STF002', full_name: 'Fatima Bello', gender: 'Female',
                department: 'Finance', position: 'Accountant', phone: '08023456789',
                basic_salary: 250000,
                allowances: { housing: 35000, transport: 15000, medical: 12000, utility: 8000, other: 5000 },
                deductions: { tax: 16000, pension: 15000, loan: 10000, cooperative: 5000, other: 2000 }
            },
            {
                id: 3, staff_id: 'STF003', full_name: 'Emeka Obi', gender: 'Male',
                department: 'Information Technology', position: 'Software Developer', phone: '08034567890',
                basic_salary: 220000,
                allowances: { housing: 30000, transport: 15000, medical: 10000, utility: 10000, other: 5000 },
                deductions: { tax: 14000, pension: 13000, loan: 10000, cooperative: 4000, other: 1000 }
            },
            {
                id: 4, staff_id: 'STF004', full_name: 'Ngozi Uche', gender: 'Female',
                department: 'Human Resources', position: 'HR Officer', phone: '08045678901',
                basic_salary: 180000,
                allowances: { housing: 25000, transport: 12000, medical: 10000, utility: 5000, other: 3000 },
                deductions: { tax: 12000, pension: 10800, loan: 8000, cooperative: 3000, other: 1000 }
            }
        ]
    };

    function load() {
        try {
            var d = JSON.parse(localStorage.getItem(STORE_KEY));
            if (d && d.employees && d.departments) {
                return d;
            }
        } catch (e) { }
        localStorage.setItem(STORE_KEY, JSON.stringify(seed));
        return seed;
    }

    function save(data) {
        localStorage.setItem(STORE_KEY, JSON.stringify(data));
    }

    function ngn(n) {
        return '\u20A6' + Number(n).toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function escapeHtml(s) {
        return String(s).replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    function totalAllowance(emp) {
        var a = emp.allowances || {};
        return (a.housing || 0) + (a.transport || 0) + (a.medical || 0) + (a.utility || 0) + (a.other || 0);
    }

    function totalDeduction(emp) {
        var d = emp.deductions || {};
        return (d.tax || 0) + (d.pension || 0) + (d.loan || 0) + (d.cooperative || 0) + (d.other || 0);
    }

    /* ---------------- Employees page ---------------- */

    function renderEmployees() {
        var tbody = document.getElementById('employee-tbody');
        var counter = document.getElementById('employee-count');
        if (!tbody) return;
        var store = load();
        var query = (doc('search-input') || {}).value || '';
        query = query.toLowerCase();

        var list = store.employees.filter(function (e) {
            return !query ||
                e.full_name.toLowerCase().indexOf(query) >= 0 ||
                e.staff_id.toLowerCase().indexOf(query) >= 0 ||
                e.position.toLowerCase().indexOf(query) >= 0;
        });

        tbody.innerHTML = '';
        if (list.length === 0) {
            var tr = document.createElement('tr');
            tr.innerHTML = '<td colspan="8" class="empty">No employee records found. Use the form above to add one.</td>';
            tbody.appendChild(tr);
        } else {
            list.forEach(function (e) {
                var tr = document.createElement('tr');
                tr.innerHTML =
                    '<td>' + escapeHtml(e.staff_id) + '</td>' +
                    '<td>' + escapeHtml(e.full_name) + '</td>' +
                    '<td>' + escapeHtml(e.gender) + '</td>' +
                    '<td>' + escapeHtml(e.department) + '</td>' +
                    '<td>' + escapeHtml(e.position) + '</td>' +
                    '<td>' + escapeHtml(e.phone || '-') + '</td>' +
                    '<td class="align-right">' + ngn(e.basic_salary) + '</td>' +
                    '<td class="no-print actions">' +
                    '<button type="button" class="btn btn-small" data-edit="' + e.id + '">Edit</button> ' +
                    '<button type="button" class="btn btn-small btn-danger" data-delete="' + e.id + '" data-name="' + escapeHtml(e.full_name) + '">Delete</button>' +
                    '</td>';
                tbody.appendChild(tr);
            });
        }
        if (counter) counter.textContent = store.employees.length;
    }

    function fillDepartmentSelect() {
        var select = doc('dept-select');
        if (!select) return;
        var store = load();
        select.innerHTML = '';
        if (store.departments.length === 0) {
            var opt = document.createElement('option');
            opt.value = '';
            opt.textContent = '-- No department yet. Add one first --';
            select.appendChild(opt);
            return;
        }
        store.departments.forEach(function (name) {
            var option = document.createElement('option');
            option.value = name;
            option.textContent = name;
            select.appendChild(option);
        });
    }

    function resetForm() {
        var form = doc('employee-form');
        if (!form) return;
        form.reset();
        if (doc('emp-id')) doc('emp-id').value = '';
        if (doc('form-title')) doc('form-title').textContent = 'Register New Employee';
        hide('btn-update');
        hide('btn-cancel');
        fillDepartmentSelect();
    }

    function editEmployee(id) {
        var store = load();
        var emp = store.employees.filter(function (e) { return e.id === id; })[0];
        if (!emp) return;
        doc('emp-id').value = emp.id;
        doc('staff_id').value = emp.staff_id;
        doc('full_name').value = emp.full_name;
        doc('gender').value = emp.gender;
        doc('dept-select').value = emp.department;
        doc('position').value = emp.position;
        doc('phone').value = emp.phone || '';
        doc('basic_salary').value = emp.basic_salary;
        if (doc('form-title')) doc('form-title').textContent = 'Edit Employee - ' + emp.full_name;
        show('btn-update');
        show('btn-cancel');
        scrollToForm();
    }

    function handleEmployeeSubmit(mode) {
        var store = load();
        var staffId = (doc('staff_id').value || '').trim();
        var fullName = (doc('full_name').value || '').trim();
        var gender = doc('gender').value;
        var department = doc('dept-select').value;
        var position = (doc('position').value || '').trim();
        var phone = (doc('phone').value || '').trim();
        var basic = parseFloat(doc('basic_salary').value || '0');
        var errorBox = doc('form-error');
        var errorMsg = '';

        if (!staffId) errorMsg = 'Staff ID is required.';
        else if (!fullName) errorMsg = 'Full name is required.';
        else if (!department) errorMsg = 'Please add a department first (Departments page).';
        else if (!position) errorMsg = 'Position is required.';
        else if (isNaN(basic) || basic < 0) errorMsg = 'Enter a valid basic salary.';
        else {
            var dup = store.employees.some(function (e) {
                return e.staff_id.toLowerCase() === staffId.toLowerCase() && String(e.id) !== String(doc('emp-id').value);
            });
            if (dup) errorMsg = 'Staff ID already exists. Please use a unique staff ID.';
        }

        if (errorMsg) {
            if (!errorBox) {
                errorBox = document.createElement('div');
                errorBox.className = 'alert alert-error';
                errorBox.id = 'form-error';
                doc('employee-form').parentNode.insertBefore(errorBox, doc('employee-form'));
            }
            errorBox.textContent = errorMsg;
            return;
        }
        if (errorBox) errorBox.remove();

        if (mode === 'update') {
            var id = parseInt(doc('emp-id').value, 10);
            store.employees.forEach(function (e) {
                if (e.id === id) {
                    e.staff_id = staffId; e.full_name = fullName; e.gender = gender;
                    e.department = department; e.position = position; e.phone = phone;
                    e.basic_salary = basic;
                }
            });
        } else {
            var maxId = 0;
            store.employees.forEach(function (e) { if (e.id > maxId) maxId = e.id; });
            store.employees.push({
                id: maxId + 1, staff_id: staffId, full_name: fullName, gender: gender,
                department: department, position: position, phone: phone, basic_salary: basic,
                allowances: { housing: 0, transport: 0, medical: 0, utility: 0, other: 0 },
                deductions: { tax: 0, pension: 0, loan: 0, cooperative: 0, other: 0 }
            });
        }
        save(store);
        resetForm();
        renderEmployees();
    }

    function deleteEmployee(id) {
        var store = load();
        var emp = store.employees.filter(function (e) { return e.id === id; })[0];
        if (!emp) return;
        if (!window.confirm('Delete employee ' + emp.full_name + '?')) return;
        store.employees = store.employees.filter(function (e) { return e.id !== id; });
        save(store);
        renderEmployees();
    }

    /* ---------------- Departments page ---------------- */

    function renderDepartments() {
        var tbody = document.getElementById('department-tbody');
        var counter = document.getElementById('department-count');
        if (!tbody) return;
        var store = load();
        tbody.innerHTML = '';
        if (store.departments.length === 0) {
            var tr = document.createElement('tr');
            tr.innerHTML = '<td colspan="3" class="empty">No departments yet. Use the form above to add one.</td>';
            tbody.appendChild(tr);
        } else {
            store.departments.forEach(function (name, index) {
                var count = store.employees.filter(function (e) { return e.department === name; }).length;
                var tr = document.createElement('tr');
                tr.innerHTML =
                    '<td>' + (index + 1) + '</td>' +
                    '<td>' + escapeHtml(name) + '</td>' +
                    '<td class="align-center">' + count + '</td>' +
                    '<td class="no-print actions">' +
                    '<button type="button" class="btn btn-small btn-danger" data-deldept="' + index + '" data-name="' + escapeHtml(name) + '">Delete</button>' +
                    '</td>';
                tbody.appendChild(tr);
            });
        }
        if (counter) counter.textContent = store.departments.length;
    }

    function addDepartment() {
        var input = doc('dept-input');
        var name = (input.value || '').trim();
        var store = load();
        var errorBox = doc('dept-error');

        if (!name) {
            if (errorBox) errorBox.textContent = 'Department name is required.';
            return;
        }
        if (store.departments.some(function (d) { return d.toLowerCase() === name.toLowerCase(); })) {
            if (!errorBox) {
                errorBox = document.createElement('div');
                errorBox.className = 'alert alert-error';
                errorBox.id = 'dept-error';
                input.parentNode.insertBefore(errorBox, input.nextSibling);
            }
            errorBox.textContent = 'Department already exists.';
            return;
        }
        if (errorBox) errorBox.remove();
        store.departments.push(name);
        save(store);
        input.value = '';
        renderDepartments();
    }

    function deleteDepartment(index) {
        var store = load();
        var name = store.departments[index];
        if (!window.confirm('Delete department ' + name + '?')) return;
        store.departments.splice(index, 1);
        save(store);
        renderDepartments();
    }

    /* ---------------- Dashboard page ---------------- */

    function renderDashboard() {
        var store = load();
        var gross = 0, ded = 0, net = 0;
        store.employees.forEach(function (e) {
            var g = e.basic_salary + totalAllowance(e);
            var d = totalDeduction(e);
            gross += g; ded += d; net += (g - d);
        });
        setText('stat-employees', store.employees.length);
        setText('stat-departments', store.departments.length);
        setText('stat-gross', ngn(gross));
        setText('stat-net', ngn(net));
        setText('summary-gross', ngn(gross));
        setText('summary-ded', ngn(ded));
        setText('summary-net', ngn(net) + ' (' + store.employees.length + ' staff)');

        var recent = doc('recent-tbody');
        if (recent) {
            recent.innerHTML = '';
            var last = store.employees.slice(-5).reverse();
            if (last.length === 0) {
                recent.innerHTML = '<tr><td colspan="3" class="empty">No employees registered yet.</td></tr>';
            } else {
                last.forEach(function (e) {
                    var tr = document.createElement('tr');
                    tr.innerHTML =
                        '<td>' + escapeHtml(e.staff_id) + '</td>' +
                        '<td>' + escapeHtml(e.full_name) + '</td>' +
                        '<td>' + escapeHtml(e.department) + '</td>';
                    recent.appendChild(tr);
                });
            }
        }
    }

    /* ---------------- Helpers ---------------- */

    function doc(id) {
        return document.getElementById(id);
    }

    function show(id) { var el = doc(id); if (el) el.style.display = ''; }
    function hide(id) { var el = doc(id); if (el) el.style.display = 'none'; }
    function setText(id, text) { var el = doc(id); if (el) el.textContent = text; }
    function scrollToForm() { var f = doc('employee-form'); if (f) f.scrollIntoView({ behavior: 'smooth', block: 'center' }); }

    /* ---------------- Init ---------------- */

    document.addEventListener('DOMContentLoaded', function () {
        load();

        var loginForm = document.getElementById('demo-login-form');
        if (loginForm) {
            loginForm.addEventListener('submit', function (event) {
                event.preventDefault();
                var user = doc('username').value.trim();
                var pass = doc('password').value;
                if (user === 'admin' && pass === 'admin123') {
                    window.location.href = 'dashboard.html';
                } else {
                    var errorBox = doc('login-error');
                    if (!errorBox) {
                        errorBox = document.createElement('div');
                        errorBox.className = 'alert alert-error';
                        errorBox.id = 'login-error';
                        loginForm.parentNode.insertBefore(errorBox, loginForm);
                    }
                    errorBox.textContent = 'Invalid username or password. (Demo: admin / admin123)';
                }
            });
        }

        var search = doc('search-input');
        if (search) {
            search.addEventListener('keyup', function () { renderEmployees(); });
        }

        var saveBtn = doc('btn-save');
        if (saveBtn) {
            saveBtn.addEventListener('click', function () { handleEmployeeSubmit('add'); });
            hide('btn-update');
            hide('btn-cancel');
            fillDepartmentSelect();
        }
        var updateBtn = doc('btn-update');
        if (updateBtn) {
            updateBtn.addEventListener('click', function () { handleEmployeeSubmit('update'); });
        }
        var cancelBtn = doc('btn-cancel');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', function () { resetForm(); });
        }

        document.addEventListener('click', function (event) {
            var editBtn = event.target.closest('[data-edit]');
            if (editBtn) {
                editEmployee(parseInt(editBtn.getAttribute('data-edit'), 10));
                return;
            }
            var delBtn = event.target.closest('[data-delete]');
            if (delBtn) {
                deleteEmployee(parseInt(delBtn.getAttribute('data-delete'), 10));
                return;
            }
            var delDept = event.target.closest('[data-deldept]');
            if (delDept) {
                deleteDepartment(parseInt(delDept.getAttribute('data-deldept'), 10));
                return;
            }
        });

        var addDeptBtn = doc('btn-add-dept');
        if (addDeptBtn) {
            addDeptBtn.addEventListener('click', function () { addDepartment(); });
        }

        document.querySelectorAll('[data-print]').forEach(function (btn) {
            btn.addEventListener('click', function () { window.print(); });
        });

        document.querySelectorAll('.navbar a').forEach(function (link) {
            if (link.getAttribute('href') === window.location.pathname.split('/').pop()) {
                link.classList.add('active');
            }
        });

        renderEmployees();
        renderDepartments();
        renderDashboard();
    });
})();