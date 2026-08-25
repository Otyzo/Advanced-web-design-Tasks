<?php
// ============================================================
// index.php — Product Catalog (Homepage)
// New Wave Jewellery — Silver Jewellery E-Commerce
// ============================================================

session_start();
require_once 'db.php';

// ── Cart session init ─────────────────────────────────────────
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ── Handle Add to Cart POST ───────────────────────────────────
$cart_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('Invalid CSRF token.');
    }

    $product_id = (int)($_POST['product_id'] ?? 0);

    $stmt = $conn->prepare("SELECT id, name, price, stock FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($product && $product['stock'] > 0) {
        if (isset($_SESSION['cart'][$product_id])) {
            $new_qty = $_SESSION['cart'][$product_id]['quantity'] + 1;
            if ($new_qty <= $product['stock']) {
                $_SESSION['cart'][$product_id]['quantity'] = $new_qty;
            }
        } else {
            $_SESSION['cart'][$product_id] = [
                'quantity' => 1,
                'name'     => $product['name'],
                'price'    => $product['price'],
            ];
        }
        $cart_message = '✓ "' . $product['name'] . '" added to cart';
    }
}

// ── Cart item count for navbar badge ─────────────────────────
$cart_count = 0;
foreach ($_SESSION['cart'] as $item) {
    $cart_count += $item['quantity'];
}

// ── Fetch products (Week 13 — search: relevance ranking, ─────
//    filters, and sorting, in the spirit of the search-engine
//    concepts covered that week, implemented via SQL rather
//    than an external engine like Elasticsearch) ──────────────
$category_filter = $_GET['category'] ?? '';
$search_query    = trim($_GET['search'] ?? '');
$min_price       = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? (float)$_GET['min_price'] : null;
$max_price       = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (float)$_GET['max_price'] : null;
$sort            = $_GET['sort'] ?? 'newest';

$sql    = "SELECT * FROM products WHERE 1=1";
$params = [];
$types  = '';

if ($category_filter) {
    $sql     .= " AND category = ?";
    $types   .= 's';
    $params[] = $category_filter;
}
if ($search_query) {
    // Simple relevance weighting: name matches rank above description-only matches
    $sql     .= " AND (name LIKE ? OR description LIKE ?)";
    $types   .= 'ss';
    $like     = "%$search_query%";
    $params[] = $like;
    $params[] = $like;
}
if ($min_price !== null) {
    $sql     .= " AND price >= ?";
    $types   .= 'd';
    $params[] = $min_price;
}
if ($max_price !== null) {
    $sql     .= " AND price <= ?";
    $types   .= 'd';
    $params[] = $max_price;
}

switch ($sort) {
    case 'price_asc':  $sql .= " ORDER BY price ASC";  break;
    case 'price_desc': $sql .= " ORDER BY price DESC"; break;
    case 'name_az':    $sql .= " ORDER BY name ASC";   break;
    default:
        // "newest" — and, when searching, surface exact/starting-with
        // name matches first (a lightweight relevance signal)
        if ($search_query) {
            $sql .= " ORDER BY (name LIKE ?) DESC, created_at DESC";
            $types   .= 's';
            $params[] = "$search_query%";
        } else {
            $sql .= " ORDER BY created_at DESC";
        }
}

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ── Categories for filter ─────────────────────────────────────
$cats = $conn->query("SELECT DISTINCT category FROM products WHERE category != 'Watches' ORDER BY category")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>New Wave Jewellery — Silver Jewellery Catalog</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,700;1,600&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --bg:      #0a0a0d;
    --surface: #141417;
    --card:    #1b1b1f;
    --card2:   #212226;
    --border:  #2c2c33;
    --accent:  #c7cad1;
    --accent2: #eceef1;
    --text:    #f4f4f6;
    --muted:   #9a9aa5;
    --danger:  #f87171;
  }

  body {
    background: var(--bg);
    font-family: 'DM Sans', sans-serif;
    color: var(--text);
    min-height: 100vh;
  }

  /* ── Navbar ── */
  nav {
    background: rgba(20,20,23,0.95);
    backdrop-filter: blur(10px);
    border-bottom: 1px solid var(--border);
    position: sticky;
    top: 0;
    z-index: 100;
    padding: 0 24px;
  }

  .nav-inner {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 68px;
    gap: 16px;
  }

  .logo {
    font-family: 'Playfair Display', serif;
    font-size: 1.5rem;
    font-weight: 700;
    background: linear-gradient(135deg, var(--accent2), var(--accent) 60%, #8f939c);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    white-space: nowrap;
    text-decoration: none;
    letter-spacing: 0.02em;
  }
  .logo .sub { display: block; font-family: 'DM Sans', sans-serif; font-size: 0.6rem; letter-spacing: 0.25em; color: var(--muted); -webkit-text-fill-color: var(--muted); text-transform: uppercase; margin-top: -2px; }

  .nav-search { flex: 1; max-width: 400px; }
  .nav-search form { display: flex; gap: 8px; }
  .nav-search input {
    flex: 1; padding: 9px 14px;
    background: var(--surface); border: 1px solid var(--border);
    border-radius: 8px; color: var(--text);
    font-family: 'DM Sans', sans-serif; font-size: 0.875rem;
    outline: none; transition: border-color .2s;
  }
  .nav-search input:focus { border-color: var(--accent); }
  .nav-search input::placeholder { color: var(--muted); }

  .btn-search {
    padding: 9px 16px; background: var(--accent); border: none;
    border-radius: 8px; color: #0a0a0d; font-size: 0.875rem;
    font-weight: 600; cursor: pointer; white-space: nowrap; transition: opacity .2s;
  }
  .btn-search:hover { opacity: .85; }

  .nav-links { display: flex; align-items: center; gap: 12px; }
  .nav-links a {
    color: var(--muted); text-decoration: none; font-size: 0.875rem;
    padding: 8px 14px; border-radius: 7px; transition: color .2s, background .2s;
  }
  .nav-links a:hover { color: var(--text); background: var(--card); }

  .btn-login {
    padding: 8px 18px; background: var(--card);
    border: 1px solid var(--border); border-radius: 7px;
    color: var(--text); text-decoration: none; font-size: 0.875rem;
    font-weight: 500; transition: background .2s, border-color .2s;
  }
  .btn-login:hover { background: var(--card2); border-color: var(--accent); }

  .btn-admin {
    padding: 8px 18px;
    background: linear-gradient(135deg, var(--accent2), var(--accent));
    border: none; border-radius: 7px; color: #0a0a0d;
    text-decoration: none; font-size: 0.875rem; font-weight: 700; transition: opacity .2s;
  }
  .btn-admin:hover { opacity: .85; }

  /* Cart icon */
  .cart-link {
    position: relative; display: flex; align-items: center;
    text-decoration: none;
  }
  .cart-icon {
    width: 38px; height: 38px; background: var(--accent);
    border-radius: 8px; display: flex; align-items: center;
    justify-content: center; transition: opacity .2s;
  }
  .cart-icon:hover { opacity: .85; }
  .cart-icon svg { width: 18px; height: 18px; color: #0a0a0d; stroke: #0a0a0d; }
  .cart-badge {
    position: absolute; top: -6px; right: -6px;
    background: var(--danger); color: #fff;
    border-radius: 50%; width: 18px; height: 18px;
    font-size: 0.65rem; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
  }

  /* ── Hero ── */
  .hero {
    background:
      radial-gradient(ellipse 700px 400px at 50% -10%, rgba(199,202,209,0.08) 0%, transparent 70%),
      linear-gradient(135deg, #0a0a0d 0%, #101013 50%, #0a0a0d 100%);
    padding: 70px 24px 56px; text-align: center;
    border-bottom: 1px solid var(--border);
    position: relative; overflow: hidden;
  }
  .hero h1 {
    font-family: 'Playfair Display', serif;
    font-weight: 700;
    font-size: clamp(1.8rem, 4vw, 2.8rem);
    margin-bottom: 12px;
    letter-spacing: 0.01em;
  }
  .hero h1 span {
    background: linear-gradient(135deg, var(--accent2), var(--accent));
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
  }
  .hero p { color: var(--muted); font-size: 0.95rem; max-width: 480px; margin: 0 auto; }

  /* ── Main / filters ── */
  .main { max-width: 1200px; margin: 0 auto; padding: 40px 24px 60px; }

  .filters { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; margin-bottom: 28px; }
  .filter-label { font-size: 0.8rem; color: var(--muted); margin-right: 4px; }
  .filter-btn {
    padding: 7px 16px; background: var(--card); border: 1px solid var(--border);
    border-radius: 20px; color: var(--muted);
    font-size: 0.8rem; cursor: pointer; text-decoration: none; transition: all .2s;
  }
  .filter-btn:hover, .filter-btn.active { background: var(--accent); border-color: var(--accent); color: #0a0a0d; font-weight: 600; }

  /* ── Products grid ── */
  .section-header { display: flex; align-items: baseline; justify-content: space-between; margin-bottom: 20px; }
  .section-title { font-family: 'Playfair Display', serif; font-size: 1.25rem; font-weight: 600; }
  .results-count { font-size: 0.8rem; color: var(--muted); }

  .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 20px; }

  .product-card {
    background: var(--card); border: 1px solid var(--border); border-radius: 14px;
    overflow: hidden; transition: transform .25s, border-color .25s, box-shadow .25s;
    animation: fadeIn .4s ease both;
  }
  @keyframes fadeIn { from { opacity:0; transform:translateY(12px); } to { opacity:1; transform:translateY(0); } }
  .product-card:hover {
    transform: translateY(-4px); border-color: rgba(199,202,209,0.45);
    box-shadow: 0 12px 32px rgba(0,0,0,0.4), 0 0 0 1px rgba(199,202,209,0.12);
  }

  .product-img {
    width: 100%; height: 180px;
    background: linear-gradient(135deg, var(--surface) 0%, var(--card2) 100%);
    display: flex; align-items: center; justify-content: center;
    font-size: 3rem; border-bottom: 1px solid var(--border);
    overflow: hidden;
  }
  .product-img img {
    width: 100%; height: 100%; object-fit: cover; display: block;
    transition: transform .4s ease;
  }
  .product-card:hover .product-img img { transform: scale(1.05); }

  .product-body { padding: 18px; }
  .product-category { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.08em; color: var(--accent); font-weight: 600; margin-bottom: 6px; }
  .product-name { font-family: 'Playfair Display', serif; font-size: 1.05rem; font-weight: 600; margin-bottom: 6px; line-height: 1.3; }
  .product-desc { font-size: 0.8rem; color: var(--muted); line-height: 1.5; margin-bottom: 8px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
  .product-meta { font-size: 0.72rem; color: var(--muted); margin-bottom: 12px; }
  .product-meta strong { color: var(--accent); font-weight: 600; }
  .product-footer { display: flex; align-items: center; justify-content: space-between; }
  .product-price { font-family: 'Playfair Display', serif; font-size: 1.1rem; font-weight: 700; color: var(--accent2); }
  .product-stock { font-size: 0.75rem; color: var(--muted); }
  .stock-low { color: var(--danger); }

  .btn-cart {
    padding: 7px 16px; background: rgba(199,202,209,0.12);
    border: 1px solid rgba(199,202,209,0.35); border-radius: 6px;
    color: var(--accent2); font-size: 0.8rem; font-weight: 600;
    cursor: pointer; transition: all .2s; font-family: 'DM Sans', sans-serif;
  }
  .btn-cart:hover { background: var(--accent); color: #0a0a0d; border-color: var(--accent); }
  .btn-cart:disabled { opacity: 0.4; cursor: not-allowed; }

  .empty-state { text-align: center; padding: 80px 20px; color: var(--muted); }
  .empty-state .icon { font-size: 3rem; margin-bottom: 16px; }
  .empty-state h3 { font-family:'Playfair Display',serif; font-size:1.2rem; margin-bottom:8px; color:var(--text); }

  /* ── Toast ── */
  #toast {
    position: fixed; bottom: 24px; right: 24px;
    background: var(--card); border: 1px solid var(--accent);
    border-radius: 10px; padding: 12px 20px;
    color: var(--accent2); font-size: 0.875rem; font-weight: 500;
    transform: translateY(80px); opacity: 0;
    transition: all .35s cubic-bezier(.34,1.56,.64,1);
    z-index: 999; pointer-events: none;
  }
  #toast.show { transform: translateY(0); opacity: 1; }

  footer {
    text-align: center; padding: 32px 20px;
    border-top: 1px solid var(--border);
    color: var(--muted); font-size: 0.8rem; margin-top: 40px;
  }
</style>
</head>
<body>

<!-- Navbar -->
<nav>
  <div class="nav-inner">
    <a href="index.php" class="logo">New Wave<span class="sub">Jewellery</span></a>

    <div class="nav-search">
      <form method="GET" action="index.php">
        <?php if ($category_filter): ?>
          <input type="hidden" name="category" value="<?= htmlspecialchars($category_filter) ?>">
        <?php endif; ?>
        <input type="text" name="search" placeholder="Search rings, necklaces…"
               value="<?= htmlspecialchars($search_query) ?>">
        <button type="submit" class="btn-search">Search</button>
      </form>
    </div>

    <div class="nav-links">
      <?php if (isset($_SESSION['user_id'])): ?>
        <span style="font-size:.85rem;color:var(--muted)">Hi, <?= htmlspecialchars($_SESSION['name']) ?></span>
        <?php if ($_SESSION['role'] === 'admin'): ?>
          <a href="admin.php" class="btn-admin">Admin Panel</a>
        <?php endif; ?>

        <!-- Cart icon with live badge -->
        <a href="cart.php" class="cart-link">
          <div class="cart-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
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

<!-- Hero -->
<div class="hero">
  <h1>Timeless <span>Sterling Silver</span> Jewellery</h1>
  <p>Handcrafted rings, necklaces, bracelets and more — 925 silver, delivered across Kenya.</p>
</div>

<!-- Main content -->
<div class="main">

  <!-- Category Filters -->
  <div class="filters">
    <span class="filter-label">Filter:</span>
    <a href="index.php<?= $search_query ? '?search='.urlencode($search_query) : '' ?>"
       class="filter-btn <?= !$category_filter ? 'active' : '' ?>">All</a>
    <a href="watches.php" class="filter-btn">⌚ Watches</a>
    <?php foreach ($cats as $cat): ?>
      <a href="index.php?category=<?= urlencode($cat['category']) ?><?= $search_query ? '&search='.urlencode($search_query) : '' ?>"
         class="filter-btn <?= $category_filter === $cat['category'] ? 'active' : '' ?>">
        <?= htmlspecialchars($cat['category']) ?>
      </a>
    <?php endforeach; ?>
  </div>

  <!-- Price range + sort (Week 13 — search refinement) -->
  <form method="GET" action="index.php" class="filters" style="margin-top:-8px">
    <?php if ($category_filter): ?><input type="hidden" name="category" value="<?= htmlspecialchars($category_filter) ?>"><?php endif; ?>
    <?php if ($search_query): ?><input type="hidden" name="search" value="<?= htmlspecialchars($search_query) ?>"><?php endif; ?>
    <span class="filter-label">Price (KES):</span>
    <input type="number" name="min_price" placeholder="Min" value="<?= htmlspecialchars($_GET['min_price'] ?? '') ?>"
           style="width:90px;padding:7px 10px;background:var(--surface);border:1px solid var(--border);border-radius:8px;color:var(--text);font-size:.8rem">
    <input type="number" name="max_price" placeholder="Max" value="<?= htmlspecialchars($_GET['max_price'] ?? '') ?>"
           style="width:90px;padding:7px 10px;background:var(--surface);border:1px solid var(--border);border-radius:8px;color:var(--text);font-size:.8rem">
    <span class="filter-label" style="margin-left:8px">Sort:</span>
    <select name="sort" onchange="this.form.submit()"
            style="padding:7px 10px;background:var(--surface);border:1px solid var(--border);border-radius:8px;color:var(--text);font-size:.8rem">
      <option value="newest"    <?= $sort === 'newest'    ? 'selected' : '' ?>>Newest</option>
      <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
      <option value="price_desc"<?= $sort === 'price_desc'? 'selected' : '' ?>>Price: High to Low</option>
      <option value="name_az"   <?= $sort === 'name_az'   ? 'selected' : '' ?>>Name: A–Z</option>
    </select>
    <button type="submit" class="filter-btn">Apply</button>
  </form>

  <!-- Section header -->
  <div class="section-header">
    <div class="section-title">
      <?= $category_filter ? htmlspecialchars($category_filter) : 'All Jewellery' ?>
      <?= $search_query ? ' — results for "<em>'.htmlspecialchars($search_query).'</em>"' : '' ?>
    </div>
    <div class="results-count"><?= count($products) ?> item<?= count($products) !== 1 ? 's' : '' ?></div>
  </div>

  <!-- Product Grid -->
  <?php if (empty($products)): ?>
    <div class="empty-state">
      <div class="icon">💍</div>
      <h3>No pieces found</h3>
      <p>Try a different search or browse all categories.</p>
    </div>
  <?php else: ?>
    <div class="grid">
      <?php
      $emojis = ['Rings'=>'💍','Necklaces'=>'📿','Bracelets'=>'⛓️','Earrings'=>'💎','Anklets'=>'✨','Pendants'=>'🔗','Sets'=>'🎁','Watches'=>'⌚'];
      foreach ($products as $i => $p):
        $emoji      = $emojis[$p['category']] ?? '💍';
        $stockClass = $p['stock'] < 5 ? 'stock-low' : '';
        $out        = $p['stock'] <= 0;
      ?>
      <div class="product-card" style="animation-delay:<?= $i * 0.05 ?>s">
        <div class="product-img">
          <?php if (!empty($p['image_url'])): ?>
            <img src="<?= htmlspecialchars($p['image_url']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy">
          <?php else: ?>
            <?= $emoji ?>
          <?php endif; ?>
        </div>
        <div class="product-body">
          <div class="product-category"><?= htmlspecialchars($p['category']) ?></div>
          <div class="product-name"><?= htmlspecialchars($p['name']) ?></div>
          <div class="product-desc"><?= htmlspecialchars($p['description']) ?></div>
          <div class="product-meta">
            <strong><?= htmlspecialchars($p['metal_type'] ?: 'Sterling Silver 925') ?></strong>
            <?php if (!empty($p['weight_grams'])): ?> · <?= number_format($p['weight_grams'], 2) ?>g<?php endif; ?>
          </div>
          <div class="product-footer">
            <div>
              <div class="product-price"><?= $p['call_for_price'] ? 'Contact for price' : 'KES ' . number_format($p['price'], 2) ?></div>
              <div class="product-stock <?= $stockClass ?>">
                <?= $out ? 'Out of stock' : $p['stock'].' in stock' ?>
              </div>
            </div>

            <!-- REAL PHP cart form -->
            <?php if ($p['call_for_price']): ?>
              <a href="https://wa.me/254795349957?text=<?= urlencode('Hi, I\'m interested in the '.$p['name'].' — could you tell me the price?') ?>" target="_blank" class="btn-cart" style="text-align:center;text-decoration:none">
                💬 WhatsApp
              </a>
            <?php else: ?>
            <form method="POST" action="index.php<?= $category_filter ? '?category='.urlencode($category_filter) : '' ?><?= $search_query ? ($category_filter ? '&' : '?').'search='.urlencode($search_query) : '' ?>" style="margin:0">
              <input type="hidden" name="add_to_cart"  value="1">
              <input type="hidden" name="product_id"   value="<?= $p['id'] ?>">
              <input type="hidden" name="csrf_token"   value="<?= $_SESSION['csrf_token'] ?>">
              <button type="submit" class="btn-cart" <?= $out ? 'disabled' : '' ?>>
                <?= $out ? 'Sold Out' : '+ Cart' ?>
              </button>
            </form>
            <?php endif; ?>

          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<footer>
  &copy; <?= date('Y') ?> New Wave Jewellery — BIT3208 Capstone Project
</footer>

<!-- Toast notification -->
<div id="toast">✓ Added to cart</div>

<script>
// Show toast if PHP added an item
<?php if ($cart_message): ?>
const toast = document.getElementById('toast');
toast.textContent = '<?= addslashes($cart_message) ?>';
toast.classList.add('show');
setTimeout(() => toast.classList.remove('show'), 2800);
<?php endif; ?>
</script>

</body>
</html>
