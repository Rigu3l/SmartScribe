<?php
// Quick fix for notes visibility issue
require_once __DIR__ . '/api/config/database.php';

echo "<h1>Quick Notes Visibility Fix</h1>\n";
echo "<style>body { font-family: Arial, sans-serif; margin: 20px; }</style>\n";

try {
    $db = getDbConnection();

    // Find the user with the most recent notes (likely the active user)
    $stmt = $db->query("
        SELECT u.id, u.name, u.email, COUNT(n.id) as notes_count, MAX(n.created_at) as last_note_date
        FROM users u
        LEFT JOIN notes n ON u.id = n.user_id
        GROUP BY u.id, u.name, u.email
        ORDER BY notes_count DESC, last_note_date DESC
        LIMIT 1
    ");
    $activeUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($activeUser && $activeUser['notes_count'] > 0) {
        echo "<h2>✅ Found Active User with Notes</h2>\n";
        echo "<p><strong>User:</strong> {$activeUser['name']} (ID: {$activeUser['id']})</p>\n";
        echo "<p><strong>Email:</strong> {$activeUser['email']}</p>\n";
        echo "<p><strong>Notes Count:</strong> {$activeUser['notes_count']}</p>\n";
        echo "<p><strong>Last Note:</strong> {$activeUser['last_note_date']}</p>\n";

        // Show the notes
        echo "<h3>Your Notes:</h3>\n";
        $stmt = $db->prepare("SELECT id, title, LEFT(original_text, 100) as preview, created_at FROM notes WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$activeUser['id']]);
        $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "<div style='max-height: 400px; overflow-y: auto;'>\n";
        foreach ($notes as $note) {
            echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 5px; border-radius: 5px;'>\n";
            echo "<h4 style='margin: 0 0 5px 0;'>{$note['title']}</h4>\n";
            echo "<p style='margin: 0; color: #666; font-size: 14px;'>{$note['preview']}...</p>\n";
            echo "<small style='color: #999;'>Created: {$note['created_at']}</small>\n";
            echo "</div>\n";
        }
        echo "</div>\n";

        echo "<h2>🎯 Frontend Fix</h2>\n";
        echo "<p>Make sure your frontend is sending <strong>User ID: {$activeUser['id']}</strong> in the X-User-ID header.</p>\n";

        echo "<h3>Browser Console Check:</h3>\n";
        echo "<ol>\n";
        echo "<li>Open Developer Tools (F12)</li>\n";
        echo "<li>Go to Console tab</li>\n";
        echo "<li>Look for messages like: 'API: Added user ID to request: [ID]'</li>\n";
        echo "<li>Make sure the ID matches: <strong>{$activeUser['id']}</strong></li>\n";
        echo "</ol>\n";

        echo "<h3>LocalStorage Check:</h3>\n";
        echo "<ol>\n";
        echo "<li>Open Developer Tools (F12)</li>\n";
        echo "<li>Go to Application > Local Storage</li>\n";
        echo "<li>Find the 'user' key</li>\n";
        echo "<li>Check that the 'id' field is: <strong>{$activeUser['id']}</strong></li>\n";
        echo "</ol>\n";

    } else {
        echo "<h2>❌ No Users with Notes Found</h2>\n";
        echo "<p>Creating test data...</p>\n";

        // Create a test user and notes
        $stmt = $db->prepare("INSERT INTO users (name, email, password, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute(['Demo User', 'demo@example.com', password_hash('demo123', PASSWORD_DEFAULT)]);
        $userId = $db->lastInsertId();

        // Create sample notes
        $sampleNotes = [
            ['title' => 'Welcome to SmartScribe', 'content' => 'This is your first note! SmartScribe helps you organize and manage your study materials.'],
            ['title' => 'Getting Started', 'content' => 'You can create notes by scanning documents or typing directly. Use the camera button to scan your notes.'],
            ['title' => 'Features Overview', 'content' => 'SmartScribe includes note-taking, organization, search, and AI-powered features to enhance your learning experience.']
        ];

        foreach ($sampleNotes as $note) {
            $stmt = $db->prepare("INSERT INTO notes (user_id, title, original_text, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$userId, $note['title'], $note['content']]);
        }

        echo "<h2>✅ Test Data Created</h2>\n";
        echo "<p><strong>New User:</strong> Demo User (ID: $userId)</p>\n";
        echo "<p><strong>Login:</strong> demo@example.com / demo123</p>\n";
        echo "<p><strong>Notes Created:</strong> " . count($sampleNotes) . "</p>\n";
        echo "<p><strong>Next:</strong> Log in with these credentials to see your notes.</p>\n";
    }

    echo "<hr>\n";
    echo "<h2>🔧 Troubleshooting Steps</h2>\n";
    echo "<ol>\n";
    echo "<li><strong>Check User Authentication:</strong> Ensure you're logged in with the correct user</li>\n";
    echo "<li><strong>Verify API Headers:</strong> Check that X-User-ID header is sent correctly</li>\n";
    echo "<li><strong>Clear Cache:</strong> Hard refresh (Ctrl+F5) your browser</li>\n";
    echo "<li><strong>Check Console:</strong> Look for API errors in browser console</li>\n";
    echo "</ol>\n";

} catch (Exception $e) {
    echo "<h2>❌ Error: " . htmlspecialchars($e->getMessage()) . "</h2>\n";
    echo "<p>Please check your database configuration.</p>\n";
}
?>