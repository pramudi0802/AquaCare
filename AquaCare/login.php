<?php
session_start();
require_once 'db_config.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
        header('Location: admin_dashboard.php');
    } else {
        header('Location: index.php');
    }
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Initialize variables with empty values
    $username = '';
    $password = '';
    
    // Check if POST values exist before accessing them
    if (isset($_POST['username'])) {
        $username = trim($_POST['username']);
    }
    if (isset($_POST['password'])) {
        $password = $_POST['password'];
    }
    
    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password";
    } else {
        try {
            // Use prepared statement to prevent SQL injection
            $stmt = $pdo->prepare("SELECT * FROM Users WHERE username = ? LIMIT 1");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password_hash'])) { // Changed from 'password' to 'password_hash'
                // Update last login time
                $updateStmt = $pdo->prepare("UPDATE Users SET last_login = NOW() WHERE user_id = ?");
                $updateStmt->execute([$user['user_id']]);
                
                // Set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                
// Check if admin (use is_admin column)
if (!empty($user['is_admin']) && (int)$user['is_admin'] === 1) {
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['user_role'] = 'admin'; // <-- ADD THIS
    header('Location: admin_dashboard.php');
} else {
    $_SESSION['user_logged_in'] = true;
    $_SESSION['user_role'] = 'user'; // <-- ADD THIS
    header('Location: index.php');
}
exit;
            } else {
                $error = "Invalid username or password";
            }
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            $error = "Database error. Please try again later.";
        }
    }
}







?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AquaCare - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .login-container {
            max-width: 400px;
            margin-top: 100px;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .card-header {
            background-color: #0066cc;
            color: white;
            border-radius: 10px 10px 0 0 !important;
            text-align: center;
            padding: 20px;
        }
        .form-control:focus {
            border-color: #0066cc;
            box-shadow: 0 0 0 0.25rem rgba(0, 102, 204, 0.25);
        }
        .btn-primary {
            background-color: #0066cc;
            border-color: #0066cc;
        }
        .admin-login-note {
            margin-top: 20px;
            text-align: center;
            font-size: 0.9rem;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <div class="row justify-content-center">
            <div class="login-container">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-sign-in-alt"></i> Login to AquaCare</h3>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control" id="username" name="username" required
                                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-key"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-sign-in-alt"></i> Login
                                </button>
                            </div>
                        </form>
                        
                        <div class="mt-3 text-center">
                            <p>Don't have an account? <a href="register.php">Register here</a></p>
                            <p><a href="forgot_password.php">Forgot password?</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <?php include 'footer.php'; ?>
</body>
</html>