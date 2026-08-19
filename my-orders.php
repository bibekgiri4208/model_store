<?php
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$selected_order_id = (int)($_GET['view'] ?? 0);

$selected_order = null;
$order_items = [];

if ($selected_order_id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmt->execute([$selected_order_id, $user_id]);
    $selected_order = $stmt->fetch();

    if ($selected_order) {
        $items_stmt = $pdo->prepare("
            SELECT oi.*, p.title, p.image_url, p.scale 
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = ?
        ");
        $items_stmt->execute([$selected_order_id]);
        $order_items = $items_stmt->fetchAll();
    }
}

$stmt = $pdo->prepare("
    SELECT o.*, COUNT(oi.id) AS total_items 
    FROM orders o 
    LEFT JOIN order_items oi ON o.id = oi.order_id 
    WHERE o.user_id = ? 
    GROUP BY o.id 
    ORDER BY o.created_at DESC
");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();
?>

<?php include 'includes/header.php'; ?>

<main class="orders-container">
    <?php if ($selected_order): ?>
        <a href="my-orders.php" class="btn-back">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Back to Order History
        </a>

        <?php
            $subtotal = 0;
            foreach ($order_items as $item) {
                $subtotal += $item['unit_price'] * $item['quantity'];
            }
            $pay_state = strtolower($selected_order['payment_status'] ?? 'pending');
            $pay_class = 'pay-pending';
            $pay_label = 'Pending';
            if ($pay_state === 'completed') { $pay_class = 'pay-ok'; $pay_label = 'Paid'; }
            elseif ($pay_state === 'failed') { $pay_class = 'pay-failed'; $pay_label = 'Failed'; }
        ?>

        <div class="detail-card">
            <div class="detail-header">
                <div class="detail-heading">
                    <h1 class="detail-title">Order #<?= $selected_order['id'] ?></h1>
                    <div class="detail-meta">
                        <span class="meta-chip">Placed <?= date('M d, Y', strtotime($selected_order['created_at'])) ?></span>
                        <span class="meta-chip"><?= htmlspecialchars($selected_order['payment_method'] ?? 'esewa') ?></span>
                        <span class="meta-chip meta-chip-accent"><?= htmlspecialchars($selected_order['transaction_uuid']) ?></span>
                    </div>
                </div>
                <span class="badge-status status-<?= strtolower($selected_order['status']) ?>">
                    <?= htmlspecialchars($selected_order['status']) ?>
                </span>
            </div>

            <div class="detail-section">
                <h3 class="detail-subtitle">Items</h3>
                <div class="order-items-list">
                    <?php foreach ($order_items as $item): ?>
                        <div class="order-item">
                            <img src="<?= htmlspecialchars($item['image_url']) ?>" class="order-item-img" alt="<?= htmlspecialchars($item['title']) ?>">
                            <div class="order-item-info">
                                <div class="order-item-title"><?= htmlspecialchars($item['title']) ?></div>
                                <div class="order-item-meta">Scale: <?= htmlspecialchars($item['scale']) ?> &middot; <?= format_price($item['unit_price']) ?></div>
                            </div>
                            <span class="order-item-qty">&times; <?= $item['quantity'] ?></span>
                            <span class="order-item-subtotal"><?= format_price($item['unit_price'] * $item['quantity']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="order-summary">
                <div class="summary-row"><span>Items Subtotal</span><span><?= format_price($subtotal) ?></span></div>
                <div class="summary-row"><span>Shipping</span><span>Free</span></div>
                <div class="summary-row"><span>Payment</span><span class="<?= $pay_class ?>"><?= $pay_label ?></span></div>
                <div class="summary-row total"><span>Total</span><span><?= format_price($selected_order['total_amount']) ?></span></div>
            </div>
        </div>

    <?php else: ?>
        <h1 class="page-title">Order History</h1>
        <p class="page-subtitle">Track and review your previous purchases.</p>

        <?php if (empty($orders)): ?>
            <div class="empty-state" style="padding: 40px 0;">
                <div class="empty-state-icon">&#128196;</div>
                <p>You haven't placed any orders yet.</p>
                <a href="index.php" class="btn btn-primary">Explore Catalog</a>
            </div>
        <?php else: ?>
            <div class="orders-list">
                <?php foreach ($orders as $order): ?>
                    <a href="my-orders.php?view=<?= $order['id'] ?>" class="order-card">
                        <div class="order-main">
                            <div class="order-id">Order #<?= $order['id'] ?></div>
                            <div class="order-sub">
                                <span class="order-date"><?= date('M d, Y', strtotime($order['created_at'])) ?></span>
                                <span class="order-dot">&middot;</span>
                                <span class="order-items-count"><?= $order['total_items'] ?> <?= $order['total_items'] === 1 ? 'item' : 'items' ?></span>
                            </div>
                        </div>

                        <div class="order-side">
                            <div class="order-total"><?= format_price($order['total_amount']) ?></div>
                            <span class="badge-status status-<?= strtolower($order['status']) ?>">
                                <?= htmlspecialchars($order['status']) ?>
                            </span>
                        </div>

                        <span class="order-chevron" aria-hidden="true">&#8594;</span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</main>

<?php include 'includes/footer.php'; ?>
</body>
</html>
