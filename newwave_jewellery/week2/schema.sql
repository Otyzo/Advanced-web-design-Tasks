-- ============================================================
-- New Wave Jewellery — Database Schema
-- BIT3208 Capstone Project | Stephen Otiende | BBIT/2018/36596
-- Run this once in phpMyAdmin (SQL tab) or the MySQL CLI.
-- Then run setup.php once in your browser to seed sample data.
-- ============================================================

CREATE DATABASE IF NOT EXISTS newwave_jewellery CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE newwave_jewellery;

-- ── Users ────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100)  NOT NULL,
    email       VARCHAR(150)  NOT NULL UNIQUE,
    password    VARCHAR(255)  NOT NULL,
    role        ENUM('admin','customer') DEFAULT 'customer',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ── Products (silver jewellery items) ───────────────────────
CREATE TABLE IF NOT EXISTS products (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(200)   NOT NULL,
    description   TEXT,
    price         DECIMAL(10,2)  NOT NULL,
    stock         INT            NOT NULL DEFAULT 0,
    category      VARCHAR(100),                        -- Rings, Necklaces, Bracelets, Earrings, Anklets, Pendants, Sets
    metal_type    VARCHAR(100)   DEFAULT 'Sterling Silver 925',
    purity        VARCHAR(20)    DEFAULT '925',         -- e.g. 925, 999
    weight_grams  DECIMAL(6,2)   DEFAULT 0,
    image_url     VARCHAR(500)   DEFAULT '',
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ── Remember-Me tokens (Week 9 — session & cookie management) ─
ALTER TABLE users ADD COLUMN IF NOT EXISTS remember_token VARCHAR(64) NULL DEFAULT NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS remember_expires DATETIME NULL DEFAULT NULL;

-- ── Orders (Week 10-11 — dynamic pages + DB-backed continuous project) ─
CREATE TABLE IF NOT EXISTS orders (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    user_id        INT NOT NULL,
    subtotal       DECIMAL(10,2) NOT NULL,
    shipping       DECIMAL(10,2) NOT NULL DEFAULT 0,
    total          DECIMAL(10,2) NOT NULL,
    payment_method ENUM('mpesa','card','cash') DEFAULT 'mpesa',
    status         ENUM('pending','paid','shipped','completed','cancelled') DEFAULT 'pending',
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ── Order line items ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS order_items (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    order_id     INT NOT NULL,
    product_id   INT NOT NULL,
    product_name VARCHAR(200) NOT NULL,
    unit_price   DECIMAL(10,2) NOT NULL,
    quantity     INT NOT NULL,
    subtotal     DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id)   REFERENCES orders(id)   ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- ── Call-for-price items (Batch 7 — rings/pendants without fixed tag price) ─
ALTER TABLE products ADD COLUMN IF NOT EXISTS call_for_price TINYINT(1) NOT NULL DEFAULT 0;

-- ── M-Pesa Daraja STK Push tracking ─────────────────────────
ALTER TABLE orders ADD COLUMN IF NOT EXISTS phone_number VARCHAR(15) NULL DEFAULT NULL;
ALTER TABLE orders ADD COLUMN IF NOT EXISTS mpesa_checkout_id VARCHAR(60) NULL DEFAULT NULL;
ALTER TABLE orders ADD COLUMN IF NOT EXISTS mpesa_receipt VARCHAR(30) NULL DEFAULT NULL;
ALTER TABLE orders ADD COLUMN IF NOT EXISTS mpesa_result_desc VARCHAR(255) NULL DEFAULT NULL;

-- ============================================================
-- NOTE: Run setup.php once (in your browser) to seed sample
--       users and products with properly hashed passwords.
--       Delete setup.php afterwards for security.
-- ============================================================
