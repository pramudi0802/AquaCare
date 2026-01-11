<?php
require_once 'db_config.php';

try {
    // Get all fish names
    $stmt = $pdo->query("SELECT fish_id, common_name FROM Fish");
    $fishes = $stmt->fetchAll();

    foreach ($fishes as $fish) {
        // Convert name to lowercase and remove spaces for file name
        $filename = strtolower(str_replace(' ', '', $fish['common_name'])) . ".jpg";
        $relativePath = "FISHCARE/fish/" . $filename;

        // Update the image_path
        $update = $pdo->prepare("UPDATE Fish SET image_path = ? WHERE fish_id = ?");
        $update->execute([$relativePath, $fish['fish_id']]);

        echo "Updated: {$fish['common_name']} → $relativePath<br>";
    }

    echo "<br><strong>All image paths updated successfully!</strong>";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
