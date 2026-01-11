<?php
// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection and other initializations
require_once 'db_config.php';
?>

<?php
require_once 'init.php';  // This replaces all individual session_start() calls
include 'header.php';
?>

<!-- Page content here -->

<?php include 'footer.php'; ?>