<?php
// Debug script to check notes display functionality
require_once __DIR__ . '/api/config/database.php';

echo "<h1>SmartScribe Notes Debug</h1>\n";
echo "<style>body { font-family: Arial, sans-serif; margin: 20px; } .note { border: 1px solid #ccc; padding: 10px; margin: 10px 0; }</style>\n";

try {
    $db = getDbConnection();
    echo "<h2>✅ Database Connection Successful</h2>\n";

    // Check all users
    echo "<h3>All Users:</h3>\n";
    $stmt = $db->query("SELECT id, name, email FROM users ORDER BY id");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($users) > 0) {
        echo "<ul>\n";
        foreach ($users as $user) {
            echo "<li><strong>ID:</strong> {$user['id']}, <strong>Name:</strong> {$user['name']}, <strong>Email:</strong> {$user['email']}</li>\n";
        }
        echo "</ul>\n";
    } else {
        echo "<p>No users found in database.</p>\n";
    }

    // Check all notes
    echo "<h3>All Notes in Database:</h3>\n";
    $stmt = $db->query("SELECT n.id, n.user_id, n.title, LEFT(n.original_text, 100) as text_preview, n.created_at, u.name as user_name
                       FROM notes n
                       LEFT JOIN users u ON n.user_id = u.id
                       ORDER BY n.created_at DESC");
    $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($notes) > 0) {
        echo "<div>Total notes: " . count($notes) . "</div>\n";
        foreach ($notes as $note) {
            echo "<div class='note'>\n";
            echo "<h4>{$note['title']}</h4>\n";
            echo "<p><strong>Note ID:</strong> {$note['id']}</p>\n";
            echo "<p><strong>User ID:</strong> {$note['user_id']} ({$note['user_name']})</p>\n";
            echo "<p><strong>Created:</strong> {$note['created_at']}</p>\n";
            echo "<p><strong>Content:</strong> {$note['text_preview']}...</p>\n";
            echo "</div>\n";
        }
    } else {
        echo "<p>No notes found in database.</p>\n";
    }

    // Test API simulation
    echo "<h3>API Simulation Test:</h3>\n";

    // Simulate user ID 22 (the one with notes)
    $testUserId = 22;

    echo "<h4>Simulating API call for User ID: $testUserId</h4>\n";

    $stmt = $db->prepare("SELECT n.* FROM notes n WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$testUserId]);
    $userNotes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<p>Found " . count($userNotes) . " notes for this user.</p>\n";

    if (count($userNotes) > 0) {
        echo "<h5>Notes that should be returned by API:</h5>\n";
        foreach ($userNotes as $note) {
            echo "<div style='border: 1px solid #ddd; padding: 8px; margin: 5px;'>\n";
            echo "<strong>{$note['title']}</strong><br>\n";
            echo "<small>ID: {$note['id']}, Created: {$note['created_at']}</small><br>\n";
            echo "<small>Text: " . substr($note['original_text'], 0, 50) . "...</small>\n";
            echo "</div>\n";
        }
    }

    // Check what user ID the frontend is likely sending
    echo "<h3>Frontend Authentication Check:</h3>\n";
    echo "<p>The frontend sends user ID via 'X-User-ID' header.</p>\n";
    echo "<p>Make sure your frontend is logged in with User ID: $testUserId</p>\n";

    echo "<h3>Next Steps:</h3>\n";
    echo "<ol>\n";
    echo "<li>Make sure you're logged in as User ID $testUserId in your frontend</li>\n";
    echo "<li>Check browser console for any API errors</li>\n";
    echo "<li>Verify the 'X-User-ID' header is being sent correctly</li>\n";
    echo "<li>Refresh your Notes page to see if notes appear</li>\n";
    echo "</ol>\n";

} catch (Exception $e) {
    echo "<h2>❌ Error: " . htmlspecialchars($e->getMessage()) . "</h2>\n";
}
?>