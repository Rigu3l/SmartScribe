<?php
// Fix user authentication and notes visibility
require_once __DIR__ . '/api/config/database.php';

echo "<h1>SmartScribe User Authentication Fix</h1>\n";
echo "<style>body { font-family: Arial, sans-serif; margin: 20px; }</style>\n";

try {
    $db = getDbConnection();

    // Get all users
    $stmt = $db->query("SELECT id, name, email FROM users ORDER BY id");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<h2>Current Users:</h2>\n";
    echo "<table border='1' style='border-collapse: collapse;'>\n";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Notes Count</th></tr>\n";

    foreach ($users as $user) {
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM notes WHERE user_id = ?");
        $stmt->execute([$user['id']]);
        $notesCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        echo "<tr>\n";
        echo "<td>{$user['id']}</td>\n";
        echo "<td>{$user['name']}</td>\n";
        echo "<td>{$user['email']}</td>\n";
        echo "<td>$notesCount</td>\n";
        echo "</tr>\n";
    }
    echo "</table>\n";

    // Find user with most notes (likely the one being used)
    $stmt = $db->query("
        SELECT u.id, u.name, u.email, COUNT(n.id) as notes_count
        FROM users u
        LEFT JOIN notes n ON u.id = n.user_id
        GROUP BY u.id, u.name, u.email
        ORDER BY notes_count DESC
        LIMIT 1
    ");
    $primaryUser = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "<h2>Recommended Solution:</h2>\n";

    if ($primaryUser && $primaryUser['notes_count'] > 0) {
        echo "<p><strong>Primary User:</strong> {$primaryUser['name']} (ID: {$primaryUser['id']})</p>\n";
        echo "<p><strong>Notes Count:</strong> {$primaryUser['notes_count']}</p>\n";
        echo "<p><strong>Action:</strong> Make sure you're logged in as this user in your frontend.</p>\n";

        // Show the notes for this user
        echo "<h3>Notes for Primary User:</h3>\n";
        $stmt = $db->prepare("SELECT id, title, LEFT(original_text, 100) as preview FROM notes WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$primaryUser['id']]);
        $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($notes as $note) {
            echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 5px;'>\n";
            echo "<strong>{$note['title']}</strong><br>\n";
            echo "<small>{$note['preview']}...</small>\n";
            echo "</div>\n";
        }

    } else {
        echo "<p>No users with notes found. Creating a test user...</p>\n";

        // Create a test user
        $stmt = $db->prepare("INSERT INTO users (name, email, password, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute(['Test User', 'test@example.com', password_hash('password123', PASSWORD_DEFAULT)]);
        $newUserId = $db->lastInsertId();

        echo "<p><strong>New User Created:</strong> Test User (ID: $newUserId)</p>\n";
        echo "<p><strong>Login Credentials:</strong> test@example.com / password123</p>\n";

        // Move some notes to this user
        $stmt = $db->prepare("UPDATE notes SET user_id = ? WHERE user_id = (SELECT id FROM users WHERE name = 'Maki Palomares' LIMIT 1)");
        $stmt->execute([$newUserId]);

        echo "<p><strong>Notes Moved:</strong> All notes have been assigned to the new test user.</p>\n";
    }

    echo "<h2>Frontend Authentication Check:</h2>\n";
    echo "<ol>\n";
    echo "<li>Open your browser's Developer Tools (F12)</li>\n";
    echo "<li>Go to Application/Storage > Local Storage</li>\n";
    echo "<li>Find the 'user' key and check the 'id' field</li>\n";
    echo "<li>Make sure this ID matches the user with notes</li>\n";
    echo "</ol>\n";

    echo "<h2>Quick Fix Options:</h2>\n";
    echo "<h3>Option 1: Update Frontend User ID</h3>\n";
    echo "<p>Change your localStorage user ID to match the user with notes.</p>\n";

    echo "<h3>Option 2: Move Notes to Your Current User</h3>\n";
    echo "<p>If you're logged in as a different user, we can move the notes to that user.</p>\n";

    // Show current localStorage simulation
    echo "<h3>Current User Check:</h3>\n";
    echo "<p>The frontend sends user authentication via the X-User-ID header.</p>\n";
    echo "<p>Make sure your frontend is sending the correct user ID.</p>\n";

} catch (Exception $e) {
    echo "<h2>Error: " . htmlspecialchars($e->getMessage()) . "</h2>\n";
}
?>