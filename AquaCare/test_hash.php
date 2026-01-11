<?php
require_once 'db_config.php';
$test = $pdo->query("SELECT password FROM Users WHERE username = 'admin'")->fetchColumn();
echo "Stored hash: $test<br>";
echo "Verify 'admin123': " . (password_verify('admin123', $test) ? "Match" : "No match");
?>