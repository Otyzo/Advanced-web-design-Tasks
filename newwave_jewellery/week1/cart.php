<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['cart']))       $_SESSION['cart'] = [];
if (!isset($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

$message = $message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token'])
        die('Invalid CSRF token.');

    $action     = $_POST['action']     ?? '';
    $product_id = (int)($_POST['product_id'] ?? 0);

    if ($action === 'update') {
        $qty = (int)($_POST['quantity'] ?? 1);
        if ($qty <= 0) {
            unset($_SESSION['cart'][$product_id]);
            $message = 'Item removed.'; $message_type = 'info';
        } else {
            $stmt = $conn->prepare("SELECT name, stock FROM products WHERE id=?");
            $stmt->bind_param("i", $product_id); $stmt->execute();
            $p = $stmt->get_result()->fetch_assoc(); $stmt->close();
            if ($p && $qty <= (int)$p['stock']) {
                $_SESSION['cart'][$product_id]['quantity'] = $qty;
                $message = "Updated: {$p['name']}"; $message_type = 'success';
            } else {
                $message = 'Exceeds stock.'; $message_type = 'error';
            }
        }
    }
    if ($action === 'remove') {
        $name = $_SESSION['cart'][$product_id]['name'] ?? 'Item';
        unset($_SESSION['cart'][$product_id]);
        $message = "$name removed."; $message_type = 'info';
    }
    if ($action === 'clear') {
        $_SESSION['cart'] = [];
        $message = 'Cart cleared.'; $message_type = 'info';
    }
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Build cart from DB
$cart_items = []; $cart_total = 0; $cart_count = 0;
if (!empty($_SESSION['cart'])) {
    $ids   = array_keys($_SESSION['cart']);
    $ph    = implode(',', array_fill(0, count($ids), '?'));
    $stmt  = $conn->prepare("SELECT * FROM products WHERE id IN ($ph)");
    $stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
    $stmt->execute();
    $rows  = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    foreach ($rows as $p) {
        $qty          = $_SESSION['cart'][$p['id']]['quantity'] ?? 1;
        $sub          = $p['price'] * $qty;
        $cart_total  += $sub;
        $cart_count  += $qty;
        $cart_items[] = ['id'=>$p['id'],'name'=>$p['name'],'category'=>$p['category']??'',
                         'metal_type'=>$p['metal_type']??'Sterling Silver 925','image_url'=>$p['image_url']??'',
                         'price'=>$p['price'],'stock'=>$p['stock'],'qty'=>$qty,'subtotal'=>$sub];
    }
}
$shipping    = ($cart_total > 0 && $cart_total < 5000) ? 300 : 0;
$grand_total = $cart_total + $shipping;
$user        = $_SESSION['user'] ?? null;
$is_admin    = ($_SESSION['role'] ?? '') === 'admin';
$emojis      = ['Rings'=>'💍','Necklaces'=>'📿','Bracelets'=>'⛓️','Earrings'=>'💎','Anklets'=>'✨','Pendants'=>'🔗','Sets'=>'🎁'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Shopping Cart — New Wave Jewellery</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --bg:#0a0a0d;--surface:#141417;--card:#1b1b1f;--card2:#212226;
  --border:#2c2c33;--accent:#c7cad1;--accent2:#eceef1;
  --text:#f4f4f6;--muted:#9a9aa5;--danger:#f87171;
}
body{background:var(--bg);font-family:'DM Sans',sans-serif;color:var(--text);min-height:100vh}
img{max-width:100%;height:auto;display:block}

/* NAV */
nav{background:rgba(20,20,23,.95);backdrop-filter:blur(10px);border-bottom:1px solid var(--border);position:sticky;top:0;z-index:100;padding:0 24px}
.nav-inner{max-width:1200px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;height:64px;gap:16px}
.logo{font-family:'Playfair Display',serif;font-size:1.5rem;font-weight:700;background:linear-gradient(135deg,var(--accent2),var(--accent));-webkit-background-clip:text;-webkit-text-fill-color:transparent;text-decoration:none;white-space:nowrap}
.nav-search{flex:1;max-width:400px}
.nav-search form{display:flex;gap:8px}
.nav-search input{flex:1;padding:9px 14px;background:var(--surface);border:1px solid var(--border);border-radius:8px;color:var(--text);font-family:'DM Sans',sans-serif;font-size:.875rem;outline:none;transition:border-color .2s}
.nav-search input:focus{border-color:var(--accent)}
.nav-search input::placeholder{color:var(--muted)}
.btn-search{padding:9px 16px;background:var(--accent);border:none;border-radius:8px;color:#0a0a0d;font-size:.875rem;font-weight:600;cursor:pointer;transition:opacity .2s}
.btn-search:hover{opacity:.85}
.nav-links{display:flex;align-items:center;gap:10px}
.btn-login{padding:8px 18px;background:var(--card);border:1px solid var(--border);border-radius:7px;color:var(--text);text-decoration:none;font-size:.875rem;font-weight:500;transition:background .2s,border-color .2s}
.btn-login:hover{background:var(--card2);border-color:var(--accent)}
.btn-admin{padding:8px 18px;background:linear-gradient(135deg,var(--accent2),var(--accent));border:none;border-radius:7px;color:#0a0a0d;text-decoration:none;font-size:.875rem;font-weight:700;transition:opacity .2s}
.btn-admin:hover{opacity:.85}
.cart-link{position:relative;display:flex;align-items:center;text-decoration:none}
.cart-icon-wrap{width:38px;height:38px;background:var(--accent);border-radius:8px;display:flex;align-items:center;justify-content:center}
.cart-icon-wrap svg{width:18px;height:18px;stroke:#0a0a0d;fill:none}
.cart-badge{position:absolute;top:-6px;right:-6px;background:var(--danger);color:#fff;border-radius:50%;width:18px;height:18px;font-size:.65rem;font-weight:700;display:flex;align-items:center;justify-content:center}

/* TOAST */
.toast{position:fixed;bottom:24px;right:24px;z-index:999;padding:12px 20px;border-radius:10px;font-size:.875rem;font-weight:500;display:flex;align-items:center;gap:8px;animation:slideUp .35s cubic-bezier(.34,1.56,.64,1),fadeOut .4s ease 3.5s forwards;pointer-events:none}
.toast-success{background:var(--card);border:1px solid var(--accent);color:var(--accent2)}
.toast-error{background:var(--card);border:1px solid var(--danger);color:var(--danger)}
.toast-info{background:var(--card);border:1px solid var(--border);color:var(--muted)}
@keyframes slideUp{from{transform:translateY(60px);opacity:0}to{transform:translateY(0);opacity:1}}
@keyframes fadeOut{to{opacity:0;transform:translateY(60px)}}

/* MAIN */
.main{max-width:1200px;margin:0 auto;padding:36px 24px}
.breadcrumb{display:flex;align-items:center;gap:6px;font-size:.8rem;color:var(--muted);margin-bottom:28px}
.breadcrumb a{color:var(--muted);text-decoration:none;transition:color .2s}
.breadcrumb a:hover{color:var(--accent)}
.breadcrumb span{color:var(--text)}
.badge{padding:2px 10px;background:var(--accent);color:#0a0a0d;border-radius:20px;font-size:.7rem;font-weight:700}
h1{font-family:'Playfair Display',serif;font-size:1.8rem;font-weight:700;margin-bottom:32px}

/* LAYOUT */
.cart-layout{display:flex;gap:28px;align-items:flex-start}
.cart-main{flex:1;min-width:0}
.cart-side{width:320px;flex-shrink:0;position:sticky;top:84px}

/* TABLE HEADER */
.table-head{display:grid;grid-template-columns:1fr 120px 150px 100px;gap:12px;padding:10px 16px;font-size:.7rem;text-transform:uppercase;letter-spacing:.07em;color:var(--muted);border-bottom:1px solid var(--border);font-weight:600}

/* ITEM ROW */
.item-row{display:grid;grid-template-columns:1fr 120px 150px 100px;gap:12px;align-items:center;padding:18px 16px;border-bottom:1px solid rgba(44,44,51,.5);transition:background .2s}
.item-row:hover{background:rgba(199,202,209,.03)}
.item-info{display:flex;align-items:center;gap:14px}
.item-thumb{width:56px;height:56px;background:var(--surface);border:1px solid var(--border);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.6rem;flex-shrink:0;overflow:hidden}
.item-name{font-family:'Playfair Display',serif;font-size:.95rem;font-weight:600;margin-bottom:3px}
.item-cat{font-size:.65rem;text-transform:uppercase;letter-spacing:.08em;color:var(--accent);font-weight:600}
.item-stock{font-size:.7rem;color:var(--muted);margin-top:2px}
.item-price{font-family:'Playfair Display',serif;font-size:.95rem;font-weight:700;color:var(--accent2)}
.item-subtotal{font-family:'Playfair Display',serif;font-size:.95rem;font-weight:700;text-align:right}

/* QTY STEPPER */
.qty-wrap{display:flex;align-items:center;gap:4px}
.qty-btn{width:30px;height:30px;background:var(--card2);border:1px solid var(--border);border-radius:6px;color:var(--text);font-size:1rem;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .2s;font-family:'DM Sans',sans-serif}
.qty-btn:hover{background:var(--accent);border-color:var(--accent);color:#0a0a0d}
.qty-input{width:40px;height:30px;background:var(--surface);border:1px solid var(--border);border-radius:6px;color:var(--text);text-align:center;font-size:.85rem;font-weight:600;font-family:'DM Sans',sans-serif;outline:none;-moz-appearance:textfield}
.qty-input::-webkit-inner-spin-button,.qty-input::-webkit-outer-spin-button{-webkit-appearance:none}
.btn-update{margin-left:4px;padding:4px 10px;background:transparent;border:1px solid var(--border);border-radius:6px;color:var(--muted);font-size:.72rem;cursor:pointer;transition:all .2s;font-family:'DM Sans',sans-serif}
.btn-update:hover{border-color:var(--accent);color:var(--accent2)}
.btn-remove{padding:5px 12px;background:transparent;border:1px solid rgba(248,113,113,.3);border-radius:6px;color:var(--danger);font-size:.75rem;font-weight:600;cursor:pointer;transition:all .2s;font-family:'DM Sans',sans-serif}
.btn-remove:hover{background:var(--danger);color:#fff;border-color:var(--danger)}

/* CART ACTIONS */
.cart-actions{display:flex;align-items:center;justify-content:space-between;padding:20px 0 0}
.btn-continue{display:flex;align-items:center;gap:6px;color:var(--muted);font-size:.85rem;font-weight:500;text-decoration:none;transition:color .2s;background:none;border:none;cursor:pointer;font-family:'DM Sans',sans-serif}
.btn-continue:hover{color:var(--accent2)}
.btn-clear{padding:8px 18px;background:transparent;border:1px solid rgba(248,113,113,.3);border-radius:7px;color:var(--danger);font-size:.8rem;font-weight:600;cursor:pointer;transition:all .2s;font-family:'DM Sans',sans-serif}
.btn-clear:hover{background:var(--danger);color:#fff;border-color:var(--danger)}

/* SUMMARY CARD */
.summary-card{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:24px}
.summary-title{font-family:'Playfair Display',serif;font-size:1.1rem;font-weight:700;margin-bottom:20px}
.summary-line{display:flex;justify-content:space-between;align-items:center;font-size:.875rem;margin-bottom:10px}
.summary-line .label{color:var(--muted)}
.summary-line .val{color:var(--text);font-weight:500}
.summary-line .free{color:var(--accent2);font-weight:700}
.summary-divider{border:none;border-top:1px solid var(--border);margin:16px 0}
.summary-total{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px}
.summary-total .label{font-family:'Playfair Display',serif;font-size:1rem;font-weight:600}
.summary-total .amount{font-family:'Playfair Display',serif;font-size:1.3rem;font-weight:700;color:var(--accent2)}
.csrf-box{background:var(--surface);border:1px solid var(--border);border-radius:8px;padding:8px 12px;font-family:monospace;font-size:.65rem;color:var(--muted);word-break:break-all;line-height:1.5;margin-bottom:16px}
.btn-checkout{width:100%;padding:14px;background:linear-gradient(135deg,var(--accent2),var(--accent));border:none;border-radius:10px;color:#0a0a0d;font-family:'Playfair Display',serif;font-size:1rem;font-weight:700;cursor:pointer;transition:opacity .2s;letter-spacing:.02em}
.btn-checkout:hover{opacity:.9}
.pay-badges{display:flex;align-items:center;justify-content:center;gap:6px;margin-top:14px;flex-wrap:wrap}
.pay-badge{padding:4px 10px;background:var(--surface);border:1px solid var(--border);border-radius:5px;font-size:.7rem;font-weight:600}
.pay-badge.mpesa{color:var(--accent2);border-color:rgba(199,202,209,.35)}
.pay-badge.other{color:var(--muted)}
.mpesa-instructions{background:var(--surface);border:1px solid var(--border);border-left:3px solid var(--accent2);border-radius:8px;padding:12px 14px;margin-bottom:14px;font-size:.8rem;color:var(--text);line-height:1.5}
.mpesa-instructions strong{color:var(--accent2);letter-spacing:.02em}

/* EMPTY */
.empty-wrap{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:100px 20px;text-align:center}
.empty-icon{font-size:5rem;opacity:.1;margin-bottom:24px}
.empty-wrap h2{font-family:'Playfair Display',serif;font-size:1.5rem;font-weight:700;margin-bottom:10px}
.empty-wrap p{color:var(--muted);font-size:.9rem;margin-bottom:28px}
.btn-browse{padding:12px 32px;background:var(--accent);border:none;border-radius:10px;color:#0a0a0d;font-family:'Playfair Display',serif;font-size:.95rem;font-weight:700;text-decoration:none;transition:opacity .2s}
.btn-browse:hover{opacity:.85}

/* ITEM BREAKDOWN */
.breakdown-item{display:flex;justify-content:space-between;font-size:.8rem;margin-bottom:8px}
.breakdown-item .bname{color:var(--muted)}
.breakdown-item .bval{color:var(--text);font-weight:500}

footer{text-align:center;padding:32px 20px;border-top:1px solid var(--border);color:var(--muted);font-size:.8rem;margin-top:48px}

/* ══ Media Queries — Mobile-First Breakpoints ══
   Base layout above targets desktop. These breakpoints stack the
   navbar, cart summary, and item rows for tablets and phones, per
   Week 8 responsive/mobile-first requirements. */

@media(max-width:900px){
  .cart-layout{flex-direction:column}
  .cart-side{width:100%;position:static}
  .table-head{display:none}
  .item-row{grid-template-columns:1fr auto;gap:10px}
  .item-price,.item-subtotal{display:none}
}

/* Tablet and below: stack the navbar so search doesn't get crushed */
@media(max-width:768px){
  .nav-inner{flex-wrap:wrap;height:auto;padding:12px 0;row-gap:10px}
  .nav-search{order:3;max-width:100%;flex-basis:100%}
  .nav-links{flex-wrap:wrap;justify-content:flex-end;gap:8px}
  .main{padding:24px 16px 40px}
}

/* Mobile phones: tighten spacing and controls further */
@media(max-width:480px){
  .logo{font-size:1.25rem}
  h1{font-size:1.4rem}
  .nav-links a,.btn-login,.btn-admin{padding:6px 10px;font-size:.78rem}
  .cart-icon-wrap{width:34px;height:34px}
  .summary-card{padding:18px}
  .item-info{gap:10px}
  .item-thumb{width:46px;height:46px;font-size:1.3rem}
}
</style>
</head>
<body>

<!-- NAVBAR -->
<nav>
  <div class="nav-inner">
    <a href="index.php" class="logo">New Wave Jewellery</a>
    <div class="nav-search">
      <form method="GET" action="index.php">
        <input type="text" name="search" placeholder="Search rings, necklaces…">
        <button type="submit" class="btn-search">Search</button>
      </form>
    </div>
    <div class="nav-links">
      <?php if (isset($_SESSION['user_id'])): ?>
        <span style="font-size:.85rem;color:var(--muted)">Hi, <?= htmlspecialchars($_SESSION['name'] ?? 'User') ?></span>
        <?php if ($is_admin): ?>
          <a href="admin.php" class="btn-admin">Admin Panel</a>
        <?php endif; ?>
        <a href="cart.php" class="cart-link">
          <div class="cart-icon-wrap">
            <svg viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
          </div>
          <?php if ($cart_count > 0): ?>
            <span class="cart-badge"><?= $cart_count ?></span>
          <?php endif; ?>
        </a>
        <a href="logout.php" class="btn-login">Logout</a>
      <?php else: ?>
        <a href="register.php" style="color:var(--muted);text-decoration:none;font-size:.875rem">Register</a>
        <a href="login.php" class="btn-login">Login</a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<!-- TOAST -->
<?php if ($message): ?>
<div class="toast toast-<?= $message_type ?>">
  <?= $message_type === 'success' ? '✓' : ($message_type === 'error' ? '✕' : 'ℹ') ?>
  <?= htmlspecialchars($message) ?>
</div>
<?php endif; ?>

<!-- MAIN -->
<div class="main">

  <!-- Breadcrumb -->
  <div class="breadcrumb">
    <a href="index.php">Home</a>
    <span>›</span>
    <span>Shopping Cart</span>
    <?php if ($cart_count > 0): ?>
      <span class="badge"><?= $cart_count ?> item<?= $cart_count !== 1 ? 's' : '' ?></span>
    <?php endif; ?>
  </div>

  <h1>Shopping Cart</h1>

  <?php if (empty($cart_items)): ?>
  <!-- EMPTY STATE -->
  <div class="empty-wrap">
    <div class="empty-icon">💍</div>
    <h2>Your cart is empty</h2>
    <p>You haven't added any jewellery pieces yet.</p>
    <a href="index.php" class="btn-browse">Browse Jewellery</a>
  </div>

  <?php else: ?>
  <div class="cart-layout">

    <!-- LEFT: Items -->
    <div class="cart-main">
      <div class="table-head">
        <div>Product</div>
        <div style="text-align:center">Unit Price</div>
        <div style="text-align:center">Quantity</div>
        <div style="text-align:right">Subtotal</div>
      </div>

      <?php foreach ($cart_items as $item): ?>
      <div class="item-row">

        <!-- Product info -->
        <div class="item-info">
          <div class="item-thumb">
            <?php if (!empty($item['image_url'])): ?>
              <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:10px">
            <?php else: ?>
              <?= $emojis[$item['category']] ?? '💍' ?>
            <?php endif; ?>
          </div>
          <div>
            <div class="item-name"><?= htmlspecialchars($item['name']) ?></div>
            <div class="item-cat"><?= htmlspecialchars($item['category']) ?> · <?= htmlspecialchars($item['metal_type']) ?></div>
            <div class="item-stock"><?= $item['stock'] ?> in stock</div>
            <!-- Mobile price -->
            <div style="margin-top:4px;font-family:'Playfair Display',serif;font-weight:700;color:var(--accent2);font-size:.9rem">
              KES <?= number_format($item['price'],2) ?> × <?= $item['qty'] ?>
              = <span style="color:var(--text)">KES <?= number_format($item['subtotal'],2) ?></span>
            </div>
          </div>
        </div>

        <!-- Unit price -->
        <div class="item-price" style="text-align:center">
          KES <?= number_format($item['price'],2) ?>
        </div>

        <!-- Qty stepper + update -->
        <div>
          <form method="POST" style="margin:0">
            <input type="hidden" name="csrf_token"  value="<?= $_SESSION['csrf_token'] ?>">
            <input type="hidden" name="action"      value="update">
            <input type="hidden" name="product_id"  value="<?= $item['id'] ?>">
            <div class="qty-wrap">
              <button type="button" class="qty-btn" onclick="step(this,-1)">−</button>
              <input type="number" class="qty-input" name="quantity"
                     value="<?= $item['qty'] ?>" min="0" max="<?= $item['stock'] ?>">
              <button type="button" class="qty-btn" onclick="step(this,1)">+</button>
              <button type="submit" class="btn-update">Update</button>
            </div>
          </form>
          <!-- Remove -->
          <form method="POST" style="margin:6px 0 0">
            <input type="hidden" name="csrf_token"  value="<?= $_SESSION['csrf_token'] ?>">
            <input type="hidden" name="action"      value="remove">
            <input type="hidden" name="product_id"  value="<?= $item['id'] ?>">
            <button type="submit" class="btn-remove">Remove</button>
          </form>
        </div>

        <!-- Subtotal -->
        <div class="item-subtotal">KES <?= number_format($item['subtotal'],2) ?></div>
      </div>
      <?php endforeach; ?>

      <!-- Bottom actions -->
      <div class="cart-actions">
        <a href="index.php" class="btn-continue">← Continue Shopping</a>
        <form method="POST" onsubmit="return confirm('Clear entire cart?')">
          <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
          <input type="hidden" name="action"     value="clear">
          <button type="submit" class="btn-clear">Clear Cart</button>
        </form>
      </div>
    </div>

    <!-- RIGHT: Summary -->
    <div class="cart-side">
      <div class="summary-card">
        <div class="summary-title">Order Summary</div>

        <!-- Breakdown -->
        <?php foreach ($cart_items as $item): ?>
        <div class="breakdown-item">
          <span class="bname"><?= htmlspecialchars($item['name']) ?> ×<?= $item['qty'] ?></span>
          <span class="bval">KES <?= number_format($item['subtotal'],2) ?></span>
        </div>
        <?php endforeach; ?>

        <hr class="summary-divider">

        <div class="summary-line">
          <span class="label">Subtotal</span>
          <span class="val">KES <?= number_format($cart_total,2) ?></span>
        </div>
        <div class="summary-line">
          <span class="label">Delivery</span>
          <?php if ($shipping === 0): ?>
            <span class="free">FREE</span>
          <?php else: ?>
            <span class="val">KES <?= number_format($shipping,2) ?></span>
          <?php endif; ?>
        </div>
        <?php if ($shipping > 0): ?>
          <p style="font-size:.72rem;color:var(--muted);margin-bottom:10px">
            Free nationwide delivery on orders over KES 5,000
          </p>
        <?php endif; ?>

        <hr class="summary-divider">

        <!-- CSRF visible for logbook screenshot -->
        <div class="csrf-box">🔒 CSRF Token: <?= substr($_SESSION['csrf_token'],0,28) ?>...</div>

        <div class="summary-total">
          <span class="label">Total</span>
          <span class="amount">KES <?= number_format($grand_total,2) ?></span>
        </div>

        <form method="POST" action="checkout.php">
          <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
          <div class="field" style="margin-bottom:14px">
            <label style="display:block;font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin-bottom:6px">Payment Method</label>
            <select name="payment_method" id="payment_method" onchange="toggleMpesaBox()" style="width:100%;padding:10px;background:var(--surface);border:1px solid var(--border);border-radius:8px;color:var(--text);font-family:'DM Sans',sans-serif">
              <option value="mpesa">M-Pesa</option>
              <option value="card">Card (Visa/Mastercard)</option>
              <option value="cash">Cash on Delivery</option>
            </select>
          </div>

          <div class="mpesa-instructions" id="mpesaBox">
            <label style="display:block;font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin-bottom:6px">M-Pesa Phone Number</label>
            <input type="tel" name="phone" id="mpesaPhone" placeholder="07XXXXXXXX"
                   style="width:100%;padding:10px;background:var(--surface);border:1px solid var(--border);border-radius:8px;color:var(--text);font-family:'DM Sans',sans-serif;margin-bottom:10px">
            📲 You'll get a prompt on this phone to enter your M-Pesa PIN and complete payment.
          </div>

          <button type="submit" class="btn-checkout">Proceed to Checkout →</button>
        </form>

        <div class="pay-badges">
          <span style="font-size:.7rem;color:var(--muted)">We accept:</span>
          <span class="pay-badge mpesa">M-Pesa</span>
          <span class="pay-badge other">Visa</span>
          <span class="pay-badge other">Mastercard</span>
          <span class="pay-badge other">Cash</span>
        </div>
      </div>
    </div>

  </div>
  <?php endif; ?>
</div>

<footer>&copy; <?= date('Y') ?> New Wave Jewellery — BIT3208 Capstone Project</footer>

<script>
function step(btn, delta) {
  const form  = btn.closest('form');
  const input = form.querySelector('.qty-input');
  const max   = parseInt(input.max) || 99;
  let val     = parseInt(input.value) || 1;
  val = Math.min(max, Math.max(0, val + delta));
  input.value = val;
}
const toast = document.querySelector('.toast');
if (toast) setTimeout(() => toast.style.display = 'none', 4000);

function toggleMpesaBox() {
  const sel   = document.getElementById('payment_method');
  const box   = document.getElementById('mpesaBox');
  const phone = document.getElementById('mpesaPhone');
  if (!sel || !box) return;
  const isMpesa = sel.value === 'mpesa';
  box.style.display = isMpesa ? 'block' : 'none';
  if (phone) phone.required = isMpesa;
}
if (document.getElementById('payment_method')) toggleMpesaBox();
</script>
</body>
</html>
