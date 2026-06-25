<?php
/**
 * ShopMart - Week 1: Local Environment Test
 * Confirms Apache is serving PHP correctly on localhost.
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ShopMart - Local Server Test</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #1c1f26;
            color: #e7e9ec;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        .box {
            background: #242831;
            border: 1px solid #343a46;
            border-radius: 10px;
            padding: 32px 40px;
            text-align: center;
        }
        h1 { color: #10b981; margin-bottom: 8px; }
        p { color: #9aa3b2; font-size: 0.9rem; }
        code {
            background: #1a1d23;
            padding: 2px 6px;
            border-radius: 4px;
            color: #10b981;
        }
    </style>
</head>
<body>
    <div class="box">
        <h1>Hello, ShopMart!</h1>
        <p>If you can see this page, Apache is correctly serving PHP from <code>htdocs</code>.</p>
        <p>Server time: <?= date('Y-m-d H:i:s') ?></p>
        <p>PHP version: <?= phpversion() ?></p>
    </div>
</body>
</html>
