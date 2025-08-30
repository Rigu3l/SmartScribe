<?php
// Simple test for login functionality
echo "<h1>Simple Login Test</h1>";

require_once 'api/config/database.php';
require_once 'api/controllers/AuthController.php';

$db = getDbConnection();
$auth = new AuthController();

// Test credentials
$testEmail = 'test@example.com';
$testPassword = 'password123';

echo "<h2>Testing with:</h2>";
echo "<p>Email: $testEmail</p>";
echo "<p>Password: $testPassword</p>";

// Check if user exists
$stmt = $db->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
$stmt->execute([$testEmail]);
$user = $stmt->fetch();

if (!$user) {
    echo "<h2 style='color: red;'>❌ User not found!</h2>";
    exit;
}

echo "<h2 style='color: green;'>✅ User found:</h2>";
echo "<p>ID: " . $user['id'] . "</p>";
echo "<p>Name: " . $user['name'] . "</p>";
echo "<p>Email: " . $user['email'] . "</p>";

// Test password verification
$passwordValid = password_verify($testPassword, $user['password']);
echo "<h2>Password verification: " . ($passwordValid ? '✅ SUCCESS' : '❌ FAILED') . "</h2>";

// Test token generation (simulating what login does)
if ($passwordValid) {
    echo "<h2>Testing token generation...</h2>";
    try {
        $token = bin2hex(random_bytes(32));
        echo "<h2 style='color: green;'>✅ Token generated successfully</h2>";
        echo "<p>Token: " . substr($token, 0, 20) . "...</p>";

        // Test token storage manually
        $stmt = $db->prepare("INSERT INTO user_tokens (user_id, token, created_at, expires_at) VALUES (?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 24 HOUR))");
        $result = $stmt->execute([$user['id'], $token]);
        echo "<h2 style='color: green;'>✅ Token stored in database successfully</h2>";
    } catch (Exception $e) {
        echo "<h2 style='color: red;'>❌ Token operation failed: " . $e->getMessage() . "</h2>";
    }
}

echo "<h2>Test completed!</h2>";
?>