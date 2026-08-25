<?php
// ============================================================
// mpesa_status.php — "Check your phone" waiting page
// New Wave Jewellery
//
// Shown right after an STK push is sent. Polls
// mpesa_status_check.php every 3s until the callback (Daraja →
// mpesa_callback.php) marks the order paid / cancelled, or the
// customer gives up and waits ~2 minutes.
// ============================================================

session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$order_id = (int)($_GET['order'] ?? 0);

$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param('ii', $order_id, $_SESSION['user_id']);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    header('Location: orders.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Complete Payment — New Wave Jewellery</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{background:#0a0a0d;color:#f4f4f6;font-family:'DM Sans',sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
.card{max-width:440px;width:100%;background:#1b1b1f;border:1px solid #2c2c33;border-radius:16px;padding:40px 36px;text-align:center}
h1{font-family:'Playfair Display',serif;font-size:1.4rem;margin-bottom:10px}
p{color:#9a9aa5;font-size:.9rem;margin-bottom:8px;line-height:1.55}
.amount{font-family:'Playfair Display',serif;font-size:1.6rem;font-weight:700;margin:18px 0;color:#eceef1}
.spinner{width:44px;height:44px;border:3px solid #2c2c33;border-top-color:#d4af6a;border-radius:50%;margin:22px auto;animation:spin 1s linear infinite}
@keyframes spin{to{transform:rotate(360deg)}}
.state-icon{font-size:2.6rem;margin-bottom:6px}
a.btn{display:inline-block;margin-top:20px;padding:12px 24px;background:linear-gradient(135deg,#eceef1,#c7cad1);color:#0a0a0d;text-decoration:none;border-radius:8px;font-weight:700;font-family:'Playfair Display',serif}
.btn-secondary{background:transparent;border:1px solid #2c2c33;color:#9a9aa5;margin-left:10px}
.receipt{font-size:.8rem;color:#d4af6a;margin-top:10px}
</style>
</head>
<body>
<div class="card" id="card">
  <div class="spinner" id="spinner"></div>
  <h1 id="title">Check your phone</h1>
  <p id="subtitle">An M-Pesa prompt was sent to <strong><?= htmlspecialchars($order['phone_number']) ?></strong>. Enter your PIN to complete payment.</p>
  <div class="amount">KES <?= number_format($order['total'], 2) ?></div>
  <p style="font-size:.78rem" id="waitNote">This page updates automatically — no need to refresh.</p>
</div>

<script>
const orderId = <?= (int)$order_id ?>;
let attempts = 0;
const maxAttempts = 40; // ~2 minutes at 3s intervals

async function poll() {
  attempts++;
  try {
    const res = await fetch(`mpesa_status_check.php?order=${orderId}`);
    const data = await res.json();

    if (data.status === 'paid') {
      showResult('success', '✅', 'Payment received!', data.receipt
        ? `M-Pesa receipt: <span class="receipt">${data.receipt}</span>`
        : 'Your payment was confirmed.');
      return;
    }
    if (data.status === 'cancelled') {
      showResult('failed', '⚠️', 'Payment not completed', data.desc || 'The request was cancelled or timed out.');
      return;
    }
  } catch (e) { /* keep polling */ }

  if (attempts >= maxAttempts) {
    showResult('timeout', '⏱️', 'Still waiting?', 'If you already paid, it may take a minute to reflect. Otherwise you can retry from your orders page.');
    return;
  }
  setTimeout(poll, 3000);
}

function showResult(kind, icon, title, sub) {
  document.getElementById('spinner').style.display = 'none';
  document.getElementById('title').textContent = title;
  document.getElementById('title').before(Object.assign(document.createElement('div'), {className:'state-icon', textContent: icon}));
  document.getElementById('subtitle').innerHTML = sub;
  document.getElementById('waitNote').innerHTML =
    `<a href="orders.php" class="btn">View Orders</a>` +
    (kind !== 'success' ? `<a href="cart.php" class="btn btn-secondary">Try Again</a>` : '');
}

setTimeout(poll, 3000);
</script>
</body>
</html>
