<?php
/**
 * includes/admin_layout.php
 * Single shared layout for ALL admin pages.
 * Usage at top of every admin page:
 *   $page_title   = "Products";
 *   $active_page  = "products";
 *   require_once '../includes/admin_layout.php';
 * Then at bottom: admin_footer();
 */

function admin_head(string $title): void {
    echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{$title} – Admin | College Store</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --admin-red: #b71c1c;
            --admin-red-dark: #7f0000;
            --admin-red-light: #d32f2f;
            --sidebar-width: 230px;
        }
        body { background: #f4f6f9; font-family: 'Segoe UI', sans-serif; margin: 0; }

        /* ── Sidebar ── */
        .admin-sidebar {
            background: linear-gradient(180deg, var(--admin-red-dark) 0%, var(--admin-red) 100%);
            width: var(--sidebar-width);
            min-height: 100vh;
            position: fixed;
            left: 0; top: 0;
            display: flex;
            flex-direction: column;
            z-index: 1000;
            box-shadow: 2px 0 16px rgba(0,0,0,0.18);
        }
        .admin-sidebar .brand {
            color: #fff;
            font-size: 17px;
            font-weight: 700;
            padding: 22px 20px 16px;
            border-bottom: 1px solid rgba(255,255,255,0.15);
            letter-spacing: .3px;
        }
        .admin-sidebar .brand span { opacity:.7; font-size:12px; display:block; font-weight:400; }
        .admin-sidebar .nav-link {
            color: rgba(255,255,255,0.82);
            padding: 10px 16px;
            border-radius: 8px;
            margin: 2px 10px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 9px;
            transition: background .15s;
        }
        .admin-sidebar .nav-link:hover { background: rgba(255,255,255,0.15); color:#fff; }
        .admin-sidebar .nav-link.active { background: rgba(255,255,255,0.22); color:#fff; font-weight:600; }
        .admin-sidebar .nav-divider { border-color: rgba(255,255,255,0.15); margin: 6px 14px; }
        .admin-sidebar .sidebar-footer { color:rgba(255,255,255,.5); font-size:11px; padding:14px 20px; margin-top:auto; }

        /* ── Topbar ── */
        .admin-topbar {
            background: #fff;
            padding: 13px 28px;
            margin-left: var(--sidebar-width);
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            position: sticky;
            top: 0;
            z-index: 999;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .admin-topbar h5 { margin: 0; font-weight: 600; font-size: 16px; }

        /* ── Main ── */
        .admin-main { margin-left: var(--sidebar-width); padding: 26px 28px; }

        /* ── Cards ── */
        .card { border: none; border-radius: 12px; box-shadow: 0 2px 14px rgba(0,0,0,0.07); }

        /* ── Status badges ── */
        .badge-pending   { background:#fff3e0; color:#e65100; }
        .badge-confirmed { background:#e3f2fd; color:#1565c0; }
        .badge-delivered { background:#e8f5e9; color:#2e7d32; }
        .badge-cancelled { background:#ffebee; color:#b71c1c; }

        /* ── Stat card ── */
        .stat-card { border-radius:12px; padding:18px 20px; background:#fff; box-shadow:0 2px 14px rgba(0,0,0,0.07); }
        .stat-icon { width:50px;height:50px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:20px; }

        /* ── Misc ── */
        .btn-admin { background: var(--admin-red); color:#fff; border:none; }
        .btn-admin:hover { background: var(--admin-red-light); color:#fff; }
        .table thead th { font-size:13px; font-weight:600; text-transform:uppercase; letter-spacing:.4px; color:#666; }
        .product-thumb { width:48px;height:48px;object-fit:contain;background:#f8f9fa;border-radius:7px; }
    </style>
HTML;
}

function admin_sidebar(string $active): void {
    $nav = [
        ['dashboard',    'bi-speedometer2',  'Dashboard',    'dashboard.php'],
        ['products',     'bi-box-seam',      'Products',     'products.php'],
        ['add_product',  'bi-plus-circle',   'Add Product',  'add_product.php'],
        ['orders',       'bi-bag',           'Orders',       'orders.php'],
        ['students',     'bi-people',        'Students',     'students.php'],
        ['sales_report', 'bi-bar-chart-line','Sales Report', 'sales_report.php'],
    ];

    $admin_user = htmlspecialchars($_SESSION['admin_username'] ?? 'Admin');

    echo '<div class="admin-sidebar">';
    echo '<div class="brand">🏪 College Store <span>Admin Panel</span></div>';
    echo '<nav class="nav flex-column mt-2">';
    foreach ($nav as [$key, $icon, $label, $href]) {
        $cls = $active === $key ? ' active' : '';
        echo "<a href='{$href}' class='nav-link{$cls}'><i class='bi {$icon}'></i> {$label}</a>";
    }
    echo '<hr class="nav-divider">';
    echo "<a href='logout.php' class='nav-link'><i class='bi bi-box-arrow-left'></i> Logout</a>";
    echo '</nav>';
    echo "<div class='sidebar-footer'>Logged in as: <strong>{$admin_user}</strong></div>";
    echo '</div>';
}

function admin_topbar(string $title, string $extra_html = ''): void {
    $date = date('D, d M Y');
    echo <<<HTML
    <div class="admin-topbar">
        <h5>{$title}</h5>
        <div class="d-flex align-items-center gap-3">
            {$extra_html}
            <span class="text-muted small"><i class="bi bi-calendar3"></i> {$date}</span>
        </div>
    </div>
HTML;
}

function admin_footer(): void {
    echo <<<HTML
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
HTML;
}
?>
