<?php
// Check what user is currently logged in
require_once __DIR__ . '/api/config/database.php';

echo "<h1>Current User Authentication Check</h1>\n";
echo "<style>body { font-family: Arial, sans-serif; margin: 20px; }</style>\n";

try {
    $db = getDbConnection();

    // Check all users and their authentication status
    echo "<h2>All Users in System:</h2>\n";
    $stmt = $db->query("SELECT id, name, email, created_at FROM users ORDER BY id");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Created</th><th>Notes Count</th></tr>\n";

    foreach ($users as $user) {
        // Count notes for this user
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM notes WHERE user_id = ?");
        $stmt->execute([$user['id']]);
        $notesCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        $highlight = ($user['id'] == 22) ? "background-color: #ffff99;" : "";
        echo "<tr style='$highlight'>\n";
        echo "<td>{$user['id']}</td>\n";
        echo "<td>{$user['name']}</td>\n";
        echo "<td>{$user['email']}</td>\n";
        echo "<td>{$user['created_at']}</td>\n";
        echo "<td>$notesCount</td>\n";
        echo "</tr>\n";
    }
    echo "</table>\n";

    echo "<h2>Authentication Issue Analysis:</h2>\n";
    echo "<ul>\n";
    echo "<li><strong>User ID 22</strong> has the test notes we created</li>\n";
    echo "<li>The frontend sends user ID via <code>X-User-ID</code> header</li>\n";
    echo "<li>If you're logged in as a different user, you won't see the notes</li>\n";
    echo "</ul>\n";

    echo "<h2>Frontend Check:</h2>\n";
    echo "<p>Open your browser's Developer Tools (F12) and check:</p>\n";
    echo "<ol>\n";
    echo "<li>Go to Application/Storage > Local Storage</li>\n";
    echo "<li>Look for a 'user' key</li>\n";
    echo "<li>Check the 'id' field in the user object</li>\n";
    echo "<li>Make sure it matches User ID 22</li>\n";
    echo "</ol>\n";

    echo "<h2>Quick Fix:</h2>\n";
    echo "<p>If you're logged in as a different user:</p>\n";
    echo "<ol>\n";
    echo "<li>Log out of your current session</li>\n";
    echo "<li>Log in as the user with ID 22 (Maki Palomares)</li>\n";
    echo "<li>Or update the notes to belong to your current user</li>\n";
    echo "</ol>\n";

    // Show recent notes for user 22
    echo "<h2>Notes for User ID 22:</h2>\n";
    $stmt = $db->prepare("SELECT id, title, LEFT(original_text, 50) as preview, created_at FROM notes WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([22]);
    $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($notes) > 0) {
        echo "<div>Found " . count($notes) . " notes:</div>\n";
        foreach ($notes as $note) {
            echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 5px;'>\n";
            echo "<strong>{$note['title']}</strong><br>\n";
            echo "<small>ID: {$note['id']} | Created: {$note['created_at']}</small><br>\n";
            echo "<small>{$note['preview']}...</small>\n";
            echo "</div>\n";
        }
    } else {
        echo "<p>No notes found for user ID 22.</p>\n";
    }

} catch (Exception $e) {
    echo "<h2>Error: " . htmlspecialchars($e->getMessage()) . "</h2>\n";
}
?>