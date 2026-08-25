<?php
// ============================================================
// orders.php — Order History & Confirmation
// New Wave Jewellery (Week 10-11 Continuous Project)
// Dynamic page: reads the logged-in user's orders + line items
// from the database and renders them. Mirrors the JDBC-driven
// "dynamic HTML generation" pattern from the servlet track,
// implemented here with PHP + MySQLi prepared statements.
// ============================================================

session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?next=orders.php');
    exit;
}

$user_id      = $_SESSION['user_id'];
$placed_id    = isset($_GET['placed']) ? (int)$_GET['placed'] : 0;
$view_id      = isset($_GET['view'])   ? (int)$_GET['view']   : 0;

// ── Single order detail view ──────────────────────────────────
$detail = null;
if ($view_id || $placed_id) {
    $oid = $view_id ?: $placed_id;
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmt->bind_param('ii', $oid, $user_id);
    $stmt->execute();
    $detail = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($detail) {
        $stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmt->bind_param('i', $detail['id']);
        $stmt->execute();
        $detail['items'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
}

// ── Order list ─────────────────────────────────────────────────
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$status_colors = [
    'pending'   => '#eab308', 'paid'      => '#22c55e', 'shipped'   => '#38bdf8',
    'completed' => '#4ade80', 'cancelled' => '#f87171',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Orders — New Wave Jewellery</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--bg:#0a0a0d;--surface:#141417;--card:#1b1b1f;--card2:#212226;--border:#2c2c33;--accent:#c7cad1;--accent2:#eceef1;--text:#f4f4f6;--muted:#9a9aa5;--danger:#f87171}
body{background:var(--bg);font-family:'DM Sans',sans-serif;color:var(--text);min-height:100vh}
nav{background:rgba(20,20,23,.95);border-bottom:1px solid var(--border);padding:0 24px}
.nav-inner{max-width:1000px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;height:64px}
.logo{font-family:'Playfair Display',serif;font-size:1.4rem;font-weight:700;background:linear-gradient(135deg,var(--accent2),var(--accent));-webkit-background-clip:text;-webkit-text-fill-color:transparent;text-decoration:none}
.nav-links a{color:var(--muted);text-decoration:none;font-size:.875rem;margin-left:18px}
.nav-links a:hover{color:var(--accent2)}
.main{max-width:1000px;margin:0 auto;padding:36px 24px}
h1{font-family:'Playfair Display',serif;font-size:1.8rem;margin-bottom:24px}
.banner{background:rgba(74,222,128,.1);border:1px solid rgba(74,222,128,.3);color:#4ade80;padding:14px 18px;border-radius:10px;margin-bottom:24px;font-size:.9rem}
.empty{text-align:center;padding:60px 20px;color:var(--muted)}
.order-card{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:20px 22px;margin-bottom:14px}
.order-head{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;margin-bottom:10px}
.order-id{font-family:'Playfair Display',serif;font-weight:700}
.order-date{font-size:.78rem;color:var(--muted)}
.status-pill{padding:3px 12px;border-radius:20px;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em}
.order-meta{display:flex;gap:22px;font-size:.85rem;color:var(--muted);flex-wrap:wrap}
.order-total{color:var(--accent2);font-weight:700;font-family:'Playfair Display',serif}
.view-link{font-size:.8rem;color:var(--accent2);text-decoration:none;margin-top:10px;display:inline-block}
.view-link:hover{text-decoration:underline}
.detail-card{background:var(--card);border:1px solid var(--accent);border-radius:12px;padding:24px;margin-bottom:28px}
.item-line{display:flex;justify-content:space-between;font-size:.85rem;padding:8px 0;border-bottom:1px solid rgba(44,44,51,.5)}
</style>
</head>
<body>

<nav>
  <div class="nav-inner">
    <a href="index.php" class="logo">New Wave Jewellery</a>
    <div class="nav-links">
      <a href="index.php">Shop</a>
      <a href="cart.php">Cart</a>
      <a href="orders.php">My Orders</a>
      <a href="logout.php">Logout</a>
    </div>
  </div>
</nav>

<div class="main">
  <h1>My Orders</h1>

  <?php if ($placed_id && $detail): ?>
    <div class="banner">✓ Order #<?= $detail['id'] ?> placed successfully! Thank you for shopping with New Wave Jewellery.</div>
  <?php endif; ?>

  <?php if (!empty($_GET['mpesa_failed'])): ?>
    <div class="banner" style="background:rgba(248,113,113,0.12);border-color:#f87171;color:#f87171">
      ⚠️ We couldn't send the M-Pesa prompt automatically (<?= htmlspecialchars($_GET['mpesa_failed']) ?>).
      Your order is saved — please pay manually via M-Pesa to <strong>0795349957</strong> and we'll confirm it shortly.
    </div>
  <?php endif; ?>

  <?php if ($detail): ?>
    <div class="detail-card">
      <div class="order-head">
        <span class="order-id">Order #<?= $detail['id'] ?></span>
        <span class="status-pill" style="background:<?= $status_colors[$detail['status']] ?>22;color:<?= $status_colors[$detail['status']] ?>">
          <?= htmlspecialchars($detail['status']) ?>
        </span>
      </div>
      <div class="order-date"><?= date('d M Y, g:i A', strtotime($detail['created_at'])) ?> · Paid via <?= htmlspecialchars(strtoupper($detail['payment_method'])) ?></div>
      <div style="margin:16px 0 10px;font-weight:600;font-size:.85rem;color:var(--muted);text-transform:uppercase;letter-spacing:.05em">Items</div>
      <?php foreach ($detail['items'] as $it): ?>
        <div class="item-line">
          <span><?= htmlspecialchars($it['product_name']) ?> × <?= $it['quantity'] ?></span>
          <span>KES <?= number_format($it['subtotal'], 2) ?></span>
        </div>
      <?php endforeach; ?>
      <div class="item-line" style="border:none;padding-top:14px">
        <span>Delivery</span><span>KES <?= number_format($detail['shipping'], 2) ?></span>
      </div>
      <div class="item-line" style="border:none;font-weight:700">
        <span>Total</span><span class="order-total">KES <?= number_format($detail['total'], 2) ?></span>
      </div>
    </div>
  <?php endif; ?>

  <?php if (empty($orders)): ?>
    <div class="empty">
      <p>You haven't placed any orders yet.</p>
      <p style="margin-top:8px"><a href="index.php" class="view-link">Browse jewellery →</a></p>
    </div>
  <?php else: ?>
    <?php foreach ($orders as $o): ?>
      <div class="order-card">
        <div class="order-head">
          <span class="order-id">Order #<?= $o['id'] ?></span>
          <span class="status-pill" style="background:<?= $status_colors[$o['status']] ?>22;color:<?= $status_colors[$o['status']] ?>">
            <?= htmlspecialchars($o['status']) ?>
          </span>
        </div>
        <div class="order-meta">
          <span class="order-date"><?= date('d M Y, g:i A', strtotime($o['created_at'])) ?></span>
          <span><?= htmlspecialchars(strtoupper($o['payment_method'])) ?></span>
          <span class="order-total">KES <?= number_format($o['total'], 2) ?></span>
        </div>
        <a href="orders.php?view=<?= $o['id'] ?>" class="view-link">View details →</a>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<footer style="text-align:center;padding:30px;color:var(--muted);font-size:.8rem">&copy; <?= date('Y') ?> New Wave Jewellery — BIT3208 Capstone Project</footer>
</body>
</html>
