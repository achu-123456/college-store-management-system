<?php
// admin/add_product.php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/admin_layout.php';
requireAdmin();

$error=''; $success='';

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $name        = sanitize($conn, $_POST['name']);
    $description = sanitize($conn, $_POST['description']);
    $price       = (float)$_POST['price'];
    $stock       = (int)$_POST['stock'];
    $category    = sanitize($conn, $_POST['category']);
    $image_name  = 'placeholder.jpg';

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error']===0) {
        $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
        if (in_array($_FILES['image']['type'], $allowed)) {
            $ext        = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $image_name = uniqid('prod_').'.'.$ext;
            $dest       = '../uploads/'.$image_name;
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                $error = "Failed to upload image. Check uploads/ folder permissions.";
                $image_name = 'placeholder.jpg';
            }
        } else {
            $error = "Invalid image type. Use JPG, PNG, GIF or WEBP.";
        }
    }

    if (!$error) {
        $stmt=$conn->prepare("INSERT INTO products (name,description,price,stock,category,image) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param("ssdiss",$name,$description,$price,$stock,$category,$image_name);
        if ($stmt->execute()) {
            // Redirect so student shop instantly sees new product
            header("Location: products.php?saved=1"); exit();
        } else {
            $error="Database error: ".$conn->error;
        }
        $stmt->close();
    }
}

$categories_list = ['Stationery','Electronics','Books','Clothing','Drawing','Lab Equipment','Sports','Miscellaneous'];

admin_head("Add Product");
?>
</head>
<body>
<?php admin_sidebar('add_product'); ?>
<?php admin_topbar('➕ Add New Product', '<a href="products.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back to Products</a>'); ?>
<div class="admin-main">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card p-4">
                <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
                <form method="POST" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Product Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g. Scientific Calculator">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                            <select name="category" class="form-select" required>
                                <option value="">-- Select --</option>
                                <?php foreach ($categories_list as $c): ?>
                                    <option value="<?= $c ?>"><?= $c ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Short product description…"></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Price (₹) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" name="price" class="form-control" required min="1" step="0.01" placeholder="0.00">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Stock Quantity <span class="text-danger">*</span></label>
                            <input type="number" name="stock" class="form-control" required min="0" placeholder="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Product Image</label>
                            <input type="file" name="image" class="form-control" accept="image/*" onchange="previewImg(this)">
                        </div>
                        <div class="col-12" id="preview-wrap" style="display:none;">
                            <img id="img-preview" src="" alt="Preview" style="max-height:160px;border-radius:10px;border:1px solid #dee2e6;">
                        </div>
                    </div>
                    <hr class="mt-4">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-admin px-4"><i class="bi bi-check-circle"></i> Add Product</button>
                        <a href="products.php" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
            <div class="alert alert-info mt-3 small">
                <i class="bi bi-info-circle"></i>
                <strong>Real-time sync:</strong> Once saved, this product immediately appears in the Student Shop with the correct price, stock, and image.
            </div>
        </div>
    </div>
</div>
<script>
function previewImg(input) {
    const wrap=document.getElementById('preview-wrap');
    const img=document.getElementById('img-preview');
    if(input.files&&input.files[0]) {
        const r=new FileReader();
        r.onload=e=>{ img.src=e.target.result; wrap.style.display='block'; };
        r.readAsDataURL(input.files[0]);
    }
}
</script>
<?php admin_footer(); ?>
