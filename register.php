<?php
// student/register.php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (isStudentLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = sanitize($conn, $_POST['name']);
    $email    = sanitize($conn, $_POST['email']);
    $roll_no  = sanitize($conn, $_POST['roll_no']);
    $phone    = sanitize($conn, $_POST['phone']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];

    if ($password !== $confirm) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        // Check duplicate
        $check = $conn->prepare("SELECT id FROM students WHERE email=? OR roll_no=?");
        $check->bind_param("ss", $email, $roll_no);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "Email or Roll Number already registered.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt   = $conn->prepare("INSERT INTO students (name,email,roll_no,phone,password) VALUES (?,?,?,?,?)");
            $stmt->bind_param("sssss", $name, $email, $roll_no, $phone, $hashed);
            if ($stmt->execute()) {
                $success = "Registration successful! You can now login.";
            } else {
                $error = "Registration failed. Please try again.";
            }
            $stmt->close();
        }
        $check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register – College Store</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #1a237e, #283593); min-height: 100vh; display: flex; align-items: center; padding: 30px 0; }
        .card { border-radius: 16px; box-shadow: 0 8px 32px rgba(0,0,0,0.3); }
        .card-header { background: linear-gradient(135deg, #1a237e, #3949ab); border-radius: 16px 16px 0 0 !important; }
        .btn-primary { background: #1a237e; border: none; }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header text-white text-center py-3">
                    <h4 class="mb-0">🎓 Student Registration</h4>
                </div>
                <div class="card-body p-4">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?= $success ?> <a href="login.php">Login here</a></div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g. Rahul Sharma">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" required placeholder="college email preferred">
                        </div>
                        <div class="row mb-3">
                            <div class="col">
                                <label class="form-label">Roll Number</label>
                                <input type="text" name="roll_no" class="form-control" required placeholder="e.g. 21CS001">
                            </div>
                            <div class="col">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control" placeholder="10-digit number">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required placeholder="Min 6 characters">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary btn-lg">Register</button>
                        </div>
                        <div class="text-center">
                            <a href="login.php" class="text-decoration-none small">Already registered? Login</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
