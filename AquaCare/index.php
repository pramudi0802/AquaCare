<?php
session_start();
require_once 'db_config.php';

// Set default page title
$pageTitle = "AquaCare - Smart Aquarium Management";

// Check if user is logged in
$loggedIn = isset($_SESSION['user_id']);
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
        .hero-section {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('images/aquarium-bg.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
            text-align: center;
        }
        .feature-icon {
            font-size: 2.5rem;
            color: #17a2b8;
            margin-bottom: 1rem;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            transition: transform 0.3s ease;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <h1 class="display-4">Welcome to AquaCare</h1>
            <p class="lead">Your comprehensive aquarium management system</p>
            <?php if(!$loggedIn): ?>
                <a href="login.php" class="btn btn-primary btn-lg me-2">Login</a>
                <a href="register.php" class="btn btn-outline-light btn-lg">Register</a>
            <?php else: ?>
                <a href="fish_encyclopedia.php" class="btn btn-success btn-lg me-2">Explore Fish</a>
                <a href="compatibility.php" class="btn btn-info btn-lg">Check Compatibility</a>
            <?php endif; ?>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-5">Key Features</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card h-100 card-hover">
                        <div class="card-body text-center">
                            <i class="fas fa-fish feature-icon"></i>
                            <h3 class="h4">Fish Encyclopedia</h3>
                            <p>Comprehensive database of freshwater and saltwater fish species with detailed care information.</p>
                            <a href="fish_encyclopedia.php" class="btn btn-outline-primary">Explore</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="card h-100 card-hover">
                        <div class="card-body text-center">
                            <i class="fas fa-heart feature-icon"></i>
                            <h3 class="h4">Compatibility Checker</h3>
                            <p>Determine which fish can safely coexist in your aquarium with our intelligent compatibility tool.</p>
                            <a href="compatibility.php" class="btn btn-outline-primary">Check Now</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="card h-100 card-hover">
                        <div class="card-body text-center">
                            <i class="fas fa-heartbeat feature-icon"></i>
                            <h3 class="h4">Disease Diagnosis</h3>
                            <p>Identify and treat common fish diseases with our symptom-based diagnosis system.</p>
                            <a href="diagnosis.php" class="btn btn-outline-primary">Diagnose</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Recent Fish Section (for logged-in users) -->
    <?php if($loggedIn): ?>
    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-5">Recently Added Fish</h2>
            <div class="row">
                <?php
                // Fetch 3 recently added fish
                $stmt = $pdo->query("SELECT * FROM Fish ORDER BY fish_id DESC LIMIT 3");
                while($fish = $stmt->fetch()):
                ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h3 class="h5"><?php echo htmlspecialchars($fish['common_name']); ?></h3>
                            <p><strong>Scientific Name:</strong> <?php echo htmlspecialchars($fish['scientific_name']); ?></p>
                            <p><strong>Water Type:</strong> <?php echo htmlspecialchars($fish['water_type']); ?></p>
                            <a href="view_fish.php?id=<?php echo $fish['fish_id']; ?>" class="btn btn-sm btn-outline-secondary">View Details</a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Simple greeting for logged-in users
        <?php if($loggedIn): ?>
            console.log("Welcome back, <?php echo $_SESSION['username']; ?>!");
        <?php endif; ?>
    </script>
</body>
</html>