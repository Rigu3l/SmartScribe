<?php
// Test frontend API call simulation
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-User-ID");
header("Content-Type: application/json");

// Simulate what the frontend sends
$userId = $_GET['user_id'] ?? '22'; // Default to user 22 who has notes

echo "<h1>Frontend API Call Test</h1>";
echo "<p>Testing API call with User ID: $userId</p>";

// Make the API call
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://localhost/SmartScribe-main/public/index.php?resource=notes");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "X-User-ID: $userId",
    "Content-Type: application/json"
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<h2>API Response (HTTP $httpCode):</h2>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";

// Parse and display the response
$data = json_decode($response, true);
if ($data && isset($data['success']) && $data['success']) {
    echo "<h3>✅ Success! Found " . count($data['data']) . " notes</h3>";
    if (isset($data['debug'])) {
        echo "<h4>Debug Info:</h4>";
        echo "<ul>";
        echo "<li>User ID: " . $data['debug']['user_id'] . "</li>";
        echo "<li>Notes Found: " . $data['debug']['notes_found'] . "</li>";
        echo "<li>Total Notes in DB: " . $data['debug']['total_notes'] . "</li>";
        echo "</ul>";
    }
} else {
    echo "<h3>❌ Error or no data returned</h3>";
    if ($data && isset($data['error'])) {
        echo "<p>Error: " . $data['error'] . "</p>";
    }
}
?>