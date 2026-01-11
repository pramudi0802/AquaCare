<?php
require_once 'db_config.php';

// Get fish IDs first
$fishIds = [];
$result = $pdo->query("SELECT fish_id, common_name FROM Fish");
while ($row = $result->fetch()) {
    $fishIds[$row['common_name']] = $row['fish_id'];
}

// Sample compatibility data (adjust with your actual fish names)
$compatibilityPairs = [
    ['Goldfish', 'Betta', 'Not Compatible', 'Different temperature needs'],
    ['Goldfish', 'Guppy', 'Compatible', 'Peaceful community fish'],
    ['Betta', 'Guppy', 'Not Compatible', 'Bettas may attack guppies']
    // Add all your compatibility pairs
];

try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("INSERT INTO compatibility (fish1_id, fish2_id, compatibility_status, notes) VALUES (?, ?, ?, ?)");
    
    foreach ($compatibilityPairs as $pair) {
        $fish1_id = $fishIds[$pair[0]] ?? null;
        $fish2_id = $fishIds[$pair[1]] ?? null;
        
        if ($fish1_id && $fish2_id) {
            $stmt->execute([$fish1_id, $fish2_id, $pair[2], $pair[3]]);
        }
    }
    
    $pdo->commit();
    echo "Compatibility data populated successfully!";
    
} catch (PDOException $e) {
    $pdo->rollBack();
    die("Error: " . $e->getMessage());
}