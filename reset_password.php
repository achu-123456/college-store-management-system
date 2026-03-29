<?php
// student/reset_password.php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';

$token   = sanitize($conn, $_GET['token'] ?? '');
$message = '';
$error   = '';
$valid   = false;
$email   = '';

if ($token) {
    // Check token (valid for 1 hour)
    $result = $conn->query("SELECT * FROM password_resets WHERE token='$token' AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
    if ($result->num_rows === 1) {
        $valid = true;
        $email = $result->fetch_assoc()['email'];
    } else {
        $error = "This reset link has expired or is invalid. Please request a new one.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid) {
    $new_pass = $_POST['password'];
    $confirm  = $_POST['confirm_password'];
    $token_post = sanitize($conn, $_POST['token']);

    if ($new_pass !== $confirm) {
        $error = "Passwords do not match.";
    } elseif (strlen($new_pass) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
        $stmt   = $conn->prepare("UPDATE students SET password=? WHERE email=?");
        $stmt->bind_param("ss", $hashed, $email);
        $stmt->execute();
        $stmt->close();

        // Delete used tokens
        $conn->query("DELETE FROM password_resets WHERE email='$email'");

        $message = "Password reset successfully! You can now <a href='login.php'>login</a>.";
        $valid    = false;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password – College Store</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #1a237e, #283593); min-height: 100vh; display: flex; align-items: center; }
        .card { border-radius: 16px; box-shadow: 0 8px 32px rgba(0,0,0,0.3); }
        .card-header { background: linear-gradient(135deg, #1a237e, #3949ab); border-radius: 16px 16px 0 0 !important; }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card">
                <div class="card-header text-white text-center py-3">
                    <h5 class="mb-0">🔒 Reset Password</h5>
                </div>
                <div class="card-body p-4">
                    <?php if ($message): ?>
                        <div class="alert alert-success"><?= $message ?></div>
                    <?php elseif ($error): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>

                    <?php if ($valid): ?>
                    <p class="text-muted small">Resetting password for: <strong><?= htmlspecialchars($email) ?></strong></p>
                    <form method="POST">
                        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="password" class="form-control" required minlength="6">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="confirm_password" class="form-control" required minlength="6">
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Reset Password</button>
                        </div>
                    </form>
                    <?php else: ?>
                        <div class="text-center">
                            <a href="forgot_password.php" class="btn btn-outline-primary">Request New Link</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
