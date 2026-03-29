<?php
// student/dashboard.php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/student_layout.php';
requireStudent();

$search   = isset($_GET['search']) ? sanitize($conn, $_GET['search']) : '';
$category = isset($_GET['category']) ? sanitize($conn, $_GET['category']) : '';

$where = "WHERE stock > 0";
if ($search)   $where .= " AND (name LIKE '%$search%' OR description LIKE '%$search%')";
if ($category) $where .= " AND category = '$category'";

$products   = $conn->query("SELECT * FROM products $where ORDER BY created_at DESC");
$categories = $conn->query("SELECT DISTINCT category FROM products WHERE stock > 0 ORDER BY category");

student_head("Shop");
?>
    <style>
        .product-card { transition: transform .2s, box-shadow .2s; }
        .product-card:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0,0,0,0.13) !important; }
        .product-img { height: 155px; object-fit: contain; background: #f8f9fa; padding: 10px; border-radius: 12px 12px 0 0; width:100%; }
        .badge-cat { background:#e8eaf6; color:#1a237e; font-size:11px; }
        .btn-add { background:#1a237e; color:#fff; border:none; }
        .btn-add:hover { background:#283593; color:#fff; }
        .search-box { background:#fff; border-radius:12px; padding:16px; box-shadow:0 2px 8px rgba(0,0,0,0.06); }
    </style>
</head>
<body>
<?php student_navbar('dashboard', $conn); ?>
<div class="container py-4">
    <div class="search-box mb-4">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search products…" value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
            <div class="col-md-4">
                <select name="category" class="form-select">
                    <option value="">All Categories</option>
                    <?php while ($cat = $categories->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($cat['category']) ?>" <?= $category === $cat['category'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['category']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-blue w-100" type="submit">Filter</button>
                <a href="dashboard.php" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
    <?php if (isset($_GET['added'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            ✅ Item added to cart! <a href="cart.php">View Cart</a>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['error']) && $_GET['error'] === 'stock'): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            ⚠️ Requested quantity exceeds available stock.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <div class="row g-4">
        <?php if ($products->num_rows === 0): ?>
            <div class="col-12 text-center py-5">
                <i class="bi bi-search display-3 text-muted"></i>
                <p class="mt-3 text-muted">No products found. <a href="dashboard.php">Browse all</a></p>
            </div>
        <?php else: ?>
            <?php while ($p = $products->fetch_assoc()): ?>
            <div class="col-6 col-md-4 col-lg-3">
                <div class="card product-card h-100">
                    <img src="../uploads/<?= htmlspecialchars($p['image']) ?>"
                         onerror="this.src='https://via.placeholder.com/200x155?text=<?= urlencode($p['name']) ?>'"
                         class="product-img" alt="<?= htmlspecialchars($p['name']) ?>">
                    <div class="card-body d-flex flex-column p-3">
                        <span class="badge badge-cat mb-1"><?= htmlspecialchars($p['category']) ?></span>
                        <h6 class="card-title mb-1"><?= htmlspecialchars($p['name']) ?></h6>
                        <p class="text-muted small mb-2 flex-grow-1"><?= htmlspecialchars(substr($p['description'], 0, 65)) ?>…</p>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="price-tag fs-5">₹<?= number_format($p['price'], 2) ?></span>
                            <?php if ($p['stock'] <= 5): ?>
                                <span class="text-danger small"><i class="bi bi-exclamation-circle"></i> Only <?= $p['stock'] ?> left</span>
                            <?php else: ?>
                                <span class="text-success small"><i class="bi bi-check-circle"></i> In Stock</span>
                            <?php endif; ?>
                        </div>
                        <form method="POST" action="cart_add.php">
                            <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                            <div class="input-group input-group-sm">
                                <input type="number" name="quantity" value="1" min="1" max="<?= $p['stock'] ?>" class="form-control">
                                <button class="btn btn-add" type="submit"><i class="bi bi-cart-plus"></i> Add</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</div>
<?php student_footer(); ?>
