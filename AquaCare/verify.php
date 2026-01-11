<?php
require_once 'db_config.php';

if(isset($_GET['token'])) {
    $token = $_GET['token'];
    
    try {
        $stmt = $pdo->prepare("
            UPDATE Users 
            SET is_active = TRUE, verification_token = NULL 
            WHERE verification_token = ?
        ");
        $stmt->execute([$token]);
        
        if($stmt->rowCount() > 0) {
            header("Location: login.php?verified=1");
        } else {
            header("Location: login.php?error=Invalid token");
        }
        exit();
    } catch(PDOException $e) {
        header("Location: login.php?error=Database error");
        exit();
    }
}
?>