<?php
// student/orders.php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/student_layout.php';
requireStudent();

$student_id = $_SESSION['student_id'];
$orders = $conn->query("
    SELECT o.*, COUNT(oi.id) AS item_count
    FROM orders o LEFT JOIN order_items oi ON o.id=oi.order_id
    WHERE o.student_id=$student_id
    GROUP BY o.id ORDER BY o.created_at DESC");

student_head("My Orders");
?>
</head>
<body>
<?php student_navbar('orders', $conn); ?>
<div class="container py-4">
    <h4 class="mb-4"><i class="bi bi-bag-check"></i> My Orders</h4>
    <?php if ($orders->num_rows===0): ?>
        <div class="text-center py-5">
            <i class="bi bi-bag-x display-3 text-muted"></i>
            <p class="mt-3 text-muted">You haven't placed any orders yet.</p>
            <a href="dashboard.php" class="btn btn-blue">Start Shopping</a>
        </div>
    <?php else: ?>
    <div class="card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr><th>#Order</th><th>Date</th><th>Items</th><th>Total</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                <?php while ($o=$orders->fetch_assoc()): ?>
                    <tr>
                        <td><strong>#<?= $o['id'] ?></strong></td>
                        <td><?= date('d M Y, h:i A', strtotime($o['created_at'])) ?></td>
                        <td><?= $o['item_count'] ?> item(s)</td>
                        <td><strong>₹<?= number_format($o['total_amount'],2) ?></strong></td>
                        <td><span class="badge badge-<?= $o['status'] ?> px-3 py-1 rounded-pill"><?= ucfirst($o['status']) ?></span></td>
                        <td>
                            <a href="order_detail.php?id=<?= $o['id'] ?>" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-eye"></i> View</a>
                            <a href="invoice.php?order_id=<?= $o['id'] ?>" class="btn btn-sm btn-outline-danger"><i class="bi bi-file-pdf"></i> Invoice</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php student_footer(); ?>
