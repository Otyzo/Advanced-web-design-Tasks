<?php
// ============================================================
// add_chains.php — One-Time Seeder: Real Chain Inventory
// New Wave Jewellery
//
// Adds 15 real silver chain products (photographed in-store)
// on top of whatever is already in the `products` table — it
// does NOT touch or remove existing rows. Run ONCE:
//   http://localhost/newwave_jewellery/add_chains.php
//
// Source: shop display photos. Product name, weight, and price
// were read directly off each price tag. The shop's phone number
// shown on the tags is intentionally NOT stored anywhere in the
// database — only name / weight / price / photo are kept.
//
// Delete this file after running it.
// ============================================================

require_once 'db.php';

// [name, description, price, stock, category, metal_type, purity, weight_grams, image_url]
$products = [
    ['Rope Silver Chain',          'Classic rope-twist sterling silver chain — a popular everyday piece, sold by weight.',        13500.00, 1, 'Necklaces', 'Sterling Silver', '925', 37.5, 'images/rope-chain-37g.jpg'],
    ['Cashmoney Silver Chain',     'Bold Byzantine box-link chain, hand-finished for a heavy, substantial feel.',                  9500.00, 1, 'Necklaces', 'Sterling Silver', '925', 26.3, 'images/cashmoney-chain-26g.jpg'],
    ['Cashmoney Silver Chain',     'Bold Byzantine box-link chain — larger link gauge, on offer at 300 KES/gram.',                13500.00, 1, 'Necklaces', 'Sterling Silver', '925', 44.2, 'images/cashmoney-chain-44g.jpg'],
    ['Cuban Silver Chain',         'Classic Cuban curb-link chain, flat interlocking links for a bold everyday look.',            13000.00, 1, 'Necklaces', 'Sterling Silver', '925', 37.2, 'images/cuban-chain-37g.jpg'],
    ['Cashmoney Iced Silver Chain','Cashmoney-style box-link chain with an iced, textured finish along the links.',              12500.00, 1, 'Necklaces', 'Sterling Silver', '925', 35.8, 'images/cashmoney-iced-chain-36g.jpg'],
    ['Cashmoney Silver Chain',     'Bold Byzantine box-link chain, hand-finished for a heavy, substantial feel.',                  9500.00, 1, 'Necklaces', 'Sterling Silver', '925', 26.2, 'images/cashmoney-chain-26g-b.jpg'],
    ['Cashmoney Silver Chain',     'Bold Byzantine box-link chain — a heavier, statement-length piece.',                         20500.00, 1, 'Necklaces', 'Sterling Silver', '925', 58.6, 'images/cashmoney-chain-59g.jpg'],
    ['Cashmoney Silver Chain',     'Bold Byzantine box-link chain, shown here with a matching skull pendant charm.',             17500.00, 1, 'Necklaces', 'Sterling Silver', '925', 49.2, 'images/cashmoney-chain-49g-skull.jpg'],
    ['Rope Silver Chain',          'Classic rope-twist sterling silver chain — a popular everyday piece, sold by weight.',        13500.00, 1, 'Necklaces', 'Sterling Silver', '925', 17.7, 'images/rope-chain-18g.jpg'],
    ['Cuban Silver Chain',         'Classic Cuban curb-link chain, flat interlocking links for a bold everyday look.',             9000.00, 1, 'Necklaces', 'Sterling Silver', '925', 25.3, 'images/cuban-chain-25g.jpg'],
    ['Basentine Silver Chain',     'Heavyweight Byzantine-style link chain — a substantial, statement-length piece.',           38600.00, 1, 'Necklaces', 'Sterling Silver', '925', 110.4, 'images/basentine-chain-110g.jpg'],
    ['Cuban Silver Chain',         'Classic Cuban curb-link chain, flat interlocking links for a bold everyday look.',            11700.00, 1, 'Necklaces', 'Sterling Silver', '925', 33.4, 'images/cuban-chain-33g.jpg'],
    ['Cashmoney Silver Chain',     'Bold Byzantine box-link chain — a heavier, statement-length piece.',                         24600.00, 1, 'Necklaces', 'Sterling Silver', '925', 70.6, 'images/cashmoney-chain-71g.jpg'],
    ['Cuban Silver Chain',         'Classic Cuban curb-link chain, flat interlocking links for a bold everyday look.',             7500.00, 1, 'Necklaces', 'Sterling Silver', '925', 20.5, 'images/cuban-chain-21g.jpg'],
    ['Champion Silver Chain',      'Flattened mariner/anchor-link chain with a polished, high-shine finish.',                     12500.00, 1, 'Necklaces', 'Sterling Silver', '925', 35.5, 'images/champion-chain-36g.jpg'],
];

$stmt = $conn->prepare(
    "INSERT INTO products (name, description, price, stock, category, metal_type, purity, weight_grams, image_url)
     VALUES (?,?,?,?,?,?,?,?,?)"
);
$added = 0;
foreach ($products as $p) {
    $stmt->bind_param('ssdisssds', $p[0], $p[1], $p[2], $p[3], $p[4], $p[5], $p[6], $p[7], $p[8]);
    if ($stmt->execute()) $added++;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Chains Added — New Wave Jewellery</title>
<style>
body{font-family:sans-serif;max-width:560px;margin:60px auto;padding:0 20px;background:#0a0a0d;color:#f4f4f6}
.card{background:#1b1b1f;border:1px solid #2c2c33;border-radius:14px;padding:28px}
a.btn{display:inline-block;margin-top:16px;padding:11px 22px;background:linear-gradient(135deg,#c7cad1,#8f939c);color:#0a0a0d;font-weight:700;border-radius:8px;text-decoration:none}
</style>
</head>
<body>
<div class="card">
  <h2>✅ <?= $added ?> chain products added</h2>
  <p>Real inventory photos from <code>/images</code> are now linked to <?= $added ?> new product rows (category: Necklaces). Existing products were left untouched.</p>
  <p style="color:#f87171;font-weight:bold">⚠️ Delete this file (add_chains.php) now that it's run.</p>
  <a href="index.php" class="btn">View Storefront →</a>
</div>
</body>
</html>
