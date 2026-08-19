<?php
session_start();
require_once '../config/db.php';

// Enforce Admin Access Check
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    die("Access Denied: You must be an administrator to view this page.");
}

$message = '';
$error = '';

// Fetch categories for dropdown
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $title       = trim($_POST['title']);
    $category_id = (int)$_POST['category_id'];
    $price       = (float)$_POST['price'];
    $stock       = (int)($_POST['stock'] ?? 10);
    $scale       = trim($_POST['scale'] ?? '1:18');
    $type        = trim($_POST['type'] ?? 'Diecast');
    $description = trim($_POST['description']);
    $image_url   = trim($_POST['image_url'] ?? '');
    $image_url   = empty($image_url) ? 'assets/images/placeholder.jpg' : $image_url;

    // File upload (if provided) takes priority over the asset/URL picker
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp   = $_FILES['image']['tmp_name'];
        $file_name  = $_FILES['image']['name'];
        $file_ext   = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($file_ext, $allowed_exts)) {
            // Generate unique filename to prevent overwriting existing files
            $image_name = time() . '_' . uniqid() . '.' . $file_ext;

            // Destination path: assets/images/
            $upload_dir = '../assets/images/';

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $dest_path = $upload_dir . $image_name;

            if (move_uploaded_file($file_tmp, $dest_path)) {
                $image_url = 'assets/images/' . $image_name;
            } else {
                $error = "Failed to upload image. Check directory permissions.";
            }
        } else {
            $error = "Invalid file type. Only JPG, JPEG, PNG, and WEBP files are allowed.";
        }
    }

    // Insert Product into Database if no errors
    if (empty($error)) {
        if (!empty($title) && $price > 0 && $category_id > 0) {
            $stmt = $pdo->prepare("INSERT INTO products (category_id, title, description, price, stock, scale, type, image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$category_id, $title, $description, $price, $stock, $scale, $type, $image_url]);
            $message = "Product '{$title}' added successfully!";
        } else {
            $error = "Please fill in all required fields properly.";
        }
    }
}

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
    <title>Add Product - Jester Admin</title>
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
        <h1>Add New Model Car</h1>
        <nav class="admin-nav">
            <a href="dashboard.php">Overview</a>
            <a href="products.php">Products</a>
            <a href="orders.php" class="active">Orders</a>
            <a href="../index.php">View Store</a>
            <a href="../logout.php">Sign Out</a>
        </nav>
    </header>

    <div class="card">
        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form action="add-product.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Product Title *</label>
                <input type="text" id="title" name="title" class="form-control" placeholder="e.g., Porsche 911 GT3 RS (1:18)" required>
            </div>

            <div class="form-group">
                <label for="category_id">Category *</label>
                <select id="category_id" name="category_id" class="form-control" required>
                    <option value="">-- Select Category --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="price">Price (Rs.) *</label>
                <input type="number" id="price" name="price" class="form-control" step="0.01" min="0" placeholder="149.99" required>
            </div>

            <div class="form-group">
                <label for="stock">Stock *</label>
                <input type="number" id="stock" name="stock" class="form-control" step="1" min="0" value="10">
            </div>

            <div class="form-group" style="display:flex; gap:10px;">
                <div style="flex:1;">
                    <label for="scale">Scale</label>
                    <input type="text" id="scale" name="scale" class="form-control" value="1:18" placeholder="1:18">
                </div>
                <div style="flex:1;">
                    <label for="type">Type</label>
                    <input type="text" id="type" name="type" class="form-control" value="Diecast" placeholder="Diecast">
                </div>
            </div>

            <div class="form-group">
                <label for="asset_picker">Asset Image <span style="font-weight:400;color:var(--text-muted);">(pick from the image library)</span></label>
                <select id="asset_picker" name="asset" class="form-control" onchange="setAssetImage(this.value)">
                    <option value="">-- No asset selected --</option>
                    <?php foreach ($assets as $a): ?>
                        <option value="assets/images/<?= htmlspecialchars($a) ?>"><?= htmlspecialchars($a) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="image_url">Image URL <span style="font-weight:400;color:var(--text-muted);">(or paste an external URL)</span></label>
                <input type="text" id="image_url" name="image_url" class="form-control" placeholder="https://example.com/car.jpg" oninput="updateImagePreview()">
            </div>

            <div class="form-group">
                <label for="image">Upload Image (JPG, PNG, WEBP) <span style="font-weight:400;color:var(--text-muted);">(optional — takes priority)</span></label>
                <input type="file" id="image" name="image" class="form-control" accept="image/*">
            </div>

            <div class="form-group">
                <label>Image Preview</label>
                <img id="image_preview" src="" alt="Preview" style="max-width:160px; border-radius:10px; display:none; border:1px solid var(--border);">
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control" placeholder="Enter scale details, die-cast materials, opening parts..."></textarea>
            </div>

            <button type="submit" name="add_product" class="btn-submit">Save Product</button>
        </form>
    </div>
</div>

<script>
function setAssetImage(val) {
    document.getElementById('image_url').value = val || '';
    updateImagePreview();
}
function updateImagePreview() {
    var input = document.getElementById('image_url');
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
updateImagePreview();
</script>

</body>
</html>