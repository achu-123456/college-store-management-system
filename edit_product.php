<?php
// admin/edit_product.php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/admin_layout.php';
requireAdmin();

$id = (int)($_GET['id'] ?? 0);
$p  = $conn->query("SELECT * FROM products WHERE id=$id")->fetch_assoc();
if (!$p) { header("Location: products.php"); exit(); }

$error='';

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $name        = sanitize($conn, $_POST['name']);
    $description = sanitize($conn, $_POST['description']);
    $price       = (float)$_POST['price'];
    $stock       = (int)$_POST['stock'];
    $category    = sanitize($conn, $_POST['category']);
    $image_name  = $p['image']; // keep existing unless new uploaded

    if (isset($_FILES['image']) && $_FILES['image']['error']===0) {
        $allowed=['image/jpeg','image/png','image/gif','image/webp'];
        if (in_array($_FILES['image']['type'], $allowed)) {
            $ext        = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $image_name = uniqid('prod_').'.'.$ext;
            move_uploaded_file($_FILES['image']['tmp_name'], '../uploads/'.$image_name);
        } else {
            $error="Invalid image type.";
        }
    }

    if (!$error) {
        $stmt=$conn->prepare("UPDATE products SET name=?,description=?,price=?,stock=?,category=?,image=? WHERE id=?");
        $stmt->bind_param("ssdissi",$name,$description,$price,$stock,$category,$image_name,$id);
        if ($stmt->execute()) {
            // Changes reflect instantly on student shop
            header("Location: products.php?saved=1"); exit();
        }
        $stmt->close();
    }
}

$categories_list=['Stationery','Electronics','Books','Clothing','Drawing','Lab Equipment','Sports','Miscellaneous'];

admin_head("Edit Product");
?>
</head>
<body>
<?php admin_sidebar('products'); ?>
<?php admin_topbar("✏️ Edit Product: ".htmlspecialchars($p['name']),
    '<a href="products.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back</a>'); ?>
<div class="admin-main">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card p-4">
                <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
                <form method="POST" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Product Name</label>
                            <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($p['name']) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Category</label>
                            <select name="category" class="form-select" required>
                                <?php foreach ($categories_list as $c): ?>
                                    <option value="<?= $c ?>" <?= $p['category']===$c?'selected':'' ?>><?= $c ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($p['description']) ?></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Price (₹)</label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" name="price" class="form-control" required min="1" step="0.01" value="<?= $p['price'] ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Stock Quantity</label>
                            <input type="number" name="stock" class="form-control" required min="0" value="<?= $p['stock'] ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Replace Image</label>
                            <input type="file" name="image" class="form-control" accept="image/*" onchange="previewImg(this)">
                        </div>
                        <div class="col-12">
                            <p class="text-muted small mb-1">Current image:</p>
                            <img id="img-preview"
                                 src="../uploads/<?= htmlspecialchars($p['image']) ?>"
                                 onerror="this.src='https://via.placeholder.com/120?text=No+Image'"
                                 style="max-height:120px;border-radius:10px;border:1px solid #dee2e6;" alt="Current">
                        </div>
                    </div>
                    <hr class="mt-4">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-admin px-4"><i class="bi bi-check-circle"></i> Save Changes</button>
                        <a href="products.php" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
            <div class="alert alert-info mt-3 small">
                <i class="bi bi-info-circle"></i>
                <strong>Real-time sync:</strong> Changes to price, stock, name, or image are instantly visible to students browsing the shop.
            </div>
        </div>
    </div>
</div>
<script>
function previewImg(input) {
    if(input.files&&input.files[0]) {
        const r=new FileReader();
        r.onload=e=>document.getElementById('img-preview').src=e.target.result;
        r.readAsDataURL(input.files[0]);
    }
}
</script>
<?php admin_footer(); ?>
