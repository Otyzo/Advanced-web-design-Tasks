<?php
// ============================================================
// mpesa_config.php — Safaricom Daraja API credentials
// New Wave Jewellery
//
// SANDBOX (for testing — works out of the box):
//   Get your own free sandbox Consumer Key/Secret at
//   https://developer.safaricom.co.ke  →  My Apps  →  Add a new App
//   (attach the "Lipa Na M-Pesa Sandbox" product to it)
//
// The Shortcode + Passkey below are Safaricom's PUBLIC sandbox
// test values — they are safe to leave as-is for testing and
// only work in sandbox mode. Everyone uses the same ones.
//
// PRODUCTION (real STK push to real phones):
//   Apply for a Paybill/Till number from Safaricom, then replace
//   ALL values below with the ones from your live Daraja app +
//   your business's real Shortcode and Passkey.
// ============================================================

// ── 1. Environment: 'sandbox' or 'production' ────────────────
define('MPESA_ENV', 'sandbox');

// ── 2. Your Daraja app credentials (get these yourself — free) ─
define('MPESA_CONSUMER_KEY',    'YOUR_CONSUMER_KEY_HERE');
define('MPESA_CONSUMER_SECRET', 'YOUR_CONSUMER_SECRET_HERE');

// ── 3. Shortcode + Passkey ─────────────────────────────────────
// Sandbox defaults (Safaricom's public test values — leave as-is
// for testing). Replace both with your real ones for production.
define('MPESA_SHORTCODE', '174379');
define('MPESA_PASSKEY',   'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919');

// ── 4. Callback URL — Safaricom calls THIS to confirm payment ──
// localhost is NOT reachable from the internet, so Safaricom can
// never reach it. For testing, run ngrok (https://ngrok.com) and
// paste the https URL it gives you here, e.g.:
//   https://abcd1234.ngrok-free.app/newwave_jewellery/mpesa_callback.php
// For production, use your real domain instead.
define('MPESA_CALLBACK_URL', 'https://YOUR-NGROK-OR-DOMAIN-HERE/newwave_jewellery/mpesa_callback.php');

// ── Base URL switches automatically with environment ──────────
define('MPESA_BASE_URL', MPESA_ENV === 'production'
    ? 'https://api.safaricom.co.ke'
    : 'https://sandbox.safaricom.co.ke'
);
