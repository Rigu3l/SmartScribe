<?php
// Create test notes for demonstration
require_once __DIR__ . '/api/config/database.php';

try {
    $db = getDbConnection();
    echo "Connected to database successfully!\n";

    // Check if we have any users
    $stmt = $db->query("SELECT id, name FROM users LIMIT 1");
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "No users found. Creating a test user...\n";

        // Create a test user
        $stmt = $db->prepare("INSERT INTO users (name, email, password, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute(['Test User', 'test@example.com', password_hash('password123', PASSWORD_DEFAULT)]);
        $userId = $db->lastInsertId();

        echo "Created test user with ID: $userId\n";
    } else {
        $userId = $user['id'];
        echo "Using existing user: {$user['name']} (ID: $userId)\n";
    }

    // Create some test notes
    $testNotes = [
        [
            'title' => 'Mathematics Notes - Algebra',
            'content' => 'Today we learned about quadratic equations. The formula is ax² + bx + c = 0. We solved several problems using the quadratic formula: x = (-b ± √(b²-4ac)) / 2a.'
        ],
        [
            'title' => 'History Notes - World War II',
            'content' => 'Key events of WWII: 1939 - Germany invades Poland, 1941 - Pearl Harbor attack, 1944 - D-Day Normandy landings, 1945 - Atomic bombs dropped on Hiroshima and Nagasaki.'
        ],
        [
            'title' => 'Science Notes - Photosynthesis',
            'content' => 'Photosynthesis is the process by which plants convert sunlight, carbon dioxide, and water into glucose and oxygen. The equation is: 6CO₂ + 6H₂O + light energy → C₆H₁₂O₆ + 6O₂.'
        ],
        [
            'title' => 'Literature Notes - Romeo and Juliet',
            'content' => 'Shakespeare\'s tragedy about two young lovers from feuding families. Key themes include love, fate, and the destructive nature of hatred between families.'
        ],
        [
            'title' => 'Computer Science Notes - Algorithms',
            'content' => 'An algorithm is a step-by-step procedure for solving a problem. Common types include sorting algorithms (bubble sort, quicksort) and searching algorithms (binary search, linear search).'
        ]
    ];

    echo "\nCreating test notes...\n";

    foreach ($testNotes as $index => $note) {
        $stmt = $db->prepare("INSERT INTO notes (user_id, title, original_text, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$userId, $note['title'], $note['content']]);

        $noteId = $db->lastInsertId();
        echo "Created note " . ($index + 1) . ": {$note['title']} (ID: $noteId)\n";
    }

    // Verify notes were created
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM notes WHERE user_id = ?");
    $stmt->execute([$userId]);
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    echo "\n✅ Successfully created $count test notes for user ID $userId!\n";
    echo "You can now view these notes in your SmartScribe application.\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>