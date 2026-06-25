-- students.sql
-- Database for Week 6 Class Activities 1–3
-- Matches the studentdb / students table used in the lecture demonstrations.
-- Import via phpMyAdmin, or run: mysql -u root -p < students.sql

CREATE DATABASE IF NOT EXISTS studentdb;
USE studentdb;

DROP TABLE IF EXISTS students;
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    course VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sample records
INSERT INTO students (fullname, email, course) VALUES
('Alice Wambui', 'alice.wambui@example.com', 'Business Information Technology'),
('Kevin Mutua', 'kevin.mutua@example.com', 'Computer Science'),
('Grace Nyambura', 'grace.nyambura@example.com', 'Information Technology');
