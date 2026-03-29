<?php
// student/cart_add.php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireStudent();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = (int)$_POST['product_id'];
    $quantity   = max(1, (int)$_POST['quantity']);
    $student_id = $_SESSION['student_id'];

    // Check stock
    $product = $conn->query("SELECT stock FROM products WHERE id = $product_id")->fetch_assoc();
    if (!$product || $product['stock'] < $quantity) {
        header("Location: dashboard.php?error=stock");
        exit();
    }

    // Check if already in cart
    $existing = $conn->prepare("SELECT id, quantity FROM cart WHERE student_id=? AND product_id=?");
    $existing->bind_param("ii", $student_id, $product_id);
    $existing->execute();
    $row = $existing->get_result()->fetch_assoc();
    $existing->close();

    if ($row) {
        $new_qty = $row['quantity'] + $quantity;
        $conn->query("UPDATE cart SET quantity=$new_qty WHERE id={$row['id']}");
    } else {
        $stmt = $conn->prepare("INSERT INTO cart (student_id, product_id, quantity) VALUES (?,?,?)");
        $stmt->bind_param("iii", $student_id, $product_id, $quantity);
        $stmt->execute();
        $stmt->close();
    }
}

header("Location: dashboard.php?added=1");
exit();
?>
