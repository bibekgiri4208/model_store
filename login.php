<?php
session_start();
require_once 'config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($email) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = strtolower(trim($user['role'])); // Ensures lowercase matching ('admin' or 'user')

            // Redirect based on role
            if ($_SESSION['role'] === 'admin') {
                header('Location: admin/dashboard.php');
                exit;
            } else {
                header('Location: index.php');
                exit;
            }
        } else {
            $error = "Invalid email or password.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Apex Replica Store</title>
    <style>
        * { box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background: #0f172a; color: #f8fafc; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .login-card { background: #1e293b; padding: 30px; border-radius: 12px; border: 1px solid #334155; width: 100%; max-width: 400px; }
        h2 { margin-top: 0; color: #38bdf8; text-align: center; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #94a3b8; font-size: 14px; }
        input { width: 100%; background: #0f172a; border: 1px solid #334155; color: #fff; padding: 10px; border-radius: 8px; outline: none; }
        button { width: 100%; background: #0284c7; color: #fff; border: none; padding: 12px; border-radius: 8px; font-weight: bold; cursor: pointer; }
        button:hover { background: #0369a1; }
        .alert { background: #991b1b; color: #fecaca; padding: 10px; border-radius: 6px; margin-bottom: 15px; font-size: 14px; }
    </style>
</head>
<body>
<div class="login-card">
    <h2>Login</h2>
    <?php if (!empty($error)): ?>
        <div class="alert"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form action="login.php" method="POST">
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit">Sign In</button>
    </form>
</div>
</body>
</html>