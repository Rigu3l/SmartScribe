<?php
// Direct view of all notes with user identification
require_once __DIR__ . '/api/config/database.php';

echo "<!DOCTYPE html>\n";
echo "<html>\n";
echo "<head>\n";
echo "<title>Direct Notes View - SmartScribe</title>\n";
echo "<style>\n";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }\n";
echo ".note { border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 8px; background: white; }\n";
echo ".user-section { background: #e8f4f8; padding: 15px; margin: 20px 0; border-radius: 8px; }\n";
echo ".error { background: #fee; border: 1px solid #fcc; padding: 15px; margin: 20px 0; border-radius: 8px; }\n";
echo ".success { background: #efe; border: 1px solid #cfc; padding: 15px; margin: 20px 0; border-radius: 8px; }\n";
echo "h1, h2, h3 { color: #333; }\n";
echo ".note-title { font-size: 18px; font-weight: bold; color: #2c5aa0; }\n";
echo ".note-content { margin: 10px 0; line-height: 1.5; }\n";
echo ".note-meta { font-size: 12px; color: #666; border-top: 1px solid #eee; padding-top: 10px; }\n";
echo "</style>\n";
echo "</head>\n";
echo "<body>\n";

echo "<h1>🔍 Direct Notes View - SmartScribe</h1>\n";
echo "<p>This page shows all notes in your database and helps identify authentication issues.</p>\n";

try {
    $db = getDbConnection();
    echo "<div class='success'>✅ Database connection successful!</div>\n";

    // Show all users
    echo "<h2>👥 All Users in System</h2>\n";
    $stmt = $db->query("SELECT id, name, email, created_at FROM users ORDER BY id");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($users) > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%; background: white;'>\n";
        echo "<tr style='background: #f0f0f0;'><th>ID</th><th>Name</th><th>Email</th><th>Created</th><th>Notes Count</th></tr>\n";

        foreach ($users as $user) {
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM notes WHERE user_id = ?");
            $stmt->execute([$user['id']]);
            $notesCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            $highlight = ($notesCount > 0) ? "background: #ffffe0;" : "";
            echo "<tr style='$highlight'>\n";
            echo "<td style='padding: 8px;'>{$user['id']}</td>\n";
            echo "<td style='padding: 8px;'>{$user['name']}</td>\n";
            echo "<td style='padding: 8px;'>{$user['email']}</td>\n";
            echo "<td style='padding: 8px;'>{$user['created_at']}</td>\n";
            echo "<td style='padding: 8px; font-weight: bold;'>$notesCount</td>\n";
            echo "</tr>\n";
        }
        echo "</table>\n";
    } else {
        echo "<div class='error'>❌ No users found in database!</div>\n";
    }

    // Show all notes grouped by user
    echo "<h2>📝 All Notes in Database</h2>\n";

    $stmt = $db->query("
        SELECT n.*, u.name as user_name, u.email as user_email
        FROM notes n
        LEFT JOIN users u ON n.user_id = u.id
        ORDER BY n.created_at DESC
    ");
    $allNotes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($allNotes) > 0) {
        echo "<p><strong>Total notes found:</strong> " . count($allNotes) . "</p>\n";

        $currentUserId = null;
        foreach ($allNotes as $note) {
            if ($currentUserId !== $note['user_id']) {
                if ($currentUserId !== null) {
                    echo "</div>\n"; // Close previous user section
                }

                $currentUserId = $note['user_id'];
                echo "<div class='user-section'>\n";
                echo "<h3>👤 Notes for User: {$note['user_name']} (ID: {$note['user_id']})</h3>\n";
                echo "<p><strong>Email:</strong> {$note['user_email']}</p>\n";
            }

            echo "<div class='note'>\n";
            echo "<div class='note-title'>{$note['title']}</div>\n";
            echo "<div class='note-content'>" . nl2br(htmlspecialchars(substr($note['original_text'], 0, 300))) . (strlen($note['original_text']) > 300 ? '...' : '') . "</div>\n";
            echo "<div class='note-meta'>\n";
            echo "<strong>Note ID:</strong> {$note['id']} | \n";
            echo "<strong>Created:</strong> {$note['created_at']} | \n";
            echo "<strong>Has Image:</strong> " . ($note['image_path'] ? 'Yes' : 'No') . "\n";
            if ($note['image_path']) {
                echo " | <strong>Image:</strong> {$note['image_path']}\n";
            }
            echo "</div>\n";
            echo "</div>\n";
        }

        if ($currentUserId !== null) {
            echo "</div>\n"; // Close last user section
        }

    } else {
        echo "<div class='error'>\n";
        echo "<h3>❌ No Notes Found!</h3>\n";
        echo "<p>Your database doesn't contain any notes. This could be because:</p>\n";
        echo "<ul>\n";
        echo "<li>No users have created notes yet</li>\n";
        echo "<li>The notes were deleted or not saved properly</li>\n";
        echo "<li>There's an issue with the note creation process</li>\n";
        echo "</ul>\n";
        echo "<p><strong>Solution:</strong> Create some test notes or check your note creation functionality.</p>\n";
        echo "</div>\n";

        // Offer to create test notes
        echo "<div style='background: #f0f8ff; border: 1px solid #b0d0ff; padding: 20px; margin: 20px 0; border-radius: 8px;'>\n";
        echo "<h3>🛠️ Create Test Notes</h3>\n";
        echo "<p>Would you like me to create some test notes for you?</p>\n";
        echo "<form method='post' style='margin-top: 10px;'>\n";
        echo "<button type='submit' name='create_test_notes' style='background: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;'>Create Test Notes</button>\n";
        echo "</form>\n";
        echo "</div>\n";
    }

    // Handle test note creation
    if (isset($_POST['create_test_notes'])) {
        // Find or create a test user
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute(['test@example.com']);
        $userId = $stmt->fetchColumn();

        if (!$userId) {
            $stmt = $db->prepare("INSERT INTO users (name, email, password, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute(['Test User', 'test@example.com', password_hash('test123', PASSWORD_DEFAULT)]);
            $userId = $db->lastInsertId();
            echo "<div class='success'>✅ Created test user: Test User (ID: $userId)</div>\n";
        }

        // Create test notes
        $testNotes = [
            ['title' => 'Welcome Note', 'content' => 'This is your first test note! Welcome to SmartScribe.'],
            ['title' => 'Study Tips', 'content' => 'Effective study techniques include active recall, spaced repetition, and regular review.'],
            ['title' => 'Quick Reminder', 'content' => 'Remember to take breaks while studying to maintain focus and retention.']
        ];

        foreach ($testNotes as $note) {
            $stmt = $db->prepare("INSERT INTO notes (user_id, title, original_text, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$userId, $note['title'], $note['content']]);
        }

        echo "<div class='success'>✅ Created " . count($testNotes) . " test notes for user ID $userId</div>\n";
        echo "<p><strong>Test User Login:</strong> test@example.com / test123</p>\n";
        echo "<p><strong>Next:</strong> <a href='javascript:window.location.reload();'>Refresh this page</a> to see the new notes.</p>\n";
    }

    echo "<h2>🔧 Frontend Authentication Check</h2>\n";
    echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 8px;'>\n";
    echo "<h3>Check Your Browser:</h3>\n";
    echo "<ol>\n";
    echo "<li>Open Developer Tools (F12)</li>\n";
    echo "<li>Go to <strong>Application → Local Storage</strong></li>\n";
    echo "<li>Find the <code>user</code> key</li>\n";
    echo "<li>Check the <code>id</code> field - it should match a user with notes</li>\n";
    echo "<li>If it doesn't match, log in with the correct user</li>\n";
    echo "</ol>\n";
    echo "</div>\n";

} catch (Exception $e) {
    echo "<div class='error'>\n";
    echo "<h2>❌ Database Error</h2>\n";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>\n";
    echo "<p>Please check your database configuration.</p>\n";
    echo "</div>\n";
}

echo "</body>\n";
echo "</html>\n";
?>