<?php
// ============================================================
// checkout.php — Order Placement (Week 10-11 Continuous Project)
// New Wave Jewellery
// Converts the session cart into a persisted order using a
// database transaction: orders + order_items are inserted,
// product stock is decremented, and the cart is cleared.
// ============================================================

session_start();
require_once 'db.php';
require_once 'mpesa_helper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?next=cart.php');
    exit;
}

if (empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' ||
    !isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
    header('Location: cart.php');
    exit;
}

// ── Rebuild cart from DB (never trust client-side totals) ────
$ids = array_keys($_SESSION['cart']);
$ph  = implode(',', array_fill(0, count($ids), '?'));
$stmt = $conn->prepare("SELECT * FROM products WHERE id IN ($ph) FOR UPDATE");
$stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$items = [];
$subtotal = 0;
$error = '';

foreach ($products as $p) {
    $qty = (int)($_SESSION['cart'][$p['id']]['quantity'] ?? 0);
    if ($qty < 1) continue;
    if ($qty > (int)$p['stock']) {
        $error = "Sorry, \"{$p['name']}\" only has {$p['stock']} left in stock.";
        break;
    }
    $line = $p['price'] * $qty;
    $subtotal += $line;
    $items[] = ['id' => $p['id'], 'name' => $p['name'], 'price' => $p['price'], 'qty' => $qty, 'line' => $line];
}

if (!$error && empty($items)) $error = 'Your cart is empty.';

if (!$error) {
    $shipping = ($subtotal > 0 && $subtotal < 5000) ? 300 : 0;
    $total    = $subtotal + $shipping;
    $payment_method = in_array($_POST['payment_method'] ?? '', ['mpesa','card','cash'])
                      ? $_POST['payment_method'] : 'mpesa';

    $phone = trim($_POST['phone'] ?? '');
    if ($payment_method === 'mpesa') {
        if (!mpesa_format_phone($phone)) {
            $error = 'Please enter a valid M-Pesa phone number (e.g. 07XXXXXXXX).';
        }
    }

    // ── Transaction: order header + line items + stock decrement ──
    if (!$error) {
    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare(
            "INSERT INTO orders (user_id, subtotal, shipping, total, payment_method, phone_number, status)
             VALUES (?, ?, ?, ?, ?, ?, 'pending')"
        );
        $stmt->bind_param('idddss', $_SESSION['user_id'], $subtotal, $shipping, $total, $payment_method, $phone);
        $stmt->execute();
        $order_id = $stmt->insert_id;
        $stmt->close();

        $itemStmt  = $conn->prepare(
            "INSERT INTO order_items (order_id, product_id, product_name, unit_price, quantity, subtotal)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stockStmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");

        foreach ($items as $it) {
            $itemStmt->bind_param('iisdid', $order_id, $it['id'], $it['name'], $it['price'], $it['qty'], $it['line']);
            $itemStmt->execute();

            $stockStmt->bind_param('iii', $it['qty'], $it['id'], $it['qty']);
            $stockStmt->execute();
            if ($stockStmt->affected_rows < 1) {
                throw new Exception("Stock ran out for \"{$it['name']}\" during checkout.");
            }
        }
        $itemStmt->close();
        $stockStmt->close();

        $conn->commit();

        // Success — clear cart & rotate CSRF token
        $_SESSION['cart'] = [];
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        if ($payment_method === 'mpesa') {
            // Order is safely saved regardless of what happens next —
            // trigger the STK push now that stock/order are committed.
            try {
                $stk = mpesa_stk_push($phone, $total, 'NWJ' . $order_id, 'NewWaveJewellery');

                $upd = $conn->prepare("UPDATE orders SET mpesa_checkout_id = ? WHERE id = ?");
                $upd->bind_param('si', $stk['CheckoutRequestID'], $order_id);
                $upd->execute();
                $upd->close();

                header('Location: mpesa_status.php?order=' . $order_id);
                exit;
            } catch (Exception $e) {
                // STK push failed (bad credentials, no internet reaching
                // Safaricom, etc.) — order still exists as 'pending', let
                // the customer know to pay manually instead.
                header('Location: orders.php?placed=' . $order_id . '&mpesa_failed=' . urlencode($e->getMessage()));
                exit;
            }
        }

        header('Location: orders.php?placed=' . $order_id);
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        $error = 'Checkout failed: ' . $e->getMessage() . ' Please try again.';
    }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Checkout — New Wave Jewellery</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{background:#0a0a0d;color:#f4f4f6;font-family:'DM Sans',sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
.card{max-width:440px;background:#1b1b1f;border:1px solid #2c2c33;border-radius:16px;padding:36px;text-align:center}
h1{font-family:'Playfair Display',serif;font-size:1.4rem;margin-bottom:14px}
p{color:#9a9aa5;font-size:.9rem;margin-bottom:24px;line-height:1.5}
.error{color:#f87171}
a.btn{display:inline-block;padding:12px 24px;background:linear-gradient(135deg,#eceef1,#c7cad1);color:#0a0a0d;text-decoration:none;border-radius:8px;font-weight:700;font-family:'Playfair Display',serif}
</style>
</head>
<body>
<div class="card">
  <h1>⚠️ Checkout Issue</h1>
  <p class="error"><?= htmlspecialchars($error) ?></p>
  <a href="cart.php" class="btn">← Back to Cart</a>
</div>
</body>
</html>
