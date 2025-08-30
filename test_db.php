<?php
try {
    require_once 'api/config/database.php';
    $db = getDbConnection();
    echo "Database connection successful\n";

    $stmt = $db->query('SELECT COUNT(*) as count FROM users');
    $result = $stmt->fetch();
    echo "Total users: " . $result['count'] . "\n";

    $stmt = $db->prepare('SELECT id, email FROM users LIMIT 5');
    $stmt->execute();
    $users = $stmt->fetchAll();
    echo "Users:\n";
    foreach ($users as $user) {
        echo "  ID: " . $user['id'] . ", Email: " . $user['email'] . "\n";
    }

    // Test specific user
    $stmt = $db->prepare('SELECT id, email, password FROM users WHERE email = ?');
    $stmt->execute(['makipalomares@gmail.com']);
    $user = $stmt->fetch();
    if ($user) {
        echo "\nUser found: ID=" . $user['id'] . ", Email=" . $user['email'] . "\n";
        echo "Password verification test: " . (password_verify('password123', $user['password']) ? 'SUCCESS' : 'FAILED') . "\n";
    } else {
        echo "\nUser not found\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>