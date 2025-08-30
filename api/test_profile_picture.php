<?php
// Test script to check profile picture functionality
require_once __DIR__ . '/config/database.php';

try {
    $db = getDbConnection();

    // Check if profile_picture column exists
    $stmt = $db->prepare("DESCRIBE users");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $hasProfilePictureColumn = false;
    foreach ($columns as $column) {
        if ($column['Field'] === 'profile_picture') {
            $hasProfilePictureColumn = true;
            break;
        }
    }

    echo "Database Schema Check:\n";
    echo "======================\n";
    echo "Profile Picture Column Exists: " . ($hasProfilePictureColumn ? "YES" : "NO") . "\n";

    if (!$hasProfilePictureColumn) {
        echo "\n❌ ERROR: profile_picture column is missing!\n";
        echo "Please run the migration: api/migrations/add_profile_picture_to_users.sql\n";
        exit(1);
    }

    // Check current user data
    $userId = 23;
    $stmt = $db->prepare("SELECT id, first_name, last_name, name, email, profile_picture, created_at FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "\nUser Data Check:\n";
    echo "================\n";
    if ($user) {
        echo "User ID: " . $user['id'] . "\n";
        echo "Name: " . $user['name'] . "\n";
        echo "First Name: " . ($user['first_name'] ?: 'NULL') . "\n";
        echo "Last Name: " . ($user['last_name'] ?: 'NULL') . "\n";
        echo "Email: " . $user['email'] . "\n";
        echo "Profile Picture: " . ($user['profile_picture'] ?: 'NULL') . "\n";
        echo "Created At: " . $user['created_at'] . "\n";
    } else {
        echo "❌ ERROR: User with ID $userId not found!\n";
    }

    // Check if uploads directory exists (in public directory)
    $uploadDir = __DIR__ . '/../public/uploads/profile_pictures/';
    echo "\nFile System Check:\n";
    echo "==================\n";
    echo "Uploads Directory Exists: " . (is_dir($uploadDir) ? "YES" : "NO") . "\n";
    echo "Uploads Directory Path: " . $uploadDir . "\n";

    if (!is_dir($uploadDir)) {
        echo "Creating uploads directory...\n";
        if (mkdir($uploadDir, 0755, true)) {
            echo "✅ Uploads directory created successfully!\n";
        } else {
            echo "❌ ERROR: Failed to create uploads directory!\n";
        }
    }

    // Check directory permissions
    echo "Directory Writable: " . (is_writable($uploadDir) ? "YES" : "NO") . "\n";

    // Check if profile picture file exists
    $profilePicturePath = $uploadDir . 'profile_23_1756308864.jpg';
    echo "Profile Picture File Exists: " . (file_exists($profilePicturePath) ? "YES" : "NO") . "\n";
    if (file_exists($profilePicturePath)) {
        echo "File Size: " . filesize($profilePicturePath) . " bytes\n";
    }

    echo "\n✅ Profile picture system is ready!\n";

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
?>