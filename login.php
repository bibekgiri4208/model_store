<?php
session_start();
require_once 'config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $pass  = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($pass, $user['password'])) {
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_role'] = $user['role'];

        $redirect = $_GET['redirect'] ?? 'index.php';
        header("Location: " . $redirect);
        exit;
    } else {
        $error = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Model Store</title>
    <style>
        * { box-sizing: border-box; font-family: Arial, sans-serif; }
        body { background: #f4f6f8; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .auth-card { background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        .auth-card h2 { margin-top: 0; margin-bottom: 20px; color: #1e293b; text-align: center; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 4px; }
        .btn { width: 100%; padding: 10px; background: #16a34a; color: white; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; }
        .btn:hover { background: #15803d; }
        .alert { padding: 10px; background: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; border-radius: 4px; margin-bottom: 15px; font-size: 14px; }
        .alert-success { background: #f0fdf4; border-color: #86efac; color: #166534; }
        .link { text-align: center; margin-top: 15px; font-size: 14px; }
        .link a { color: #0284c7; text-decoration: none; }
    </style>
</head>
<body>
<div class="auth-card">
    <h2>Login</h2>
    <?php if (isset($_GET['registered'])): ?>
        <div class="alert alert-success">Registration successful! Please log in.</div>
    <?php endif; ?>
    <?php if ($error): ?><div class="alert"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form action="login.php<?= isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : '' ?>" method="POST">
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit" class="btn">Log In</button>
    </form>
    <div class="link">
        Don't have an account? <a href="register.php">Register here</a>
    </div>
</div>
</body>
</html>