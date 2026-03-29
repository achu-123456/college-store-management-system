<?php
// student/invoice.php — PDF Invoice Generator
// Requires: composer require setasign/fpdf
// OR download fpdf.php from http://www.fpdf.org/ and place in /includes/

session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireStudent();

$student_id = $_SESSION['student_id'];
$order_id   = (int)($_GET['order_id'] ?? 0);

// Fetch order
$order = $conn->query("SELECT * FROM orders WHERE id=$order_id AND student_id=$student_id")->fetch_assoc();
if (!$order) {
    header("Location: orders.php");
    exit();
}

$student = $conn->query("SELECT * FROM students WHERE id=$student_id")->fetch_assoc();
$items   = $conn->query("
    SELECT oi.*, p.name, p.category
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = $order_id
");

// ---- FPDF-based PDF Generation ----
// If FPDF is not installed, show HTML invoice that can be printed to PDF

$items_data = [];
while ($item = $items->fetch_assoc()) {
    $items_data[] = $item;
}

// Check if FPDF is available
$fpdf_path = '../includes/fpdf/fpdf.php';
$use_fpdf  = file_exists($fpdf_path);

if ($use_fpdf) {
    require_once $fpdf_path;

    class Invoice extends FPDF {
        function Header() {
            $this->SetFont('Arial', 'B', 18);
            $this->SetTextColor(26, 35, 126);
            $this->Cell(0, 10, 'College Store - Tax Invoice', 0, 1, 'C');
            $this->SetFont('Arial', '', 10);
            $this->SetTextColor(100, 100, 100);
            $this->Cell(0, 6, 'Official College Campus Store', 0, 1, 'C');
            $this->Ln(4);
            $this->SetDrawColor(26, 35, 126);
            $this->SetLineWidth(0.5);
            $this->Line(10, $this->GetY(), 200, $this->GetY());
            $this->Ln(4);
        }
        function Footer() {
            $this->SetY(-15);
            $this->SetFont('Arial', 'I', 8);
            $this->SetTextColor(150, 150, 150);
            $this->Cell(0, 10, 'Thank you for your purchase! For queries, contact the College Store.', 0, 0, 'C');
        }
    }

    $pdf = new Invoice();
    $pdf->AddPage();

    // Invoice meta
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(95, 7, 'Invoice To:', 0, 0);
    $pdf->Cell(95, 7, 'Invoice Details:', 0, 1);

    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(95, 6, $student['name'], 0, 0);
    $pdf->Cell(60, 6, 'Invoice #:', 0, 0);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(35, 6, 'INV-' . str_pad($order_id, 6, '0', STR_PAD_LEFT), 0, 1);

    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(95, 6, 'Roll No: ' . $student['roll_no'], 0, 0);
    $pdf->Cell(60, 6, 'Date:', 0, 0);
    $pdf->Cell(35, 6, date('d-m-Y', strtotime($order['created_at'])), 0, 1);

    $pdf->Cell(95, 6, $student['email'], 0, 0);
    $pdf->Cell(60, 6, 'Status:', 0, 0);
    $pdf->Cell(35, 6, ucfirst($order['status']), 0, 1);

    $pdf->Ln(6);

    // Table Header
    $pdf->SetFillColor(26, 35, 126);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(8,  8, '#',        1, 0, 'C', true);
    $pdf->Cell(80, 8, 'Product',  1, 0, 'C', true);
    $pdf->Cell(35, 8, 'Category', 1, 0, 'C', true);
    $pdf->Cell(20, 8, 'Qty',      1, 0, 'C', true);
    $pdf->Cell(25, 8, 'Price',    1, 0, 'C', true);
    $pdf->Cell(22, 8, 'Subtotal', 1, 1, 'C', true);

    // Table Rows
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial', '', 9);
    $row_num = 1;
    $subtotal = 0;
    foreach ($items_data as $item) {
        $item_sub = $item['price'] * $item['quantity'];
        $subtotal += $item_sub;
        $fill = ($row_num % 2 === 0);
        if ($fill) $pdf->SetFillColor(240, 240, 250);
        $pdf->Cell(8,  7, $row_num,                                    1, 0, 'C', $fill);
        $pdf->Cell(80, 7, $item['name'],                               1, 0, 'L', $fill);
        $pdf->Cell(35, 7, $item['category'],                           1, 0, 'C', $fill);
        $pdf->Cell(20, 7, $item['quantity'],                           1, 0, 'C', $fill);
        $pdf->Cell(25, 7, 'Rs.' . number_format($item['price'], 2),   1, 0, 'R', $fill);
        $pdf->Cell(22, 7, 'Rs.' . number_format($item_sub, 2),        1, 1, 'R', $fill);
        $row_num++;
    }

    // Totals
    $pdf->Ln(2);
    $delivery = $order['total_amount'] - $subtotal;
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(140, 7, '', 0, 0);
    $pdf->Cell(35,  7, 'Subtotal:', 0, 0, 'R');
    $pdf->Cell(15,  7, 'Rs.' . number_format($subtotal, 2), 0, 1, 'R');

    $pdf->Cell(140, 7, '', 0, 0);
    $pdf->Cell(35,  7, 'Delivery:', 0, 0, 'R');
    $pdf->Cell(15,  7, $delivery > 0 ? 'Rs.' . number_format($delivery, 2) : 'FREE', 0, 1, 'R');

    $pdf->SetFont('Arial', 'B', 11);
    $pdf->SetDrawColor(26, 35, 126);
    $pdf->Cell(140, 8, '', 0, 0);
    $pdf->Cell(50,  8, 'TOTAL: Rs.' . number_format($order['total_amount'], 2), 'T', 1, 'R');

    $pdf->Ln(8);
    $pdf->SetFont('Arial', 'I', 9);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->Cell(0, 6, 'Payment Mode: Cash on Pickup at College Store Counter', 0, 1, 'C');

    header('Content-Type: application/pdf');
    $pdf->Output('D', 'Invoice_' . $order_id . '_' . $student['roll_no'] . '.pdf');
    exit();
}

// Fallback: HTML Invoice (print to PDF via browser)
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #<?= $order_id ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; color: #333; }
        .invoice-header { text-align: center; border-bottom: 3px solid #1a237e; padding-bottom: 15px; margin-bottom: 20px; }
        .invoice-header h1 { color: #1a237e; margin: 0; font-size: 24px; }
        .invoice-header p { color: #666; margin: 4px 0; }
        .meta { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .meta div { width: 48%; }
        .meta h4 { color: #1a237e; margin-bottom: 8px; border-bottom: 1px solid #ddd; padding-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background: #1a237e; color: white; padding: 10px; text-align: left; }
        td { padding: 8px 10px; border-bottom: 1px solid #eee; }
        tr:nth-child(even) td { background: #f0f0ff; }
        .totals { float: right; width: 280px; }
        .totals table td { border-bottom: none; }
        .grand-total { font-size: 16px; font-weight: bold; color: #1a237e; border-top: 2px solid #1a237e !important; }
        .footer { text-align: center; margin-top: 40px; color: #888; font-size: 12px; border-top: 1px dashed #ccc; padding-top: 12px; }
        @media print { .print-btn { display: none; } }
        .print-btn { background: #1a237e; color: white; padding: 10px 24px; border: none; border-radius: 8px; cursor: pointer; font-size: 15px; display: block; margin: 0 auto 20px; }
    </style>
</head>
<body>
<button class="print-btn" onclick="window.print()">🖨️ Print / Save as PDF</button>

<div class="invoice-header">
    <h1>🎓 College Store</h1>
    <p>Official Campus Store — Tax Invoice</p>
</div>

<div class="meta">
    <div>
        <h4>Invoice To:</h4>
        <strong><?= htmlspecialchars($student['name']) ?></strong><br>
        Roll No: <?= htmlspecialchars($student['roll_no']) ?><br>
        <?= htmlspecialchars($student['email']) ?><br>
        <?= htmlspecialchars($student['phone'] ?: '') ?>
    </div>
    <div>
        <h4>Invoice Details:</h4>
        Invoice No: <strong>INV-<?= str_pad($order_id, 6, '0', STR_PAD_LEFT) ?></strong><br>
        Date: <?= date('d-m-Y', strtotime($order['created_at'])) ?><br>
        Status: <?= ucfirst($order['status']) ?><br>
        Payment: Cash on Pickup
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Product</th>
            <th>Category</th>
            <th>Qty</th>
            <th>Unit Price</th>
            <th>Subtotal</th>
        </tr>
    </thead>
    <tbody>
    <?php $subtotal = 0; $sn = 1; foreach ($items_data as $item): ?>
        <?php $item_sub = $item['price'] * $item['quantity']; $subtotal += $item_sub; ?>
        <tr>
            <td><?= $sn++ ?></td>
            <td><?= htmlspecialchars($item['name']) ?></td>
            <td><?= htmlspecialchars($item['category']) ?></td>
            <td><?= $item['quantity'] ?></td>
            <td>₹<?= number_format($item['price'], 2) ?></td>
            <td>₹<?= number_format($item_sub, 2) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<div class="totals">
    <table>
        <tr><td>Subtotal</td><td>₹<?= number_format($subtotal, 2) ?></td></tr>
        <?php $delivery = $order['total_amount'] - $subtotal; ?>
        <tr><td>Delivery</td><td><?= $delivery > 0 ? '₹' . number_format($delivery, 2) : 'FREE' ?></td></tr>
        <tr class="grand-total"><td>TOTAL</td><td>₹<?= number_format($order['total_amount'], 2) ?></td></tr>
    </table>
</div>

<div style="clear:both;"></div>
<div class="footer">
    <p>Thank you for shopping at the College Store!<br>
    For any queries, contact the store counter during college hours.</p>
</div>
</body>
</html>
