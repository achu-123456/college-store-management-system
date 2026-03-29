<?php
// admin/products.php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/admin_layout.php';
requireAdmin();

if (isset($_GET['delete'])) {
    $id=(int)$_GET['delete'];
    $conn->query("DELETE FROM products WHERE id=$id");
    header("Location: products.php?deleted=1"); exit();
}

$filter = $_GET['filter'] ?? '';
$where  = $filter==='low_stock' ? "WHERE stock<=5" : '';
$products = $conn->query("SELECT * FROM products $where ORDER BY created_at DESC");

admin_head("Products");
?>
</head>
<body>
<?php admin_sidebar('products'); ?>
<?php admin_topbar('📦 Product Management', '<a href="add_product.php" class="btn btn-admin btn-sm"><i class="bi bi-plus-circle"></i> Add Product</a>'); ?>
<div class="admin-main">
    <?php if (isset($_GET['deleted'])): ?><div class="alert alert-success">Product deleted successfully.</div><?php endif; ?>
    <?php if (isset($_GET['saved'])): ?><div class="alert alert-success">Product saved successfully.</div><?php endif; ?>
    <?php if ($filter==='low_stock'): ?>
        <div class="alert alert-warning">Showing low-stock products (≤5 units). <a href="products.php">Show all</a></div>
    <?php endif; ?>
    <div class="card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr><th>ID</th><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Actions</th></tr>
                </thead>
                <tbody>
                <?php if ($products->num_rows===0): ?>
                    <tr><td colspan="7" class="text-center text-muted py-5">No products found.</td></tr>
                <?php endif; ?>
                <?php while ($p=$products->fetch_assoc()): ?>
                    <tr>
                        <td><small class="text-muted">#<?= $p['id'] ?></small></td>
                        <td><img src="../uploads/<?= htmlspecialchars($p['image']) ?>"
                                 onerror="this.src='https://via.placeholder.com/48?text=?'"
                                 class="product-thumb" alt=""></td>
                        <td>
                            <strong><?= htmlspecialchars($p['name']) ?></strong>
                            <br><small class="text-muted"><?= htmlspecialchars(substr($p['description'],0,55)) ?>…</small>
                        </td>
                        <td><?= htmlspecialchars($p['category']) ?></td>
                        <td>₹<?= number_format($p['price'],2) ?></td>
                        <td>
                            <?php if ($p['stock']<=5): ?>
                                <span class="badge bg-danger"><?= $p['stock'] ?> left</span>
                            <?php elseif ($p['stock']<=20): ?>
                                <span class="badge bg-warning text-dark"><?= $p['stock'] ?></span>
                            <?php else: ?>
                                <span class="badge bg-success"><?= $p['stock'] ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="edit_product.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></a>
                            <a href="products.php?delete=<?= $p['id'] ?>" class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('Delete this product? This cannot be undone.')"><i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php admin_footer(); ?>
