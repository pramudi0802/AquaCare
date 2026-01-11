<?php 
session_start();
require_once 'db_config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate inputs
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } else {
        try {
            // Check if username or email already exists
            $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            
            if ($stmt->rowCount() > 0) {
                $error = "Username or email already exists";
            } else {
                // Insert new user
                $stmt = $pdo->prepare("
                    INSERT INTO Users (username, password_hash, email, is_admin, created_at) 
                    VALUES (?, ?, ?, 0, NOW())
                ");
                $stmt->execute([
                    $username,
                    password_hash($password, PASSWORD_DEFAULT),
                    $email
                ]);

                // Auto-login the user
                $_SESSION['user_id'] = $pdo->lastInsertId();
                $_SESSION['username'] = $username;
                $_SESSION['user_logged_in'] = true;
                
                header("Location: index.php");
                exit();
            }
        } catch (PDOException $e) {
            error_log("Registration error: " . $e->getMessage());
            $error = "Database error. Please try again later.";
        }
    }
}
?>

<?php include 'header.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0 text-center"><i class="fas fa-user-plus"></i> Create Account</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <form method="post" id="registerForm">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username*</label>
                            <input type="text" class="form-control" id="username" name="username" required 
                                   minlength="4" maxlength="50" pattern="[a-zA-Z0-9_]+"
                                   value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                            <small class="text-muted">Letters, numbers, and underscores only</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email*</label>
                            <input type="email" class="form-control" id="email" name="email" required
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password*</label>
                            <input type="password" class="form-control" id="password" name="password" required
                                   minlength="8" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}">
                            <small class="text-muted">8+ chars with uppercase, lowercase, and number</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password*</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success">Register</button>
                        </div>
                    </form>
                    
                    <div class="mt-3 text-center">
                        <p>Already have an account? <a href="login.php">Login here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Client-side password match validation
document.getElementById('registerForm').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (password !== confirmPassword) {
        e.preventDefault();
        alert('Passwords do not match!');
    }
});
</script>

<?php include 'footer.php'; ?>