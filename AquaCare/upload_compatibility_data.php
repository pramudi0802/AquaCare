<?php
require_once 'db_config.php';

// Your fish compatibility data
$fishCompatibility = [
    'Goldfish' => [
        'compatible' => ['White Cloud Minnow', 'Dojo Loach', 'Rosy Barb', 'Bristlenose Pleco'],
        'incompatible' => ['Betta', 'Guppy', 'Oscar', 'Tetra', 'Molly'],
        'type' => 'Freshwater'
    ],
    // ... [include all your other fish data from your original array]
];

function ensureFishExists($name) {
    global $pdo;
    
    // Check if fish exists
    $stmt = $pdo->prepare("SELECT fish_id FROM Fish WHERE common_name = ?");
    $stmt->execute([$name]);
    $fish = $stmt->fetch();
    
    if ($fish) {
        return $fish['fish_id'];
    }
    
    // Insert new fish without type
    $stmt = $pdo->prepare("INSERT INTO Fish (common_name) VALUES (?)");
    $stmt->execute([$name]);
    return $pdo->lastInsertId();
}

// Function to add compatibility record
function addCompatibility($fish1_id, $fish2_id, $status, $notes = '') {
    global $pdo;
    
    // Ensure we don't duplicate entries (A+B is same as B+A)
    if ($fish1_id > $fish2_id) {
        $temp = $fish1_id;
        $fish1_id = $fish2_id;
        $fish2_id = $temp;
    }
    
    // Check if relationship already exists
    $stmt = $pdo->prepare("SELECT compatibility_id FROM compatibility WHERE fish1_id = ? AND fish2_id = ?");
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

// Process all compatibility data
foreach ($fishCompatibility as $mainFish => $data) {
    $mainFishId = ensureFishExists($mainFish);
    
    // Add compatible fish relationships
    foreach ($data['compatible'] as $compatibleFish) {
        $otherFishId = ensureFishExists($compatibleFish);
        addCompatibility($mainFishId, $otherFishId, 'compatible', 'Automatically added by system');
    }
    
    // Add incompatible fish relationships
    foreach ($data['incompatible'] as $incompatibleFish) {
        $otherFishId = ensureFishExists($incompatibleFish);
        addCompatibility($mainFishId, $otherFishId, 'incompatible', 'Automatically added by system');
    }
}

echo "Compatibility data uploaded successfully!";
?>