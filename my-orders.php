<?php
require_once 'config/db.php';
include 'includes/header.php';

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

<style>
    .orders-container { max-width: 900px; margin: 0 auto; padding: 60px 24px 96px; }
    .page-title { font-size: 24px; font-weight: 500; margin-bottom: 32px; border-bottom: 1px solid var(--border-color); padding-bottom: 16px; color: var(--text-primary); }
    
    .orders-list { display: flex; flex-direction: column; gap: 16px; }
    
    .order-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 8px; padding: 20px 24px; display: flex; justify-content: space-between; align-items: center; text-decoration: none; transition: border-color 0.15s ease; }
    .order-card:hover { border-color: var(--border-light); }

    .order-meta { display: flex; flex-direction: column; gap: 4px; }
    .order-id { font-size: 15px; font-weight: 600; color: var(--text-primary); }
    .order-date { font-size: 12px; color: var(--text-muted); }
    .order-items-count { font-size: 13px; color: var(--text-muted); margin-top: 4px; }
    
    .order-right { display: flex; align-items: center; gap: 24px; }
    .order-total { font-size: 16px; font-weight: 600; color: var(--text-primary); text-align: right; }
    
    .badge-status { font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; padding: 4px 10px; border-radius: 4px; border: 1px solid var(--border-color); font-weight: 500; }
    .status-pending { background: rgba(234, 179, 8, 0.1); color: #eab308; border-color: rgba(234, 179, 8, 0.2); }
    .status-processing { background: rgba(59, 130, 246, 0.1); color: #3b82f6; border-color: rgba(59, 130, 246, 0.2); }
    .status-shipped { background: rgba(168, 85, 247, 0.1); color: #a855f7; border-color: rgba(168, 85, 247, 0.2); }
    .status-completed { background: rgba(34, 197, 94, 0.1); color: #22c55e; border-color: rgba(34, 197, 94, 0.2); }
    .status-cancelled { background: rgba(239, 68, 68, 0.1); color: #ef4444; border-color: rgba(239, 68, 68, 0.2); }

    .btn-back { display: inline-block; color: var(--text-muted); text-decoration: none; font-size: 13px; margin-bottom: 24px; transition: color 0.15s ease; }
    .btn-back:hover { color: var(--text-primary); }

    .detail-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 8px; padding: 32px; }
    .detail-header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 1px solid var(--border-color); padding-bottom: 20px; margin-bottom: 24px; }
    
    .items-table { width: 100%; border-collapse: collapse; text-align: left; }
    .items-table th { font-size: 12px; text-transform: uppercase; color: var(--text-muted); border-bottom: 1px solid var(--border-color); padding: 12px 0; font-weight: 500; }
    .items-table td { padding: 16px 0; border-bottom: 1px solid var(--border-color); font-size: 14px; color: var(--text-primary); vertical-align: middle; }

    .item-cell { display: flex; align-items: center; gap: 16px; }
    .item-thumb { width: 50px; height: 38px; object-fit: cover; border-radius: 4px; border: 1px solid var(--border-color); background: #000; }

    .btn-catalog { display: inline-block; background: var(--text-primary); color: var(--bg-main); padding: 10px 20px; border-radius: 6px; font-size: 14px; font-weight: 600; text-decoration: none; margin-top: 16px; }
</style>

<main class="orders-container">
    <?php if ($selected_order): ?>
        <a href="my-orders.php" class="btn-back">&larr; Back to Order History</a>
        
        <div class="detail-card">
            <div class="detail-header">
                <div>
                    <h1 style="font-size: 20px; font-weight: 600; margin-bottom: 4px;">Order #<?= $selected_order['id'] ?></h1>
                    <div style="font-size: 13px; color: var(--text-muted);">Placed on <?= date('F j, Y', strtotime($selected_order['created_at'])) ?></div>
                </div>
                <span class="badge-status status-<?= strtolower($selected_order['status']) ?>">
                    <?= htmlspecialchars($selected_order['status']) ?>
                </span>
            </div>

            <table class="items-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th style="text-align: right;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order_items as $item): ?>
                        <tr>
                            <td>
                                <div class="item-cell">
                                    <img src="<?= htmlspecialchars($item['image_url']) ?>" class="item-thumb" alt="">
                                    <div>
                                        <div style="font-weight: 500;"><?= htmlspecialchars($item['title']) ?></div>
                                        <div style="font-size: 12px; color: var(--text-muted);"><?= htmlspecialchars($item['scale']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><?= format_price($item['price']) ?></td>
                            <td><?= $item['quantity'] ?></td>
                            <td style="text-align: right; font-weight: 500;"><?= format_price($item['price'] * $item['quantity']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div style="display: flex; justify-content: space-between; margin-top: 24px; padding-top: 16px;">
                <div style="font-size: 13px; color: var(--text-muted);">
                    <strong>Payment Status:</strong> Paid
                </div>
                <div style="font-size: 18px; font-weight: 600;">
                    Total: <?= format_price($selected_order['total_amount']) ?>
                </div>
            </div>
        </div>

    <?php else: ?>
        <h1 class="page-title">Order History</h1>

        <?php if (empty($orders)): ?>
            <p style="color: var(--text-muted); margin-bottom: 16px;">You haven't placed any orders yet.</p>
            <a href="index.php" class="btn-catalog">Explore Catalog</a>
        <?php else: ?>
            <div class="orders-list">
                <?php foreach ($orders as $order): ?>
                    <a href="my-orders.php?view=<?= $order['id'] ?>" class="order-card">
                        <div class="order-meta">
                            <span class="order-id">Order #<?= $order['id'] ?></span>
                            <span class="order-date">Placed on <?= date('M d, Y', strtotime($order['created_at'])) ?></span>
                            <span class="order-items-count"><?= $order['total_items'] ?> <?= $order['total_items'] === 1 ? 'item' : 'items' ?></span>
                        </div>

                        <div class="order-right">
                            <span class="badge-status status-<?= strtolower($order['status']) ?>">
                                <?= htmlspecialchars($order['status']) ?>
                            </span>
                            <div class="order-total">
                                <?= format_price($order['total_amount']) ?>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</main>

</body>
</html>