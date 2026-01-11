<?php
session_start();
require_once 'db_config.php';

// Redirect if not admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit;
}

// Get fish data if editing
$fish = null;
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM Fish WHERE fish_id = ?");
    $stmt->execute([$_GET['id']]);
    $fish = $stmt->fetch();
    
    if (!$fish) {
        $_SESSION['message'] = "Fish not found";
        header('Location: admin_dashboard.php');
        exit;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $commonName = $_POST['common_name'];
    $scientificName = $_POST['scientific_name'];
    $family = $_POST['family'] ?? '';
    $origin = $_POST['origin'] ?? '';
    $adultSize = $_POST['adult_size'] ?? '';
    $lifespan = $_POST['lifespan'] ?? '';
    $minTankSize = $_POST['min_tank_size'] ?? '';
    $tempRange = $_POST['temp_range'] ?? '';
    $phRange = $_POST['ph_range'] ?? '';
    $temperament = $_POST['temperament'] ?? '';
    $careLevel = $_POST['care_level'] ?? '';
    $diet = $_POST['diet'] ?? '';
    $description = $_POST['description'] ?? '';
    $waterType = $_POST['water_type'];
    
    $imagePath = $fish['image_path'] ?? '';
    
    // Handle image upload if new image is provided
    if (isset($_FILES['fish_image']) && $_FILES['fish_image']['error'] === UPLOAD_ERR_OK) {
        // First delete old image if exists
        if ($imagePath) {
            $oldImagePath = $_SERVER['DOCUMENT_ROOT'] . $imagePath;
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }
        }
        
        // Upload new image
        $uploadDir = 'uploads/fish/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileExt = pathinfo($_FILES['fish_image']['name'], PATHINFO_EXTENSION);
        $fileName = uniqid('fish_') . '.' . $fileExt;
        $targetPath = $uploadDir . $fileName;
        
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array(strtolower($fileExt), $allowedTypes)) {
            if (move_uploaded_file($_FILES['fish_image']['tmp_name'], $targetPath)) {
                $imagePath = '/' . $targetPath;
            }
        }
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE Fish SET 
            common_name = ?, 
            scientific_name = ?,
            family = ?,
            origin = ?,
            adult_size = ?,
            lifespan = ?,
            min_tank_size = ?,
            temp_range = ?,
            ph_range = ?,
            temperament = ?,
            care_level = ?,
            diet = ?,
            description = ?,
            water_type = ?,
            image_path = ?
            WHERE fish_id = ?");
        
        $stmt->execute([
            $commonName, $scientificName, $family, $origin, $adultSize, $lifespan,
            $minTankSize, $tempRange, $phRange, $temperament, $careLevel,
            $diet, $description, $waterType, $imagePath, $id
        ]);
        
        $_SESSION['message'] = "Fish updated successfully";
        header('Location: admin_dashboard.php');
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header("Location: edit_fish.php?id=$id");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($fish) ? 'Edit' : 'Add' ?> Fish</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .fish-image-preview {
            max-width: 200px;
            max-height: 150px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h2 class="h4 mb-0">Edit Fish: <?= htmlspecialchars($fish['common_name']) ?></h2>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
                            <?php unset($_SESSION['error']); ?>
                        <?php endif; ?>
                        
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="id" value="<?= $fish['fish_id'] ?>">
                            
                            <div class="mb-3">
                                <?php if ($fish['image_path']): ?>
                                    <img src="<?= $fish['image_path'] ?>" class="fish-image-preview img-thumbnail">
                                <?php endif; ?>
                                <label class="form-label">Fish Image</label>
                                <input type="file" name="fish_image" class="form-control" accept="image/*">
                                <small class="text-muted">Leave blank to keep current image</small>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Common Name*</label>
                                    <input type="text" name="common_name" class="form-control" 
                                           value="<?= htmlspecialchars($fish['common_name']) ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Scientific Name*</label>
                                    <input type="text" name="scientific_name" class="form-control" 
                                           value="<?= htmlspecialchars($fish['scientific_name']) ?>" required>
                                </div>
                            </div>
                            
                            <!-- Add all other fields similar to add_fish.php -->
                            <!-- Make sure to populate them with existing values -->
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Fish
                                </button>
                                <a href="admin_dashboard.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>