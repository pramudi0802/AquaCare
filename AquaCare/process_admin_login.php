<?php
session_start();
require_once 'db_config.php';

// Debug output
echo "<pre>POST Data: ";
print_r($_POST);
echo "</pre>";

$stmt = $pdo->prepare("SELECT * FROM Users WHERE username = ?");
$stmt->execute([$_POST['username']]);
$user = $stmt->fetch();

echo "<pre>Database Result: ";
print_r($user);
echo "</pre>";

if ($user) {
    echo "Password verify result: " . (password_verify($_POST['password'], $user['password']) ? "MATCH" : "NO MATCH");
    echo "<br>User role: " . $user['role'];
}
die();