<?php
session_start();
require_once 'config/db.php';

$error = '';

// Optional safe redirect target (e.g. returning to checkout after login)
$redirect = trim($_GET['redirect'] ?? '');
if (!empty($redirect) && !preg_match('#^[a-zA-Z0-9_\-./?&=:%+]+$#', $redirect)) {
    $redirect = '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $redirect = trim($_POST['redirect'] ?? '');
    if (!empty($redirect) && !preg_match('#^[a-zA-Z0-9_\-./?&=:%+]+$#', $redirect)) {
        $redirect = '';
    }

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
            } elseif (!empty($redirect)) {
                header('Location: ' . $redirect);
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
    <title>Login - Jester Scale Models</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <script>
        (function() {
            try {
                var stored = localStorage.getItem('theme');
                var theme = stored || (window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark');
                document.documentElement.setAttribute('data-theme', theme);
            } catch (e) {}
        })();
    </script>
<link rel="stylesheet" href="assets/css/style.css?v=16">
    <script src="assets/js/app.js" defer></script>
</head>
<body class="auth-page">
<div class="auth-card">
    <h2>Welcome Back</h2>
    <p class="subtitle">Sign in to your account</p>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form action="login.php" method="POST">
        <?php if (!empty($redirect)): ?>
            <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
        <?php endif; ?>
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" required placeholder="you@example.com">
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required placeholder="Enter your password">
        </div>
        <button type="submit" class="btn btn-primary btn-lg btn-block">Sign In</button>
    </form>
    <div class="auth-link">
        Don't have an account? <a href="register.php">Create one</a>
    </div>
</div>
</body>
</html>
