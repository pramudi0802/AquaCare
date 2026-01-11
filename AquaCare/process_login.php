<?php
session_start();
require_once 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Validate inputs
    if (empty($username) || empty($password)) {
        header("Location: login.php?error=Please enter both username and password");
        exit();
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM Users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Update last login time
            $pdo->prepare("UPDATE Users SET last_login = NOW() WHERE user_id = ?")
                ->execute([$user['user_id']]);

            // Set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            
            // Check if admin
            if ($user['role'] === 'admin') {
                $_SESSION['admin_logged_in'] = true;
                header('Location: admin_dashboard.php');
            } else {
                $_SESSION['user_logged_in'] = true;
                header('Location: index.php');
            }
            exit();
        }
        
        // Generic error message to prevent username enumeration
        header("Location: login.php?error=Invalid username or password");
        exit();
        
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        header("Location: login.php?error=System error. Please try again later.");
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}