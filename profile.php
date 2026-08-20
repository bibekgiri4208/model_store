<?php
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: logout.php');
    exit;
}
?>

<?php include 'includes/header.php'; ?>

<main class="orders-container">
    <h1 class="page-title">My Profile</h1>
    <p class="page-subtitle">Your account details.</p>

    <div class="profile-card">
        <div class="profile-head">
            <span class="profile-avatar" aria-hidden="true"><?= strtoupper(substr(trim($user['full_name']), 0, 1)) ?></span>
            <div>
                <h2><?= htmlspecialchars($user['full_name']) ?></h2>
                <span class="badge-status status-<?= $user['role'] === 'admin' ? 'shipped' : 'completed' ?>"><?= htmlspecialchars($user['role']) ?></span>
            </div>
        </div>

        <div class="profile-info">
            <div class="detail-row"><span>Full Name</span><strong><?= htmlspecialchars($user['full_name']) ?></strong></div>
            <div class="detail-row"><span>Email</span><strong><?= htmlspecialchars($user['email']) ?></strong></div>
            <div class="detail-row"><span>Role</span><strong><?= htmlspecialchars($user['role']) ?></strong></div>
            <div class="detail-row"><span>Member Since</span><strong><?= date('F d, Y', strtotime($user['created_at'])) ?></strong></div>
        </div>

        <div class="profile-actions">
            <a href="my-orders.php" class="btn btn-ghost">My Orders</a>
            <a href="logout.php" class="btn btn-primary">Sign Out</a>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
</body>
</html>