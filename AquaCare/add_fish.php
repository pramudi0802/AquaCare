<?php
session_start();
require_once 'db_config.php';

// Redirect if not admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
    $userId = $_SESSION['user_id']; // Get the logged-in user's ID
    
    $imagePath = '';
    
    // Handle image upload
    if (isset($_FILES['fish_image']) && $_FILES['fish_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/fish/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileExt = pathinfo($_FILES['fish_image']['name'], PATHINFO_EXTENSION);
        $fileName = uniqid('fish_') . '.' . $fileExt;
        $targetPath = $uploadDir . $fileName;
        
        // Validate image
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array(strtolower($fileExt), $allowedTypes)) {
            if (move_uploaded_file($_FILES['fish_image']['tmp_name'], $targetPath)) {
                $imagePath = $targetPath; // Store relative path
            }
        }
    }
    
    try {
        // First, check if the procedure exists
        $checkStmt = $pdo->query("SHOW PROCEDURE STATUS LIKE 'AddFishWithLogging'");
        $procedureExists = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($procedureExists) {
            // Call the stored procedure with audit logging
            $stmt = $pdo->prepare("CALL AddFishWithLogging(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $commonName,      // p_common_name
                $scientificName,  // p_scientific_name
                $family,          // p_family
                $origin,          // p_origin
                $adultSize,       // p_adult_size
                $lifespan,        // p_lifespan
                $minTankSize,     // p_min_tank_size
                $tempRange,       // p_temp_range
                $phRange,         // p_ph_range
                $temperament,     // p_temperament
                $careLevel,       // p_care_level
                $diet,            // p_diet
                $description,     // p_description
                $waterType,       // p_water_type
                $imagePath,       // p_image_path
                $userId           // p_user_id
            ]);
            
            $_SESSION['message'] = "Fish added successfully with audit log";
        } else {
            // Fallback: Direct INSERT if procedure doesn't exist
            $stmt = $pdo->prepare("
                INSERT INTO Fish (
                    common_name, scientific_name, family, origin, adult_size, lifespan,
                    min_tank_size, temperature, ph, temperament, care_level,
                    diet, description, water_type, image_path, added_by_user_id
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $commonName, $scientificName, $family, $origin, $adultSize, $lifespan,
                $minTankSize, $tempRange, $phRange, $temperament, $careLevel,
                $diet, $description, $waterType, $imagePath, $userId
            ]);
            
            $fishId = $pdo->lastInsertId();
            
            // Manually add to audit log
            $auditStmt = $pdo->prepare("
                INSERT INTO fish_audit_log (fish_id, action, action_timestamp, user_id)
                VALUES (?, 'INSERT', NOW(), ?)
            ");
            $auditStmt->execute([$fishId, $userId]);
            
            $_SESSION['message'] = "Fish added successfully (using fallback method)";
        }
        
        header('Location: admin_dashboard.php');
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        error_log("Add fish error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Fish</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .back-btn {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container mt-4">
        <!-- Back to Dashboard Button -->
        <div class="back-btn">
            <a href="admin_dashboard.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h2 class="h4 mb-0">Add New Fish</h2>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
                            <?php unset($_SESSION['error']); ?>
                        <?php endif; ?>
                        
                        <form method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Common Name*</label>
                                    <input type="text" name="common_name" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Scientific Name*</label>
                                    <input type="text" name="scientific_name" class="form-control" required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Family</label>
                                    <input type="text" name="family" class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Origin</label>
                                    <input type="text" name="origin" class="form-control">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Adult Size</label>
                                    <input type="text" name="adult_size" class="form-control">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Lifespan</label>
                                    <input type="text" name="lifespan" class="form-control">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Min Tank Size</label>
                                    <input type="text" name="min_tank_size" class="form-control">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Temperature Range</label>
                                    <input type="text" name="temp_range" class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">pH Range</label>
                                    <input type="text" name="ph_range" class="form-control">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Temperament</label>
                                    <select name="temperament" class="form-select">
                                        <option value="">Select</option>
                                        <option value="Peaceful">Peaceful</option>
                                        <option value="Semi-aggressive">Semi-aggressive</option>
                                        <option value="Aggressive">Aggressive</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Care Level</label>
                                    <select name="care_level" class="form-select">
                                        <option value="">Select</option>
                                        <option value="Easy">Easy</option>
                                        <option value="Moderate">Moderate</option>
                                        <option value="Difficult">Difficult</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Diet</label>
                                    <select name="diet" class="form-select">
                                        <option value="">Select</option>
                                        <option value="Herbivore">Herbivore</option>
                                        <option value="Carnivore">Carnivore</option>
                                        <option value="Omnivore">Omnivore</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Water Type*</label>
                                <select name="water_type" class="form-select" required>
                                    <option value="">Select Water Type</option>
                                    <option value="Freshwater">Freshwater</option>
                                    <option value="Saltwater">Saltwater</option>
                                    <option value="Brackish">Brackish</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Fish Image</label>
                                <input type="file" name="fish_image" class="form-control" accept="image/*">
                                <small class="text-muted">Max size: 2MB (JPG, PNG, GIF)</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="4"></textarea>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Save Fish
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

    <?php include 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>