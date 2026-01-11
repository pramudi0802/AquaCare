<?php
// manage_foods.php
session_start();
require_once 'db_config.php';

// Admin authentication check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true || $_SESSION['user_role'] !== 'admin') {
    header('Location: admin_login.php');
    exit;
}

// Handle food deletion
if (isset($_GET['delete_food'])) {
    $foodId = (int)$_GET['delete_food'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM food_types WHERE id = ?");
        $stmt->execute([$foodId]);
        $_SESSION['message'] = "Food item deleted successfully";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error deleting food item: " . $e->getMessage();
    }
    
    header('Location: manage_foods.php');
    exit;
}

// Handle food addition/editing form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $type = $_POST['type'];
    $description = trim($_POST['description']);
    $suitableFor = trim($_POST['suitable_for']);
    $foodId = isset($_POST['food_id']) ? (int)$_POST['food_id'] : 0;
    
    if (empty($name) || empty($description)) {
        $_SESSION['error'] = "Please fill in all required fields";
    } else {
        try {
            if ($foodId > 0) {
                // Update existing food
                $stmt = $pdo->prepare("UPDATE food_types SET name = ?, type = ?, description = ?, suitable_for = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$name, $type, $description, $suitableFor, $foodId]);
                $_SESSION['message'] = "Food item updated successfully";
            } else {
                // Try to use the procedure first
                try {
                    $stmt = $pdo->prepare("CALL AddFoodType(?, ?, ?, ?)");
                    $stmt->execute([$name, $type, $description, $suitableFor]);
                    $_SESSION['message'] = "Food item added successfully using procedure";
                } catch (PDOException $e) {
                    // If procedure fails, fall back to direct INSERT
                    if (strpos($e->getMessage(), 'already exists') !== false || strpos($e->getMessage(), 'PROCEDURE') !== false) {
                        $stmt = $pdo->prepare("INSERT INTO food_types (name, type, description, suitable_for) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$name, $type, $description, $suitableFor]);
                        $_SESSION['message'] = "Food item added successfully (fallback method)";
                    } else {
                        // Re-throw other errors
                        throw $e;
                    }
                }
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = "Database error: " . $e->getMessage();
            error_log("Food management error: " . $e->getMessage());
        }
    }
    
    header('Location: manage_foods.php');
    exit;
}

// Get all food items
$foods = [];
try {
    $stmt = $pdo->query("SELECT * FROM food_types ORDER BY type, name");
    $foods = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = "Error fetching food items: " . $e->getMessage();
}

// Get food item for editing
$editFood = null;
if (isset($_GET['edit_food'])) {
    $foodId = (int)$_GET['edit_food'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM food_types WHERE id = ?");
        $stmt->execute([$foodId]);
        $editFood = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error fetching food item: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Foods - AquaCare Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .food-type-badge {
            font-size: 0.8rem;
            padding: 4px 8px;
            border-radius: 12px;
        }
        .type-live { background-color: #dc3545; color: white; }
        .type-frozen { background-color: #0dcaf0; color: black; }
        .type-commercial { background-color: #198754; color: white; }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Manage Food Types</h2>
            <a href="admin_dashboard.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <!-- Flash Messages -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= htmlspecialchars($_SESSION['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="row">
            <!-- Add/Edit Food Form -->
            <div class="col-md-5 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><?= $editFood ? 'Edit Food Item' : 'Add New Food Item' ?></h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <?php if ($editFood): ?>
                                <input type="hidden" name="food_id" value="<?= $editFood['id'] ?>">
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <label for="name" class="form-label">Food Name *</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?= $editFood ? htmlspecialchars($editFood['name']) : '' ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="type" class="form-label">Food Type *</label>
                                <select class="form-select" id="type" name="type" required>
                                    <option value="live" <?= ($editFood && $editFood['type'] === 'live') ? 'selected' : '' ?>>Live Food</option>
                                    <option value="frozen" <?= ($editFood && $editFood['type'] === 'frozen') ? 'selected' : '' ?>>Frozen Food</option>
                                    <option value="commercial" <?= ($editFood && $editFood['type'] === 'commercial') ? 'selected' : '' ?>>Commercial Food</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description *</label>
                                <textarea class="form-control" id="description" name="description" rows="3" required><?= $editFood ? htmlspecialchars($editFood['description']) : '' ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="suitable_for" class="form-label">Suitable For</label>
                                <textarea class="form-control" id="suitable_for" name="suitable_for" rows="2"><?= $editFood ? htmlspecialchars($editFood['suitable_for']) : '' ?></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?= $editFood ? 'Update Food Item' : 'Add Food Item' ?>
                            </button>
                            
                            <?php if ($editFood): ?>
                                <a href="manage_foods.php" class="btn btn-secondary">Cancel</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Food List -->
            <div class="col-md-7">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Food Items</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($foods)): ?>
                            <p class="text-muted">No food items found.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Type</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($foods as $food): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($food['name']) ?></td>
                                            <td>
                                                <span class="food-type-badge type-<?= $food['type'] ?>">
                                                    <?= ucfirst($food['type']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="manage_foods.php?edit_food=<?= $food['id'] ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <button class="btn btn-sm btn-danger" data-bs-toggle="modal" 
                                                        data-bs-target="#deleteModal" data-id="<?= $food['id'] ?>" 
                                                        data-name="<?= htmlspecialchars($food['name']) ?>">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <strong id="foodName"></strong>?</p>
                    <p class="text-danger"><small>This action cannot be undone.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="#" class="btn btn-danger" id="confirmDelete">Delete</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Delete confirmation modal
        const deleteModal = document.getElementById('deleteModal');
        deleteModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const foodId = button.getAttribute('data-id');
            const foodName = button.getAttribute('data-name');
            
            document.getElementById('foodName').textContent = foodName;
            document.getElementById('confirmDelete').href = `manage_foods.php?delete_food=${foodId}`;
        });
    </script>

    <?php include 'footer.php'; ?>
</body>
</html>