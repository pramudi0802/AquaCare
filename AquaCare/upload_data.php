<?php
require_once 'db_config.php';

// Function to ensure fish exists in database
function ensureFishExists($name, $type = null) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT fish_id FROM Fish WHERE common_name = ?");
    $stmt->execute([$name]);
    $fish = $stmt->fetch();
    
    if ($fish) return $fish['fish_id'];
    
    // Insert new fish
    $stmt = $pdo->prepare("INSERT INTO Fish (common_name, type) VALUES (?, ?)");
    $stmt->execute([$name, $type]);
    return $pdo->lastInsertId();
}

// Function to add compatibility
function addCompatibility($fish1_id, $fish2_id, $status, $notes = '') {
    global $pdo;
    
    // Avoid duplicate entries (A+B is same as B+A)
    if ($fish1_id > $fish2_id) {
        $temp = $fish1_id;
        $fish1_id = $fish2_id;
        $fish2_id = $temp;
    }
    
    // Check if relationship exists
    $stmt = $pdo->prepare("SELECT 1 FROM compatibility WHERE fish1_id = ? AND fish2_id = ?");
    $stmt->execute([$fish1_id, $fish2_id]);
    
    if (!$stmt->fetch()) {
        $stmt = $pdo->prepare("
            INSERT INTO compatibility (fish1_id, fish2_id, compatibility_status, notes)
            VALUES (?, ?, ?, ?)
        ");
        return $stmt->execute([$fish1_id, $fish2_id, $status, $notes]);
    }
    return false;
}

// Your compatibility data
$fishCompatibility = [
    // ... [your existing compatibility array]
];

// Upload all data
foreach ($fishCompatibility as $mainFish => $data) {
    $mainFishId = ensureFishExists($mainFish, $data['type']);
    
    // Add compatible relationships
    foreach ($data['compatible'] as $fish) {
        $otherFishId = ensureFishExists($fish, $fishCompatibility[$fish]['type'] ?? 'Freshwater');
        addCompatibility($mainFishId, $otherFishId, 'compatible', 'Automatically added');
    }
    
    // Add incompatible relationships
    foreach ($data['incompatible'] as $fish) {
        $otherFishId = ensureFishExists($fish, $fishCompatibility[$fish]['type'] ?? 'Freshwater');
        addCompatibility($mainFishId, $otherFishId, 'incompatible', 'Automatically added');
    }
}

echo "Data uploaded successfully!";
?>