<?php
session_start();
require_once 'db_config.php';


// Set page title
$pageTitle = "Fish Encyclopedia | FishCare";

// Get all fish from database
try {
    $stmt = $pdo->query("SELECT * FROM Fish ORDER BY common_name");
    $allFish = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Filter by water type if requested
$waterType = $_GET['water_type'] ?? '';
if ($waterType && in_array($waterType, ['Freshwater', 'Saltwater', 'Brackish'])) {
    $stmt = $pdo->prepare("SELECT * FROM Fish WHERE water_type = ? ORDER BY common_name");
    $stmt->execute([$waterType]);
    $allFish = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .fish-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
        }
        .fish-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .water-type-badge {
            position: absolute;
            top: 10px;
            right: 10px;
        }
        .fish-image {
            height: 200px;
            object-fit: cover;
            width: 100%;
        }
        .filter-buttons .btn {
            margin-right: 5px;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container py-5">
        <h1 class="text-center mb-4">Fish Encyclopedia</h1>
        
        <!-- Filter Buttons -->
        <div class="filter-buttons text-center mb-4">
            <a href="encyclopedia.php" class="btn btn-outline-primary <?php echo empty($waterType) ? 'active' : ''; ?>">
                All Fish
            </a>
            <a href="encyclopedia.php?water_type=Freshwater" class="btn btn-outline-primary <?php echo $waterType === 'Freshwater' ? 'active' : ''; ?>">
                <i class="fas fa-tint"></i> Freshwater
            </a>
            <a href="encyclopedia.php?water_type=Saltwater" class="btn btn-outline-primary <?php echo $waterType === 'Saltwater' ? 'active' : ''; ?>">
                <i class="fas fa-umbrella-beach"></i> Saltwater
            </a>
            <a href="encyclopedia.php?water_type=Brackish" class="btn btn-outline-primary <?php echo $waterType === 'Brackish' ? 'active' : ''; ?>">
                <i class="fas fa-water"></i> Brackish
            </a>
        </div>



        <!-- Fish Cards -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php foreach ($allFish as $fish): ?>
                <div class="col">
                    <div class="card fish-card">
                        <?php if ($fish['image_path']): ?>
                            <img src="<?php echo $fish['image_path']; ?>" class="card-img-top fish-image" alt="<?php echo htmlspecialchars($fish['common_name']); ?>">
                        <?php else: ?>
                            <div class="card-img-top fish-image bg-light d-flex align-items-center justify-content-center">
                                <i class="fas fa-fish fa-5x text-muted"></i>
                            </div>
                        <?php endif; ?>
                        
                        <span class="water-type-badge badge 
                            <?php echo $fish['water_type'] === 'Freshwater' ? 'bg-primary' : 
                                  ($fish['water_type'] === 'Saltwater' ? 'bg-info' : 'bg-warning'); ?>">
                            <?php echo $fish['water_type']; ?>
                        </span>
                        
                        <div class="card-body">
                            <h2 class="card-title h5"><?php echo htmlspecialchars($fish['common_name']); ?></h2>
                            <p class="card-text text-muted">
                                <em><?php echo htmlspecialchars($fish['scientific_name']); ?></em>
                            </p>
                            <ul class="list-unstyled">
                                <li><strong>Temperament:</strong> <?php echo htmlspecialchars($fish['temperament']); ?></li>
                                <li><strong>Size:</strong> <?php echo htmlspecialchars($fish['adult_size']); ?></li>
                                <li><strong>Care Level:</strong> <?php echo htmlspecialchars($fish['care_level']); ?></li>
                            </ul>
                            <a href="view_fish.php?id=<?php echo $fish['fish_id']; ?>" class="btn btn-sm btn-outline-primary">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (empty($allFish)): ?>
            <div class="alert alert-info text-center mt-4">
                No fish found matching your criteria.
            </div>
        <?php endif; ?>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php include 'footer.php'; ?>

</body>
</html>