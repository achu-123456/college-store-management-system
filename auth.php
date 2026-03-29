<?php
// includes/auth.php — Session & Auth helpers

function isStudentLoggedIn() {
    return isset($_SESSION['student_id']);
}

function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

function requireStudent() {
    if (!isStudentLoggedIn()) {
        header("Location: /student/login.php");
        exit();
    }
}

function requireAdmin() {
    if (!isAdminLoggedIn()) {
        header("Location: /admin/login.php");
        exit();
    }
}

function sanitize($conn, $data) {
    return $conn->real_escape_string(htmlspecialchars(trim($data)));
}

function generateToken() {
    return bin2hex(random_bytes(32));
}
?>
