<?php
session_start();
require_once 'db_config.php';

$error = '';
$success = '';
$validToken = false;
$email = '';

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    // Check if token exists and is not expired
    $stmt = $pdo->prepare("SELECT email FROM password_resets WHERE token = ? AND expires_at > NOW()");
    $stmt->execute([$token]);
    $resetRequest = $stmt->fetch();
    
    if ($resetRequest) {
        $validToken = true;
        $email = $resetRequest['email'];
    } else {
        $error = "Invalid or expired password reset link";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    
    if (empty($password) || strlen($password) < 8) {
        $error = "Password must be at least 8 characters long";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match";
    } else {
        // Hash the new password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Update user password
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->execute([$hashedPassword, $email]);
        
        // Delete the used token
        $pdo->prepare("DELETE FROM password_resets WHERE token = ?")->execute([$token]);
        
        $success = "Password updated successfully. You can now <a href='login.php'>login</a> with your new password.";
        $validToken = false; // Prevent form from showing again
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - FishCare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .password-reset-container {
            max-width: 500px;
            margin: 100px auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="password-reset-container">
            <div class="logo">
                <h2>FishCare</h2>
                <p class="text-muted">Reset Your Password</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>
            
            <?php if ($validToken): ?>
                <form method="POST" action="reset_password.php?token=<?= htmlspecialchars($_GET['token']) ?>">
                    <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="password" name="password" required minlength="8">
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="8">
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">Reset Password</button>
                </form>
            <?php elseif (empty($success)): ?>
                <div class="alert alert-warning">
                    No valid password reset request found. Please <a href="forgot_password.php">request a new reset link</a>.
                </div>
            <?php endif; ?>
            
            <div class="mt-3 text-center">
                <a href="login.php">Back to Login</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Simple password match validation
        document.querySelector('form')?.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match');
            }
        });
    </script>
</body>
</html>