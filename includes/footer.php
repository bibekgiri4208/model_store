<footer class="site-footer">
    <div class="footer-inner">
        <div class="footer-brand">
            <span class="footer-logo">Jester Scale Models</span>
            <p>Precision-engineered diecast and resin miniatures for collector purists.</p>
        </div>

        <div class="footer-col">
            <h5>Shop</h5>
            <a href="<?= $base_path ?>index.php">Catalog</a>
            <a href="<?= $base_path ?>cart.php">Cart</a>
            <a href="<?= $base_path ?>checkout.php">Checkout</a>
        </div>

        <div class="footer-col">
            <h5>Account</h5>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="<?= $base_path ?>my-orders.php">My Orders</a>
                <a href="<?= $base_path ?>logout.php">Sign Out</a>
            <?php else: ?>
                <a href="<?= $base_path ?>login.php">Sign In</a>
                <a href="<?= $base_path ?>register.php">Register</a>
            <?php endif; ?>
        </div>

        <div class="footer-col">
            <h5>Payments</h5>
            <span class="footer-pay">eSewa</span>
            <span class="footer-pay">Cash on Delivery</span>
        </div>
    </div>

    <div class="footer-bottom">
        <span>&copy; <?= date('Y') ?> Jester Scale Models. All rights reserved.</span>
        <span>Made for collectors.</span>
    </div>
</footer>