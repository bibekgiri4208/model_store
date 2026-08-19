<?php
session_start();
require_once 'config/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['full_name']);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $pass  = $_POST['password'];

    if (empty($name) || empty($email) || empty($pass)) {
        $error = "Please fill in all fields.";
    } else {
        $hashedPassword = password_hash($pass, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)");
        
        try {
            $stmt->execute([$name, $email, $hashedPassword]);
            header("Location: login.php?registered=1");
            exit;
        } catch (PDOException $e) {
            $error = "Email address is already registered.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Jester Scale Models</title>
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
<link rel="stylesheet" href="assets/css/style.css?v=22">
    <script src="assets/js/app.js" defer></script>
</head>
<body class="auth-page">
<div class="auth-card">
    <h2>Create Account</h2>
    <p class="subtitle">Join the Jester Scale Models community</p>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form action="register.php" method="POST">
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="full_name" required placeholder="Your full name">
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required placeholder="you@example.com">
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required placeholder="Create a password">
        </div>
        <button type="submit" class="btn btn-primary btn-lg btn-block">Create Account</button>
    </form>
    <div class="auth-link">
        Already have an account? <a href="login.php">Sign in</a>
    </div>
</div>
</body>
</html>
