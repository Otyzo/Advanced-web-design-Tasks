<?php
// ============================================================
// admin.php — Admin CRUD Dashboard
// New Wave Jewellery
// ============================================================

session_start();

// Access control — admin only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

require_once 'db.php';

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = '';
$msg_type = 'success';

// ── Handle DELETE (POST-only, CSRF-protected) ──────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('Invalid CSRF token.');
    }
    $id = (int)$_POST['delete_id'];
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        $message = 'Product deleted successfully.';
    } else {
        $message = 'Error deleting product.';
        $msg_type = 'error';
    }
    $stmt->close();
}

// ── Handle ADD / EDIT ──────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('Invalid CSRF token.');
    }
    $id           = (int)($_POST['id'] ?? 0);
    $name         = trim($_POST['name']        ?? '');
    $description  = trim($_POST['description'] ?? '');
    $price        = (float)($_POST['price']    ?? 0);
    $stock        = (int)($_POST['stock']      ?? 0);
    $category     = trim($_POST['category']    ?? '');
    $metal_type   = trim($_POST['metal_type']  ?? 'Sterling Silver 925');
    $purity       = trim($_POST['purity']      ?? '925');
    $weight_grams = (float)($_POST['weight_grams'] ?? 0);
    $image_url    = trim($_POST['image_url']   ?? '');

    if (empty($name) || $price <= 0) {
        $message  = 'Name and a valid price are required.';
        $msg_type = 'error';
    } else {
        if ($id > 0) {
            // UPDATE
            $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, stock=?, category=?, metal_type=?, purity=?, weight_grams=?, image_url=? WHERE id=?");
            $stmt->bind_param('ssdisssdsi', $name, $description, $price, $stock, $category, $metal_type, $purity, $weight_grams, $image_url, $id);
        } else {
            // INSERT
            $stmt = $conn->prepare("INSERT INTO products (name, description, price, stock, category, metal_type, purity, weight_grams, image_url) VALUES (?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param('ssdisssds', $name, $description, $price, $stock, $category, $metal_type, $purity, $weight_grams, $image_url);
        }

        if ($stmt->execute()) {
            $message = $id > 0 ? 'Product updated successfully.' : 'Product added successfully.';
        } else {
            $message  = 'Database error: ' . $conn->error;
            $msg_type = 'error';
        }
        $stmt->close();
    }
}

// ── Load product for edit ──────────────────────────────────
$edit_product = null;
if (isset($_GET['edit'])) {
    $id   = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $edit_product = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// ── Fetch all products ─────────────────────────────────────
$products = $conn->query("SELECT * FROM products ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
$total    = count($products);
$users    = $conn->query("SELECT COUNT(*) AS c FROM users")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Panel — New Wave Jewellery</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
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
    --warn:    #fbbf24;
  }

  body {
    background: var(--bg);
    font-family: 'DM Sans', sans-serif;
    color: var(--text);
    min-height: 100vh;
    display: flex;
  }

  /* ── Sidebar ── */
  .sidebar {
    width: 220px;
    background: var(--surface);
    border-right: 1px solid var(--border);
    padding: 24px 0;
    position: sticky;
    top: 0;
    height: 100vh;
    flex-shrink: 0;
  }

  .sidebar-logo {
    font-family: 'Playfair Display', serif;
    font-size: 1.3rem;
    font-weight: 700;
    background: linear-gradient(135deg, var(--accent2), var(--accent));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    padding: 0 20px 20px;
    border-bottom: 1px solid var(--border);
    display: block;
  }

  .sidebar-section {
    padding: 16px 20px 8px;
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--muted);
    font-weight: 600;
  }

  .sidebar-link {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 20px;
    color: var(--muted);
    text-decoration: none;
    font-size: 0.875rem;
    transition: color .2s, background .2s;
    border-left: 3px solid transparent;
  }

  .sidebar-link:hover,
  .sidebar-link.active {
    color: var(--text);
    background: var(--card);
    border-left-color: var(--accent);
  }

  .sidebar-link .icon { font-size: 1rem; }

  .sidebar-user {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 16px 20px;
    border-top: 1px solid var(--border);
    background: var(--surface);
  }

  .sidebar-user .name {
    font-size: 0.875rem;
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .sidebar-user .role {
    font-size: 0.7rem;
    color: var(--accent);
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }

  /* ── Main area ── */
  .main { flex: 1; overflow-x: hidden; }

  .topbar {
    background: rgba(20,20,23,0.95);
    border-bottom: 1px solid var(--border);
    padding: 0 28px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: sticky;
    top: 0;
    z-index: 50;
  }

  .topbar-title {
    font-family: 'Playfair Display', serif;
    font-size: 1.1rem;
    font-weight: 600;
  }

  .btn-outline {
    padding: 7px 16px;
    background: transparent;
    border: 1px solid var(--border);
    border-radius: 7px;
    color: var(--muted);
    font-family: 'DM Sans', sans-serif;
    font-size: 0.8rem;
    cursor: pointer;
    text-decoration: none;
    transition: all .2s;
  }
  .btn-outline:hover { border-color: var(--accent); color: var(--accent2); }

  .content { padding: 28px; }

  /* ── Stats ── */
  .stats {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 16px;
    margin-bottom: 28px;
  }

  .stat-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 20px;
    transition: border-color .2s;
  }
  .stat-card:hover { border-color: rgba(199,202,209,0.35); }

  .stat-label {
    font-size: 0.75rem;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin-bottom: 8px;
  }

  .stat-value {
    font-family: 'Playfair Display', serif;
    font-size: 2rem;
    font-weight: 700;
    color: var(--accent2);
  }

  /* ── Alert ── */
  .alert {
    padding: 12px 18px;
    border-radius: 8px;
    font-size: 0.875rem;
    margin-bottom: 20px;
    animation: fadeIn .3s ease;
  }
  .alert.success { background: rgba(199,202,209,0.1); border: 1px solid rgba(199,202,209,0.3); color: var(--accent2); }
  .alert.error   { background: rgba(248,113,113,0.1); border: 1px solid rgba(248,113,113,0.3); color: var(--danger); }

  @keyframes fadeIn { from { opacity:0; transform:translateY(-6px); } to { opacity:1; transform:translateY(0); } }

  /* ── Two-column layout ── */
  .two-col {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 24px;
    align-items: start;
  }

  /* ── Form Card ── */
  .form-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 24px;
    position: sticky;
    top: 88px;
  }

  .form-card h2 {
    font-family: 'Playfair Display', serif;
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 20px;
    padding-bottom: 14px;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .field { margin-bottom: 16px; }

  label {
    display: block;
    font-size: 0.75rem;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-weight: 500;
    margin-bottom: 6px;
  }

  input[type="text"],
  input[type="number"],
  input[type="url"],
  textarea,
  select {
    width: 100%;
    padding: 10px 14px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 8px;
    color: var(--text);
    font-family: 'DM Sans', sans-serif;
    font-size: 0.875rem;
    outline: none;
    transition: border-color .2s;
    resize: vertical;
  }

  input:focus, textarea:focus, select:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(199,202,209,0.12);
  }

  select option { background: var(--card); }

  .form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
  }

  .btn-primary {
    width: 100%;
    padding: 11px;
    background: linear-gradient(135deg, var(--accent2), var(--accent));
    border: none;
    border-radius: 8px;
    color: #0a0a0d;
    font-family: 'Playfair Display', serif;
    font-size: 0.9rem;
    font-weight: 700;
    cursor: pointer;
    transition: opacity .2s, transform .1s;
    margin-top: 4px;
  }
  .btn-primary:hover  { opacity: .9; }
  .btn-primary:active { transform: scale(.98); }

  .btn-cancel {
    width: 100%;
    padding: 10px;
    background: transparent;
    border: 1px solid var(--border);
    border-radius: 8px;
    color: var(--muted);
    font-family: 'DM Sans', sans-serif;
    font-size: 0.875rem;
    cursor: pointer;
    margin-top: 8px;
    text-align: center;
    text-decoration: none;
    display: block;
    transition: all .2s;
  }
  .btn-cancel:hover { border-color: var(--danger); color: var(--danger); }

  /* ── Table ── */
  .table-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 14px;
    overflow: hidden;
  }

  .table-header {
    padding: 18px 20px;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .table-header h2 {
    font-family: 'Playfair Display', serif;
    font-size: 1rem;
    font-weight: 600;
  }

  table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.875rem;
  }

  thead th {
    padding: 12px 16px;
    text-align: left;
    background: var(--surface);
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    color: var(--muted);
    font-weight: 600;
    border-bottom: 1px solid var(--border);
  }

  tbody tr {
    border-bottom: 1px solid rgba(44,44,51,0.6);
    transition: background .15s;
  }
  tbody tr:hover { background: var(--card2); }
  tbody tr:last-child { border-bottom: none; }

  td { padding: 13px 16px; vertical-align: middle; }

  .badge {
    display: inline-block;
    padding: 2px 10px;
    border-radius: 20px;
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }
  .badge-rings      { background: rgba(96,165,250,0.15);  color: #60a5fa; }
  .badge-necklaces  { background: rgba(251,191,36,0.15);  color: #fbbf24; }
  .badge-bracelets  { background: rgba(167,139,250,0.15); color: #a78bfa; }
  .badge-earrings   { background: rgba(249,115,22,0.15);  color: #fb923c; }
  .badge-anklets    { background: rgba(52,211,153,0.15);  color: #34d399; }
  .badge-pendants   { background: rgba(236,72,153,0.15);  color: #f472b6; }
  .badge-sets       { background: rgba(199,202,209,0.18); color: #eceef1; }
  .badge-default    { background: rgba(148,163,184,0.15); color: #94a3b8; }

  .btn-edit, .btn-del {
    padding: 5px 12px;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    border: 1px solid;
    transition: all .2s;
    margin-right: 6px;
    display: inline-block;
  }
  .btn-edit { color: #60a5fa; border-color: rgba(96,165,250,0.3); background: rgba(96,165,250,0.08); }
  .btn-edit:hover { background: #60a5fa; color: #fff; }
  .btn-del  { color: var(--danger); border-color: rgba(248,113,113,0.3); background: rgba(248,113,113,0.08); font-family: 'DM Sans', sans-serif; }
  .btn-del:hover  { background: var(--danger); color: #fff; }

  .price-cell { color: var(--accent2); font-weight: 600; font-family:'Playfair Display',serif; }
  .stock-low  { color: var(--danger); }
  .meta-cell  { color: var(--muted); font-size: 0.78rem; }
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <div class="sidebar-logo">New Wave Jewellery</div>

  <div class="sidebar-section">Menu</div>
  <a href="admin.php" class="sidebar-link active">
    <span class="icon">💍</span> Products
  </a>
  <a href="index.php" class="sidebar-link">
    <span class="icon">🛍️</span> View Store
  </a>

  <div class="sidebar-user">
    <div class="role">Admin</div>
    <div class="name"><?= htmlspecialchars($_SESSION['name']) ?></div>
    <a href="logout.php" style="font-size:.75rem;color:var(--danger);text-decoration:none;margin-top:6px;display:inline-block">Logout →</a>
  </div>
</div>

<!-- Main -->
<div class="main">
  <div class="topbar">
    <div class="topbar-title">Product Management</div>
    <a href="index.php" class="btn-outline">← View Storefront</a>
  </div>

  <div class="content">

    <!-- Stats -->
    <div class="stats">
      <div class="stat-card">
        <div class="stat-label">Total Products</div>
        <div class="stat-value"><?= $total ?></div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Registered Users</div>
        <div class="stat-value"><?= $users ?></div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Low Stock (&lt;5)</div>
        <div class="stat-value" style="color:var(--danger)">
          <?= count(array_filter($products, fn($p) => $p['stock'] < 5)) ?>
        </div>
      </div>
    </div>

    <!-- Alert -->
    <?php if ($message): ?>
      <div class="alert <?= $msg_type ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <!-- Two column layout -->
    <div class="two-col">

      <!-- Products Table -->
      <div class="table-card">
        <div class="table-header">
          <h2>All Products (<?= $total ?>)</h2>
        </div>
        <table>
          <thead>
            <tr>
              <th>Photo</th>
              <th>ID</th>
              <th>Name</th>
              <th>Category</th>
              <th>Metal / Weight</th>
              <th>Price (KES)</th>
              <th>Stock</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($products as $p):
              $cat_class = 'badge-' . strtolower($p['category'] ?: 'default');
              $stockClass = $p['stock'] < 5 ? 'stock-low' : '';
            ?>
            <tr>
              <td>
                <?php if (!empty($p['image_url'])): ?>
                  <img src="<?= htmlspecialchars($p['image_url']) ?>" alt="" style="width:42px;height:42px;object-fit:cover;border-radius:8px;border:1px solid var(--border)">
                <?php else: ?>
                  <div style="width:42px;height:42px;border-radius:8px;background:var(--surface);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:1.1rem">💍</div>
                <?php endif; ?>
              </td>
              <td style="color:var(--muted);font-size:.75rem">#<?= $p['id'] ?></td>
              <td style="font-weight:500"><?= htmlspecialchars($p['name']) ?></td>
              <td>
                <span class="badge <?= $cat_class ?>"><?= htmlspecialchars($p['category']) ?></span>
              </td>
              <td class="meta-cell"><?= htmlspecialchars($p['metal_type'] ?: 'Sterling Silver 925') ?><?= $p['weight_grams'] ? ' · '.number_format($p['weight_grams'],2).'g' : '' ?></td>
              <td class="price-cell"><?= $p['call_for_price'] ? 'Contact for price' : number_format($p['price'], 2) ?></td>
              <td class="<?= $stockClass ?>"><?= $p['stock'] ?></td>
              <td>
                <a href="admin.php?edit=<?= $p['id'] ?>" class="btn-edit">Edit</a>
                <form method="POST" action="admin.php" style="display:inline"
                      onsubmit="return confirm('Delete \'<?= addslashes(htmlspecialchars($p['name'])) ?>\'? This cannot be undone.')">
                  <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                  <input type="hidden" name="delete_id" value="<?= $p['id'] ?>">
                  <button type="submit" class="btn-del">Delete</button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- Add / Edit Form -->
      <div class="form-card">
        <h2>
          <?php if ($edit_product): ?>
            ✏️ Edit Product
          <?php else: ?>
            ➕ Add Product
          <?php endif; ?>
        </h2>

        <form method="POST" action="admin.php" id="productForm">
          <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
          <?php if ($edit_product): ?>
            <input type="hidden" name="id" value="<?= $edit_product['id'] ?>">
          <?php endif; ?>

          <div class="field">
            <label for="p_name">Product Name *</label>
            <input type="text" id="p_name" name="name" required
                   placeholder="e.g. Infinity Knot Ring"
                   value="<?= htmlspecialchars($edit_product['name'] ?? '') ?>">
          </div>

          <div class="field">
            <label for="p_desc">Description</label>
            <textarea id="p_desc" name="description" rows="3"
                      placeholder="Brief product description…"><?= htmlspecialchars($edit_product['description'] ?? '') ?></textarea>
          </div>

          <div class="form-row">
            <div class="field">
              <label for="p_price">Price (KES) *</label>
              <input type="number" id="p_price" name="price" required min="0.01" step="0.01"
                     placeholder="0.00"
                     value="<?= $edit_product['price'] ?? '' ?>">
            </div>
            <div class="field">
              <label for="p_stock">Stock Qty</label>
              <input type="number" id="p_stock" name="stock" min="0"
                     placeholder="0"
                     value="<?= $edit_product['stock'] ?? '0' ?>">
            </div>
          </div>

          <div class="field">
            <label for="p_cat">Category</label>
            <select id="p_cat" name="category">
              <option value="">— Select category —</option>
              <?php
              $cats_list = ['Rings','Necklaces','Bracelets','Earrings','Anklets','Pendants','Sets','Other'];
              foreach ($cats_list as $c):
                $sel = (($edit_product['category'] ?? '') === $c) ? 'selected' : '';
              ?>
              <option value="<?= $c ?>" <?= $sel ?>><?= $c ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-row">
            <div class="field">
              <label for="p_metal">Metal Type</label>
              <select id="p_metal" name="metal_type">
                <?php
                $metals = ['Sterling Silver 925','Fine Silver 999','Silver-Plated','Rhodium-Plated Silver'];
                $current_metal = $edit_product['metal_type'] ?? 'Sterling Silver 925';
                foreach ($metals as $m):
                  $sel = ($current_metal === $m) ? 'selected' : '';
                ?>
                <option value="<?= $m ?>" <?= $sel ?>><?= $m ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="field">
              <label for="p_purity">Purity</label>
              <input type="text" id="p_purity" name="purity"
                     placeholder="e.g. 925"
                     value="<?= htmlspecialchars($edit_product['purity'] ?? '925') ?>">
            </div>
          </div>

          <div class="field">
            <label for="p_weight">Weight (grams)</label>
            <input type="number" id="p_weight" name="weight_grams" min="0" step="0.01"
                   placeholder="0.00"
                   value="<?= $edit_product['weight_grams'] ?? '' ?>">
          </div>

          <div class="field">
            <label for="p_image">Image URL</label>
            <input type="url" id="p_image" name="image_url"
                   placeholder="https://images.example.com/photo.jpg"
                   value="<?= htmlspecialchars($edit_product['image_url'] ?? '') ?>">
          </div>

          <button type="submit" class="btn-primary">
            <?= $edit_product ? 'Update Product' : 'Add Product' ?>
          </button>

          <?php if ($edit_product): ?>
            <a href="admin.php" class="btn-cancel">Cancel Edit</a>
          <?php endif; ?>
        </form>
      </div>

    </div><!-- /two-col -->
  </div>
</div><!-- /main -->

</body>
</html>
