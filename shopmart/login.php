<?php
// ============================================================
// login.php — Login Page with JS Validation
// BIT3208 Capstone — ShopMart
// ============================================================

session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: ' . ($_SESSION['role'] === 'admin' ? 'admin.php' : 'index.php'));
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'db.php';

    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        // Prepared statement — prevents SQL injection
        $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name']    = $user['name'];
                $_SESSION['email']   = $user['email'];
                $_SESSION['role']    = $user['role'];

                header('Location: ' . ($user['role'] === 'admin' ? 'admin.php' : 'index.php'));
                exit;
            }
        }
        $error = 'Invalid email or password.';
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — ShopMart</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --bg:      #0b0f1a;
    --surface: #141824;
    --card:    #1c2235;
    --border:  #2a3245;
    --accent:  #10b981;
    --accent2: #34d399;
    --text:    #f1f5f9;
    --muted:   #94a3b8;
    --danger:  #f87171;
  }

  body {
    min-height: 100vh;
    background: var(--bg);
    font-family: 'DM Sans', sans-serif;
    color: var(--text);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    background-image:
      radial-gradient(ellipse 600px 400px at 80% 20%, rgba(16,185,129,0.08) 0%, transparent 70%),
      radial-gradient(ellipse 400px 600px at 10% 80%, rgba(16,185,129,0.05) 0%, transparent 60%);
  }

  .wrapper {
    width: 100%;
    max-width: 420px;
    animation: fadeUp .5s ease both;
  }

  @keyframes fadeUp {
    from { opacity:0; transform:translateY(20px); }
    to   { opacity:1; transform:translateY(0); }
  }

  .logo {
    text-align: center;
    margin-bottom: 32px;
  }

  .logo-text {
    font-family: 'Outfit', sans-serif;
    font-size: 2rem;
    font-weight: 700;
    background: linear-gradient(135deg, var(--accent), var(--accent2));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    letter-spacing: -0.5px;
  }

  .logo-sub {
    color: var(--muted);
    font-size: 0.85rem;
    margin-top: 4px;
  }

  .card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 36px;
    box-shadow: 0 25px 60px rgba(0,0,0,0.4);
  }

  .card-title {
    font-family: 'Outfit', sans-serif;
    font-size: 1.4rem;
    font-weight: 600;
    margin-bottom: 6px;
  }

  .card-sub {
    color: var(--muted);
    font-size: 0.875rem;
    margin-bottom: 28px;
  }

  .field { margin-bottom: 20px; }

  label {
    display: block;
    font-size: 0.82rem;
    font-weight: 500;
    color: var(--muted);
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }

  input[type="email"],
  input[type="password"],
  input[type="text"] {
    width: 100%;
    padding: 12px 16px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 8px;
    color: var(--text);
    font-family: 'DM Sans', sans-serif;
    font-size: 0.95rem;
    transition: border-color .2s, box-shadow .2s;
    outline: none;
  }

  input:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(16,185,129,0.15);
  }

  input.error-input { border-color: var(--danger); }

  .field-error {
    font-size: 0.78rem;
    color: var(--danger);
    margin-top: 5px;
    display: none;
  }

  .field-error.show { display: block; }

  .alert {
    background: rgba(248,113,113,0.1);
    border: 1px solid rgba(248,113,113,0.3);
    border-radius: 8px;
    padding: 12px 16px;
    color: var(--danger);
    font-size: 0.875rem;
    margin-bottom: 20px;
  }

  .btn {
    width: 100%;
    padding: 13px;
    background: linear-gradient(135deg, var(--accent), #059669);
    border: none;
    border-radius: 8px;
    color: #fff;
    font-family: 'Outfit', sans-serif;
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
    letter-spacing: 0.02em;
    transition: opacity .2s, transform .1s;
    margin-top: 8px;
  }

  .btn:hover  { opacity: .9; }
  .btn:active { transform: scale(.98); }

  .footer-link {
    text-align: center;
    margin-top: 22px;
    font-size: 0.875rem;
    color: var(--muted);
  }

  .footer-link a {
    color: var(--accent);
    text-decoration: none;
    font-weight: 500;
  }

  .footer-link a:hover { text-decoration: underline; }

  .divider {
    text-align: center;
    margin: 20px 0;
    color: var(--muted);
    font-size: 0.8rem;
    position: relative;
  }

  .divider::before, .divider::after {
    content: '';
    position: absolute;
    top: 50%;
    width: 40%;
    height: 1px;
    background: var(--border);
  }

  .divider::before { left: 0; }
  .divider::after  { right: 0; }
</style>
</head>
<body>

<div class="wrapper">
  <div class="logo">
    <div class="logo-text">ShopMart</div>
    <div class="logo-sub">Smart E-Commerce Platform</div>
  </div>

  <div class="card">
    <div class="card-title">Welcome back</div>
    <div class="card-sub">Sign in to your account</div>

    <?php if ($error): ?>
      <div class="alert"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form id="loginForm" method="POST" action="login.php" novalidate>
      <div class="field">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email"
               placeholder="you@example.com"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        <div class="field-error" id="emailErr">Please enter a valid email address.</div>
      </div>

      <div class="field">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="••••••••">
        <div class="field-error" id="passErr">Password must be at least 6 characters.</div>
      </div>

      <button type="submit" class="btn">Sign In</button>
    </form>

    <div class="divider">or</div>

    <div class="footer-link">
      Don't have an account? <a href="register.php">Create one</a>
    </div>
    <div class="footer-link" style="margin-top:10px">
      <a href="index.php">← Browse as guest</a>
    </div>
  </div>
</div>

<script>
// ── Client-side JS Validation ──────────────────────────────
const form     = document.getElementById('loginForm');
const emailEl  = document.getElementById('email');
const passEl   = document.getElementById('password');
const emailErr = document.getElementById('emailErr');
const passErr  = document.getElementById('passErr');

function validateEmail(v) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v.trim());
}

function showError(input, errEl) {
  input.classList.add('error-input');
  errEl.classList.add('show');
}

function clearError(input, errEl) {
  input.classList.remove('error-input');
  errEl.classList.remove('show');
}

emailEl.addEventListener('input', () => {
  if (validateEmail(emailEl.value)) clearError(emailEl, emailErr);
});

passEl.addEventListener('input', () => {
  if (passEl.value.length >= 6) clearError(passEl, passErr);
});

form.addEventListener('submit', function (e) {
  let valid = true;

  if (!validateEmail(emailEl.value)) {
    showError(emailEl, emailErr);
    valid = false;
  } else {
    clearError(emailEl, emailErr);
  }

  if (passEl.value.length < 6) {
    showError(passEl, passErr);
    valid = false;
  } else {
    clearError(passEl, passErr);
  }

  if (!valid) e.preventDefault();
});
</script>

</body>
</html>
