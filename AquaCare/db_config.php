<?php
// db_config.php
// Only start a session if one isn't already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database configuration
$host = 'localhost';
$db   = 'AquaCare';  // Make sure this matches your actual database name
$user = 'root';
$pass = '';          // Empty password for root - not recommended for production
$charset = 'utf8mb4';

// Set DSN (Data Source Name)
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// PDO options
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::ATTR_PERSISTENT         => false,
];

// Create PDO instance
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    $pdo->exec("SET time_zone = '+00:00'");
} catch (PDOException $e) {
    error_log('Database connection error: ' . $e->getMessage());
    header('HTTP/1.1 503 Service Unavailable');
    die('Service Temporarily Unavailable');
} ('<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Service Unavailable | FishCare</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body { background-color: #f8f9fa; }
            .error-container { margin-top: 100px; text-align: center; }
            .error-icon { font-size: 5rem; color: #dc3545; }
        </style>
    </head>
    <body>
        <div class="container error-container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <i class="fas fa-exclamation-triangle error-icon"></i>
                    <h1 class="mt-4">Service Temporarily Unavailable</h1>
                    <p class="lead">We apologize for the inconvenience. Our team has been notified and is working to resolve the issue.</p>
                    <p>Please try again later.</p>
                    <a href="index.php" class="btn btn-primary mt-3">
                        <i class="fas fa-home"></i> Return Home
                    </a>
                </div>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>');

?>