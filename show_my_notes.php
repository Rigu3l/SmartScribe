<?php
// Show notes for the currently logged-in user
require_once __DIR__ . '/api/config/database.php';

echo "<!DOCTYPE html>\n";
echo "<html lang='en'>\n";
echo "<head>\n";
echo "<meta charset='UTF-8'>\n";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>\n";
echo "<title>My Notes - SmartScribe</title>\n";
echo "<style>\n";
echo "body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }\n";
echo ".container { max-width: 1200px; margin: 0 auto; }\n";
echo ".header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 15px; margin-bottom: 30px; text-align: center; }\n";
echo ".notes-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 20px; }\n";
echo ".note-card { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); transition: transform 0.2s; }\n";
echo ".note-card:hover { transform: translateY(-2px); }\n";
echo ".note-title { font-size: 18px; font-weight: bold; color: #333; margin-bottom: 10px; }\n";
echo ".note-content { color: #666; line-height: 1.6; margin-bottom: 15px; }\n";
echo ".note-meta { font-size: 12px; color: #999; border-top: 1px solid #eee; padding-top: 10px; }\n";
echo ".empty-state { text-align: center; padding: 60px 20px; color: #666; }\n";
echo ".empty-state h2 { color: #333; margin-bottom: 20px; }\n";
echo ".user-info { background: #e8f4f8; border: 1px solid #bee3f8; border-radius: 8px; padding: 15px; margin-bottom: 20px; }\n";
echo ".debug-info { background: #f7fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; margin-bottom: 20px; font-family: monospace; font-size: 12px; }\n";
echo "</style>\n";
echo "</head>\n";
echo "<body>\n";

try {
    $db = getDbConnection();

    echo "<div class='container'>\n";
    echo "<div class='header'>\n";
    echo "<h1>📝 My Notes - SmartScribe</h1>\n";
    echo "<p>View all your saved notes</p>\n";
    echo "</div>\n";

    // First, let's check if there are any users at all
    $stmt = $db->query("SELECT COUNT(*) as count FROM users");
    $userCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    if ($userCount == 0) {
        echo "<div class='empty-state'>\n";
        echo "<h2>No Users Found</h2>\n";
        echo "<p>The database appears to be empty. Let's create some test data.</p>\n";

        // Create a test user
        $stmt = $db->prepare("INSERT INTO users (name, email, password, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute(['Demo User', 'demo@smartscribe.com', password_hash('demo123', PASSWORD_DEFAULT)]);
        $userId = $db->lastInsertId();

        // Create sample notes
        $sampleNotes = [
            [
                'title' => 'Welcome to SmartScribe!',
                'content' => 'Congratulations on setting up SmartScribe! This powerful note-taking application helps you organize your study materials, create summaries, and enhance your learning experience with AI-powered features.'
            ],
            [
                'title' => 'Getting Started Guide',
                'content' => 'To get started: 1) Use the camera to scan your notes, 2) Create text notes directly, 3) Organize with tags, 4) Search through your collection, 5) Generate summaries and quizzes.'
            ],
            [
                'title' => 'Study Tips & Best Practices',
                'content' => 'Effective study techniques: Use active recall, spaced repetition, create mind maps, teach concepts to others, take regular breaks, and maintain a consistent study schedule.'
            ],
            [
                'title' => 'Mathematics Fundamentals',
                'content' => 'Key mathematical concepts: Algebra (equations, functions), Calculus (derivatives, integrals), Geometry (shapes, theorems), Statistics (probability, data analysis).'
            ],
            [
                'title' => 'Science Overview',
                'content' => 'Core sciences: Physics (motion, energy), Chemistry (matter, reactions), Biology (life processes), Earth Science (geology, weather). Each field builds understanding of our world.'
            ]
        ];

        foreach ($sampleNotes as $note) {
            $stmt = $db->prepare("INSERT INTO notes (user_id, title, original_text, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$userId, $note['title'], $note['content']]);
        }

        echo "<p><strong>✅ Created demo user and 5 sample notes!</strong></p>\n";
        echo "<p><strong>User:</strong> Demo User (ID: $userId)</p>\n";
        echo "<p><strong>Email:</strong> demo@smartscribe.com</p>\n";
        echo "<p><strong>Password:</strong> demo123</p>\n";
        echo "<p><strong>Next:</strong> Log in with these credentials to see your notes.</p>\n";
        echo "</div>\n";

        // Now show the notes we just created
        $currentUserId = $userId;
        $currentUserName = 'Demo User';
    } else {
        // Try to determine the current user (this would normally come from session/frontend)
        // For now, let's show notes for the user with the most recent activity
        $stmt = $db->query("
            SELECT u.id, u.name, u.email, COUNT(n.id) as notes_count, MAX(n.created_at) as last_activity
            FROM users u
            LEFT JOIN notes n ON u.id = n.user_id
            GROUP BY u.id, u.name, u.email
            ORDER BY notes_count DESC, last_activity DESC
            LIMIT 1
        ");
        $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($currentUser && $currentUser['notes_count'] > 0) {
            $currentUserId = $currentUser['id'];
            $currentUserName = $currentUser['name'];

            echo "<div class='user-info'>\n";
            echo "<h3>👤 Current User: {$currentUser['name']}</h3>\n";
            echo "<p><strong>User ID:</strong> {$currentUser['id']}</p>\n";
            echo "<p><strong>Email:</strong> {$currentUser['email']}</p>\n";
            echo "<p><strong>Total Notes:</strong> {$currentUser['notes_count']}</p>\n";
            echo "<p><strong>Last Activity:</strong> " . ($currentUser['last_activity'] ?? 'Never') . "</p>\n";
            echo "</div>\n";
        } else {
            // No user has notes, show all users and let them choose
            echo "<div class='debug-info'>\n";
            echo "<h3>All Users in System:</h3>\n";

            $stmt = $db->query("SELECT u.id, u.name, u.email, COUNT(n.id) as notes_count FROM users u LEFT JOIN notes n ON u.id = n.user_id GROUP BY u.id, u.name, u.email ORDER BY u.id");
            $allUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($allUsers as $user) {
                echo "User ID {$user['id']}: {$user['name']} ({$user['email']}) - {$user['notes_count']} notes<br>\n";
            }

            echo "<br><strong>Note:</strong> No user currently has notes. The frontend should be sending the correct user ID via X-User-ID header.\n";
            echo "</div>\n";

            echo "<div class='empty-state'>\n";
            echo "<h2>No Notes Found</h2>\n";
            echo "<p>No user has any notes yet. This could be because:</p>\n";
            echo "<ul style='text-align: left; display: inline-block;'>\n";
            echo "<li>The frontend isn't sending the correct user ID</li>\n";
            echo "<li>The user is logged in with a different account</li>\n";
            echo "<li>The notes were created for a different user</li>\n";
            echo "</ul>\n";
            echo "<p><strong>Solution:</strong> Check your browser's localStorage for the 'user' key and verify the ID matches a user with notes.</p>\n";
            echo "</div>\n";

            echo "</div>\n";
            echo "</body>\n";
            echo "</html>\n";
            exit;
        }
    }

    // Show notes for the current user
    $stmt = $db->prepare("SELECT * FROM notes WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$currentUserId]);
    $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<div class='debug-info'>\n";
    echo "<strong>Debug Info:</strong><br>\n";
    echo "Query: SELECT * FROM notes WHERE user_id = $currentUserId ORDER BY created_at DESC<br>\n";
    echo "Found: " . count($notes) . " notes<br>\n";
    echo "User ID being queried: $currentUserId<br>\n";
    echo "Current User: $currentUserName<br>\n";
    echo "</div>\n";

    if (count($notes) > 0) {
        echo "<div class='notes-grid'>\n";

        foreach ($notes as $note) {
            echo "<div class='note-card'>\n";
            echo "<div class='note-title'>{$note['title']}</div>\n";
            echo "<div class='note-content'>" . nl2br(htmlspecialchars(substr($note['original_text'], 0, 200))) . (strlen($note['original_text']) > 200 ? '...' : '') . "</div>\n";
            echo "<div class='note-meta'>\n";
            echo "📅 Created: " . date('M j, Y g:i A', strtotime($note['created_at'])) . "<br>\n";
            echo "🆔 Note ID: {$note['id']}<br>\n";
            if ($note['image_path']) {
                echo "🖼️ Has Image: Yes<br>\n";
            }
            echo "</div>\n";
            echo "</div>\n";
        }

        echo "</div>\n";
    } else {
        echo "<div class='empty-state'>\n";
        echo "<h2>No Notes Yet</h2>\n";
        echo "<p>You haven't created any notes yet. Start by:</p>\n";
        echo "<ul style='text-align: left; display: inline-block;'>\n";
        echo "<li>Using the camera to scan your study materials</li>\n";
        echo "<li>Creating text notes directly in the application</li>\n";
        echo "<li>Uploading images of your handwritten notes</li>\n";
        echo "</ul>\n";
        echo "</div>\n";
    }

    echo "</div>\n";

} catch (Exception $e) {
    echo "<div style='background: #fee; border: 1px solid #fcc; padding: 20px; margin: 20px; border-radius: 8px;'>\n";
    echo "<h2 style='color: #c33;'>❌ Database Error</h2>\n";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>\n";
    echo "<p>Please check your database configuration in <code>api/config/database.php</code></p>\n";
    echo "</div>\n";
}

echo "</body>\n";
echo "</html>\n";
?>