<?php
// student/cart.php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/student_layout.php';
requireStudent();

$student_id = $_SESSION['student_id'];

if (isset($_GET['remove'])) {
    $rid = (int)$_GET['remove'];
    $conn->query("DELETE FROM cart WHERE id=$rid AND student_id=$student_id");
    header("Location: cart.php"); exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['qty'] as $cid => $qty) {
        $cid = (int)$cid; $qty = max(1,(int)$qty);
        $conn->query("UPDATE cart SET quantity=$qty WHERE id=$cid AND student_id=$student_id");
    }
    header("Location: cart.php?updated=1"); exit();
}

$cart_items = $conn->query("
    SELECT c.id AS cart_id, c.quantity, p.id AS product_id,
           p.name, p.price, p.stock, p.image, p.category
    FROM cart c JOIN products p ON c.product_id=p.id
    WHERE c.student_id=$student_id");

$total=0; $items=[];
while ($item=$cart_items->fetch_assoc()) {
    $item['subtotal']=$item['price']*$item['quantity']; $total+=$item['subtotal']; $items[]=$item;
}
$delivery    = $total>=500 ? 0 : 40;
$grand_total = $total + $delivery;

student_head("My Cart");
?>
    <style>
        .product-thumb { width:64px;height:64px;object-fit:contain;background:#f8f9fa;border-radius:8px; }
        .qty-input { width:70px; text-align:center; }
        .summary-card { background:linear-gradient(135deg,#1a237e,#3949ab); color:#fff; border-radius:14px; }
        .btn-checkout { background:#4caf50;color:#fff;border:none;border-radius:8px;font-weight:600; }
        .btn-checkout:hover { background:#388e3c;color:#fff; }
    </style>
</head>
<body>
<?php student_navbar('cart', $conn); ?>
<div class="container py-4">
    <h4 class="mb-4"><i class="bi bi-cart3"></i> My Cart</h4>
    <?php if (isset($_GET['updated'])): ?>
        <div class="alert alert-success">Cart updated!</div>
    <?php endif; ?>
    <?php if (empty($items)): ?>
        <div class="text-center py-5">
            <i class="bi bi-cart-x display-3 text-muted"></i>
            <p class="mt-3 text-muted">Your cart is empty.</p>
            <a href="dashboard.php" class="btn btn-blue">Browse Products</a>
        </div>
    <?php else: ?>
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card p-0">
                <form method="POST">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr><th>Product</th><th>Price</th><th>Qty</th><th>Subtotal</th><th></th></tr>
                            </thead>
                            <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <img src="../uploads/<?= htmlspecialchars($item['image']) ?>"
                                                 onerror="this.src='https://via.placeholder.com/64?text=?'"
                                                 class="product-thumb" alt="">
                                            <div>
                                                <strong><?= htmlspecialchars($item['name']) ?></strong>
                                                <br><small class="text-muted"><?= htmlspecialchars($item['category']) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>₹<?= number_format($item['price'],2) ?></td>
                                    <td><input type="number" name="qty[<?= $item['cart_id'] ?>]" value="<?= $item['quantity'] ?>" min="1" max="<?= $item['stock'] ?>" class="form-control qty-input"></td>
                                    <td><strong>₹<?= number_format($item['subtotal'],2) ?></strong></td>
                                    <td>
                                        <a href="cart.php?remove=<?= $item['cart_id'] ?>" class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Remove this item?')"><i class="bi bi-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="p-3 border-top d-flex justify-content-between">
                        <a href="dashboard.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Continue Shopping</a>
                        <button type="submit" name="update_cart" class="btn btn-outline-primary"><i class="bi bi-arrow-clockwise"></i> Update Cart</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="summary-card p-4">
                <h5 class="mb-3"><i class="bi bi-receipt"></i> Order Summary</h5>
                <hr class="border-light">
                <?php foreach ($items as $item): ?>
                    <div class="d-flex justify-content-between small mb-1">
                        <span><?= htmlspecialchars(substr($item['name'],0,22)) ?>… × <?= $item['quantity'] ?></span>
                        <span>₹<?= number_format($item['subtotal'],2) ?></span>
                    </div>
                <?php endforeach; ?>
                <hr class="border-light">
                <div class="d-flex justify-content-between mb-1">
                    <span>Subtotal</span><span>₹<?= number_format($total,2) ?></span>
                </div>
                <div class="d-flex justify-content-between small opacity-75 mb-2">
                    <span>Delivery</span><span><?= $delivery===0 ? 'FREE' : '₹40.00' ?></span>
                </div>
                <hr class="border-light">
                <div class="d-flex justify-content-between fw-bold fs-5 mb-3">
                    <span>Total</span><span>₹<?= number_format($grand_total,2) ?></span>
                </div>
                <?php if ($delivery>0): ?>
                    <small class="opacity-75">Add ₹<?= number_format(500-$total,2) ?> more for free delivery!</small><br><br>
                <?php endif; ?>
                <a href="checkout.php" class="btn btn-checkout w-100 btn-lg"><i class="bi bi-bag-check-fill"></i> Proceed to Checkout</a>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php student_footer(); ?>
