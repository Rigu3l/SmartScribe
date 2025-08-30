<?php
require_once 'api/config/database.php';

echo "=== PROFILE PICTURE DIAGNOSTIC ===\n\n";

try {
    $db = getDbConnection();

    // Check user data
    echo "1. USER DATA CHECK:\n";
    $stmt = $db->prepare('SELECT id, name, profile_picture FROM users WHERE id = ?');
    $stmt->execute([25]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo "   User ID: {$user['id']}\n";
        echo "   Name: {$user['name']}\n";
        echo "   Profile Picture Path: " . ($user['profile_picture'] ?: 'NULL') . "\n";

        if ($user['profile_picture']) {
            $fullPath = __DIR__ . '/public/' . $user['profile_picture'];
            echo "   Full File Path: {$fullPath}\n";
            echo "   File Exists: " . (file_exists($fullPath) ? 'YES' : 'NO') . "\n";

            if (file_exists($fullPath)) {
                echo "   File Size: " . filesize($fullPath) . " bytes\n";
                echo "   File Permissions: " . substr(sprintf('%o', fileperms($fullPath)), -4) . "\n";
            }

            // Test URL accessibility
            echo "\n2. URL ACCESSIBILITY TEST:\n";
            $testUrls = [
                "http://localhost/SmartScribe-main/public/{$user['profile_picture']}",
                "http://localhost/{$user['profile_picture']}",
                "/SmartScribe-main/public/{$user['profile_picture']}",
                "/{$user['profile_picture']}"
            ];

            foreach ($testUrls as $url) {
                echo "   Testing: {$url}\n";
                $headers = @get_headers($url);
                if ($headers) {
                    $status = substr($headers[0], 9, 3);
                    echo "   Status: {$status}\n";
                } else {
                    echo "   Status: Unable to access\n";
                }
            }
        }
    } else {
        echo "   ERROR: User not found!\n";
    }

    // Check directory structure
    echo "\n3. DIRECTORY STRUCTURE:\n";
    $uploadDir = __DIR__ . '/public/uploads/profile_pictures/';
    echo "   Upload Directory: {$uploadDir}\n";
    echo "   Directory Exists: " . (is_dir($uploadDir) ? 'YES' : 'NO') . "\n";

    if (is_dir($uploadDir)) {
        echo "   Directory Writable: " . (is_writable($uploadDir) ? 'YES' : 'NO') . "\n";
        echo "   Directory Permissions: " . substr(sprintf('%o', fileperms($uploadDir)), -4) . "\n";

        $files = scandir($uploadDir);
        echo "   Files in directory:\n";
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $filePath = $uploadDir . $file;
                echo "     - {$file} (" . filesize($filePath) . " bytes)\n";
            }
        }
    }

    // Check .htaccess configuration
    echo "\n4. HTACCESS CONFIGURATION:\n";
    $htaccessPath = __DIR__ . '/public/.htaccess';
    if (file_exists($htaccessPath)) {
        $htaccess = file_get_contents($htaccessPath);
        if (strpos($htaccess, 'uploads/') !== false) {
            echo "   ✓ .htaccess contains uploads exception\n";
        } else {
            echo "   ✗ .htaccess missing uploads exception\n";
        }
    } else {
        echo "   ✗ .htaccess file not found\n";
    }

    // Recommendations
    echo "\n5. RECOMMENDATIONS:\n";
    if (!$user['profile_picture']) {
        echo "   - Upload a profile picture first\n";
    } elseif (!file_exists(__DIR__ . '/public/' . $user['profile_picture'])) {
        echo "   - File upload is not saving files correctly\n";
        echo "   - Check PHP error logs for upload issues\n";
        echo "   - Verify file permissions on upload directory\n";
    } else {
        echo "   - File exists but may not be accessible via web\n";
        echo "   - Check browser network tab for failed requests\n";
        echo "   - Verify URL construction in frontend\n";
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>