<?php
// header.php
require_once 'db_config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | ' : '' ?>FishCare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
        }
        .nav-link {
            font-size: 1.1rem;
            padding: 0.5rem 1rem;
            transition: all 0.2s;
        }
        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.15);
        }
        .active {
            font-weight: bold;
            color: #fff !important;
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 5px;
        }
        .dropdown-menu {
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-fish"></i> AquaCare
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>" href="index.php"><i class="fas fa-home"></i> Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'fish_encyclopedia.php' ? 'active' : '' ?>" href="fish_encyclopedia.php"><i class="fas fa-book"></i> Fish Encyclopedia</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'compatibility.php' ? 'active' : '' ?>" href="compatibility.php"><i class="fas fa-heart"></i> Compatibility Checker</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'diagnosis.php' ? 'active' : '' ?>" href="diagnosis.php"><i class="fas fa-heartbeat"></i> Disease Diagnosis</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'feeding-guide.php' ? 'active' : '' ?>" href="feeding-guide.php"><i class="fas fa-utensils"></i> Feeding Guide</a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-user-shield"></i> Admin
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                                    <li><a class="dropdown-item" href="add_fish.php"><i class="fas fa-plus"></i> Add Fish</a></li>
                                    <li><a class="dropdown-item" href="add_food.php"><i class="fas fa-plus-circle"></i> Add Food</a></li>
                                    <li><a class="dropdown-item" href="manage_feeding_guides.php"><i class="fas fa-edit"></i> Manage Feeding Guides</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                                </ul>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <span class="nav-link">
                                    <i class="fas fa-user"></i> <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?>
                                </span>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                            </li>
                        <?php endif; ?>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php"><i class="fas fa-user-plus"></i> Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container my-4">