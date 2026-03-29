<?php
// student/checkout.php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/student_layout.php';
requireStudent();

$student_id = $_SESSION['student_id'];
$cart_items = $conn->query("
    SELECT c.id AS cart_id, c.quantity, p.id AS product_id, p.name, p.price, p.stock
    FROM cart c JOIN products p ON c.product_id=p.id WHERE c.student_id=$student_id");

$items=[]; $total=0;
while ($item=$cart_items->fetch_assoc()) {
    $item['subtotal']=$item['price']*$item['quantity']; $total+=$item['subtotal']; $items[]=$item;
}
if (empty($items)) { header("Location: cart.php"); exit(); }

$delivery    = $total>=500 ? 0 : 40;
$grand_total = $total + $delivery;
$success=false; $order_id=0; $stock_error='';

if ($_SERVER['REQUEST_METHOD']==='POST') {
    foreach ($items as $item) {
        if ($item['quantity']>$item['stock']) { $stock_error="'{$item['name']}' has only {$item['stock']} units in stock."; break; }
    }
    if (!$stock_error) {
        $stmt=$conn->prepare("INSERT INTO orders (student_id,total_amount) VALUES (?,?)");
        $stmt->bind_param("id",$student_id,$grand_total); $stmt->execute();
        $order_id=$stmt->insert_id; $stmt->close();
        foreach ($items as $item) {
            $oi=$conn->prepare("INSERT INTO order_items (order_id,product_id,quantity,price) VALUES (?,?,?,?)");
            $oi->bind_param("iiid",$order_id,$item['product_id'],$item['quantity'],$item['price']); $oi->execute(); $oi->close();
            $conn->query("UPDATE products SET stock=stock-{$item['quantity']} WHERE id={$item['product_id']}");
        }
        $conn->query("DELETE FROM cart WHERE student_id=$student_id");
        $success=true;
    }
}
$student=$conn->query("SELECT * FROM students WHERE id=$student_id")->fetch_assoc();

student_head("Checkout");
?>
    <style>
        .summary-card { background:linear-gradient(135deg,#1a237e,#3949ab);color:#fff;border-radius:14px; }
        .btn-place { background:#4caf50;color:#fff;border:none;border-radius:8px;font-weight:600; }
        .btn-place:hover { background:#388e3c;color:#fff; }
        .success-box { background:#e8f5e9;border-left:5px solid #4caf50;border-radius:8px; }
    </style>
</head>
<body>
<?php student_navbar('dashboard', $conn); ?>
<div class="container py-4">
    <?php if ($success): ?>
        <div class="text-center py-5">
            <div class="success-box p-4 d-inline-block text-start mb-4">
                <h4><i class="bi bi-check-circle-fill text-success"></i> Order Placed Successfully!</h4>
                <p class="mb-1">Order ID: <strong>#<?= $order_id ?></strong></p>
                <p class="mb-1">Total Paid: <strong>₹<?= number_format($grand_total,2) ?></strong></p>
                <p class="mb-0">Collect your items at the College Store Counter.</p>
            </div>
            <div class="d-flex justify-content-center gap-3 flex-wrap">
                <a href="invoice.php?order_id=<?= $order_id ?>" class="btn btn-outline-primary"><i class="bi bi-file-earmark-pdf"></i> Download Invoice</a>
                <a href="orders.php" class="btn btn-blue">View My Orders</a>
                <a href="dashboard.php" class="btn btn-outline-secondary">Continue Shopping</a>
            </div>
        </div>
    <?php else: ?>
        <?php if ($stock_error): ?><div class="alert alert-danger"><?= $stock_error ?></div><?php endif; ?>
        <h4 class="mb-4"><i class="bi bi-bag-check"></i> Checkout</h4>
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card p-4 mb-4">
                    <h5 class="mb-3">📋 Your Details</h5>
                    <div class="row">
                        <div class="col-6"><p class="mb-1 text-muted small">Name</p><p class="fw-bold"><?= htmlspecialchars($student['name']) ?></p></div>
                        <div class="col-6"><p class="mb-1 text-muted small">Roll No</p><p class="fw-bold"><?= htmlspecialchars($student['roll_no']) ?></p></div>
                        <div class="col-6"><p class="mb-1 text-muted small">Email</p><p><?= htmlspecialchars($student['email']) ?></p></div>
                        <div class="col-6"><p class="mb-1 text-muted small">Phone</p><p><?= htmlspecialchars($student['phone']??'N/A') ?></p></div>
                    </div>
                    <div class="alert alert-info small mb-0"><i class="bi bi-info-circle"></i> Pick up from <strong>College Store Counter</strong>.</div>
                </div>
                <div class="card p-4">
                    <h5 class="mb-3">🛒 Order Items</h5>
                    <?php foreach ($items as $item): ?>
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <div><strong><?= htmlspecialchars($item['name']) ?></strong><br><small class="text-muted">Qty: <?= $item['quantity'] ?> × ₹<?= number_format($item['price'],2) ?></small></div>
                            <span class="fw-bold">₹<?= number_format($item['subtotal'],2) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="summary-card p-4">
                    <h5 class="mb-3"><i class="bi bi-receipt"></i> Payment Summary</h5>
                    <div class="d-flex justify-content-between mb-2"><span>Subtotal</span><span>₹<?= number_format($total,2) ?></span></div>
                    <div class="d-flex justify-content-between mb-2 small opacity-75"><span>Delivery</span><span><?= $delivery===0?'FREE':'₹40.00' ?></span></div>
                    <hr class="border-light">
                    <div class="d-flex justify-content-between fw-bold fs-5 mb-3"><span>Total</span><span>₹<?= number_format($grand_total,2) ?></span></div>
                    <div class="alert alert-light text-dark small mb-3"><i class="bi bi-cash-coin"></i> Payment: <strong>Cash on Pickup</strong></div>
                    <form method="POST">
                        <button type="submit" class="btn btn-place w-100 btn-lg"><i class="bi bi-bag-check-fill"></i> Place Order</button>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php student_footer(); ?>
