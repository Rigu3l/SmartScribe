<?php
// Test the notes API endpoint directly
require_once __DIR__ . '/api/config/database.php';

echo "<h1>Notes API Test</h1>\n";
echo "<style>body { font-family: Arial, sans-serif; margin: 20px; }</style>\n";

try {
    $db = getDbConnection();

    // Simulate the API call with user ID 22
    $userId = 22;

    echo "<h2>Testing API for User ID: $userId</h2>\n";

    // This simulates what the NoteController::index() method does
    $query = "SELECT n.* FROM notes n WHERE user_id = :user_id ORDER BY created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();

    $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<h3>API Response:</h3>\n";
    echo "<pre>" . json_encode([
        "success" => true,
        "data" => $notes,
        "debug" => [
            "user_id" => $userId,
            "notes_found" => count($notes),
            "total_notes" => count($notes)
        ]
    ], JSON_PRETTY_PRINT) . "</pre>\n";

    echo "<h3>Formatted Notes:</h3>\n";
    if (count($notes) > 0) {
        foreach ($notes as $note) {
            echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>\n";
            echo "<h4>" . htmlspecialchars($note['title']) . "</h4>\n";
            echo "<p><strong>ID:</strong> {$note['id']}</p>\n";
            echo "<p><strong>Created:</strong> {$note['created_at']}</p>\n";
            echo "<p><strong>Content:</strong> " . htmlspecialchars(substr($note['original_text'], 0, 100)) . "...</p>\n";
            echo "</div>\n";
        }
    } else {
        echo "<p>No notes found for this user.</p>\n";
    }

    echo "<h3>Frontend Check:</h3>\n";
    echo "<p>Make sure your frontend is sending the correct user ID in the X-User-ID header.</p>\n";
    echo "<p>Current test shows " . count($notes) . " notes should be visible for user ID $userId.</p>\n";

} catch (Exception $e) {
    echo "<h2>Error: " . htmlspecialchars($e->getMessage()) . "</h2>\n";
}
?>