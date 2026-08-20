<?php
require_once 'config/db.php';

// Initialize session cart if not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// 1. Handle Cart Actions (Add, Update, Remove)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ADD ITEM TO CART
    if ($action === 'add') {
        $product_id = intval($_POST['product_id'] ?? 0);
        $quantity = max(1, intval($_POST['quantity'] ?? 1));

        if ($product_id > 0) {
            $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch();

            if ($product) {
                if (isset($_SESSION['cart'][$product_id])) {
                    $_SESSION['cart'][$product_id]['quantity'] += $quantity;
                } else {
                    $_SESSION['cart'][$product_id] = [
                        'id' => $product['id'],
                        'title' => $product['title'],
                        'price' => (float)$product['price'],
                        'scale' => $product['scale'] ?? 'N/A',
                        'image_url' => $product['image_url'],
                        'category_name' => $product['category_name'] ?? 'Scale Model',
                        'quantity' => $quantity
                    ];
                }
            }
        }
        if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest') {
            header('Content-Type: application/json');
            $count = 0;
            foreach ($_SESSION['cart'] as $item) {
                $count += (int)$item['quantity'];
            }
            echo json_encode(['success' => true, 'cart_count' => $count]);
            exit;
        }
        header('Location: cart.php');
        exit;
    }

    // UPDATE QUANTITY
    if ($action === 'update') {
        $product_id = intval($_POST['product_id'] ?? 0);
        $quantity = max(1, intval($_POST['quantity'] ?? 1));

        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]['quantity'] = $quantity;
        }
        header('Location: cart.php');
        exit;
    }

    // REMOVE ITEM
    if ($action === 'remove') {
        $product_id = intval($_POST['product_id'] ?? 0);
        if (isset($_SESSION['cart'][$product_id])) {
            unset($_SESSION['cart'][$product_id]);
        }
        header('Location: cart.php');
        exit;
    }
}

// Calculate grand total and total item count
$grand_total = 0;
$total_items = 0;
foreach ($_SESSION['cart'] as $item) {
    $grand_total += $item['price'] * $item['quantity'];
    $total_items += $item['quantity'];
}
?>

<?php include 'includes/header.php'; ?>

<div class="cart-wrapper">
    <?php if (empty($_SESSION['cart'])): ?>
        <div class="empty-state">
            <div class="empty-state-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="9" cy="21" r="1"></circle>
                    <circle cx="20" cy="21" r="1"></circle>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                </svg>
            </div>
            <h3 class="empty-state-title">Your cart is empty</h3>
            <p>Looks like you haven't added any models yet. Explore the collection and find your next masterpiece.</p>
            <div class="empty-state-actions">
                <a href="index.php" class="btn btn-primary">Explore Scale Models</a>
            </div>
        </div>
    <?php else: ?>
        <div class="cart-column">
            <div class="cart-header">
                <h2 class="section-title">
                    Shopping Cart
                    <span class="cart-count"><?= $total_items ?> <?= $total_items === 1 ? 'item' : 'items' ?></span>
                </h2>
                <a href="index.php" class="cart-continue">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    Continue Shopping
                </a>
            </div>

            <div class="cart-col-head">
                <label class="cart-check-all" title="Select all items">
                    <input type="checkbox" id="cart-check-all" checked>
                </label>
                <span>Product</span>
                <span>Quantity</span>
                <span>Subtotal</span>
                <span></span>
            </div>

            <div class="cart-list" id="cart-list">
                <?php foreach ($_SESSION['cart'] as $id => $item):
                    $subtotal = $item['price'] * $item['quantity'];
                ?>
                    <div class="cart-item">
                        <label class="cart-item-check" title="Select this item">
                            <input type="checkbox" class="cart-check" value="<?= $id ?>" checked>
                        </label>
                        <div class="cart-item-info">
                            <a href="product.php?id=<?= $id ?>" class="cart-item-thumb">
                                <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['title']) ?>">
                            </a>
                            <div class="cart-item-details">
                                <a href="product.php?id=<?= $id ?>" class="cart-item-title"><?= htmlspecialchars($item['title']) ?></a>
                                <span class="cart-item-meta">Scale: <?= htmlspecialchars($item['scale']) ?></span>
                                <span class="cart-item-price"><?= format_price($item['price']) ?></span>
                            </div>
                        </div>

                        <form method="POST" action="cart.php" class="qty-form" data-cart-qty>
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="product_id" value="<?= $id ?>">
                            <div class="qty-stepper">
                                <button type="button" class="qty-step" data-dir="-1" aria-label="Decrease quantity">&#8722;</button>
                                <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" max="999" class="qty-input" aria-label="Quantity">
                                <button type="button" class="qty-step" data-dir="1" aria-label="Increase quantity">&#43;</button>
                            </div>
                            <button type="submit" class="qty-update">Update</button>
                        </form>

                        <span class="cart-item-subtotal"><?= format_price($subtotal) ?></span>

                        <form method="POST" action="cart.php" class="cart-item-remove">
                            <input type="hidden" name="action" value="remove">
                            <input type="hidden" name="product_id" value="<?= $id ?>">
                            <button type="submit" class="btn-remove" title="Remove item" aria-label="Remove item">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <polyline points="3 6 5 6 21 6"></polyline>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                    <line x1="10" y1="11" x2="10" y2="17"></line>
                                    <line x1="14" y1="11" x2="14" y2="17"></line>
                                </svg>
                                <span class="btn-remove-label">Remove</span>
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Order Summary Sidebar & Checkout Form Binding -->
        <div class="summary-card">
            <h3 class="section-title summary-title">Order Summary</h3>

            <div class="summary-row">
                <span>Items Subtotal (<?= $total_items ?>)</span>
                <span><?= format_price($grand_total) ?></span>
            </div>
            <div class="summary-row">
                <span>Estimated Shipping</span>
                <span class="summary-free">Free</span>
            </div>

            <div class="summary-row total">
                <span>Grand Total</span>
                <span><?= format_price($grand_total) ?></span>
            </div>

            <p class="summary-note">Free shipping on all orders. Pay with eSewa or Cash on Delivery.</p>

            <form action="checkout.php" method="GET" id="checkout-form">
                <?php foreach ($_SESSION['cart'] as $id => $item): ?>
                    <input type="hidden" name="ids[]" value="<?= $id ?>" class="checkout-slot" data-product-id="<?= $id ?>">
                <?php endforeach; ?>
                <button type="submit" class="btn btn-primary btn-lg btn-block btn-checkout">
                    Proceed to Checkout (<span id="checkout-count"><?= count($_SESSION['cart']) ?></span>)
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                        <polyline points="12 5 19 12 12 19"></polyline>
                    </svg>
                </button>
            </form>
            <a href="index.php" class="btn btn-ghost btn-block btn-cart-continue">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                Continue Shopping
            </a>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>