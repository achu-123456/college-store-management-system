<?php
// student/forgot_password.php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';

$message = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($conn, $_POST['email']);

    $result = $conn->query("SELECT id FROM students WHERE email='$email'");
    if ($result->num_rows === 1) {
        $token = generateToken();

        // Delete old tokens for this email
        $conn->query("DELETE FROM password_resets WHERE email='$email'");

        // Store new token
        $stmt = $conn->prepare("INSERT INTO password_resets (email, token) VALUES (?,?)");
        $stmt->bind_param("ss", $email, $token);
        $stmt->execute();
        $stmt->close();

        // In production, send this link via email (e.g. PHPMailer)
        // For demo: show the reset link directly
        $reset_link = "http://localhost/college_store/student/reset_password.php?token=$token";

        $message = "Password reset link generated! 
        <br><br>
        <strong>Reset Link (for demo – in production this is emailed):</strong><br>
        <a href='$reset_link'>$reset_link</a>";
    } else {
        $error = "No account found with that email address.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password – College Store</title>
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
                    <h5 class="mb-0">🔑 Forgot Password</h5>
                </div>
                <div class="card-body p-4">
                    <?php if ($message): ?>
                        <div class="alert alert-success"><?= $message ?></div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>
                    <?php if (!$message): ?>
                    <p class="text-muted">Enter your registered email to receive a password reset link.</p>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary">Send Reset Link</button>
                        </div>
                    </form>
                    <?php endif; ?>
                    <div class="text-center">
                        <a href="login.php" class="text-decoration-none small">Back to Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
