<?php
session_start();
require_once 'db_config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $type = $_POST['type'];
    $description = trim($_POST['description']);
    $suitable_for = trim($_POST['suitable_for']);

    try {
        $stmt = $pdo->prepare("
            INSERT INTO food_types 
            (name, type, description, suitable_for) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$name, $type, $description, $suitable_for]);
        
        $_SESSION['message'] = 'Food type added successfully!';
        header('Location: add_food.php');
        exit;
    } catch (PDOException $e) {
        error_log('Database error in add_food.php: ' . $e->getMessage());
        $_SESSION['error'] = 'Error adding food type: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Food Type | FishCare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .form-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 30px;
            background-color: #f8f9fa;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .page-header {
            border-bottom: 2px solid #dee2e6;
            padding-bottom: 15px;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <main class="container py-5">
        <div class="form-container">
            <div class="page-header">
                <h1><i class="fas fa-plus-circle"></i> Add New Food Type</h1>
                <p class="lead">Add a new food type to the FishCare database</p>
            </div>

            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= $_SESSION['message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['message']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= $_SESSION['error'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <form method="post">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label">Food Name*</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="col-md-6">
                        <label for="type" class="form-label">Food Type*</label>
                        <select class="form-select" id="type" name="type" required>
                            <option value="">-- Select Type --</option>
                            <option value="live">Live Food</option>
                            <option value="frozen">Frozen Food</option>
                            <option value="commercial">Commercial Food</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description*</label>
                    <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                </div>

                <div class="mb-4">
                    <label for="suitable_for" class="form-label">Suitable For*</label>
                    <textarea class="form-control" id="suitable_for" name="suitable_for" rows="2" required></textarea>
                    <div class="form-text">List fish types or species this food is suitable for</div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save"></i> Save Food Type
                    </button>
                    <a href="admin_dashboard.php" class="btn btn-secondary btn-lg">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </form>
        </div>
    </main>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            
            form.addEventListener('submit', function(e) {
                let valid = true;
                
                // Validate all required fields
                document.querySelectorAll('[required]').forEach(field => {
                    if (!field.value.trim()) {
                        field.classList.add('is-invalid');
                        valid = false;
                    } else {
                        field.classList.remove('is-invalid');
                    }
                });
                
                if (!valid) {
                    e.preventDefault();
                    alert('Please fill in all required fields');
                }
            });
            
            // Clear validation on input
            document.querySelectorAll('input, textarea, select').forEach(field => {
                field.addEventListener('input', function() {
                    if (this.value.trim()) {
                        this.classList.remove('is-invalid');
                    }
                });
            });
        });
    </script>
</body>
</html>