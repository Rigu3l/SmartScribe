<?php
// reset_test_password.php - Reset test user password
require_once 'api/config/database.php';

try {
    $db = getDbConnection();

    // Hash the new password
    $newPassword = 'test123';
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    // Update test user password
    $stmt = $db->prepare("UPDATE users SET password = ? WHERE email = ?");
    $result = $stmt->execute([$hashedPassword, 'test@example.com']);

    if ($result) {
        echo "✅ Test user password reset successfully!\n";
        echo "Email: test@example.com\n";
        echo "Password: test123\n";
        echo "You can now log in with these credentials.\n";
    } else {
        echo "❌ Failed to reset password.\n";
    }

    // Verify the password works
    $stmt = $db->prepare("SELECT password FROM users WHERE email = ?");
    $stmt->execute(['test@example.com']);
    $user = $stmt->fetch();

    if ($user) {
        $isValid = password_verify($newPassword, $user['password']);
        echo "\n🔐 Password verification test: " . ($isValid ? "✅ VALID" : "❌ INVALID") . "\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>