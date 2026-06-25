<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Portal</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; display: flex;
               align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .card { background: #fff; padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                text-align: center; width: 320px; }
        h1 { color: #2c3e50; margin-bottom: 8px; }
        p { color: #6b7280; font-size: 0.9rem; margin-bottom: 24px; }
        a.btn { display: block; padding: 10px; margin-bottom: 10px; border-radius: 4px;
                text-decoration: none; font-weight: bold; }
        .login { background: #2c7be5; color: #fff; }
        .register { background: #eef2f7; color: #2c3e50; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Student Portal</h1>
        <p>BIT3208 — Week 7: User Authentication and Session Management</p>
        <a href="login.php" class="btn login">Login</a>
        <a href="register.php" class="btn register">Register</a>
    </div>
</body>
</html>
