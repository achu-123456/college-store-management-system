<?php
// admin/students.php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/admin_layout.php';
requireAdmin();

$search = isset($_GET['search']) ? sanitize($conn, $_GET['search']) : '';
$where  = $search ? "WHERE name LIKE '%$search%' OR email LIKE '%$search%' OR roll_no LIKE '%$search%'" : '';

$students = $conn->query("
    SELECT s.*, COUNT(o.id) AS order_count, COALESCE(SUM(o.total_amount),0) AS total_spent
    FROM students s LEFT JOIN orders o ON s.id=o.student_id
    $where GROUP BY s.id ORDER BY s.created_at DESC");

admin_head("Students");
?>
</head>
<body>
<?php admin_sidebar('students'); ?>
<?php admin_topbar('👩‍🎓 Registered Students'); ?>
<div class="admin-main">
    <div class="card p-3 mb-3">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search by name, email or roll no…" value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
            <div class="col-auto d-flex gap-2">
                <button class="btn btn-admin" type="submit">Search</button>
                <a href="students.php" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
    <div class="card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr><th>ID</th><th>Name</th><th>Roll No</th><th>Email</th><th>Phone</th><th>Orders</th><th>Total Spent</th><th>Joined</th></tr>
                </thead>
                <tbody>
                <?php if ($students->num_rows===0): ?>
                    <tr><td colspan="8" class="text-center text-muted py-5">No students found.</td></tr>
                <?php endif; ?>
                <?php while ($s=$students->fetch_assoc()): ?>
                    <tr>
                        <td><small class="text-muted">#<?= $s['id'] ?></small></td>
                        <td><strong><?= htmlspecialchars($s['name']) ?></strong></td>
                        <td><?= htmlspecialchars($s['roll_no']) ?></td>
                        <td><?= htmlspecialchars($s['email']) ?></td>
                        <td><?= htmlspecialchars($s['phone']??'N/A') ?></td>
                        <td><span class="badge bg-secondary"><?= $s['order_count'] ?></span></td>
                        <td>₹<?= number_format($s['total_spent'],2) ?></td>
                        <td class="small text-muted"><?= date('d M Y', strtotime($s['created_at'])) ?></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php admin_footer(); ?>
