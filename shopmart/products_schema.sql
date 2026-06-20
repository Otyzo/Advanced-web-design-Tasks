-- ShopMart Week 5: Products table for CRUD operations
-- Run in phpMyAdmin / MySQL CLI on the `shopmart` database

CREATE TABLE IF NOT EXISTS products (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(150) NOT NULL,
    category    VARCHAR(100) NOT NULL,
    price       DECIMAL(10,2) NOT NULL,
    stock       INT NOT NULL DEFAULT 0,
    description TEXT,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Sample products to start with
INSERT INTO products (name, category, price, stock, description) VALUES
('Wireless Bluetooth Headphones', 'Electronics', 3500.00, 42, 'Over-ear headphones with noise cancellation and 20-hour battery life.'),
('Stainless Steel Water Bottle', 'Home & Living', 1200.00, 120, 'Insulated 750ml bottle that keeps drinks cold for 24 hours.'),
('Men''s Running Sneakers', 'Footwear', 4800.00, 15, 'Lightweight breathable sneakers designed for daily training.');
