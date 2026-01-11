<?php
session_start();
require_once 'db_config.php';

// Enhanced security check
if (
    !isset($_SESSION['admin_logged_in']) || 
    $_SESSION['admin_logged_in'] !== true || 
    $_SESSION['user_role'] !== 'admin'
) {
    header('Location: login.php'); // redirect back to main login
    exit;
}

// Handle logout
if (isset($_GET['logout'])) {
    // Destroy all session data
    session_unset();
    session_destroy();
    
    // Redirect to login page
    header('Location: login.php');
    exit;
}

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle fish deletion with CSRF protection
if (isset($_GET['delete']) && isset($_GET['csrf_token'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_GET['csrf_token'])) {
        $_SESSION['error'] = "Invalid CSRF token";
        header('Location: admin_dashboard.php');
        exit;
    }

    $fishId = (int)$_GET['delete'];
    
    try {
        $pdo->beginTransaction();
        
        // Get fish details including image path
        $stmt = $pdo->prepare("SELECT image_path FROM Fish WHERE fish_id = ?");
        $stmt->execute([$fishId]);
        $fish = $stmt->fetch();
        
        // Delete compatibility records
        $stmt = $pdo->prepare("DELETE FROM compatibility WHERE fish1_id = ? OR fish2_id = ?");
        $stmt->execute([$fishId, $fishId]);
        
        // Delete the fish
        $stmt = $pdo->prepare("DELETE FROM Fish WHERE fish_id = ?");
        $stmt->execute([$fishId]);
        
        // Securely delete the image file
        if ($fish && !empty($fish['image_path'])) {
            $imagePath = realpath($_SERVER['DOCUMENT_ROOT'] . parse_url($fish['image_path'], PHP_URL_PATH));
            if ($imagePath && file_exists($imagePath) && is_writable($imagePath)) {
                unlink($imagePath);
            }
        }
        
        $pdo->commit();
        $_SESSION['message'] = "Fish and related records deleted successfully";
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Error deleting fish: " . $e->getMessage();
        error_log("Fish deletion error: " . $e->getMessage());
    }
    
    header('Location: admin_dashboard.php');
    exit;
}

// Handle search functionality
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$waterType = isset($_GET['water_type']) ? $_GET['water_type'] : '';

// Base query with parameters
$query = "SELECT * FROM Fish WHERE 1=1";
$countQuery = "SELECT COUNT(*) FROM Fish WHERE 1=1";
$params = [];
$countParams = [];

if (!empty($search)) {
    $query .= " AND (common_name LIKE ? OR scientific_name LIKE ?)";
    $countQuery .= " AND (common_name LIKE ? OR scientific_name LIKE ?)";
    $searchTerm = "%$search%";
    array_push($params, $searchTerm, $searchTerm);
    array_push($countParams, $searchTerm, $searchTerm);
}

if (!empty($waterType) && in_array($waterType, ['Freshwater', 'Saltwater', 'Brackish'])) {
    $query .= " AND water_type = ?";
    $countQuery .= " AND water_type = ?";
    array_push($params, $waterType);
    array_push($countParams, $waterType);
}

// Pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$query .= " ORDER BY fish_id DESC LIMIT ? OFFSET ?";
array_push($params, $limit, $offset);

// Execute queries
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$fishList = $stmt->fetchAll();

$totalStmt = $pdo->prepare($countQuery);
$totalStmt->execute($countParams);
$totalFish = $totalStmt->fetchColumn();
$totalPages = ceil($totalFish / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FishCare - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .fish-image-thumb {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
        }
        .action-btns {
            white-space: nowrap;
        }
        .search-box {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }
        .admin-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            padding: 10px 0;
        }
        .welcome-text {
            margin-right: 15px;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <!-- Custom Admin Header -->
    <div class="admin-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="welcome-text">Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?>!</span>
                    <span class="badge bg-success">Administrator</span>
                </div>
                <div>
                    <a href="admin_dashboard.php" class="btn btn-outline-primary btn-sm me-2">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    <a href="?logout" class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure you want to logout?')">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php include 'header.php'; ?>

    <div class="loading-overlay">
        <div class="spinner-border text-light" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <div class="container mt-4">
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

        <!-- In the header section of admin_dashboard.php -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Fish Management</h2>
            <div>
                <a href="add_fish.php" class="btn btn-primary me-2">
                    <i class="fas fa-plus"></i> Add New Fish
                </a>
                <a href="manage_foods.php" class="btn btn-info">
                    <i class="fas fa-utensils"></i> Manage Foods
                </a>
            </div>
        </div>
        
        <!-- Search and Filter Section -->
        <div class="search-box mb-4">
            <form method="get" action="admin_dashboard.php" class="row g-3">
                <div class="col-md-6">
                    <input type="text" name="search" class="form-control" placeholder="Search by name..." 
                           value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-4">
                    <select name="water_type" class="form-select">
                        <option value="">All Water Types</option>
                        <option value="Freshwater" <?= $waterType === 'Freshwater' ? 'selected' : '' ?>>Freshwater</option>
                        <option value="Saltwater" <?= $waterType === 'Saltwater' ? 'selected' : '' ?>>Saltwater</option>
                        <option value="Brackish" <?= $waterType === 'Brackish' ? 'selected' : '' ?>>Brackish</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
            </form>
        </div>
        
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Scientific Name</th>
                        <th>Water Type</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($fishList)): ?>
                        <tr>
                            <td colspan="6" class="text-center">No fish found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($fishList as $fish): ?>
                        <tr>
                            <td><?= htmlspecialchars($fish['fish_id']) ?></td>
                            <td>
                                <?php if (!empty($fish['image_path'])): ?>
                                    <img src="<?= htmlspecialchars($fish['image_path']) ?>" 
                                         class="fish-image-thumb" 
                                         alt="<?= htmlspecialchars($fish['common_name']) ?>">
                                <?php else: ?>
                                    <i class="fas fa-fish text-muted"></i>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($fish['common_name']) ?></td>
                            <td><?= htmlspecialchars($fish['scientific_name']) ?></td>
                            <td>
                                <span class="badge bg-<?= 
                                    $fish['water_type'] === 'Freshwater' ? 'primary' : 
                                    ($fish['water_type'] === 'Saltwater' ? 'info' : 'warning')
                                ?>">
                                    <?= htmlspecialchars($fish['water_type']) ?>
                                </span>
                            </td>
                            <td class="action-btns">
                                <a href="edit_fish.php?id=<?= $fish['fish_id'] ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <button class="btn btn-sm btn-danger delete-btn" 
                                        data-id="<?= $fish['fish_id'] ?>"
                                        data-name="<?= htmlspecialchars($fish['common_name']) ?>">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?= 
                            http_build_query(array_merge(
                                $_GET,
                                ['page' => $page - 1]
                            ))
                        ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?<?= 
                            http_build_query(array_merge(
                                $_GET,
                                ['page' => $i]
                            ))
                        ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?= 
                            http_build_query(array_merge(
                                $_GET,
                                ['page' => $page + 1]
                            ))
                        ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>
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
                    <p>Are you sure you want to delete <strong id="fishName"></strong> and all its compatibility records?</p>
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
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const fishId = this.getAttribute('data-id');
                const fishName = this.getAttribute('data-name');
                
                document.getElementById('fishName').textContent = fishName;
                const deleteUrl = `admin_dashboard.php?delete=${fishId}&csrf_token=<?= $_SESSION['csrf_token'] ?>`;
                document.getElementById('confirmDelete').href = deleteUrl;
                
                const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
                modal.show();
            });
        });

        // Show loading overlay during page transitions
        document.querySelectorAll('a').forEach(link => {
            if (link.href && !link.href.includes('#')) {
                link.addEventListener('click', () => {
                    document.querySelector('.loading-overlay').style.display = 'flex';
                });
            }
        });

        // Confirm logout
        document.querySelector('a[href="?logout"]').addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to logout?')) {
                e.preventDefault();
            }
        });
    </script>

    <?php include 'footer.php'; ?>
</body>
</html>