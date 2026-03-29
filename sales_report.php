<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/admin_layout.php';
requireAdmin();

// Date filter
$from = isset($_GET['from']) ? sanitize($conn, $_GET['from']) : date('Y-m-01');
$to   = isset($_GET['to'])   ? sanitize($conn, $_GET['to'])   : date('Y-m-d');

$summary = $conn->query("
    SELECT COUNT(*) AS total_orders,
           COALESCE(SUM(total_amount),0) AS total_revenue,
           COALESCE(AVG(total_amount),0) AS avg_order
    FROM orders
    WHERE status!='cancelled'
      AND DATE(created_at) BETWEEN '$from' AND '$to'")->fetch_assoc();

$top_products = $conn->query("
    SELECT p.name, p.category, SUM(oi.quantity) AS units_sold,
           SUM(oi.quantity*oi.price) AS revenue
    FROM order_items oi
    JOIN products p ON oi.product_id=p.id
    JOIN orders o ON oi.order_id=o.id
    WHERE o.status!='cancelled'
      AND DATE(o.created_at) BETWEEN '$from' AND '$to'
    GROUP BY p.id ORDER BY revenue DESC LIMIT 10");

$daily = $conn->query("
    SELECT DATE(created_at) AS day,
           COUNT(*) AS orders,
           SUM(total_amount) AS revenue
    FROM orders
    WHERE status!='cancelled'
      AND DATE(created_at) BETWEEN '$from' AND '$to'
    GROUP BY DATE(created_at) ORDER BY day ASC");

$chart_days=[]; $chart_rev=[]; $chart_orders=[];
while ($r=$daily->fetch_assoc()) {
    $chart_days[]   = date('d M', strtotime($r['day']));
    $chart_rev[]    = (float)$r['revenue'];
    $chart_orders[] = (int)$r['orders'];
}

admin_head("Sales Report");
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<?php admin_sidebar('sales_report'); ?>
<?php admin_topbar('📊 Sales Report'); ?>
<div class="admin-main">
    <!-- Date Filter -->
    <div class="card p-3 mb-4">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold">From Date</label>
                <input type="date" name="from" class="form-control" value="<?= $from ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">To Date</label>
                <input type="date" name="to" class="form-control" value="<?= $to ?>">
            </div>
            <div class="col-auto d-flex gap-2">
                <button type="submit" class="btn btn-admin">Generate Report</button>
                <a href="sales_report.php" class="btn btn-outline-secondary">This Month</a>
            </div>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="stat-card text-center">
                <div class="text-muted small mb-1">Total Revenue</div>
                <div class="fw-bold fs-3 text-danger">₹<?= number_format($summary['total_revenue'],2) ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card text-center">
                <div class="text-muted small mb-1">Total Orders</div>
                <div class="fw-bold fs-3"><?= $summary['total_orders'] ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card text-center">
                <div class="text-muted small mb-1">Avg Order Value</div>
                <div class="fw-bold fs-3">₹<?= number_format($summary['avg_order'],2) ?></div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card p-4">
                <h6 class="mb-3">Daily Revenue</h6>
                <canvas id="salesChart" height="110"></canvas>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card p-4 h-100">
                <h6 class="mb-3">🏆 Top Products</h6>
                <?php
                $rank=1;
                while ($tp=$top_products->fetch_assoc()):
                ?>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <div>
                            <span class="badge bg-secondary me-1">#<?= $rank++ ?></span>
                            <strong class="small"><?= htmlspecialchars($tp['name']) ?></strong>
                            <br><small class="text-muted ms-4"><?= $tp['units_sold'] ?> units sold</small>
                        </div>
                        <span class="text-success fw-bold small">₹<?= number_format($tp['revenue'],0) ?></span>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</div>
<script>
const ctx=document.getElementById('salesChart').getContext('2d');
new Chart(ctx,{type:'line',data:{labels:<?= json_encode($chart_days) ?>,datasets:[{label:'Revenue (₹)',data:<?= json_encode($chart_rev) ?>,borderColor:'#b71c1c',backgroundColor:'rgba(183,28,28,0.08)',fill:true,tension:.3,pointRadius:4}]},options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,ticks:{callback:val=>'₹'+val.toLocaleString()}}}}});
</script>
<?php admin_footer(); ?>
