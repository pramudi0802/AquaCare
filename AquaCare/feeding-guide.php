<?php
session_start();
require_once 'db_config.php';

// Function to get all fish species
function getFishSpecies() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT id, name, scientific_name FROM fish_species ORDER BY name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('[' . date('Y-m-d H:i:s') . '] Database error in getFishSpecies(): ' . $e->getMessage() . PHP_EOL, 3, __DIR__ . '/db_errors.log');
        return [];
    }
}

// Function to get feeding guide for a specific fish
function getFeedingGuide($fishId) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT fg.*, fs.name as fish_name, fs.scientific_name 
                              FROM feeding_guides fg
                              JOIN fish_species fs ON fg.fish_species_id = fs.id
                              WHERE fg.fish_species_id = ?");
        $stmt->execute([$fishId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('[' . date('Y-m-d H:i:s') . '] Database error in getFeedingGuide(): ' . $e->getMessage() . PHP_EOL, 3, __DIR__ . '/db_errors.log');
        return null;
    }
}

// Function to get all food types
function getFoodTypes($type = null) {
    global $pdo;
    try {
        $sql = "SELECT * FROM food_types";
        if ($type) {
            $sql .= " WHERE type = ? ORDER BY name";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$type]);
        } else {
            $sql .= " ORDER BY name";
            $stmt = $pdo->query($sql);
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('[' . date('Y-m-d H:i:s') . '] Database error in getFoodTypes(): ' . $e->getMessage() . PHP_EOL, 3, __DIR__ . '/db_errors.log');
        return [];
    }
}

// Get data for the page
$fishSpecies = getFishSpecies();
$selectedFishId = isset($_GET['fish_id']) ? (int)$_GET['fish_id'] : null;
$feedingGuide = $selectedFishId ? getFeedingGuide($selectedFishId) : null;

// Get food types by category
$liveFoods = getFoodTypes('live');
$frozenFoods = getFoodTypes('frozen');
$commercialFoods = getFoodTypes('commercial');

// Set page title
$pageTitle = "Feeding Guide" . ($feedingGuide ? " - " . htmlspecialchars($feedingGuide['fish_name']) : "");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> | AquaCare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .food-section {
            margin: 20px 0;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .food-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 15px;
        }
        .food-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border: 1px solid #eee;
        }
        .food-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .fish-selector {
            margin: 30px 0;
            padding: 25px;
            background-color: #e9f7fe;
            border-radius: 10px;
            border-left: 5px solid #17a2b8;
        }
        .guide-display {
            margin: 30px 0;
            padding: 30px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            border-top: 4px solid #28a745;
        }
        .diet-badge {
            font-size: 0.9rem;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 500;
        }
        .diet-herbivore {
            background-color: #28a745;
            color: white;
        }
        .diet-carnivore {
            background-color: #dc3545;
            color: white;
        }
        .diet-omnivore {
            background-color: #ffc107;
            color: #212529;
        }
        .scientific-name {
            font-style: italic;
            color: #6c757d;
            font-size: 0.9em;
        }
        .food-icon {
            font-size: 1.2em;
            margin-right: 8px;
            color: #17a2b8;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <main class="container my-5">
        <div class="row mb-4">
            <div class="col">
                <h1 class="display-4"><i class="fas fa-utensils text-primary"></i> Fish Feeding Guide</h1>
                <p class="lead text-muted">Comprehensive nutritional information for your aquarium fish</p>
            </div>
        </div>
        
        <div class="fish-selector">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2><i class="fas fa-fish text-info"></i> Select Fish Species</h2>
                    <form method="get" class="row g-3">
                        <div class="col-md-9">
                            <select name="fish_id" class="form-select form-select-lg" required>
                                <option value="" disabled selected>-- Select a Fish Species --</option>
                                <?php foreach ($fishSpecies as $fish): ?>
                                    <option value="<?= $fish['id'] ?>" <?= $selectedFishId == $fish['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($fish['name']) ?>
                                        <?php if (!empty($fish['scientific_name'])): ?>
                                            <span class="scientific-name">(<?= htmlspecialchars($fish['scientific_name']) ?>)</span>
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="fas fa-search"></i> View Guide
                            </button>
                        </div>
                    </form>
                </div>
                <div class="col-md-4 mt-3 mt-md-0">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Select a fish species to view its specific feeding requirements.
                    </div>
                </div>
            </div>
        </div>

        <?php if ($feedingGuide): ?>
            <div class="guide-display">
                <div class="row mb-4">
                    <div class="col">
                        <h2 class="mb-3">
                            <i class="fas fa-utensils text-primary"></i> <?= htmlspecialchars($feedingGuide['fish_name']) ?>
                            <?php if (!empty($feedingGuide['scientific_name'])): ?>
                                <span class="scientific-name">(<?= htmlspecialchars($feedingGuide['scientific_name']) ?>)</span>
                            <?php endif; ?>
                            <span class="diet-badge diet-<?= $feedingGuide['diet_type'] ?> ms-2">
                                <?= ucfirst($feedingGuide['diet_type']) ?>
                            </span>
                        </h2>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-lg-6">
                        <div class="card mb-4 shadow-sm">
                            <div class="card-header bg-primary text-white">
                                <i class="fas fa-info-circle"></i> Feeding Information
                            </div>
                            <div class="card-body">
                                <div class="mb-4">
                                    <h5><i class="fas fa-clock text-info"></i> Feeding Frequency</h5>
                                    <p class="ps-4"><?= htmlspecialchars($feedingGuide['feeding_frequency']) ?></p>
                                </div>
                                
                                <?php if ($feedingGuide['special_notes']): ?>
                                    <div class="mb-4">
                                        <h5><i class="fas fa-exclamation-triangle text-warning"></i> Special Notes</h5>
                                        <div class="alert alert-warning mt-2">
                                            <?= nl2br(htmlspecialchars($feedingGuide['special_notes'])) ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-6">
                        <div class="card shadow-sm">
                            <div class="card-header bg-primary text-white">
                                <i class="fas fa-utensils"></i> Recommended Foods
                            </div>
                            <div class="card-body">
                                <div class="mb-4">
                                    <h5><i class="fas fa-bolt food-icon"></i> Live Foods</h5>
                                    <div class="ps-4">
                                        <?= $feedingGuide['live_food'] ? nl2br(htmlspecialchars($feedingGuide['live_food'])) : '<p class="text-muted">No specific recommendations</p>' ?>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <h5><i class="fas fa-snowflake food-icon"></i> Frozen Foods</h5>
                                    <div class="ps-4">
                                        <?= $feedingGuide['frozen_food'] ? nl2br(htmlspecialchars($feedingGuide['frozen_food'])) : '<p class="text-muted">No specific recommendations</p>' ?>
                                    </div>
                                </div>
                                
                                <div>
                                    <h5><i class="fas fa-shopping-bag food-icon"></i> Commercial Foods</h5>
                                    <div class="ps-4">
                                        <?= $feedingGuide['commercial_food'] ? nl2br(htmlspecialchars($feedingGuide['commercial_food'])) : '<p class="text-muted">No specific recommendations</p>' ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php elseif ($selectedFishId): ?>
            <div class="alert alert-warning shadow-sm">
                <i class="fas fa-exclamation-triangle"></i> No feeding guide available for the selected fish. Please check back later or contact support.
            </div>
        <?php endif; ?>

        <div class="food-section mt-5">
            <div class="row mb-4">
                <div class="col">
                    <h2 class="mb-3"><i class="fas fa-list text-primary"></i> Fish Food Reference</h2>
                    <p class="text-muted">Browse our comprehensive list of fish food types and their benefits.</p>
                
                
                </div>
            </div>
            
            <div class="mb-5">
                <h3 class="mb-4"><i class="fas fa-bolt text-warning"></i> Live Foods</h3>
                <?php if ($liveFoods): ?>
                    <div class="food-grid">
                        <?php foreach ($liveFoods as $food): ?>
                            <div class="food-card">
                                <h4 class="mb-3"><?= htmlspecialchars($food['name']) ?></h4>
                                <p class="text-muted mb-2"><small><i class="fas fa-tag"></i> <?= ucfirst($food['type']) ?> food</small></p>
                                <p class="mb-3"><?= htmlspecialchars($food['description']) ?></p>
                                <p class="text-muted mb-0"><small><strong><i class="fas fa-check-circle"></i> Suitable for:</strong> <?= htmlspecialchars($food['suitable_for']) ?></small></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info shadow-sm">
                        <i class="fas fa-info-circle"></i> No live foods found in our database.
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="mb-5">
                <h3 class="mb-4"><i class="fas fa-snowflake text-info"></i> Frozen Foods</h3>
                <?php if ($frozenFoods): ?>
                    <div class="food-grid">
                        <?php foreach ($frozenFoods as $food): ?>
                            <div class="food-card">
                                <h4 class="mb-3"><?= htmlspecialchars($food['name']) ?></h4>
                                <p class="text-muted mb-2"><small><i class="fas fa-tag"></i> <?= ucfirst($food['type']) ?> food</small></p>
                                <p class="mb-3"><?= htmlspecialchars($food['description']) ?></p>
                                <p class="text-muted mb-0"><small><strong><i class="fas fa-check-circle"></i> Suitable for:</strong> <?= htmlspecialchars($food['suitable_for']) ?></small></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info shadow-sm">
                        <i class="fas fa-info-circle"></i> No frozen foods found in our database.
                    </div>
                <?php endif; ?>
            </div>
            
            <div>
                <h3 class="mb-4"><i class="fas fa-shopping-bag text-success"></i> Commercial Foods</h3>
                <?php if ($commercialFoods): ?>
                    <div class="food-grid">
                        <?php foreach ($commercialFoods as $food): ?>
                            <div class="food-card">
                                <h4 class="mb-3"><?= htmlspecialchars($food['name']) ?></h4>
                                <p class="text-muted mb-2"><small><i class="fas fa-tag"></i> <?= ucfirst($food['type']) ?> food</small></p>
                                <p class="mb-3"><?= htmlspecialchars($food['description']) ?></p>
                                <p class="text-muted mb-0"><small><strong><i class="fas fa-check-circle"></i> Suitable for:</strong> <?= htmlspecialchars($food['suitable_for']) ?></small></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info shadow-sm">
                        <i class="fas fa-info-circle"></i> No commercial foods found in our database.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Enhanced JavaScript for better user experience
        document.addEventListener('DOMContentLoaded', function() {
            // Smooth scroll to guide when a fish is selected
            if (window.location.search.includes('fish_id=')) {
                const guideSection = document.querySelector('.guide-display');
                if (guideSection) {
                    setTimeout(() => {
                        guideSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }, 300);
                }
            }
            
            // Add animation to food cards
            const animateOnScroll = (entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = 1;
                        entry.target.style.transform = 'translateY(0)';
                        observer.unobserve(entry.target);
                    }
                });
            };
            
            const observer = new IntersectionObserver(animateOnScroll, {
                threshold: 0.1
            });
            
            document.querySelectorAll('.food-card').forEach(card => {
                card.style.opacity = 0;
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'all 0.5s ease-out';
                observer.observe(card);
            });
        });
    </script>
</body>
</html>