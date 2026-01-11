<?php
session_start();
require_once 'db_config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address";
    } else {
        try {
            // Check if email exists
            $stmt = $pdo->prepare("SELECT email FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                // Generate secure token
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Store token in database
                $pdo->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$email]);
                $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)")
                   ->execute([$email, $token, $expires]);
                
                // Create reset link
                $resetLink = "https://" . $_SERVER['HTTP_HOST'] . "/reset_password.php?token=" . urlencode($token);
                
                // In production, send email here
                $_SESSION['reset_link_demo'] = $resetLink; // For demo only
                $success = "Password reset link has been sent to your email";
            } else {
                $error = "No account found with that email";
            }
        } catch (PDOException $e) {
            $error = "System error. Please try again later.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Recovery - FishCare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }
        .password-box {
            max-width: 400px;
            margin: 100px auto;
            padding: 30px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .logo-text {
            color: #0d6efd;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .btn-submit {
            background-color: #0d6efd;
            border: none;
            padding: 10px;
        }
        .footer-link {
            color: #6c757d;
            text-decoration: none;
            font-size: 14px;
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>
    <div class="container">
        <div class="password-box text-center">
            <h1 class="logo-text">FishCare</h1>
            <p class="text-muted mb-4">Password Recovery</p>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php if (isset($_SESSION['reset_link_demo'])): ?>
                    <div class="alert alert-info small">
                        <strong>Demo Link:</strong><br>
                        <a href="<?= htmlspecialchars($_SESSION['reset_link_demo']) ?>">
                            <?= htmlspecialchars($_SESSION['reset_link_demo']) ?>
                        </a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <form method="POST" class="mt-4">
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" required 
                           placeholder="Enter your email">
                </div>
                <button type="submit" class="btn btn-submit btn-primary w-100">
                    Send Reset Link
                </button>
            </form>
            
            <div class="mt-3">
                <a href="login.php" class="footer-link">Back to Login</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


    <?php include 'footer.php'; ?>
</body>
</html>