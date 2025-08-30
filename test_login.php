<?php
// Test script to debug login functionality
echo "<h1>Login Debug Test</h1>";

// Simulate the login request
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['CONTENT_TYPE'] = 'application/json';

// Simulate JSON input
$jsonInput = '{"email":"makipalomares@gmail.com","password":"password123"}';
file_put_contents('php://input', $jsonInput);

echo "<h2>Input Data:</h2>";
echo "<pre>" . $jsonInput . "</pre>";

// Try to parse the data like the AuthController does
$rawInput = file_get_contents('php://input');
echo "<h2>Raw Input from php://input:</h2>";
echo "<pre>" . $rawInput . "</pre>";

// Try JSON decode
$data = json_decode($rawInput, true);
echo "<h2>JSON Decoded:</h2>";
echo "<pre>" . json_encode($data) . "</pre>";

// Check if data is valid
if (isset($data['email']) && isset($data['password'])) {
    echo "<h2 style='color: green;'>✅ Data parsing successful!</h2>";
    echo "<p>Email: " . $data['email'] . "</p>";
    echo "<p>Password: " . $data['password'] . "</p>";
} else {
    echo "<h2 style='color: red;'>❌ Data parsing failed!</h2>";
    echo "<p>Data keys: " . implode(', ', array_keys($data ?: [])) . "</p>";
}
?>