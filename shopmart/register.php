<?php
// ============================================================
// register.php — Registration Page with JS Validation
// BIT3208 Capstone — ShopMart
// ============================================================

session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'db.php';

    $name     = trim($_POST['name']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']      ?? '';
    $confirm  = $_POST['confirm']       ?? '';

    // Server-side validation
    if (empty($name) || empty($email) || empty($password) || empty($confirm)) {
        $error = 'All fields are required.';
    } elseif (strlen($name) < 2) {
        $error = 'Name must be at least 2 characters.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        // Check if email exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = 'An account with this email already exists.';
        } else {
            $stmt->close();
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?,?,?,'customer')");
            $stmt->bind_param('sss', $name, $email, $hash);
            $stmt->execute();
            $stmt->close();
            $success = 'Account created! <a href="login.php" style="color:#10b981;text-decoration:underline">Sign in now →</a>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create Account — ShopMart</title>
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
    --warn:    #fbbf24;
    --good:    #4ade80;
  }

  body {
    min-height: 100vh;
    background: var(--bg);
    font-family: 'DM Sans', sans-serif;
    color: var(--text);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px 20px;
    background-image:
      radial-gradient(ellipse 600px 400px at 80% 20%, rgba(16,185,129,0.08) 0%, transparent 70%),
      radial-gradient(ellipse 400px 600px at 10% 80%, rgba(16,185,129,0.05) 0%, transparent 60%);
  }

  .wrapper {
    width: 100%;
    max-width: 440px;
    animation: fadeUp .5s ease both;
  }

  @keyframes fadeUp {
    from { opacity:0; transform:translateY(20px); }
    to   { opacity:1; transform:translateY(0); }
  }

  .logo {
    text-align: center;
    margin-bottom: 28px;
  }

  .logo-text {
    font-family: 'Outfit', sans-serif;
    font-size: 2rem;
    font-weight: 700;
    background: linear-gradient(135deg, var(--accent), var(--accent2));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
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

  .field { margin-bottom: 18px; }

  label {
    display: block;
    font-size: 0.8rem;
    font-weight: 500;
    color: var(--muted);
    margin-bottom: 7px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }

  input[type="text"],
  input[type="email"],
  input[type="password"] {
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

  input.error-input   { border-color: var(--danger); }
  input.success-input { border-color: var(--good); }

  .field-error {
    font-size: 0.78rem;
    color: var(--danger);
    margin-top: 5px;
    display: none;
  }
  .field-error.show { display: block; }

  /* Password strength bar */
  .strength-wrap { margin-top: 8px; }
  .strength-bar-bg {
    height: 4px;
    background: var(--border);
    border-radius: 2px;
    overflow: hidden;
  }
  .strength-bar {
    height: 100%;
    width: 0%;
    border-radius: 2px;
    transition: width .3s, background .3s;
  }
  .strength-label {
    font-size: 0.75rem;
    color: var(--muted);
    margin-top: 4px;
  }

  .alert-error {
    background: rgba(248,113,113,0.1);
    border: 1px solid rgba(248,113,113,0.3);
    border-radius: 8px;
    padding: 12px 16px;
    color: var(--danger);
    font-size: 0.875rem;
    margin-bottom: 20px;
  }

  .alert-success {
    background: rgba(16,185,129,0.1);
    border: 1px solid rgba(16,185,129,0.3);
    border-radius: 8px;
    padding: 12px 16px;
    color: var(--accent2);
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
  .footer-link a { color: var(--accent); text-decoration: none; font-weight: 500; }
  .footer-link a:hover { text-decoration: underline; }
</style>
</head>
<body>

<div class="wrapper">
  <div class="logo">
    <div class="logo-text">ShopMart</div>
  </div>

  <div class="card">
    <div class="card-title">Create account</div>
    <div class="card-sub">Join ShopMart today — it's free</div>

    <?php if ($error): ?>
      <div class="alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="alert-success"><?= $success ?></div>
    <?php endif; ?>

    <?php if (!$success): ?>
    <form id="regForm" method="POST" action="register.php" novalidate>

      <div class="field">
        <label for="name">Full Name</label>
        <input type="text" id="name" name="name"
               placeholder="e.g. Alice Kamau"
               value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
        <div class="field-error" id="nameErr">Name must be at least 2 characters.</div>
      </div>

      <div class="field">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email"
               placeholder="you@example.com"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        <div class="field-error" id="emailErr">Please enter a valid email address.</div>
      </div>

      <div class="field">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Min. 6 characters">
        <div class="strength-wrap">
          <div class="strength-bar-bg"><div class="strength-bar" id="strengthBar"></div></div>
          <div class="strength-label" id="strengthLabel">Enter a password</div>
        </div>
        <div class="field-error" id="passErr">Password must be at least 6 characters.</div>
      </div>

      <div class="field">
        <label for="confirm">Confirm Password</label>
        <input type="password" id="confirm" name="confirm" placeholder="Repeat your password">
        <div class="field-error" id="confirmErr">Passwords do not match.</div>
      </div>

      <button type="submit" class="btn">Create Account</button>
    </form>
    <?php endif; ?>

    <div class="footer-link">
      Already have an account? <a href="login.php">Sign in</a>
    </div>
  </div>
</div>

<script>
// ── Password Strength Checker ──────────────────────────────
function getStrength(pw) {
  let score = 0;
  if (pw.length >= 6)  score++;
  if (pw.length >= 10) score++;
  if (/[A-Z]/.test(pw)) score++;
  if (/[0-9]/.test(pw)) score++;
  if (/[^A-Za-z0-9]/.test(pw)) score++;
  return score;
}

const passEl   = document.getElementById('password');
const bar      = document.getElementById('strengthBar');
const barLabel = document.getElementById('strengthLabel');

const levels = [
  { pct: '0%',   color: 'transparent', label: 'Enter a password' },
  { pct: '25%',  color: '#f87171',     label: 'Weak' },
  { pct: '50%',  color: '#fbbf24',     label: 'Fair' },
  { pct: '75%',  color: '#60a5fa',     label: 'Good' },
  { pct: '90%',  color: '#4ade80',     label: 'Strong' },
  { pct: '100%', color: '#10b981',     label: 'Very Strong ✓' },
];

passEl.addEventListener('input', () => {
  const s = passEl.value.length === 0 ? 0 : getStrength(passEl.value);
  bar.style.width      = levels[s].pct;
  bar.style.background = levels[s].color;
  barLabel.textContent = levels[s].label;
  barLabel.style.color = levels[s].color === 'transparent' ? '#94a3b8' : levels[s].color;
});

// ── Form Validation ────────────────────────────────────────
const form      = document.getElementById('regForm');
const nameEl    = document.getElementById('name');
const emailEl   = document.getElementById('email');
const confirmEl = document.getElementById('confirm');

function showErr(el, errId) { el.classList.add('error-input'); document.getElementById(errId).classList.add('show'); }
function clrErr(el, errId)  { el.classList.remove('error-input'); document.getElementById(errId).classList.remove('show'); }

nameEl.addEventListener('input',    () => nameEl.value.trim().length >= 2    ? clrErr(nameEl,'nameErr')    : null);
emailEl.addEventListener('input',   () => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailEl.value) ? clrErr(emailEl,'emailErr')   : null);
passEl.addEventListener('input',    () => passEl.value.length >= 6           ? clrErr(passEl,'passErr')    : null);
confirmEl.addEventListener('input', () => confirmEl.value === passEl.value   ? clrErr(confirmEl,'confirmErr') : null);

form && form.addEventListener('submit', function(e) {
  let ok = true;

  if (nameEl.value.trim().length < 2)                    { showErr(nameEl,'nameErr');       ok = false; }
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailEl.value)){ showErr(emailEl,'emailErr');      ok = false; }
  if (passEl.value.length < 6)                           { showErr(passEl,'passErr');        ok = false; }
  if (confirmEl.value !== passEl.value)                  { showErr(confirmEl,'confirmErr');  ok = false; }

  if (!ok) e.preventDefault();
});
</script>

</body>
</html>
