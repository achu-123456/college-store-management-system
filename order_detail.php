<?php
// student/order_detail.php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/student_layout.php';
requireStudent();

$student_id = $_SESSION['student_id'];
$order_id   = (int)($_GET['id'] ?? 0);
$order      = $conn->query("SELECT * FROM orders WHERE id=$order_id AND student_id=$student_id")->fetch_assoc();
if (!$order) { header("Location: orders.php"); exit(); }

$order_items = $conn->query("
    SELECT oi.*, p.name, p.image, p.category
    FROM order_items oi JOIN products p ON oi.product_id=p.id
    WHERE oi.order_id=$order_id");
$student = $conn->query("SELECT * FROM students WHERE id=$student_id")->fetch_assoc();

student_head("Order #$order_id");
?>
</head>
<body>
<?php student_navbar('orders', $conn); ?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-0">Order #<?= $order_id ?></h4>
            <small class="text-muted">Placed on <?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></small>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge badge-<?= $order['status'] ?> px-3 py-2 rounded-pill fs-6"><?= ucfirst($order['status']) ?></span>
            <a href="invoice.php?order_id=<?= $order_id ?>" class="btn btn-outline-danger"><i class="bi bi-file-earmark-pdf"></i> Invoice</a>
            <a href="orders.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back</a>
        </div>
    </div>
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card p-4">
                <h6 class="mb-3">Items Ordered</h6>
                <?php while ($item=$order_items->fetch_assoc()): ?>
                    <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                        <div class="d-flex align-items-center gap-3">
                            <img src="../uploads/<?= htmlspecialchars($item['image']) ?>"
                                 onerror="this.src='https://via.placeholder.com/60?text=?'"
                                 style="width:60px;height:60px;object-fit:contain;border-radius:8px;background:#f8f9fa;" alt="">
                            <div>
                                <strong><?= htmlspecialchars($item['name']) ?></strong>
                                <br><small class="text-muted"><?= htmlspecialchars($item['category']) ?></small>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="small text-muted">Qty: <?= $item['quantity'] ?> × ₹<?= number_format($item['price'],2) ?></div>
                            <strong>₹<?= number_format($item['price']*$item['quantity'],2) ?></strong>
                        </div>
                    </div>
                <?php endwhile; ?>
                <div class="d-flex justify-content-end mt-3 fs-5 fw-bold">
                    Grand Total: ₹<?= number_format($order['total_amount'],2) ?>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card p-4 mb-3">
                <h6 class="mb-3">Your Details</h6>
                <p class="mb-1"><strong><?= htmlspecialchars($student['name']) ?></strong></p>
                <p class="mb-1 text-muted small">Roll: <?= htmlspecialchars($student['roll_no']) ?></p>
                <p class="mb-1 text-muted small"><?= htmlspecialchars($student['email']) ?></p>
                <p class="mb-0 text-muted small"><?= htmlspecialchars($student['phone']??'N/A') ?></p>
            </div>
            <div class="card p-4">
                <h6 class="mb-3">Pickup Info</h6>
                <p class="mb-1"><i class="bi bi-geo-alt-fill text-danger"></i> College Store Counter</p>
                <p class="mb-0 text-muted small">Payment: Cash on Pickup</p>
            </div>
        </div>
    </div>
</div>
<?php student_footer(); ?>
