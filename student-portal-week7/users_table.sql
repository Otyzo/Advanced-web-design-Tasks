-- Student Portal — Week 7 Authentication
-- Run in phpMyAdmin / MySQL CLI

CREATE DATABASE IF NOT EXISTS studentportal;
USE studentportal;

CREATE TABLE IF NOT EXISTS users (
    id        INT AUTO_INCREMENT PRIMARY KEY,
    fullname  VARCHAR(100) NOT NULL,
    email     VARCHAR(100) NOT NULL UNIQUE,
    password  VARCHAR(255) NOT NULL
);
