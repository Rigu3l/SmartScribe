<?php
// Test login using curl
echo "<h1>Testing Login with cURL</h1>";

// Test data
$testData = [
    'email' => 'test@example.com',
    'password' => 'password123'
];

$jsonData = json_encode($testData);
echo "<h2>Test Data:</h2>";
echo "<pre>" . $jsonData . "</pre>";

// Initialize cURL
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, 'http://localhost/SmartScribe-main/public/index.php?resource=auth&action=login');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($jsonData)
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Execute the request
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

curl_close($ch);

echo "<h2>Request Details:</h2>";
echo "<p>URL: http://localhost/SmartScribe-main/public/index.php?resource=auth&action=login</p>";
echo "<p>Method: POST</p>";
echo "<p>Content-Type: application/json</p>";
echo "<p>Data Length: " . strlen($jsonData) . " bytes</p>";

echo "<h2>Response:</h2>";
echo "<p>HTTP Code: $httpCode</p>";
if ($error) {
    echo "<p style='color: red;'>cURL Error: $error</p>";
} else {
    echo "<pre>" . $response . "</pre>";
}

echo "<h2>Response Analysis:</h2>";
if ($httpCode == 200) {
    $responseData = json_decode($response, true);
    if ($responseData && isset($responseData['user'])) {
        echo "<p style='color: green;'>✅ Login successful!</p>";
        echo "<p>User: " . $responseData['user']['name'] . " (" . $responseData['user']['email'] . ")</p>";
        echo "<p>Token: " . substr($responseData['token'] ?? '', 0, 20) . "...</p>";
    } else {
        echo "<p style='color: orange;'>⚠️  Unexpected response format</p>";
    }
} else {
    echo "<p style='color: red;'>❌ HTTP Error: $httpCode</p>";
}
?>