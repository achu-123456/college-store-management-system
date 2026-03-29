# College Store Management System
PHP + MySQL | Student & Admin Portals

## Setup
1. Import `database.sql` into MySQL (phpMyAdmin or CLI)
2. Edit `includes/db.php` — set DB_USER and DB_PASS
3. Place `college_store/` folder inside `htdocs/` (XAMPP) or `www/` (WAMP)
4. Open: http://localhost/college_store/

## Login Credentials
| Role    | URL                          | Username | Password  |
|---------|------------------------------|----------|-----------|
| Admin   | /college_store/admin/login.php  | admin    | password  |
| Student | /college_store/student/login.php | Register first | — |

## File Structure
```
college_store/
├── index.php                  ← root redirect
├── database.sql               ← import this first
├── includes/
│   ├── db.php                 ← DB config (edit credentials here)
│   ├── auth.php               ← session helpers
│   ├── admin_layout.php       ← shared admin sidebar/topbar (single source)
│   └── student_layout.php     ← shared student navbar (single source)
├── admin/
│   ├── login.php
│   ├── dashboard.php          ← stats, revenue chart, recent orders
│   ├── products.php           ← list / delete products
│   ├── add_product.php        ← add → instantly visible in student shop
│   ├── edit_product.php       ← edit price/stock → syncs to student shop
│   ├── orders.php             ← all orders, inline status update
│   ├── order_detail.php       ← order details + status update
│   ├── students.php           ← registered students list
│   ├── sales_report.php       ← date-range revenue report + chart
│   └── logout.php
├── student/
│   ├── login.php
│   ├── register.php
│   ├── forgot_password.php
│   ├── reset_password.php
│   ├── dashboard.php          ← product browse + search + filter
│   ├── cart.php               ← session cart with live totals
│   ├── cart_add.php           ← add to cart action
│   ├── checkout.php           ← order placement
│   ├── orders.php             ← order history
│   ├── order_detail.php       ← single order view
│   ├── invoice.php            ← HTML invoice (printable to PDF)
│   └── logout.php
└── uploads/                   ← product images go here (chmod 755)
```

## Real-time Sync
- Admin adds/edits product → student shop updates on next load
- Admin changes order status → student sees new status immediately
- Student places order → stock reduces → admin sees updated count
- Both sides read from the same MySQL database — no caching layer
