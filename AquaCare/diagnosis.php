<?php
session_start();

// Fish disease data
$fishDiseases = [
    'Goldfish' => [
        'Ich (White Spot Disease)' => [
            'symptoms' => ['White spots on body and fins', 'Rubbing against surfaces', 'Rapid gill movement'],
            'treatment' => [
                'Increase water temperature to around 28°C',
                'Add aquarium salt',
                'Use medications like Malachite Green or Copper-based treatments'
            ]
        ],
        'Fin Rot' => [
            'symptoms' => ['Torn, ragged, or decaying fins'],
            'treatment' => [
                'Improve water quality and filtration',
                'Use antibacterial meds like Melafix',
                'Salt baths can also help'
            ]
        ],
        'Swim Bladder Disorder' => [
            'symptoms' => ['Fish floating upside down or sinking'],
            'treatment' => [
                'Stop feeding for 24 hours',
                'Feed boiled, peeled peas (helps digestion)',
                'Avoid overfeeding and poor-quality food'
            ]
        ],
        'Fungal Infections' => [
            'symptoms' => ['White cotton-like patches on skin or fins'],
            'treatment' => [
                'Apply anti-fungal medications (e.g., Methylene Blue)',
                'Use aquarium salt',
                'Quarantine the infected fish if needed'
            ]
        ],
        'Anchor Worms / Parasites' => [
            'symptoms' => ['Worm-like parasites attached to the body'],
            'treatment' => [
                'Remove manually (carefully with tweezers)',
                'Use anti-parasitic meds like Dimilin',
                'Clean the tank thoroughly and change water regularly'
            ]
        ]
    ],
    'Guppy' => [
        'Ich (White Spot Disease)' => [
            'symptoms' => ['White dots on body/fins', 'Rubbing against surfaces'],
            'treatment' => [
                'Increase water temperature to 28°C (gradually)',
                'Add aquarium salt',
                'Use Ich treatment (e.g., Malachite Green, Copper-based meds)'
            ]
        ],
        'Fin Rot' => [
            'symptoms' => ['Torn, decaying fins', 'Red edges'],
            'treatment' => [
                'Improve water quality',
                'Use antibacterial meds (e.g., Melafix)',
                'Regular tank cleaning'
            ]
        ],
        'Velvet Disease (Oodinium)' => [
            'symptoms' => ['Gold or rust-colored dust on skin', 'Clamped fins'],
            'treatment' => [
                'Dim the lights (parasite needs light)',
                'Use copper-based medication',
                'Add aquarium salt'
            ]
        ],
        'Columnaris (Cotton Mouth Disease)' => [
            'symptoms' => ['White patches around mouth', 'Body ulcers'],
            'treatment' => [
                'Use antibiotics (e.g., Furan-2, Kanamycin)',
                'Keep water very clean',
                'Isolate the sick fish'
            ]
        ],
        'Swim Bladder Disorder' => [
            'symptoms' => ['Trouble swimming', 'Floating or sinking'],
            'treatment' => [
                'Fast the fish for 1 day',
                'Feed boiled peas (skinned)',
                'Maintain a healthy diet, avoid overfeeding'
            ]
        ],
        'External Parasites (e.g., flukes, lice)' => [
            'symptoms' => ['Scratching', 'Flashing', 'Visible parasites'],
            'treatment' => [
                'Antiparasitic treatments (e.g., PraziPro)',
                'Salt baths',
                'Clean the tank'
            ]
        ]
    ],
    // Other fish diseases would be added here following the same pattern
    // For brevity, I'm showing just Goldfish and Guppy in this example
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['fish_type'])) {
        $_SESSION['diagnosis']['fish_type'] = $_POST['fish_type'];
        $_SESSION['chat_history'][] = [
            'type' => 'user',
            'message' => "My fish is a " . $_POST['fish_type']
        ];
        $_SESSION['chat_history'][] = [
            'type' => 'bot',
            'message' => "What symptoms are you observing? Please select all that apply."
        ];
    } elseif (isset($_POST['symptoms'])) {
        $fishType = $_SESSION['diagnosis']['fish_type'];
        $selectedSymptoms = $_POST['symptoms'];
        
        $_SESSION['chat_history'][] = [
            'type' => 'user',
            'message' => "Symptoms observed: " . implode(", ", $selectedSymptoms)
        ];
        
        // Find matching diseases
        $possibleDiseases = [];
        foreach ($fishDiseases[$fishType] as $disease => $data) {
            $matchingSymptoms = array_intersect($selectedSymptoms, $data['symptoms']);
            if (count($matchingSymptoms) > 0) {
                $possibleDiseases[$disease] = [
                    'match_score' => count($matchingSymptoms) / count($data['symptoms']),
                    'treatment' => $data['treatment']
                ];
            }
        }
        
        // Sort by match score (highest first)
        arsort($possibleDiseases);
        
        $response = "";
        if (!empty($possibleDiseases)) {
            $response .= "<strong>Possible conditions:</strong><br><br>";
            
            foreach ($possibleDiseases as $disease => $info) {
                $response .= "<div class='disease-card mb-3 p-3 border rounded'>";
                $response .= "<h5>$disease</h5>";
                $response .= "<strong>Recommended treatment:</strong><ul>";
                foreach ($info['treatment'] as $treatment) {
                    $response .= "<li>$treatment</li>";
                }
                $response .= "</ul>";
                $response .= "<div class='match-score'>Match: " . round($info['match_score'] * 100) . "%</div>";
                $response .= "</div>";
            }
            
            $response .= "<br><strong>General advice:</strong> ";
            $response .= "Always quarantine sick fish when possible. Maintain excellent water quality and reduce stress. ";
            $response .= "If symptoms persist after treatment, consult a fish health specialist.";
        } else {
            $response = "No specific diseases match all your symptoms. This could be due to:";
            $response .= "<ul><li>Stress from poor water conditions</li>";
            $response .= "<li>Nutritional deficiencies</li>";
            $response .= "<li>Early stages of disease</li></ul>";
            $response .= "Please check your water parameters (ammonia, nitrite, nitrate, pH) and consider a partial water change.";
        }
        
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
    header("Location: diagnosis.php"); // Redirect to base page
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fish Disease Diagnosis</title>
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
        .diagnosis-form {
            background-color: white;
            padding: 15px;
            border-top: 1px solid #eee;
        }
        .disease-card {
            background-color: #f8f9fa;
        }
        .match-score {
            font-size: 0.8em;
            color: #6c757d;
            text-align: right;
        }
        .symptom-checkbox {
            margin-right: 10px;
        }
        .btn-restart {
            background-color: #6c757d;
            color: white;
        }
    </style>
</head>
<body>

   <?php include 'header.php'; ?>



    <div class="container py-5">
        <div class="chat-container">
            <div class="chat-header">
                <h2>🐠 Fish Disease Diagnosis</h2>
                <p>Identify and treat common fish diseases</p>
                <a href="?clear" class="btn btn-sm btn-light">Start Over</a>
            </div>
            
            <div class="chat-body" id="chatBody">
                <?php if (empty($_SESSION['chat_history'])): ?>
                    <div class="message bot-message">
                        Welcome to the Fish Disease Diagnosis tool!<br><br>
                        Please select your fish type to begin diagnosis.
                    </div>
                <?php else: ?>
                    <?php foreach ($_SESSION['chat_history'] as $message): ?>
                        <div class="message <?= $message['type'] === 'user' ? 'user-message' : 'bot-message' ?>">
                            <?= $message['message'] ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div class="diagnosis-form">
                <?php if (!isset($_SESSION['diagnosis']['fish_type'])): ?>
                    <form method="POST" action="">
                        <select name="fish_type" class="form-select mb-3" required>
                            <option value="">-- Select Fish Type --</option>
                            <?php foreach ($fishDiseases as $fish => $diseases): ?>
                                <option value="<?= $fish ?>"><?= $fish ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-primary w-100">Continue</button>
                    </form>
                <?php else: 
                    $fishType = $_SESSION['diagnosis']['fish_type'];
                    $allSymptoms = [];
                    foreach ($fishDiseases[$fishType] as $disease => $data) {
                        $allSymptoms = array_merge($allSymptoms, $data['symptoms']);
                    }
                    $uniqueSymptoms = array_unique($allSymptoms);
                    ?>
                    <form method="POST" action="">
                        <div class="mb-3">
                            <strong>Select observed symptoms:</strong>
                        </div>
                        <div class="symptoms-list" style="max-height: 150px; overflow-y: auto;">
                            <?php foreach ($uniqueSymptoms as $symptom): ?>
                                <div class="form-check">
                                    <input class="form-check-input symptom-checkbox" type="checkbox" name="symptoms[]" id="symp<?= md5($symptom) ?>" value="<?= htmlspecialchars($symptom) ?>">
                                    <label class="form-check-label" for="symp<?= md5($symptom) ?>">
                                        <?= htmlspecialchars($symptom) ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="submit" class="btn btn-primary mt-3 w-100">Diagnose</button>
                    </form>
                <?php endif; ?>
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