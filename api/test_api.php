<?php
// Test API endpoints and server status

echo "🔍 SmartScribe API Test Suite\n";
echo "===============================\n\n";

// Test 1: Check if server is running
echo "1. Testing Server Status:\n";
echo "------------------------\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:3000/api/auth/profile');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'X-User-ID: 22'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);

if ($response === false) {
    echo "❌ Server Error: " . $curlError . "\n";
    echo "💡 Make sure the server is running on localhost:3000\n";
} else {
    echo "✅ Server is running!\n";
    echo "HTTP Status: $httpCode\n";
    echo "Response:\n" . $response . "\n";
}

curl_close($ch);

// Test 2: Test database connection
echo "\n2. Testing Database Connection:\n";
echo "--------------------------------\n";

require_once __DIR__ . '/config/database.php';
try {
    $db = getDbConnection();
    echo "✅ Database connection successful!\n";

    // Test notes table
    $stmt = $db->query("SELECT COUNT(*) as count FROM notes");
    $result = $stmt->fetch();
    echo "📝 Notes in database: " . $result['count'] . "\n";

    // Test users table
    $stmt = $db->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo "👥 Users in database: " . $result['count'] . "\n";

} catch (Exception $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
}

// Test 3: Test notes endpoint
echo "\n3. Testing Notes Endpoint:\n";
echo "-------------------------\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:3000/api/notes');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'X-User-ID: 22'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($response === false) {
    echo "❌ Notes API Error: " . curl_error($ch) . "\n";
} else {
    echo "HTTP Status: $httpCode\n";
    $data = json_decode($response, true);
    if ($data && isset($data['data'])) {
        echo "📋 Notes found: " . count($data['data']) . "\n";
        if (count($data['data']) > 0) {
            echo "Latest note: " . substr($data['data'][0]['title'], 0, 50) . "...\n";
        }
    } else {
        echo "Response: " . $response . "\n";
    }
}

curl_close($ch);

// Test 4: Test note creation
echo "\n4. Testing Note Creation:\n";
echo "-------------------------\n";

$formData = [
    'title' => 'Test Note from API',
    'text' => 'This is a test note created via API call'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:3000/api/notes');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($formData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded',
    'X-User-ID: 22'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($response === false) {
    echo "❌ Note Creation Error: " . curl_error($ch) . "\n";
} else {
    echo "HTTP Status: $httpCode\n";
    echo "Response: " . $response . "\n";
}

curl_close($ch);

echo "\n✅ API test completed!\n";
echo "\n💡 If you see errors above, check:\n";
echo "   - Server is running on localhost:3000\n";
echo "   - Database connection is working\n";
echo "   - PHP error logs for detailed errors\n";
?>