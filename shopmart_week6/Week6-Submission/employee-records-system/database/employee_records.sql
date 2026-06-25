-- employee_records.sql
-- Employee Records Management System (BIT3208 Week 6 Challenge Task)
-- Import this file via phpMyAdmin, or run: mysql -u root -p < employee_records.sql

CREATE DATABASE IF NOT EXISTS employee_records_db;
USE employee_records_db;

-- ---------------------------------------------------------
-- Table: users  (login / bonus feature)
-- ---------------------------------------------------------
DROP TABLE IF EXISTS users;
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Default admin login -> username: admin | password: admin123
-- (password is bcrypt-hashed, compatible with PHP password_hash/password_verify)
INSERT INTO users (username, password) VALUES
('admin', '$2y$10$eyHHN3MwGFeJAwJAfRxJ4OuNdUFlLSe37jYLDJKNVhsVznN7VO8jm');

-- ---------------------------------------------------------
-- Table: employees
-- ---------------------------------------------------------
DROP TABLE IF EXISTS employees;
CREATE TABLE employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    department VARCHAR(50) NOT NULL,
    position VARCHAR(50) NOT NULL,
    salary DECIMAL(10,2) DEFAULT 0,
    date_hired DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Sample employee records
INSERT INTO employees (full_name, email, phone, department, position, salary, date_hired) VALUES
('John Mwangi', 'john.mwangi@company.co.ke', '0712345678', 'Finance', 'Accountant', 65000.00, '2023-03-15'),
('Faith Achieng', 'faith.achieng@company.co.ke', '0723456789', 'Human Resources', 'HR Officer', 58000.00, '2022-11-02'),
('Brian Kiptoo', 'brian.kiptoo@company.co.ke', '0734567890', 'IT', 'Systems Administrator', 75000.00, '2021-06-20'),
('Mercy Wanjiru', 'mercy.wanjiru@company.co.ke', '0745678901', 'Sales', 'Sales Executive', 52000.00, '2024-01-10'),
('Samuel Otieno', 'samuel.otieno@company.co.ke', '0756789012', 'Operations', 'Operations Manager', 90000.00, '2020-09-05');
