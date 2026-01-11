<?php
session_start();
require_once 'db_config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Function to get all feeding guides with fish names
function getFeedingGuides() {
    global $pdo;
    try {
        $stmt = $pdo->query("
            SELECT fg.*, fs.name as fish_name 
            FROM feeding_guides fg
            JOIN fish_species fs ON fg.fish_species_id = fs.id
            ORDER BY fs.name
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Database error in getFeedingGuides(): ' . $e->getMessage());
        return [];
    }
}

// Function to get all fish species for dropdown
function getFishSpecies() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT id, name FROM fish_species ORDER BY name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Database error in getFishSpecies(): ' . $e->getMessage());
        return [];
    }
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete'])) {
        // Delete feeding guide
        try {
            $stmt = $pdo->prepare("DELETE FROM feeding_guides WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $_SESSION['message'] = 'Feeding guide deleted successfully';
        } catch (PDOException $e) {
            error_log('Database error deleting feeding guide: ' . $e->getMessage());
            $_SESSION['error'] = 'Error deleting feeding guide';
        }
    } else {
        // Add/Edit feeding guide
        $data = [
            'fish_species_id' => $_POST['fish_species_id'],
            'diet_type' => $_POST['diet_type'],
            'feeding_frequency' => $_POST['feeding_frequency'],
            'live_food' => $_POST['live_food'],
            'frozen_food' => $_POST['frozen_food'],
            'commercial_food' => $_POST['commercial_food'],
            'special_notes' => $_POST['special_notes']
        ];

        try {
            if (empty($_POST['id'])) {
                // Insert new
                $stmt = $pdo->prepare("
                    INSERT INTO feeding_guides 
                    (fish_species_id, diet_type, feeding_frequency, live_food, frozen_food, commercial_food, special_notes) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute(array_values($data));
                $_SESSION['message'] = 'Feeding guide added successfully';
            } else {
                // Update existing
                $stmt = $pdo->prepare("
                    UPDATE feeding_guides SET 
                    fish_species_id = ?, 
                    diet_type = ?, 
                    feeding_frequency = ?, 
                    live_food = ?, 
                    frozen_food = ?, 
                    commercial_food = ?, 
                    special_notes = ? 
                    WHERE id = ?
                ");
                $data['id'] = $_POST['id'];
                $stmt->execute(array_values($data));
                $_SESSION['message'] = 'Feeding guide updated successfully';
            }
        } catch (PDOException $e) {
            error_log('Database error saving feeding guide: ' . $e->getMessage());
            $_SESSION['error'] = 'Error saving feeding guide: ' . $e->getMessage();
        }
    }
    
    header('Location: manage_feeding_guides.php');
    exit;
}

// Get current data
$feedingGuides = getFeedingGuides();
$fishSpecies = getFishSpecies();
$editingGuide = null;

// Check if editing
if (isset($_GET['edit'])) {
    foreach ($feedingGuides as $guide) {
        if ($guide['id'] == $_GET['edit']) {
            $editingGuide = $guide;
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Feeding Guides | FishCare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .form-section {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .table-responsive {
            margin-top: 20px;
        }
        .action-btns .btn {
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <main class="container py-4">
        <h1 class="mb-4"><i class="fas fa-utensils"></i> Manage Feeding Guides</h1>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="form-section">
            <h2><?= $editingGuide ? 'Edit' : 'Add' ?> Feeding Guide</h2>
            <form method="post">
                <?php if ($editingGuide): ?>
                    <input type="hidden" name="id" value="<?= $editingGuide['id'] ?>">
                <?php endif; ?>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="fish_species_id" class="form-label">Fish Species</label>
                        <select class="form-select" id="fish_species_id" name="fish_species_id" required>
                            <option value="">-- Select Fish --</option>
                            <?php foreach ($fishSpecies as $fish): ?>
                                <option value="<?= $fish['id'] ?>" 
                                    <?= $editingGuide && $editingGuide['fish_species_id'] == $fish['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($fish['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="diet_type" class="form-label">Diet Type</label>
                        <select class="form-select" id="diet_type" name="diet_type" required>
                            <option value="herbivore" <?= $editingGuide && $editingGuide['diet_type'] == 'herbivore' ? 'selected' : '' ?>>Herbivore</option>
                            <option value="carnivore" <?= $editingGuide && $editingGuide['diet_type'] == 'carnivore' ? 'selected' : '' ?>>Carnivore</option>
                            <option value="omnivore" <?= $editingGuide && $editingGuide['diet_type'] == 'omnivore' ? 'selected' : '' ?>>Omnivore</option>
                        </select>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="feeding_frequency" class="form-label">Feeding Frequency</label>
                    <input type="text" class="form-control" id="feeding_frequency" name="feeding_frequency" 
                           value="<?= $editingGuide ? htmlspecialchars($editingGuide['feeding_frequency']) : '' ?>" required>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="live_food" class="form-label">Live Food Recommendations</label>
                        <textarea class="form-control" id="live_food" name="live_food" rows="3"><?= $editingGuide ? htmlspecialchars($editingGuide['live_food']) : '' ?></textarea>
                    </div>
                    <div class="col-md-4">
                        <label for="frozen_food" class="form-label">Frozen Food Recommendations</label>
                        <textarea class="form-control" id="frozen_food" name="frozen_food" rows="3"><?= $editingGuide ? htmlspecialchars($editingGuide['frozen_food']) : '' ?></textarea>
                    </div>
                    <div class="col-md-4">
                        <label for="commercial_food" class="form-label">Commercial Food Recommendations</label>
                        <textarea class="form-control" id="commercial_food" name="commercial_food" rows="3"><?= $editingGuide ? htmlspecialchars($editingGuide['commercial_food']) : '' ?></textarea>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="special_notes" class="form-label">Special Notes</label>
                    <textarea class="form-control" id="special_notes" name="special_notes" rows="2"><?= $editingGuide ? htmlspecialchars($editingGuide['special_notes']) : '' ?></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> <?= $editingGuide ? 'Update' : 'Save' ?> Guide
                </button>
                
                <?php if ($editingGuide): ?>
                    <a href="manage_feeding_guides.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <div class="table-responsive">
            <h2>Existing Feeding Guides</h2>
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Fish Species</th>
                        <th>Diet Type</th>
                        <th>Feeding Frequency</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($feedingGuides as $guide): ?>
                        <tr>
                            <td><?= htmlspecialchars($guide['fish_name']) ?></td>
                            <td><?= ucfirst($guide['diet_type']) ?></td>
                            <td><?= htmlspecialchars($guide['feeding_frequency']) ?></td>
                            <td class="action-btns">
                                <a href="manage_feeding_guides.php?edit=<?= $guide['id'] ?>" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= $guide['id'] ?>">
                                    <input type="hidden" name="delete" value="1">
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this feeding guide?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>