<?php
// recover.php
require_once 'includes/db.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if ($email) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $token = bin2hex(random_bytes(16));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $ins = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
            $ins->execute([$email, $token, $expires]);
            
            $msg = "<div class='success'>Simulation mode: <a href='reset.php?token=$token'>Click here to reset your password.</a></div>";
        } else {
            $msg = "<div class='success'>If your email is registered, you will receive a reset link shortly.</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>College Store - Recover Password</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-box { background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        .login-box h2 { margin-top: 0; color: #333; text-align: center; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: .5rem; color: #666; }
        .form-group input { width: 100%; padding: .5rem; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .btn { width: 100%; padding: .75rem; background: #ffc107; color: #212529; border: none; border-radius: 4px; cursor: pointer; font-size: 1rem; font-weight:bold; transition: background 0.3s;}
        .btn:hover { background: #e0a800; }
        .success { color: #155724; background-color: #d4edda; border: 1px solid #c3e6cb; padding: 1rem; border-radius: 4px; margin-bottom: 1rem; text-align: center; word-break: break-all; }
        .links { margin-top: 1rem; text-align: center; font-size: 0.9rem;}
        .links a { color: #007bff; text-decoration: none; }
        .links a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Recover Password</h2>
        <p style="text-align:center; color:#666; font-size:0.9rem;">Enter your email to receive a reset link.</p>
        <?php echo $msg; ?>
        <form method="post" action="">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" required autofocus>
            </div>
            <button type="submit" class="btn">Send Link</button>
        </form>
        <div class="links">
            <a href="login.php">Back to Login</a>
        </div>
    </div>
</body>
</html>
