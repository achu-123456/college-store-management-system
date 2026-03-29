<?php
/**
 * includes/student_layout.php
 * Single shared layout for ALL student pages.
 * Usage at top of every student page (after session_start & requireStudent):
 *   $page_title  = "My Cart";
 *   $active_page = "cart";          // dashboard | orders | cart
 *   require_once '../includes/student_layout.php';
 * Then at bottom: student_footer();
 */

function student_head(string $title): void {
    echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{$title} – College Store</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --blue-dark: #1a237e;
            --blue-mid:  #283593;
            --blue-light:#3949ab;
        }
        body { background: #f0f2f5; font-family: 'Segoe UI', sans-serif; margin: 0; }

        /* ── Navbar ── */
        .student-navbar {
            background: linear-gradient(135deg, var(--blue-dark), var(--blue-light)) !important;
            box-shadow: 0 2px 12px rgba(0,0,0,0.18);
        }
        .student-navbar .navbar-brand { font-weight: 700; font-size: 18px; }
        .student-navbar .nav-link { color: rgba(255,255,255,0.88) !important; font-size:14px; }
        .student-navbar .nav-link:hover,
        .student-navbar .nav-link.active { color:#fff !important; }
        .student-navbar .nav-link.active { border-bottom: 2px solid rgba(255,255,255,0.7); }

        /* ── Cards ── */
        .card { border: none; border-radius: 12px; box-shadow: 0 2px 14px rgba(0,0,0,0.07); }

        /* ── Status badges ── */
        .badge-pending   { background:#fff3e0; color:#e65100; }
        .badge-confirmed { background:#e3f2fd; color:#1565c0; }
        .badge-delivered { background:#e8f5e9; color:#2e7d32; }
        .badge-cancelled { background:#ffebee; color:#b71c1c; }

        /* ── Misc ── */
        .btn-blue { background: var(--blue-dark); color:#fff; border:none; }
        .btn-blue:hover { background: var(--blue-mid); color:#fff; }
        .price-tag { color: var(--blue-dark); font-weight:700; }
    </style>
HTML;
}

/**
 * @param string $active   one of: dashboard | orders | cart
 * @param mysqli $conn     DB connection (needed for cart count)
 */
function student_navbar(string $active, $conn): void {
    $student_id   = $_SESSION['student_id'];
    $student_name = htmlspecialchars($_SESSION['student_name'] ?? 'Student');

    // Live cart count from DB
    $cart_res   = $conn->query("SELECT COALESCE(SUM(quantity),0) AS cnt FROM cart WHERE student_id = $student_id");
    $cart_count = (int)($cart_res->fetch_assoc()['cnt'] ?? 0);

    $badge = $cart_count > 0
        ? "<span class='position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger' style='font-size:10px;'>$cart_count</span>"
        : '';

    $nav_links = [
        ['dashboard', 'bi-shop',      'Shop',      'dashboard.php'],
        ['orders',    'bi-bag-check', 'My Orders', 'orders.php'],
    ];

    echo '<nav class="navbar student-navbar navbar-expand-lg navbar-dark">';
    echo '<div class="container">';
    echo '<a class="navbar-brand" href="dashboard.php">🎓 College Store</a>';
    echo '<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sNav"><span class="navbar-toggler-icon"></span></button>';
    echo '<div class="collapse navbar-collapse" id="sNav">';
    echo '<ul class="navbar-nav ms-auto align-items-center gap-1">';

    foreach ($nav_links as [$key, $icon, $label, $href]) {
        $cls = $active === $key ? ' active' : '';
        echo "<li class='nav-item'><a class='nav-link{$cls}' href='{$href}'><i class='bi {$icon}'></i> {$label}</a></li>";
    }

    // Cart with live badge
    $cart_cls = $active === 'cart' ? ' active' : '';
    echo "<li class='nav-item'>
            <a class='nav-link{$cart_cls} position-relative' href='cart.php'>
                <i class='bi bi-cart3'></i> Cart {$badge}
            </a>
          </li>";

    echo "<li class='nav-item'><span class='nav-link opacity-75 small'><i class='bi bi-person-circle'></i> {$student_name}</span></li>";
    echo "<li class='nav-item'><a class='btn btn-outline-light btn-sm ms-1' href='logout.php'>Logout</a></li>";

    echo '</ul></div></div></nav>';
}

function student_footer(): void {
    echo <<<HTML
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
HTML;
}
?>
