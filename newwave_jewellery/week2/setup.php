<?php
// ============================================================
// setup.php — One-Time Database Setup & Seeder
// New Wave Jewellery
// Run ONCE in your browser: http://localhost/newwave_jewellery/setup.php
// Delete this file after running.
// ============================================================

require_once 'db.php';

// ── Create tables (in case schema.sql wasn't run) ───────────
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
    id            INT AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(200)   NOT NULL,
    description   TEXT,
    price         DECIMAL(10,2)  NOT NULL,
    stock         INT            NOT NULL DEFAULT 0,
    category      VARCHAR(100),
    metal_type    VARCHAR(100)   DEFAULT 'Sterling Silver 925',
    purity        VARCHAR(20)    DEFAULT '925',
    weight_grams  DECIMAL(6,2)   DEFAULT 0,
    image_url     VARCHAR(500)   DEFAULT '',
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
");

// Drain multi_query results
while ($conn->more_results()) { $conn->next_result(); }

// ── Seed users ────────────────────────────────────────────
$users = [
    ['Admin User',   'admin@newwavejewellery.com', 'admin123',    'admin'],
    ['Amina Wanjiru','amina@example.com',          'password123', 'customer'],
    ['Brian Otieno', 'brian@example.com',          'password123', 'customer'],
];

$stmt = $conn->prepare("INSERT IGNORE INTO users (name, email, password, role) VALUES (?,?,?,?)");
foreach ($users as $u) {
    $hash = password_hash($u[2], PASSWORD_DEFAULT);
    $stmt->bind_param('ssss', $u[0], $u[1], $hash, $u[3]);
    $stmt->execute();
}
$stmt->close();

// ── Category stock photos ─────────────────────────────────────
// Local files in /images (run download_images.php once first,
// or place your own JPEGs at these paths — see README.md).
$photo = [
    'Rings'     => 'images/rings.jpg',
    'Necklaces' => 'images/necklaces.jpg',
    'Bracelets' => 'images/bracelets.jpg',
    'Earrings'  => 'images/earrings.jpg',
    'Anklets'   => 'images/anklets.jpg',
    'Pendants'  => 'images/pendants.jpg',
    'Sets'      => 'images/sets.jpg',
];

// ── Seed products (silver jewellery) ─────────────────────────
// [name, description, price, stock, category, metal_type, purity, weight_grams]
$products = [
    ['Infinity Knot Ring',        'Sleek sterling silver band with a polished infinity knot detail. Tarnish-resistant finish.', 3200.00, 18, 'Rings',     'Sterling Silver 925', '925', 3.10],
    ['Layered Chain Necklace',    'Delicate double-layer chain necklace with a minimalist pendant drop.',                       5400.00, 12, 'Necklaces', 'Sterling Silver 925', '925', 8.40],
    ['Cuban Link Bracelet',       'Bold Cuban-link bracelet, hand-polished for a mirror shine. Adjustable clasp.',              4800.00, 15, 'Bracelets', 'Sterling Silver 925', '925', 12.50],
    ['Pearl Drop Earrings',       'Freshwater pearl drops set in sterling silver hooks — elegant everyday wear.',              2900.00, 20, 'Earrings',  'Sterling Silver 925', '925', 2.20],
    ['Beaded Anklet',             'Fine box-chain anklet with tiny silver beads and a heart charm.',                          1800.00, 25, 'Anklets',   'Sterling Silver 925', '925', 1.80],
    ['Solitaire Pendant',         'Cubic zirconia solitaire pendant on an 18-inch silver chain.',                              3600.00, 14, 'Pendants',  'Sterling Silver 925', '925', 2.90],
    ['Bridal Jewellery Set',      'Matching necklace, earrings, and bracelet set — perfect for weddings and events.',        12500.00,  6, 'Sets',      'Sterling Silver 925', '925', 26.00],
    ['Stackable Band Ring',       'Thin stackable band, sold individually — mix and match across fingers.',                  1500.00, 30, 'Rings',     'Sterling Silver 925', '925', 1.60],
    ['Herringbone Chain Necklace','Flat herringbone-weave chain with a high-shine polish. Lobster clasp closure.',            6200.00, 10, 'Necklaces', 'Sterling Silver 925', '925', 9.70],
    ['Huggie Hoop Earrings',      'Small huggie hoops with a hammered texture — comfortable for all-day wear.',               2100.00, 22, 'Earrings',  'Sterling Silver 925', '925', 1.90],
    ['Tennis Bracelet',           'Classic tennis bracelet lined with brilliant-cut cubic zirconia stones.',                  7400.00,  8, 'Bracelets', 'Sterling Silver 925', '925', 10.80],
    ['Charm Anklet',              'Playful anklet with star and moon charms on a fine curb chain.',                          1650.00, 28, 'Anklets',   'Sterling Silver 925', '925', 1.50],
];

$stmt = $conn->prepare(
    "INSERT INTO products (name, description, price, stock, category, metal_type, purity, weight_grams, image_url)
     VALUES (?,?,?,?,?,?,?,?,?)"
);
foreach ($products as $p) {
    $image_url = $photo[$p[4]] ?? '';
    $stmt->bind_param('ssdisssds', $p[0], $p[1], $p[2], $p[3], $p[4], $p[5], $p[6], $p[7], $image_url);
    $stmt->execute();
}
$stmt->close();

echo '<div style="font-family:sans-serif;max-width:520px;margin:60px auto;padding:28px;background:#1b1b1f;color:#f4f4f6;border-radius:14px;border:1px solid #3a3a42">
    <h2 style="margin:0 0 12px;color:#e9ebee">✅ New Wave Jewellery — Setup Complete</h2>
    <p style="margin:0 0 8px;color:#c7cad1">Database seeded with 12 silver jewellery products and 3 users.</p>
    <p style="margin:0 0 8px;color:#c7cad1">📷 Run <code>download_images.php</code> once (if you haven\'t) to pull the 7 product photos into <code>/images</code>.</p>
    <p style="margin:0 0 16px;color:#c7cad1"><strong>Admin login:</strong> admin@newwavejewellery.com / admin123</p>
    <p style="color:#f87171;font-weight:bold;margin:0 0 16px">⚠️ Delete this file (setup.php) now for security.</p>
    <a href="login.php" style="display:inline-block;padding:11px 22px;background:linear-gradient(135deg,#c7cad1,#8f939c);color:#0a0a0d;font-weight:700;border-radius:8px;text-decoration:none">Go to Login →</a>
</div>';
