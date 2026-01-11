<?php
session_start();
require_once 'db_config.php';

// Initialize chat history if not set
if (!isset($_SESSION['chat_history'])) {
    $_SESSION['chat_history'] = [];
}

// Get all fish species from database for the dropdown
function getAllFishSpecies() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("SELECT fish_id, common_name, scientific_name, water_type FROM Fish ORDER BY common_name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching fish species: " . $e->getMessage());
        return [];
    }
}

// Replace the hardcoded array with database functions
function getCompatibility($fishName) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT 
            f1.common_name AS main_fish,
            f1.type,
            GROUP_CONCAT(DISTINCT IF(c.compatibility_status = 'compatible', f2.common_name, NULL) SEPARATOR '||') AS compatible,
            GROUP_CONCAT(DISTINCT IF(c.compatibility_status = 'incompatible', f2.common_name, NULL) SEPARATOR '||') AS incompatible
        FROM Fish f1
        LEFT JOIN compatibility c ON f1.fish_id = c.fish1_id OR f1.fish_id = c.fish2_id
        LEFT JOIN Fish f2 ON 
            (c.fish1_id = f2.fish_id AND c.fish2_id = f1.fish_id) OR
            (c.fish2_id = f2.fish_id AND c.fish1_id = f1.fish_id)
        WHERE f1.common_name = ? AND f2.fish_id IS NOT NULL
        GROUP BY f1.fish_id
    ");
    $stmt->execute([$fishName]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
    
}




function addCompatibility($fish1_id, $fish2_id, $status, $notes = '') {
    global $pdo;
    $stmt = $pdo->prepare("
        INSERT INTO compatibility (fish1_id, fish2_id, compatibility_status, notes)
        VALUES (?, ?, ?, ?)
    ");
    return $stmt->execute([$fish1_id, $fish2_id, $status, $notes]);
}




// Fish compatibility data
$fishCompatibility = [
    'Goldfish' => [
        'compatible' => ['White Cloud Minnow', 'Dojo Loach', 'Rosy Barb', 'Bristlenose Pleco'],
        'incompatible' => ['Betta', 'Guppy', 'Oscar', 'Tetra', 'Molly'],
        'type' => 'Freshwater'
    ],
    'Betta' => [
        'compatible' => ['Corydoras', 'Kuhli Loach', 'Ember Tetra (with caution)', 'Snails'],
        'incompatible' => ['Guppy', 'Angelfish', 'Tiger Barb', 'Other Male Bettas'],
        'type' => 'Freshwater'
    ],
    'Oscar' => [
        'compatible' => ['Other Oscars', 'Plecos', 'Firemouth', 'Jack Dempsey'],
        'incompatible' => ['Guppy', 'Goldfish', 'Neon Tetra', 'Mollies'],
        'type' => 'Freshwater'
    ],
    'Guppy' => [
        'compatible' => ['Mollies', 'Platies', 'Corydoras', 'Tetras'],
        'incompatible' => ['Betta (males)', 'Oscar', 'Tiger Barb'],
        'type' => 'Freshwater'
    ],
    'Blue Tang' => [
        'compatible' => ['Clownfish', 'Gobies', 'Wrasses', 'Damselfish'],
        'incompatible' => ['Lionfish (may eat smaller fish)', 'aggressive triggers'],
        'type' => 'Saltwater'
    ],
    'Lionfish' => [
        'compatible' => ['Larger marine fish (like Tangs, Puffers)'],
        'incompatible' => ['Small fish (Goby, Clownfish)', 'Shrimp (will eat)'],
        'type' => 'Saltwater'
    ],
    'Archerfish' => [
        'compatible' => ['Monos', 'Scats', 'Mollies (in brackish water)'],
        'incompatible' => ['Guppy', 'Neon Tetra', 'freshwater-only fish'],
        'type' => 'Brackish'
    ],
    'Clownfish' => [
        'compatible' => ['Gobies', 'Damselfish', 'Royal Gramma', 'Firefish', 'Blennies', 'Cardinalfish', 'Cleaner Shrimp'],
        'incompatible' => ['Lionfish (predatory)', 'Groupers', 'Large aggressive Triggerfish', 'Eels (may hunt clownfish)'],
        'type' => 'Saltwater'
    ],
    'Mudskipper' => [
        'compatible' => ['Archerfish', 'Monodactylus (Mono Fish)', 'Scat Fish', 'Brackish Pufferfish', 'Glassfish', 'Mollies (adaptable to brackish)'],
        'incompatible' => ['Goldfish', 'Guppy', 'Tetra (freshwater-only)', 'Clownfish', 'Blue Tang (saltwater-only)', 'Aggressive predatory fish'],
        'type' => 'Brackish'
    ]
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fish'])) {
    $selectedFish = $_POST['fish'];
    $_SESSION['chat_history'][] = [
        'type' => 'user',
        'message' => "Which fish are compatible with $selectedFish?"
    ];
    
    if (isset($fishCompatibility[$selectedFish])) {
        $compatData = $fishCompatibility[$selectedFish];
        $response = "<strong>Compatibility for $selectedFish ($compatData[type]):</strong><br><br>";
        
        $response .= "✅ <strong>Compatible with:</strong><ul>";
        foreach ($compatData['compatible'] as $fish) {
            $response .= "<li>$fish</li>";
        }
        $response .= "</ul>";
        
        $response .= "❌ <strong>Not compatible with:</strong><ul>";
        foreach ($compatData['incompatible'] as $fish) {
            $response .= "<li>$fish</li>";
        }
        $response .= "</ul>";
        
        $_SESSION['chat_history'][] = [
            'type' => 'bot',
            'message' => $response
        ];
    }
}

// Clear chat history if requested
if (isset($_GET['clear'])) {
    session_unset();
    session_destroy();
    header("Location: compatibility.php"); // Redirect to base page
    exit();
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fish Compatibility Checker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0f8ff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .chat-container {
            max-width: 800px;
            margin: 0 auto;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .chat-header {
            background-color: #17a2b8;
            color: white;
            padding: 15px;
            text-align: center;
        }
        .chat-body {
            background-color: white;
            height: 500px;
            overflow-y: auto;
            padding: 20px;
        }
        .message {
            margin-bottom: 15px;
            max-width: 80%;
        }
        .user-message {
            margin-left: auto;
            background-color: #e3f2fd;
            border-radius: 15px 15px 0 15px;
            padding: 10px 15px;
        }
        .bot-message {
            margin-right: auto;
            background-color: #f8f9fa;
            border-radius: 15px 15px 15px 0;
            padding: 10px 15px;
        }
        .fish-selector {
            background-color: white;
            padding: 15px;
            border-top: 1px solid #eee;
        }
        .compatible-list {
            color: #28a745;
        }
        .incompatible-list {
            color: #dc3545;
        }
        .fish-type-badge {
            font-size: 0.8em;
            margin-left: 5px;
        }
    </style>
</head>
<body>

    <?php include 'header.php'; ?>


    <div class="container py-5">
        <div class="chat-container">
            <div class="chat-header">
                <h2>🐠 Fish Compatibility Checker</h2>
                <p>Select a fish to check its compatible tank mates</p>
                <a href="?clear" class="btn btn-sm btn-light">Clear Chat</a>
            </div>
            
            <div class="chat-body" id="chatBody">
                <?php if (empty($_SESSION['chat_history'])): ?>
                    <div class="message bot-message">
                        Hi there! I'm here to help you find compatible fish for your aquarium. 
                        Please select a fish from the list below to get started.
                    </div>
                <?php else: ?>
                    <?php foreach ($_SESSION['chat_history'] as $message): ?>
                        <div class="message <?= $message['type'] === 'user' ? 'user-message' : 'bot-message' ?>">
                            <?= $message['message'] ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div class="fish-selector">
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-8">
                            <select name="fish" class="form-select" required>
                                <option value="">-- Select a Fish --</option>
                                <?php foreach ($fishCompatibility as $fish => $data): ?>
                                    <option value="<?= $fish ?>">
                                        <?= $fish ?> 
                                        <span class="badge bg-<?= 
                                            $data['type'] === 'Freshwater' ? 'info' : 
                                            ($data['type'] === 'Saltwater' ? 'primary' : 'warning') 
                                        ?> fish-type-badge">
                                            <?= $data['type'] ?>
                                        </span>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-info w-100">Check Compatibility</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Scroll to bottom of chat
        document.addEventListener('DOMContentLoaded', function() {
            const chatBody = document.getElementById('chatBody');
            chatBody.scrollTop = chatBody.scrollHeight;
        });
    </script>

    <?php include 'footer.php'; ?>


</body>
</html>