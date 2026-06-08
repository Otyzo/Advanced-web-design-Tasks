<?php
// ============================================================
// setup.php — One-Time Database Setup & Seeder
// Run this ONCE in your browser: http://localhost/shopmart/setup.php
// Delete this file after running.
// ============================================================

require_once 'db.php';

// ── Create tables ─────────────────────────────────────────
$conn->multi_query("
CREATE TABLE IF NOT EXISTS users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100)  NOT NULL,
    email      VARCHAR(150)  NOT NULL UNIQUE,
    password   VARCHAR(255)  NOT NULL,
    role       ENUM('admin','customer') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

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
");

// Drain multi_query results
while ($conn->more_results()) $conn->next_result();

// ── Seed users ────────────────────────────────────────────
$users = [
    ['Admin User',  'admin@shopmart.com',   'admin123',    'admin'],
    ['Alice Kamau', 'alice@example.com',    'password123', 'customer'],
    ['Brian Otieno','brian@example.com',    'password123', 'customer'],
];

$stmt = $conn->prepare("INSERT IGNORE INTO users (name, email, password, role) VALUES (?,?,?,?)");
foreach ($users as $u) {
    $hash = password_hash($u[2], PASSWORD_DEFAULT);
    $stmt->bind_param('ssss', $u[0], $u[1], $hash, $u[3]);
    $stmt->execute();
}
$stmt->close();

// ── Seed products ─────────────────────────────────────────
$products = [
    ['Wireless Headphones',  'Premium noise-cancelling Bluetooth headphones with 30-hour battery life.',       2999.00, 15, 'Electronics'],
    ['Running Shoes',        'Lightweight performance shoes with cushioned sole. Available in all sizes.',     4500.00, 30, 'Footwear'],
    ['Laptop Backpack',      'Water-resistant 30L backpack with USB charging port and padded laptop sleeve.',  1800.00, 25, 'Bags'],
    ['Smart Watch',          'Fitness tracker with heart rate monitor, GPS, and 7-day battery life.',          8500.00, 10, 'Electronics'],
    ['Coffee Maker',         'Automatic drip coffee maker with 1.5L capacity and keep-warm function.',         3200.00, 20, 'Kitchen'],
    ['Mechanical Keyboard',  'Compact TKL keyboard with blue switches and RGB backlight.',                     5500.00, 12, 'Electronics'],
    ['Yoga Mat',             'Non-slip 6mm thick eco-friendly yoga mat with carrying strap.',                   900.00, 40, 'Fitness'],
    ['Sunglasses',           'Polarized UV400 protection sunglasses with lightweight titanium frame.',         2100.00, 35, 'Fashion'],
];

$stmt = $conn->prepare("INSERT INTO products (name, description, price, stock, category) VALUES (?,?,?,?,?)");
foreach ($products as $p) {
    $stmt->bind_param('ssdis', $p[0], $p[1], $p[2], $p[3], $p[4]);
    $stmt->execute();
}
$stmt->close();

echo '<div style="font-family:sans-serif;max-width:500px;margin:40px auto;padding:24px;background:#d1fae5;border-radius:12px;border:1px solid #6ee7b7">
    <h2 style="color:#065f46;margin:0 0 12px">✅ Setup Complete!</h2>
    <p style="color:#047857;margin:0 0 8px">Database seeded successfully.</p>
    <p style="color:#047857;margin:0 0 16px"><strong>Admin login:</strong> admin@shopmart.com / admin123</p>
    <p style="color:#dc2626;font-weight:bold;margin:0">⚠️ Delete this file (setup.php) now for security.</p>
    <a href="login.php" style="display:inline-block;margin-top:16px;padding:10px 20px;background:#059669;color:white;border-radius:6px;text-decoration:none">Go to Login →</a>
</div>';
