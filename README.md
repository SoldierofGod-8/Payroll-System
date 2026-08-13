# Staff Payroll System

**Design and Implementation of a Staff Payroll System**

A web-based payroll management application built with **PHP**, **MySQL**, **HTML**, **CSS** and **JavaScript**. It automates employee registration, salary computation, allowance and deduction management, payroll processing, payslip generation and payroll reporting.

## Live Demo

A static preview of the application interface is hosted on GitHub Pages:

**https://soldierofgod-8.github.io/Payroll-System/**

Demo credentials (login page): `admin` / `admin123`

The demo is interactive: you can add, edit and delete employees, add and delete departments, and search records — changes are saved in your browser (localStorage).

> GitHub Pages only serves static files, so this is a UI preview. The full **PHP + MySQL** system runs locally with XAMPP (see Installation below) or any PHP-capable web host.

## Features

- User authentication and role-based access (Admin / User)
- Dashboard with statistics and monthly salary summary
- Employee registration, editing, search and deletion
- Department management
- Allowance management (Housing, Transport, Medical, Utility, Other)
- Deduction management (Tax, Pension, Loan, Cooperative, Other)
- Automated monthly payroll processing
- Printable employee payslips
- Payroll reports:
  - Monthly Payroll Report
  - Staff Payroll Report
  - Department Salary Summary
  - Employee List
- User account management (Admin only)
- Database backup (Admin only)
- In-app User Guide for navigation

## How Salary is Calculated

```
Gross Salary     = Basic Salary + Total Allowances
Total Deductions = Tax + Pension + Loan + Cooperative + Other
Net Salary       = Gross Salary − Total Deductions
```

## Technology Stack

| Technology   | Purpose                        |
| ------------ | ------------------------------ |
| PHP          | Server-side programming        |
| MySQL        | Database management            |
| HTML         | Web page structure             |
| CSS          | Interface design               |
| JavaScript   | Interactivity and validation   |
| XAMPP        | Local server (Apache + MySQL)  |

## Requirements

**Hardware**

- Intel Core i3 processor or higher
- 4 GB RAM (minimum)
- 500 GB Hard Disk
- Keyboard, mouse and monitor
- Printer (for payslips and reports)

**Software**

- Windows 10 / 11
- XAMPP (Apache + MySQL + PHP 8.x)
- Google Chrome or Mozilla Firefox

## Installation

1. Install [XAMPP](https://www.apachefriends.org/) and start **Apache** and **MySQL** from the XAMPP Control Panel.
2. Copy the project folder into the XAMPP web root:
   ```
   C:\xampp\htdocs\payrollsystem
   ```
3. Open your browser and visit:
   ```
   http://localhost/payrollsystem
   ```
4. You will be taken to the **Installation Wizard**. Click **Install System** (the default database settings are correct for a standard XAMPP installation).
5. Log in with the default administrator account:

   | Username | Password |
   | -------- | -------- |
   | `admin`  | `admin123` |

6. **Important:** change the default admin password after your first login (Users page) for security.

> **Alternative:** you can import `sql/payroll_db.sql` manually using phpMyAdmin instead of running the installer. Note that the default admin password in the SQL file must be created via the installer or `PASSWORD_DEFAULT` hashing.

## Usage Guide (Quick Start)

1. **Add Departments** – Departments page.
2. **Register Employees** – Employees page (Staff ID, name, gender, department, position, phone, basic salary).
3. **Set Allowances** – Allowances page for each staff member.
4. **Set Deductions** – Deductions page for each staff member.
5. **Run Payroll** – Payroll page: choose month/year, click *Run Payroll*.
6. **Print Payslips** – from the payroll table.
7. **View Reports** – Reports page.
8. **Backup Data** – Backup page (Admin) before important changes.

A full step-by-step guide is also available inside the application under the **Guide** menu.

## Project Structure

```
payrollsystem/
├── assets/
│   ├── css/style.css        # Styles and print layout
│   └── js/script.js         # Confirmation dialogs, flash messages
├── config/
│   └── db.php               # Database connection and helpers
├── includes/
│   ├── auth.php             # Session authentication
│   ├── header.php           # Shared header and navigation
│   └── footer.php           # Shared footer
├── sql/
│   └── payroll_db.sql       # Database schema (manual import)
├── install.php              # Installation wizard
├── login.php / logout.php   # Authentication
├── dashboard.php            # Dashboard with statistics
├── employees.php            # Employee list and search
├── employee_form.php        # Add / edit employee
├── employee_delete.php      # Delete employee
├── departments.php          # Department management
├── allowances.php           # Allowance management
├── deductions.php           # Deduction management
├── payroll.php              # Payroll processing
├── payslip.php              # Printable payslip
├── reports.php              # Payroll reports
├── users.php                # User management (Admin)
├── backup.php               # Database backup (Admin)
└── guide.php                # In-app user guide
```

## Database Tables

| Table        | Description                              |
| ------------ | ---------------------------------------- |
| `users`      | System login accounts                    |
| `departments`| Organization departments                 |
| `employees`  | Employee records and basic salary        |
| `allowances` | Allowances per employee                  |
| `deductions` | Deductions per employee                  |
| `payroll`    | Monthly payroll records per employee     |

## Backup and Restore

- **Backup:** go to the **Backup** page and click *Download Backup* — a `.sql` file of the whole database is saved.
- **Restore:** open phpMyAdmin, select the `payroll_db` database, go to the **Import** tab, choose the `.sql` file and run it.

## Security Notes

- Passwords are stored as secure hashes (`password_hash`).
- All database queries use prepared statements.
- Admin-only pages (Users, Backup) are protected.
- Change the default `admin` password after first login.
- Keep regular database backups.

## Limitations

- Requires a local web server (XAMPP) to run.
- No biometric attendance integration.
- No online bank payment integration.
- Tax remittance is not automated.
- No SMS or email notifications.

## Author

Wamise Tech — SoldierofGod-8
