-- ============================================================
-- ShopMart E-Commerce Database
-- BIT3208 Capstone Project - CAT 1
-- ============================================================

CREATE DATABASE IF NOT EXISTS shopmart CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE shopmart;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100)  NOT NULL,
    email       VARCHAR(150)  NOT NULL UNIQUE,
    password    VARCHAR(255)  NOT NULL,
    role        ENUM('admin','customer') DEFAULT 'customer',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(200)  NOT NULL,
    description TEXT,
    price       DECIMAL(10,2) NOT NULL,
    stock       INT           NOT NULL DEFAULT 0,
    category    VARCHAR(100),
    image_url   VARCHAR(500)  DEFAULT '',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================================
-- NOTE: Run setup.php once to insert sample data with
--       properly hashed passwords.
-- ============================================================
