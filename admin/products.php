<?php
session_start();
require_once '../config/db.php';

// Access Control
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$message = '';
$edit_product = null;

// Handle Delete Request
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: products.php?msg=deleted');
    exit;
}

// Handle Fetch for Editing
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $edit_product = $stmt->fetch();
}

// Handle Add / Edit Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $category_id = (int)$_POST['category_id'];
    $description = trim($_POST['description']);
    $image_url = trim($_POST['image_url']);
    $scale = trim($_POST['scale'] ?? '1:18');
    $type = trim($_POST['type'] ?? 'Diecast');
    $product_id = (int)($_POST['product_id'] ?? 0);

    if ($product_id > 0) {
        // Update Product
        $stmt = $pdo->prepare("UPDATE products SET title = ?, price = ?, stock = ?, category_id = ?, description = ?, image_url = ?, scale = ?, type = ? WHERE id = ?");
        $stmt->execute([$title, $price, $stock, $category_id, $description, $image_url, $scale, $type, $product_id]);
        header('Location: products.php?msg=updated');
        exit;
    } else {
        // Add Product
        $stmt = $pdo->prepare("INSERT INTO products (title, price, stock, category_id, description, image_url, scale, type) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $price, $stock, $category_id, $description, $image_url, $scale, $type]);
        header('Location: products.php?msg=added');
        exit;
    }
}

// Fetch Categories for Dropdown
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

// Fetch All Products with Category Name
$stmt = $pdo->query("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC");
$products = $stmt->fetchAll();

// List available local asset images for the picker
$asset_dir = '../assets/images/';
$assets = [];
if (is_dir($asset_dir)) {
    foreach (glob($asset_dir . '*.{jpg,jpeg,png,webp,gif}', GLOB_BRACE) as $file) {
        $assets[] = basename($file);
    }
    sort($assets);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - Jester Admin</title>
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
<link rel="stylesheet" href="../assets/css/style.css?v=22">
</head>
<body>
<div class="admin-container">
    <header class="admin-header">
        <h1>Product Management</h1>
        <nav class="admin-nav">
            <a href="dashboard.php">Overview</a>
            <a href="products.php" class="active">Products</a>
            <a href="orders.php">Orders</a>
            <a href="../index.php">View Store</a>
            <a href="../logout.php">Sign Out</a>
        </nav>
    </header>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success">Product operation completed successfully.</div>
    <?php endif; ?>

    <div class="grid-layout">
        <!-- Add / Edit Form -->
        <div class="card">
            <h3><?= $edit_product ? 'Edit Product' : 'Add New Product' ?></h3>
            <form action="products.php" method="POST">
                <input type="hidden" name="product_id" value="<?= $edit_product['id'] ?? 0 ?>">
                
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" required value="<?= htmlspecialchars($edit_product['title'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>Category</label>
                    <select name="category_id" required>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($edit_product['category_id'] ?? 0) == $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" style="display:flex; gap:10px;">
                    <div style="flex:1;">
                        <label>Price (Rs.)</label>
                        <input type="number" step="0.01" name="price" required value="<?= $edit_product['price'] ?? '' ?>">
                    </div>
                    <div style="flex:1;">
                        <label>Stock</label>
                        <input type="number" name="stock" required value="<?= $edit_product['stock'] ?? 10 ?>">
                    </div>
                </div>

                <div class="form-group" style="display:flex; gap:10px;">
                    <div style="flex:1;">
                        <label>Scale</label>
                        <input type="text" name="scale" value="<?= htmlspecialchars($edit_product['scale'] ?? '1:18') ?>">
                    </div>
                    <div style="flex:1;">
                        <label>Type</label>
                        <input type="text" name="type" value="<?= htmlspecialchars($edit_product['type'] ?? 'Diecast') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label>Asset Image <span style="font-weight:400;color:var(--text-muted);">(pick from the image library)</span></label>
                    <select id="asset_picker" class="form-control" onchange="setAssetImage(this.value)">
                        <option value="">-- No asset selected --</option>
                        <?php foreach ($assets as $a): ?>
                            <option value="assets/images/<?= htmlspecialchars($a) ?>" <?= !empty($edit_product['image_url']) && $edit_product['image_url'] === 'assets/images/' . $a ? 'selected' : '' ?>>
                                <?= htmlspecialchars($a) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Image URL <span style="font-weight:400;color:var(--text-muted);">(or paste an external URL — picking an asset fills this)</span></label>
                    <input type="text" name="image_url" id="image_url_input" value="<?= htmlspecialchars($edit_product['image_url'] ?? '') ?>" oninput="updateImagePreview()" placeholder="https://example.com/car.jpg">
                </div>

                <div class="form-group">
                    <label>Image Preview</label>
                    <img id="image_preview" src="" alt="Preview" style="max-width:160px; border-radius:10px; display:none; border:1px solid var(--border);">
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description"><?= htmlspecialchars($edit_product['description'] ?? '') ?></textarea>
                </div>

                <button type="submit" class="btn-submit"><?= $edit_product ? 'Update Product' : 'Save Product' ?></button>
                <?php if ($edit_product): ?>
                    <a href="products.php" class="btn-cancel">Cancel Edit</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Product Table List -->
        <div class="card" style="overflow-x: auto;">
            <h3>Catalog Items (<?= count($products) ?>)</h3>
            <table>
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p): 
                        $img = !empty($p['image_url']) ? $p['image_url'] : '../assets/images/placeholder.jpg';
                        if (!empty($img) && strpos($img, 'http') !== 0 && strpos($img, 'data:') !== 0 && strpos($img, '/') !== 0) {
                            $img = '../' . $img;
                        }
                    ?>
                        <tr>
                            <td><img src="<?= htmlspecialchars($img) ?>" class="img-thumb" alt=""></td>
                            <td><strong><?= htmlspecialchars($p['title']) ?></strong></td>
                            <td><span class="item-meta"><?= htmlspecialchars($p['category_name'] ?? 'N/A') ?></span></td>
                            <td><?= format_price($p['price']) ?></td>
                            <td><?= $p['stock'] ?></td>
                            <td>
                                <a href="products.php?edit=<?= $p['id'] ?>" class="btn-edit">Edit</a>
                                <a href="products.php?delete=<?= $p['id'] ?>" class="btn-delete" onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
function setAssetImage(val) {
    document.getElementById('image_url_input').value = val || '';
    updateImagePreview();
}
function updateImagePreview() {
    var input = document.getElementById('image_url_input');
    var img = document.getElementById('image_preview');
    var url = (input.value || '').trim();
    if (!url) { img.style.display = 'none'; img.src = ''; return; }
    var src = url;
    if (url.indexOf('http') !== 0 && url.indexOf('data:') !== 0 && url.indexOf('/') !== 0) {
        src = '../' + url;
    }
    img.src = src;
    img.style.display = 'inline-block';
}
(function () {
    var input = document.getElementById('image_url_input');
    var picker = document.getElementById('asset_picker');
    if (input && input.value) {
        var match = input.value;
        if (match.indexOf('assets/images/') === 0) {
            for (var i = 0; i < picker.options.length; i++) {
                if (picker.options[i].value === match) { picker.selectedIndex = i; break; }
            }
        }
    }
    updateImagePreview();
})();
</script>
</body>
</html>