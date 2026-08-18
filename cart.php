<?php
require_once 'config/db.php';
include 'includes/header.php';

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

// Calculate grand total
$grand_total = 0;
foreach ($_SESSION['cart'] as $item) {
    $grand_total += $item['price'] * $item['quantity'];
}
?>

<div class="cart-wrapper">
    <?php if (empty($_SESSION['cart'])): ?>
        <div class="empty-state">
            <div class="empty-state-icon">&#128722;</div>
            <p>Your shopping cart is currently empty.</p>
            <a href="index.php" class="btn btn-primary">Explore Scale Models</a>
        </div>
    <?php else: ?>
        <div class="cart-column">
            <h2 class="section-title">Shopping Cart <span class="cart-count"><?= count($_SESSION['cart']) ?></span></h2>

            <div class="cart-col-head">
                <span>Product</span>
                <span>Quantity</span>
                <span>Subtotal</span>
                <span></span>
            </div>

            <div class="cart-list">
                <?php foreach ($_SESSION['cart'] as $id => $item): 
                    $subtotal = $item['price'] * $item['quantity'];
                ?>
                    <div class="cart-item">
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

                        <form method="POST" action="cart.php" class="qty-form">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="product_id" value="<?= $id ?>">
                            <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" class="qty-input" aria-label="Quantity">
                            <button type="submit" class="qty-update">Update</button>
                        </form>

                        <span class="cart-item-subtotal"><?= format_price($subtotal) ?></span>

                        <form method="POST" action="cart.php" class="cart-item-remove">
                            <input type="hidden" name="action" value="remove">
                            <input type="hidden" name="product_id" value="<?= $id ?>">
                            <button type="submit" class="btn-remove" title="Remove item" aria-label="Remove item">&times;</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        </div>

        <!-- Order Summary Sidebar & Checkout Form Binding -->
        <div class="summary-card">
            <h3 class="section-title" style="font-size: 16px; margin-bottom: 16px;">Summary</h3>
            
            <div class="summary-row">
                <span>Items Subtotal</span>
                <span><?= format_price($grand_total) ?></span>
            </div>
            <div class="summary-row">
                <span>Estimated Shipping</span>
                <span>Free</span>
            </div>

            <div class="summary-row total">
                <span>Grand Total</span>
                <span><?= format_price($grand_total) ?></span>
            </div>

            <?php 
                $cart_keys = array_keys($_SESSION['cart']);
                $first_product_id = reset($cart_keys);
            ?>
            <form action="checkout.php" method="GET">
                <input type="hidden" name="id" value="<?= $first_product_id ?>">
                <button type="submit" class="btn btn-primary btn-lg btn-block" style="margin-top: 20px;">Proceed to Checkout</button>
            </form>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>
