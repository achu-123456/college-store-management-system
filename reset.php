<?php
// reset.php
require_once 'includes/db.php';

$msg = '';
$token = $_GET['token'] ?? '';
$valid = false;
$email = '';

if ($token) {
    $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW() ORDER BY id DESC LIMIT 1");
    $stmt->execute([$token]);
    $reset = $stmt->fetch();
    if ($reset) {
        $valid = true;
        $email = $reset['email'];
    } else {
        $msg = "<div class='error'>Invalid or expired token.</div>";
    }
} else {
    $msg = "<div class='error'>No token provided.</div>";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid) {
    $password = $_POST['password'] ?? '';
    if (strlen($password) >= 6) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $upd = $pdo->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
        if ($upd->execute([$hash, $email])) {
            $del = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
            $del->execute([$email]);
            $msg = "<div class='success'>Password updated successfully. <a href='login.php'>Login here</a>.</div>";
            $valid = false;
        } else {
            $msg = "<div class='error'>Failed to update password.</div>";
        }
    } else {
        $msg = "<div class='error'>Password must be at least 6 characters.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>College Store - Reset Password</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-box { background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        .login-box h2 { margin-top: 0; color: #333; text-align: center; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: .5rem; color: #666; }
        .form-group input { width: 100%; padding: .5rem; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .btn { width: 100%; padding: .75rem; background: #007bff; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-size: 1rem; font-weight:bold; transition: background 0.3s; }
        .btn:hover { background: #0056b3; }
        .error { color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 1rem; border-radius: 4px; margin-bottom: 1rem; text-align: center; }
        .success { color: #155724; background-color: #d4edda; border: 1px solid #c3e6cb; padding: 1rem; border-radius: 4px; margin-bottom: 1rem; text-align: center; }
        .links { margin-top: 1rem; text-align: center; font-size: 0.9rem;}
        .links a { color: #007bff; text-decoration: none; }
        .links a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Reset Password</h2>
        <?php if ($msg) echo $msg; ?>
        
        <?php if ($valid): ?>
        <form method="post" action="">
            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="password" required autofocus minlength="6">
            </div>
            <button type="submit" class="btn">Update Password</button>
        </form>
        <?php endif; ?>
        
        <?php if (!$valid && !$msg): ?>
            <div class="links"><a href="login.php">Back to Login</a></div>
        <?php endif; ?>
    </div>
</body>
</html>
