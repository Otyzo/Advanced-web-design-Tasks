-- ShopMart Week 4: Basic users table
-- Run in phpMyAdmin / MySQL CLI on the `shopmart` database

CREATE TABLE IF NOT EXISTS users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(50)  NOT NULL UNIQUE,
    email         VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Sample test user
-- Username: testuser | Password: Test1234
INSERT INTO users (username, email, password_hash) VALUES
('testuser', 'testuser@shopmart.test', '$2y$10$92IXUNpkjO0rOQ5byMi.YeGZkqkW.D8sN0HUyAYbDjGSwUVK99c.G');
