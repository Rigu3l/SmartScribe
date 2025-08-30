<?php
// Test login endpoint directly
echo "<h1>Testing Login Endpoint</h1>";

// Test credentials - using test user with known password
$testData = [
    'email' => 'test@example.com',
    'password' => 'password123'
];

$jsonInput = json_encode($testData);
echo "<h2>Test Data:</h2>";
echo "<pre>" . json_encode($testData, JSON_PRETTY_PRINT) . "</pre>";

// Create a temporary file to simulate php://input
$tempFile = tempnam(sys_get_temp_dir(), 'login_test');
file_put_contents($tempFile, $jsonInput);

// Simulate the request environment
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['CONTENT_TYPE'] = 'application/json';
$_SERVER['REQUEST_URI'] = '/index.php?resource=auth&action=login';
$_SERVER['QUERY_STRING'] = 'resource=auth&action=login';
$_GET['resource'] = 'auth';
$_GET['action'] = 'login';

// Redirect php://input to read from our temp file
$originalInput = fopen('php://input', 'r');
$testInput = fopen($tempFile, 'r');

// Use stream context to override php://input
$context = stream_context_create([
    'php' => [
        'input' => $testInput
    ]
]);

echo "<h2>Testing with simulated input...</h2>";

// Include the main entry point
require_once 'public/index.php';

// Clean up
fclose($testInput);
unlink($tempFile);
?>