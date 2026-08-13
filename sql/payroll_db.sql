-- ============================================
-- Staff Payroll System - Database Schema
-- Import this file in phpMyAdmin OR run install.php
-- ============================================

CREATE DATABASE IF NOT EXISTS payroll_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE payroll_db;

CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    user_type ENUM('Admin','User') NOT NULL DEFAULT 'User',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS departments (
    dept_id INT AUTO_INCREMENT PRIMARY KEY,
    dept_name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS employees (
    employee_id INT AUTO_INCREMENT PRIMARY KEY,
    staff_id VARCHAR(20) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    gender ENUM('Male','Female') NOT NULL,
    department_id INT NULL,
    position VARCHAR(50) NOT NULL,
    phone VARCHAR(20) NULL,
    basic_salary DECIMAL(12,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_emp_dept FOREIGN KEY (department_id) REFERENCES departments(dept_id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS allowances (
    allowance_id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL UNIQUE,
    housing DECIMAL(12,2) NOT NULL DEFAULT 0,
    transport DECIMAL(12,2) NOT NULL DEFAULT 0,
    medical DECIMAL(12,2) NOT NULL DEFAULT 0,
    utility DECIMAL(12,2) NOT NULL DEFAULT 0,
    other DECIMAL(12,2) NOT NULL DEFAULT 0,
    CONSTRAINT fk_allow_emp FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS deductions (
    deduction_id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL UNIQUE,
    tax DECIMAL(12,2) NOT NULL DEFAULT 0,
    pension DECIMAL(12,2) NOT NULL DEFAULT 0,
    loan DECIMAL(12,2) NOT NULL DEFAULT 0,
    cooperative DECIMAL(12,2) NOT NULL DEFAULT 0,
    other DECIMAL(12,2) NOT NULL DEFAULT 0,
    CONSTRAINT fk_ded_emp FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS payroll (
    payroll_id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    payroll_month TINYINT NOT NULL,
    payroll_year SMALLINT NOT NULL,
    basic_salary DECIMAL(12,2) NOT NULL DEFAULT 0,
    total_allowance DECIMAL(12,2) NOT NULL DEFAULT 0,
    gross_salary DECIMAL(12,2) NOT NULL DEFAULT 0,
    total_deduction DECIMAL(12,2) NOT NULL DEFAULT 0,
    net_salary DECIMAL(12,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_emp_period (employee_id, payroll_month, payroll_year),
    CONSTRAINT fk_pay_emp FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO departments (dept_name) VALUES
('Administration'), ('Finance'), ('Human Resources'), ('Information Technology'), ('Operations');

-- Default admin account: username = admin, password = admin123
-- NOTE: run install.php to create the admin account with a proper password hash,
-- or insert one manually with: SELECT password_hash('admin123', PASSWORD_DEFAULT);