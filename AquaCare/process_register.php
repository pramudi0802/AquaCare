<?php
session_start();
require_once 'db_config.php';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    try {
        // Validation
        if(empty($username) || empty($email) || empty($password)) {
            throw new Exception("All fields are required");
        }

        // Check for existing user
        $stmt = $pdo->prepare("SELECT user_id FROM Users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if($stmt->rowCount() > 0) {
            throw new Exception("Username or email already exists");
        }

        // Insert user (updated for minimal columns)
        $stmt = $pdo->prepare("
            INSERT INTO Users (username, password, email, role) 
            VALUES (?, ?, ?, 'user')
        ");
        $stmt->execute([
            $username,
            password_hash($password, PASSWORD_DEFAULT),
            $email
        ]);

        // Auto-login
        $_SESSION['user_id'] = $pdo->lastInsertId();
        $_SESSION['username'] = $username;
        $_SESSION['role'] = 'user';
        
        header("Location: index.php?registered=1");
        exit();

    } catch(Exception $e) {
        header("Location: register.php?error=" . urlencode($e->getMessage()));
        exit();
    }
}

// After successful insertion
$token = bin2hex(random_bytes(32));
$pdo->prepare("UPDATE Users SET verification_token = ? WHERE user_id = ?")
    ->execute([$token, $_SESSION['user_id']]);
// Send verification email here

?>